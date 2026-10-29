$ErrorActionPreference = 'Stop'
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

function Read-Settings($path) {
    if (-not (Test-Path -LiteralPath $path)) { throw "Missing settings file: $path (copy student.env.example to .env.student and fill it in)." }
    $settings = @{}
    Get-Content -LiteralPath $path | ForEach-Object {
        if ($_ -match '^\s*([^#=]+)\s*=\s*(.*)\s*$') {
            $settings[$matches[1].Trim()] = $matches[2].Trim().Trim('"').Trim("'")
        }
    }
    return $settings
}

function Save-State($path, $state) {
    $temporary = "$path.tmp"
    $state | ConvertTo-Json -Depth 6 -Compress | Set-Content -LiteralPath $temporary -Encoding UTF8
    Move-Item -Force -LiteralPath $temporary -Destination $path
}

function Load-State($path) {
    if (Test-Path -LiteralPath $path) {
        $loaded = Get-Content -LiteralPath $path -Raw | ConvertFrom-Json
        return @{
            sent = @($loaded.sent | ForEach-Object { [string]$_ })
            pending = @($loaded.pending | ForEach-Object { $_ })
        }
    }
    return @{ sent = @(); pending = @() }
}

function Get-ReceiverUrl($baseUrl, $receiverSn) {
    $withoutSn = $baseUrl -replace '([?&])SN=[^&]*', '$1'
    $withoutSn = $withoutSn.TrimEnd('?', '&')
    $separator = if ($withoutSn.Contains('?')) { '&' } else { '?' }
    return ($withoutSn + $separator + 'SN=' + $receiverSn)
}

function Send-StudentRecord($url, $record) {
    $body = "$($record.enroll_id)	$($record.timestamp)
"
    Invoke-RestMethod -Uri $url -Method Post -ContentType 'text/plain; charset=utf-8' -Body $body | Out-Null
}

$settingsPath = Join-Path $PSScriptRoot '.env.student'
$settings = Read-Settings $settingsPath
foreach ($name in @('F495_SDK_PATH', 'STUDENT_WEBSITE_URL')) {
    if (-not $settings[$name]) { throw "Missing .env.student setting: $name" }
}

$devicesFile = Join-Path $PSScriptRoot 'student-devices.csv'
if (Test-Path -LiteralPath $devicesFile) {
    $devices = @(Import-Csv -LiteralPath $devicesFile)
    if ($devices.Count -eq 0) { throw "No device rows found in $devicesFile." }
} else {
    foreach ($name in @('F495_IP', 'F495_PORT', 'F495_PASSWORD', 'F495_DEVICE_ID')) {
        if (-not $settings[$name]) { throw "Missing .env.student setting: $name" }
    }
    $devices = @([pscustomobject]@{
        Name = 'Years 4-6'
        SerialNumber = ''
        IP = $settings.F495_IP
        Port = $settings.F495_PORT
        Password = $settings.F495_PASSWORD
        SdkDeviceId = $settings.F495_DEVICE_ID
        ReceiverSN = '00002'
        MinEventDate = $settings.MIN_EVENT_DATE
    })
}

$seenReceiverIds = [Collections.Generic.HashSet[string]]::new([StringComparer]::Ordinal)
$seenSerials = [Collections.Generic.HashSet[string]]::new([StringComparer]::OrdinalIgnoreCase)
$seenSdkIds = [Collections.Generic.HashSet[string]]::new([StringComparer]::Ordinal)
foreach ($reader in $devices) {
    foreach ($field in @('Name', 'IP', 'Port', 'Password', 'SdkDeviceId', 'ReceiverSN')) {
        $value = $reader.PSObject.Properties[$field].Value
        if (-not [string]$value) { throw "Device row is missing $field. Check student-devices.csv." }
    }
    if (-not [string]$reader.SerialNumber) { Write-Warning "No physical serial number set for $($reader.Name); verify the IP carefully." }
    if (-not $seenReceiverIds.Add([string]$reader.ReceiverSN)) { throw "ReceiverSN values must be unique; duplicate: $($reader.ReceiverSN)" }
    if (-not $seenSdkIds.Add([string]$reader.SdkDeviceId)) { throw "SdkDeviceId values must be unique; duplicate: $($reader.SdkDeviceId)" }
    if ($reader.SerialNumber -and -not $seenSerials.Add([string]$reader.SerialNumber)) { throw "Physical serial numbers must be unique; duplicate: $($reader.SerialNumber)" }
    if ([string]$reader.ReceiverSN -notmatch '^\d{1,20}$') { throw "ReceiverSN must contain digits only: $($reader.ReceiverSN)" }
    if ([string]$reader.SdkDeviceId -notmatch '^\d+$') { throw "SdkDeviceId must be numeric: $($reader.SdkDeviceId)" }
}

