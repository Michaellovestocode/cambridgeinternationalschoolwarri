# F-G495 Connector

This connector reads staff attendance from the Realand F-G495 through the installed Realand SDK and sends it to the school website. It does not send fingerprint or face templates.

## Replacement laptop

1. Copy this entire `connector` folder to the laptop.
2. Install the official RAMS-2980 attendance software, or copy `RealandAPI.dll` and `Riss.Devices.dll` from its installation folder. The default SDK folder is `C:\Program Files (x86)\RAMS-2980`.
3. Copy `.env.example` to `.env`.
4. Set `F495_IP` to the machine's current local IP and keep `F495_PORT=5500`.
5. Set `F495_PASSWORD=0`, `F495_DEVICE_ID=1`, and the same `STAFF_ATTENDANCE_KEY` configured on the VPS.
6. Keep `WEBSITE_URL` unchanged.
7. Double-click `start-connector.bat` and confirm it reports `Sent staff Enroll ID ...` after a test punch.
8. Run `install-startup.bat` once to start the connector automatically at Windows login.

The F-G495 and this laptop must be connected to the same router, Ethernet network, or phone hotspot. The laptop also needs internet access to upload records. If the website/network connection drops, punches already read from the device are saved in `connector-state.json` and retried automatically. Keep that file with the connector and do not delete it; it tracks sent and pending punches and prevents duplicate uploads.

The normal staff connector reads newly available punches only. To backfill punches still stored on the clock, stop the connector, set `RECOVER_ALL_RECORDS_ONCE=true` in `.env`, and start it again. It reads the clock's full history once, applies `MIN_EVENT_DATE` if configured, saves eligible punches to the local queue, and uploads/retries them. If the SDK's full-history method fails or returns no records, it tries the SDK general-log reader, then unread punches. The completion marker is saved in `connector-state.json`; leave the setting enabled for normal restarts and do not delete the state file. A recovery read returning no records is retried on the next poll.

Do not copy the laptop's `.env` into GitHub or share it. The website key is private.
