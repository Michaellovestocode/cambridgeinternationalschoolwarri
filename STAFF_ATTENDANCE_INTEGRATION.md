# F-G495 Staff Attendance Integration

The student scanner remains unchanged. The F-G495 is used only for teachers, administrators, and non-teaching staff.

## Website setup

Set this environment variable on your Hostinger VPS and restart/redeploy Laravel:

```text
STAFF_ATTENDANCE_KEY=<long-random-secret>
```

The receiving endpoint is:

```text
POST https://<your-school-domain>/api/staff-attendance/f-g495
X-Staff-Attendance-Key: <same-secret>
Content-Type: application/json
```

The F-G495 native push endpoint is also available at `/iclock/cdata`. It accepts the device's standard ADMS-style request and replies `OK`.

Expected JSON:

```json
{
  "device_id": "1",
  "enroll_id": "2",
  "timestamp": "2026-09-04 06:09:33",
  "direction": "in",
  "event_id": "1-2-20260904060933"
}
```

`direction` may be `in` or `out`. If the connector cannot determine direction, omit it: the first event that day is treated as clock-in and the next as clock-out. The connector should always send a stable `event_id`; repeated uploads with the same ID are ignored.

## Staff mapping

1. Open Admin -> Attendance -> Cards.
2. Find the teacher or staff member.
3. Enter the F-G495 `Enroll ID` in the `F-G495 Enroll ID` field.
4. Save.

For example, if the software shows `Enroll: 2`, enter `2` for Michael Opot. The connector uses the Realand SDK user field (`DIN`), not the device number (`DN`).

## Laptop connector

The screenshots show the desktop software polling the machine with `Get New Log` every two seconds. The website does not receive those records automatically. A Windows connector must read the new records from the existing software or F-G495 SDK and POST them to the endpoint above.

The starter connector is in the `connector` folder. Copy that folder to the Windows laptop, rename `.env.example` to `.env`, fill in `STAFF_ATTENDANCE_KEY`, and update `F495_IP` if DHCP changes the machine address. Run `start-connector.bat`; it uses the installed `RealandAPI.dll` every five seconds, remembers sent event IDs, and retries failed uploads. Add its shortcut to the Windows Startup folder after testing.

The connector uses the F-G495 local IP and configured port (your current settings show `192.168.43.184` and `5500`) and the installed Realand SDK's LAN mode (`Communication=1`). Do not expose the F-G495 local IP to the public internet.

The machine's fingerprint and face templates stay on the F-G495. Only the Enroll ID, timestamp, direction, and device ID are sent to the website.