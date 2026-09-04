$ErrorActionPreference = 'Stop'

function Read-Settings {
    $settings = @{}
    Get-Content (Join-Path $PSScriptRoot '.env') | ForEach-Object {
        if ($_ -match '^\s*([^#=]+)\s*=\s*(.*)\s*$') {
            $settings[$matches[1].Trim()] = $matches[2].Trim().Trim('"').Trim("'")
        }
    }
    return $settings
}

function Save-State($path, $state) {
    $temporary = "$path.tmp"
    $state | ConvertTo-Json -Compress | Set-Content -Path $temporary -Encoding UTF8
    Move-Item -Force $temporary $path
}

function Send-Record($settings, $record) {
    $timestamp = $record.Clock.ToString('yyyy-MM-dd HH:mm:ss')
    $direction = if ($record.Action -eq 1) { 'out' } else { 'in' }
    $machineUserId = [string]$record.DIN
    $eventId = "{0}:{1}:{2}:{3}" -f $settings.F495_DEVICE_ID, $machineUserId, $timestamp, $record.Action
    $payload = @{
        device_id = $settings.F495_DEVICE_ID
        enroll_id = $machineUserId
        timestamp = $timestamp
        direction = $direction
        event_id = $eventId
    } | ConvertTo-Json

    Invoke-RestMethod -Uri $settings.WEBSITE_URL -Method Post -ContentType 'application/json' `
        -Headers @{ 'X-Staff-Attendance-Key' = $settings.STAFF_ATTENDANCE_KEY } -Body $payload | Out-Null
    return $eventId
}

$settings = Read-Settings
foreach ($required in @('F495_IP', 'F495_PORT', 'F495_PASSWORD', 'F495_DEVICE_ID', 'WEBSITE_URL', 'STAFF_ATTENDANCE_KEY')) {
    if (-not $settings[$required]) { throw "Missing .env setting: $required" }
}

$apiPath = Join-Path $settings.F495_SDK_PATH 'RealandAPI.dll'
[Reflection.Assembly]::LoadFrom($apiPath) | Out-Null
[Reflection.Assembly]::LoadFrom((Join-Path $settings.F495_SDK_PATH 'Riss.Devices.dll')) | Out-Null

$stateFile = if ($settings.STATE_FILE) { $settings.STATE_FILE } else { 'connector-state.json' }
$statePath = Join-Path $PSScriptRoot $stateFile
$state = if (Test-Path $statePath) { Get-Content $statePath -Raw | ConvertFrom-Json } else { [pscustomobject]@{ sent = @() } }
$sent = [Collections.Generic.HashSet[string]]::new([string[]]@($state.sent))
Write-Host "F-G495 connector started for $($settings.F495_IP):$($settings.F495_PORT)"

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
        if (-not $connected) { throw 'Realand SDK could not open the F-G495 connection.' }
        $dates = [Collections.Generic.List[DateTime]]::new()
        $records = $device.GetNewlyRecords($dates)
        foreach ($record in @($records)) {
            $timestamp = $record.Clock.ToString('yyyy-MM-dd HH:mm:ss')
            $machineUserId = [string]$record.DIN
            $eventId = "{0}:{1}:{2}:{3}" -f $settings.F495_DEVICE_ID, $machineUserId, $timestamp, $record.Action
            if ($sent.Contains($eventId)) { continue }
            try {
                $eventId = Send-Record $settings $record
                $sent.Add($eventId) | Out-Null
                $state.sent = @($sent | Select-Object -Last 5000)
                Save-State $statePath $state
                Write-Host "Sent Enroll ID $machineUserId at $timestamp"
            } catch {
                Write-Warning "Website upload failed for $eventId`: $($_.Exception.Message)"
            }
        }
    } catch {
        Write-Warning "Machine sync failed: $($_.Exception.Message)"
    } finally {
        if ($connected) { $device.CloseCommunication() }
    }
    $pollSeconds = if ($settings.POLL_SECONDS) { [int]$settings.POLL_SECONDS } else { 5 }
    Start-Sleep -Seconds $pollSeconds
}