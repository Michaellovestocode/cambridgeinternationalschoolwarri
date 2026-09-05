# F-G495 Connector

This connector reads staff attendance from the Realand F-G495 through the installed Realand SDK and sends it to the school website. It does not send fingerprint or face templates.

## Replacement laptop

1. Copy this entire `connector` folder to the laptop.
2. Install the official RAMS-2980 attendance software, or copy `RealandAPI.dll` and `Riss.Devices.dll` from its installation folder. The default SDK folder is `C:\Program Files (x86)\RAMS-2980`.
3. Copy `.env.example` to `.env`.
4. Set `F495_IP` to the machine's current local IP and keep `F495_PORT=5500`.
5. Set `F495_PASSWORD=0`, `F495_DEVICE_ID=1`, and the same `STAFF_ATTENDANCE_KEY` configured on the VPS.
6. Keep `WEBSITE_URL` unchanged.
7. Double-click `start-connector.bat` and confirm it reports `Sent Enroll ID ...` after a test punch.
8. Run `install-startup.bat` once to start the connector automatically at Windows login.

The F-G495 and this laptop must be connected to the same router, Ethernet network, or phone hotspot. The laptop also needs internet access to upload records. If the laptop is offline, the machine retains punches until the connector can read and upload them.

Do not copy the laptop's `.env` into GitHub or share it. The website key is private.