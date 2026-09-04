import json
import logging
import os
import time
from datetime import datetime
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

from dotenv import load_dotenv
from zk import ZK


load_dotenv()

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s %(levelname)s %(message)s',
    handlers=[logging.StreamHandler(), logging.FileHandler('connector.log', encoding='utf-8')],
)


def env_int(name, default):
    return int(os.getenv(name, str(default)))


def load_state(path):
    if not path.exists():
        return {'sent': []}
    try:
        return json.loads(path.read_text(encoding='utf-8'))
    except (OSError, json.JSONDecodeError):
        logging.warning('Could not read state file; starting with an empty state.')
        return {'sent': []}


def save_state(path, state):
    temporary = path.with_suffix('.tmp')
    temporary.write_text(json.dumps(state), encoding='utf-8')
    temporary.replace(path)


def post_event(event):
    request = Request(
        os.environ['WEBSITE_URL'],
        data=json.dumps(event).encode('utf-8'),
        headers={
            'Content-Type': 'application/json',
            'X-Staff-Attendance-Key': os.environ['STAFF_ATTENDANCE_KEY'],
        },
        method='POST',
    )
    with urlopen(request, timeout=20) as response:
        if response.status < 200 or response.status >= 300:
            raise RuntimeError(f'Website returned HTTP {response.status}')


def parse_min_date():
    value = os.getenv('MIN_EVENT_DATE', '')
    return datetime.strptime(value, '%Y-%m-%d').date() if value else None


def event_from_attendance(attendance):
    timestamp = attendance.timestamp
    direction = 'out' if str(getattr(attendance, 'punch', '')).lower() in {'1', 'out', 'checkout'} else 'in'
    machine_user_id = str(getattr(attendance, 'user_id', '')).strip()
    timestamp_text = timestamp.strftime('%Y-%m-%d %H:%M:%S')
    event_id = f"{os.environ.get('F495_DEVICE_ID', '1')}:{machine_user_id}:{timestamp_text}:{getattr(attendance, 'status', '')}:{getattr(attendance, 'punch', '')}"
    return {
        'device_id': os.environ.get('F495_DEVICE_ID', '1'),
        'enroll_id': machine_user_id,
        'timestamp': timestamp_text,
        'direction': direction,
        'event_id': event_id,
    }, event_id


def sync_once(state, state_path, minimum_date):
    device = ZK(
        os.environ['F495_IP'],
        port=env_int('F495_PORT', 5500),
        password=env_int('F495_PASSWORD', 0),
        timeout=15,
        ommit_ping=True,
    )
    connection = None
    try:
        connection = device.connect()
        connection.disable_device()
        attendance_logs = connection.get_attendance() or []
        sent = set(state.get('sent', []))
        for attendance in sorted(attendance_logs, key=lambda item: item.timestamp):
            if minimum_date and attendance.timestamp.date() < minimum_date:
                continue
            event, event_id = event_from_attendance(attendance)
            if not event['enroll_id'] or event_id in sent:
                continue
            try:
                post_event(event)
            except (HTTPError, URLError, OSError, RuntimeError) as error:
                logging.error('Could not send %s: %s', event_id, error)
                continue
            sent.add(event_id)
            state['sent'] = list(sent)[-5000:]
            save_state(state_path, state)
            logging.info('Sent Enroll ID %s at %s', event['enroll_id'], event['timestamp'])
        connection.enable_device()
    finally:
        if connection:
            connection.disconnect()


def main():
    required = ['F495_IP', 'WEBSITE_URL', 'STAFF_ATTENDANCE_KEY']
    missing = [name for name in required if not os.getenv(name)]
    if missing:
        raise SystemExit(f'Missing settings: {", ".join(missing)}')
    state_path = Path(os.getenv('STATE_FILE', 'connector-state.json'))
    state = load_state(state_path)
    minimum_date = parse_min_date()
    logging.info('F-G495 connector started for %s:%s', os.environ['F495_IP'], os.getenv('F495_PORT', '5500'))
    while True:
        try:
            sync_once(state, state_path, minimum_date)
        except Exception as error:
            logging.error('Machine sync failed: %s', error)
        time.sleep(env_int('POLL_SECONDS', 5))


if __name__ == '__main__':
    main()