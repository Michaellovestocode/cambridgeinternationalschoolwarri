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
    # The record was persisted to the outbox before this request. Keep the same
    # staff endpoint, authentication header, payload, and event ID semantics.
    $payload = @{
        device_id = $record.device_id
        enroll_id = $record.enroll_id
        timestamp = $record.timestamp
        direction = $record.direction
        event_id = $record.event_id
    } | ConvertTo-Json

    Invoke-RestMethod -Uri $settings.WEBSITE_URL -Method Post -ContentType 'application/json' `
        -Headers @{ 'X-Staff-Attendance-Key' = $settings.STAFF_ATTENDANCE_KEY } -Body $payload | Out-Null
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
if (-not ($state.PSObject.Properties.Name -contains 'pending')) {
    $state | Add-Member -NotePropertyName pending -NotePropertyValue @()
}
$sent = [Collections.Generic.HashSet[string]]::new([string[]]@($state.sent))
$pendingIds = [Collections.Generic.HashSet[string]]::new([string[]]@(@($state.pending | ForEach-Object { [string]$_.event_id })))
$minimumDate = if ($settings.MIN_EVENT_DATE) { [DateTime]::ParseExact($settings.MIN_EVENT_DATE, 'yyyy-MM-dd', $null).Date } else { $null }
$pollSeconds = if ($settings.POLL_SECONDS) { [int]$settings.POLL_SECONDS } else { 5 }
Write-Host "F-G495 staff connector started for $($settings.F495_IP):$($settings.F495_PORT). Pending uploads are saved locally and retried. Press Ctrl+C to stop."

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
            if ($minimumDate -and $record.Clock.Date -lt $minimumDate) { continue }
            $machineUserId = [string]$record.DIN
            $eventId = "{0}:{1}:{2}:{3}" -f $settings.F495_DEVICE_ID, $machineUserId, $timestamp, $record.Action
            if ($sent.Contains($eventId) -or $pendingIds.Contains($eventId)) { continue }
            # GetNewlyRecords may advance the device's cursor. Persist each
            # punch locally first so a weak internet connection cannot lose it.
            $state.pending += [pscustomobject]@{
                event_id = $eventId
                device_id = [string]$settings.F495_DEVICE_ID
                enroll_id = $machineUserId
                timestamp = $timestamp
                direction = ''
            }
            $pendingIds.Add($eventId) | Out-Null
        }
        if (@($state.pending).Count -gt 0) { Save-State $statePath $state }
    } catch {
        Write-Warning "Machine sync failed: $($_.Exception.Message)"
    } finally {
        if ($connected) { $device.CloseCommunication() }
    }
    # Drain the durable outbox every cycle, even if reading the machine failed.
    foreach ($queuedRecord in @($state.pending)) {
        try {
            Send-Record $settings $queuedRecord
            $sent.Add([string]$queuedRecord.event_id) | Out-Null
            $state.sent = @($sent | Select-Object -Last 5000)
            $state.pending = @($state.pending | Where-Object { $_.event_id -ne $queuedRecord.event_id })
            $pendingIds.Remove([string]$queuedRecord.event_id) | Out-Null
            Save-State $statePath $state
            Write-Host "Sent staff Enroll ID $($queuedRecord.enroll_id) at $($queuedRecord.timestamp)"
        } catch {
            Write-Warning "Staff upload pending for Enroll ID $($queuedRecord.enroll_id) at $($queuedRecord.timestamp): $($_.Exception.Message)"
        }
    }
    Start-Sleep -Seconds $pollSeconds
}
