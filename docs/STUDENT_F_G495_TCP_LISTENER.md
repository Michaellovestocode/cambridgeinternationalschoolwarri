# Student F-G495 TCP listener

The listener is student-only and uses the Realand `SbLogServer` NUL-terminated
XML-over-TCP protocol. It invokes the existing Laravel student ADMS handler, so
student matching, attendance records, event history, duplicate prevention, and
parent SMS use the existing application flow. It does not create or require an
`attendance_logs` table. The existing staff receiver is unchanged.

## Configure the clock and Laravel

The device's `Server` should be the VPS IPv4 address and its `Server Port` should
be `5055`. Keep Real Time Push enabled. Add these values to the production
`.env` (preserving the existing student device ID):

```dotenv
STUDENT_F_G495_DEVICE_ID=00002
CLOCK_TCP_PORT=5055
CLOCK_ALLOWED_SERIALS=THE_DEVICE_SERIAL_FROM_THE_CLOCK_ABOUT_SCREEN
```

`CLOCK_ALLOWED_SERIALS` accepts comma-separated physical `DeviceSerialNo` values
from TCP messages. The protocol has no password or encryption; the serial check
is an allowlist, not cryptographic authentication. If the school's public IP is
static, restrict inbound TCP 5055 to that source in the Hostinger firewall.
Do not open MySQL to the internet.

After editing `.env`, refresh Laravel's cached configuration:

```bash
cd /var/www/cambridgeinternationalschoolwarri
php artisan config:clear
php artisan config:cache
```

The listener requires Workerman (already included in `composer.json` and
`composer.lock`). No database migration is needed for this listener; the student
event and attendance tables already exist.

## Run in the foreground first

```bash
php /var/www/cambridgeinternationalschoolwarri/clock-tcp-listener.php start
```

The Hostinger firewall must accept inbound **TCP 5055**. The service binds to
all VPS interfaces on that port. From another SSH session, inspect
`storage/logs/laravel.log` for `Student clock TCP:` entries. The separate
student device test page shows matched and unmatched punches.

For a local protocol smoke test, start the listener with a test serial in
`CLOCK_ALLOWED_SERIALS`, then run this from another terminal:

```bash
export CLOCK_TEST_SERIAL=TEST-ONLY-SERIAL
php scripts/test-student-clock-tcp.php 127.0.0.1 5055
```

On PowerShell, set `$env:CLOCK_TEST_SERIAL = 'TEST-ONLY-SERIAL'` before running
the PHP command. This smoke test sends only KeepAlive messages; it does not
write attendance records. A real attendance integration test should use an
isolated test database and a dedicated test student to avoid changing school
attendance or sending a parent SMS.

## Supervisor

Create `/etc/supervisor/conf.d/student-clock-tcp.conf`:

```ini
[program:student-clock-tcp]
directory=/var/www/cambridgeinternationalschoolwarri
command=/usr/bin/php /var/www/cambridgeinternationalschoolwarri/clock-tcp-listener.php start
user=www-data
autostart=true
autorestart=true
startsecs=3
stopasgroup=true
killasgroup=true
redirect_stderr=true
stdout_logfile=/var/log/supervisor/student-clock-tcp.log
stdout_logfile_maxbytes=20MB
stdout_logfile_backups=5
```

Then load and start it:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status student-clock-tcp
```

Ensure the `www-data` account can read `.env` and the application files and can
write to `storage/logs`. No Nginx route is needed for this raw TCP service.
