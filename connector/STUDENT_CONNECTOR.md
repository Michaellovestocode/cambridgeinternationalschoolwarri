# Student connector: three clocks

One student connector process polls each configured Realand clock in turn. The laptop must be on the same local network as the clocks. The clocks do not need Real Time Push; leave it Off because this connector pulls stored punches over the local SDK connection.

## Configure

1. Copy `student-devices.example.csv` to `student-devices.csv` in this folder.
2. Fill the IP column with each machine's current local IP from its Comm. screen/router. Each clock needs its own IP; the public hotspot IP is not used here.
3. Confirm SDK communication IDs 2, 3, and 4 match the device setup. ReceiverSN values 00002, 00003, and 00004 identify the three readers to the website.
4. Keep the existing `.env.student` settings for `F495_SDK_PATH`, `STUDENT_WEBSITE_URL`, and `POLL_SECONDS`.
5. The website deployment must allow all three receiver IDs with `STUDENT_F_G495_DEVICE_IDS=00002,00003,00004` in the VPS `.env`. Source changes alone do not change production.

The connector saves each clock's retry queue separately. It keeps the old `student-connector-state.json` for receiver 00002 so existing deduplication history and pending punches stay intact. Keep all state files beside the connector; do not delete them during normal operation.

If `student-devices.csv` is absent, the connector falls back to the existing one-reader `.env.student` configuration and receiver 00002.

Student Enroll IDs are currently matched globally in the school database. Keep IDs unique across the three machines because the attendance mapping currently expects one learner per ID.