$apiPath = Join-Path $settings.F495_SDK_PATH 'RealandAPI.dll'
$devicesPath = Join-Path $settings.F495_SDK_PATH 'Riss.Devices.dll'
if (-not (Test-Path -LiteralPath $apiPath) -or -not (Test-Path -LiteralPath $devicesPath)) {
    throw "Realand SDK DLLs not found under $($settings.F495_SDK_PATH)."
}
[Reflection.Assembly]::LoadFrom($apiPath) | Out-Null
[Reflection.Assembly]::LoadFrom($devicesPath) | Out-Null

$pollSeconds = if ($settings.POLL_SECONDS) { [Math]::Max(1, [int]$settings.POLL_SECONDS) } else { 5 }
$readerStates = @{}
foreach ($reader in $devices) {
    $receiverSn = [string]$reader.ReceiverSN
    $stateName = if ($receiverSn -eq '00002') { 'student-connector-state.json' } else { "student-connector-state-$receiverSn.json" }
    $statePath = Join-Path $PSScriptRoot $stateName
    $state = Load-State $statePath
    $readerStates[$receiverSn] = @{
        path = $statePath
        state = $state
        sent = [Collections.Generic.HashSet[string]]::new([string[]]$state.sent)
        pendingIds = [Collections.Generic.HashSet[string]]::new([string[]]@($state.pending | ForEach-Object { [string]$_.event_id }))
        url = Get-ReceiverUrl $settings.STUDENT_WEBSITE_URL $receiverSn
    }
}
Write-Host "STUDENT connector started for $($devices.Count) readers. Punches are queued per reader and retried when internet returns. Press Ctrl+C to stop."
foreach ($reader in $devices) {
    Write-Host "  $($reader.Name): $($reader.IP):$($reader.Port), serial $($reader.SerialNumber), receiver SN=$($reader.ReceiverSN)"
}

while ($true) {
    foreach ($reader in $devices) {
        $receiverSn = [string]$reader.ReceiverSN
        $readerState = $readerStates[$receiverSn]
        $state = $readerState.state
        try {
            $device = New-Object RealandAPI.ZDC2911Finger
            $device.Communication = 1
            $device.IpAddress = [string]$reader.IP
            $device.IpPort = [int]$reader.Port
            $device.Password = [int]$reader.Password
            $device.DN = [int]$reader.SdkDeviceId
            $connected = $false
            try {
                $connected = $device.OpenCommunication()
                if (-not $connected) { throw "SDK could not open $($reader.Name)." }
                $dates = [Collections.Generic.List[DateTime]]::new()
                $records = $device.GetNewlyRecords($dates)
                $minimumText = if ($reader.MinEventDate) { [string]$reader.MinEventDate } else { [string]$settings.MIN_EVENT_DATE }
                $minimumDate = if ($minimumText) { [DateTime]::ParseExact($minimumText, 'yyyy-MM-dd', $null).Date } else { $null }
                foreach ($record in @($records)) {
                    if ($minimumDate -and $record.Clock.Date -lt $minimumDate) { continue }
                    $enrollId = [string]$record.DIN
                    $timestamp = $record.Clock.ToString('yyyy-MM-dd HH:mm:ss')
                    $action = [string]$record.Action
                    $dedupeDeviceId = if ($receiverSn -eq '00002' -and $settings.F495_DEVICE_ID) { $settings.F495_DEVICE_ID } else { $reader.SdkDeviceId }
                    $eventId = "{0}:{1}:{2}:{3}" -f $dedupeDeviceId, $enrollId, $timestamp, $action
                    if ($readerState.sent.Contains($eventId) -or $readerState.pendingIds.Contains($eventId)) { continue }
                    $state.pending += [pscustomobject]@{
                        event_id = $eventId
                        enroll_id = $enrollId
                        timestamp = $timestamp
                        action = $action
                    }
                    $readerState.pendingIds.Add($eventId) | Out-Null
                }
                if (@($state.pending).Count -gt 0) { Save-State $readerState.path $state }
            } finally {
                if ($connected) { $device.CloseCommunication() }
            }
        } catch {
            Write-Warning "$($reader.Name) clock read failed: $($_.Exception.Message)"
        }

        foreach ($record in @($state.pending)) {
            try {
                Send-StudentRecord $readerState.url $record
                $readerState.sent.Add([string]$record.event_id) | Out-Null
                $state.sent = @($readerState.sent | Select-Object -Last 5000)
                $state.pending = @($state.pending | Where-Object { $_.event_id -ne $record.event_id })
                $readerState.pendingIds.Remove([string]$record.event_id) | Out-Null
                Save-State $readerState.path $state
                Write-Host "Uploaded $($reader.Name) Enroll ID $($record.enroll_id) at $($record.timestamp)"
            } catch {
                Write-Warning "$($reader.Name) upload queued for Enroll ID $($record.enroll_id): $($_.Exception.Message)"
                break
            }
        }
        $readerState.state = $state
    }
    Start-Sleep -Seconds $pollSeconds
}
