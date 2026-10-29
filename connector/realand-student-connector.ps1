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

function Send-StudentRecord($settings, $record) {
    $body = "$($record.enroll_id)`t$($record.timestamp)`t`r`n"
    Invoke-RestMethod -Uri $settings.STUDENT_WEBSITE_URL -Method Post `
        -ContentType 'text/plain; charset=utf-8' -Body $body | Out-Null
}

$settingsPath = Join-Path $PSScriptRoot '.env.student'
$settings = Read-Settings $settingsPath
$required = @('F495_IP', 'F495_PORT', 'F495_PASSWORD', 'F495_DEVICE_ID', 'F495_SDK_PATH', 'STUDENT_WEBSITE_URL')
foreach ($name in $required) {
    if (-not $settings[$name]) { throw "Missing .env.student setting: $name" }
}
if ($settings.STUDENT_WEBSITE_URL -notmatch '^https://www\.cambridgeinternationalschoolwarri\.com/student/iclock/cdata\?SN=00002$') {
    throw 'For this first test, STUDENT_WEBSITE_URL must be the approved HTTPS student receiver URL ending in SN=00002.'
}

$apiPath = Join-Path $settings.F495_SDK_PATH 'RealandAPI.dll'
$devicesPath = Join-Path $settings.F495_SDK_PATH 'Riss.Devices.dll'
if (-not (Test-Path -LiteralPath $apiPath) -or -not (Test-Path -LiteralPath $devicesPath)) {
    throw "Realand SDK DLLs not found under $($settings.F495_SDK_PATH)."
}
[Reflection.Assembly]::LoadFrom($apiPath) | Out-Null
[Reflection.Assembly]::LoadFrom($devicesPath) | Out-Null

$statePath = Join-Path $PSScriptRoot 'student-connector-state.json'
$state = if (Test-Path -LiteralPath $statePath) { Get-Content -LiteralPath $statePath -Raw | ConvertFrom-Json } else { [pscustomobject]@{ sent = @(); pending = @() } }
if (-not ($state.PSObject.Properties.Name -contains 'pending')) {
    $state | Add-Member -NotePropertyName pending -NotePropertyValue @()
}
$sent = [Collections.Generic.HashSet[string]]::new([string[]]@($state.sent))
$pendingIds = [Collections.Generic.HashSet[string]]::new([string[]]@(@($state.pending | ForEach-Object { [string]$_.event_id })))
$minimumDate = if ($settings.MIN_EVENT_DATE) { [DateTime]::ParseExact($settings.MIN_EVENT_DATE, 'yyyy-MM-dd', $null).Date } else { $null }
$pollSeconds = if ($settings.POLL_SECONDS) { [int]$settings.POLL_SECONDS } else { 5 }
Write-Host "STUDENT connector started for $($settings.F495_IP):$($settings.F495_PORT); receiver SN=00002. Pending records are saved locally and retried. Press Ctrl+C to stop."

while ($true) {
    $device = New-Object RealandAPI.ZDC2911Finger
    $device.Communication = 1
    $device.IpAddress = $settings.F495_IP
    $device.IpPort = [int]$settings.F495_PORT
    $device.Password = [int]$settings.F495_PASSWORD
    $device.DN = [int]$settings.F495_DEVICE_ID
    $connected = $false
    try {
        $connected = $device.OpenCommunication()
        if (-not $connected) { throw 'Realand SDK could not open the student F-G495 connection.' }
        $dates = [Collections.Generic.List[DateTime]]::new()
        $records = $device.GetNewlyRecords($dates)
        foreach ($record in @($records)) {
            if ($minimumDate -and $record.Clock.Date -lt $minimumDate) { continue }
            $enrollId = [string]$record.DIN
            $timestamp = $record.Clock.ToString('yyyy-MM-dd HH:mm:ss')
            $eventId = "{0}:{1}:{2}:{3}" -f $settings.F495_DEVICE_ID, $enrollId, $timestamp, $record.Action
            if ($sent.Contains($eventId) -or $pendingIds.Contains($eventId)) { continue }
            $state.pending += [pscustomobject]@{
                event_id = $eventId
                enroll_id = $enrollId
                timestamp = $timestamp
                action = [string]$record.Action
            }
            $pendingIds.Add($eventId) | Out-Null
        }
        # Persist every fetched punch before attempting any website upload.
        if (@($state.pending).Count -gt 0) { Save-State $statePath $state }
    } catch {
        Write-Warning "Student machine sync failed: $($_.Exception.Message)"
    } finally {
        if ($connected) { $device.CloseCommunication() }
    }

    # Retry the durable outbox on every pass, including when the clock is offline.
    foreach ($record in @($state.pending)) {
        try {
            Send-StudentRecord $settings $record
            $sent.Add([string]$record.event_id) | Out-Null
            $state.sent = @($sent | Select-Object -Last 5000)
            $state.pending = @($state.pending | Where-Object { $_.event_id -ne $record.event_id })
            $pendingIds.Remove([string]$record.event_id) | Out-Null
            Save-State $statePath $state
            Write-Host "Sent student Enroll ID $($record.enroll_id) at $($record.timestamp)"
        } catch {
            Write-Warning "Student upload pending for Enroll ID $($record.enroll_id) at $($record.timestamp): $($_.Exception.Message)"
        }
    }
    Start-Sleep -Seconds $pollSeconds
}
