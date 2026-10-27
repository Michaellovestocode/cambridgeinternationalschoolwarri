<?php

declare(strict_types=1);

/**
 * Student-only Realand TCP push receiver.
 * Run from the Laravel project root: php clock-tcp-listener.php start
 *
 * It accepts the NUL-terminated XML protocol used by Realand's SbLogServer
 * sample and hands TimeLog punches to the existing student ADMS handler.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Workerman\Timer;
use Workerman\Worker;

const CLOCK_MAX_BUFFER_BYTES = 1048576;

function clock_log(string $message, array $context = []): void
{
    Log::info('Student clock TCP: ' . $message, $context);
}

/** Parse one vendor XML document without loading external entities. */
function clock_parse_xml(string $xml): ?array
{
    if (stripos($xml, '<!DOCTYPE') !== false || strlen($xml) > CLOCK_MAX_BUFFER_BYTES) {
        return null;
    }

    $document = new DOMDocument();
    $document->resolveExternals = false;
    $document->substituteEntities = false;
    $previous = libxml_use_internal_errors(true);

    try {
        if (! $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS)
            || ! $document->documentElement
            || $document->documentElement->tagName !== 'Message') {
            return null;
        }

        $fields = [];
        foreach ($document->documentElement->childNodes as $node) {
            if ($node instanceof DOMElement) {
                $fields[$node->tagName] = trim($node->textContent);
            }
        }

        return $fields;
    } finally {
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }
}

function clock_reply(string $request, int $transactionId): string
{
    return '<?xml version="1.0"?><Message><Request>' . $request . '</Request><TransID>'
        . $transactionId . '</TransID></Message>' . "\0";
}

function clock_punch_datetime(array $fields): ?string
{
    foreach (['Year', 'Month', 'Day', 'Hour', 'Minute', 'Second'] as $key) {
        if (! isset($fields[$key]) || ! ctype_digit($fields[$key])) {
            return null;
        }
    }

    $year = (int) $fields['Year'];
    $month = (int) $fields['Month'];
    $day = (int) $fields['Day'];
    $hour = (int) $fields['Hour'];
    $minute = (int) $fields['Minute'];
    $second = (int) $fields['Second'];

    if ($year < 2000 || ! checkdate($month, $day, $year)
        || $hour > 23 || $minute > 59 || $second > 59) {
        return null;
    }

    return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
}

function clock_direction_status(string $status): string
{
    $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', trim($status)) ?? '');

    if (in_array($normalized, ['0', 'in', 'checkin', 'dutyin', 'entry'], true)) {
        return '0';
    }

    if (in_array($normalized, ['1', 'out', 'checkout', 'dutyout', 'exit'], true)) {
        return '1';
    }

    // The existing student receiver resolves unknown status codes by punch order.
    return '';
}

function clock_process_time_log(array $fields, string $remoteIp, string $studentDeviceId): bool
{
    $userId = $fields['UserID'] ?? '';
    $punchedAt = clock_punch_datetime($fields);

    if ($punchedAt === null || ! preg_match('/^[\w.-]{1,100}$/', $userId)) {
        clock_log('Rejected TimeLog with invalid UserID or timestamp', [
            'serial' => $fields['DeviceSerialNo'] ?? '',
            'transaction_id' => $fields['TransID'] ?? '',
        ]);

        return false;
    }

    // Reuse the existing ADMS student path so matching, event deduplication,
    // attendance_records, student event history, and parent SMS stay consistent.
    $line = implode("\t", [
        $userId,
        $punchedAt,
        clock_direction_status($fields['AttendStat'] ?? ''),
    ]);
    $request = Request::create(
        '/student/iclock/cdata?SN=' . rawurlencode($studentDeviceId),
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'text/plain', 'REMOTE_ADDR' => $remoteIp],
        $line
    );

    try {
        $response = app(AttendanceController::class)->studentAdmsPush($request);
        return $response->getStatusCode() === 200;
    } catch (Throwable $exception) {
        clock_log('TimeLog processing failed', [
            'serial' => $fields['DeviceSerialNo'] ?? '',
            'transaction_id' => $fields['TransID'] ?? '',
            'error' => $exception->getMessage(),
        ]);

        return false;
    }
}

$port = (int) config('services.student_attendance.tcp_port', 5055);
$studentDeviceId = (string) config('services.student_attendance.device_id', '');
$serialConfig = (string) config('services.student_attendance.tcp_allowed_serials', '');
$allowedSerials = array_values(array_filter(array_map('trim', explode(',', $serialConfig))));

if ($port < 1 || $port > 65535) {
    throw new RuntimeException('CLOCK_TCP_PORT must be between 1 and 65535.');
}
if ($studentDeviceId === '') {
    throw new RuntimeException('Set STUDENT_F_G495_DEVICE_ID in .env before starting the listener.');
}
if ($allowedSerials === []) {
    throw new RuntimeException('Set CLOCK_ALLOWED_SERIALS to the clock serial shown in its TimeLog messages.');
}

$worker = new Worker('tcp://0.0.0.0:' . $port);
$worker->name = 'student-clock-tcp-listener';
$worker->count = 1;

$buffers = [];
$lastSeen = [];

$worker->onWorkerStart = function () use (&$lastSeen, $worker): void {
    Timer::add(30, function () use (&$lastSeen, $worker): void {
        foreach ($worker->connections as $id => $connection) {
            if (time() - ($lastSeen[$id] ?? time()) > 120) {
                $connection->close();
            }
        }
    });
};

$worker->onConnect = function ($connection) use (&$buffers, &$lastSeen): void {
    $buffers[$connection->id] = '';
    $lastSeen[$connection->id] = time();
    clock_log('connected', ['remote_ip' => $connection->getRemoteIp()]);
};

$worker->onMessage = function ($connection, string $data) use (&$buffers, &$lastSeen, $allowedSerials, $studentDeviceId): void {
    $id = $connection->id;
    $lastSeen[$id] = time();
    $buffers[$id] = ($buffers[$id] ?? '') . $data;

    if (strlen($buffers[$id]) > CLOCK_MAX_BUFFER_BYTES) {
        clock_log('Closing connection after oversized unterminated message', ['remote_ip' => $connection->getRemoteIp()]);
        $connection->close();
        return;
    }

    while (($end = strpos($buffers[$id], "\0")) !== false) {
        $xml = trim(substr($buffers[$id], 0, $end), "\0 \r\n\t");
        $buffers[$id] = substr($buffers[$id], $end + 1);
        if ($xml === '') {
            continue;
        }

        $fields = clock_parse_xml($xml);
        if ($fields === null) {
            clock_log('Rejected malformed XML message', ['remote_ip' => $connection->getRemoteIp()]);
            $connection->close();
            return;
        }

        $serial = $fields['DeviceSerialNo'] ?? '';
        if ($serial === '' || ! in_array($serial, $allowedSerials, true)) {
            clock_log('Rejected unapproved device serial', ['serial' => $serial, 'remote_ip' => $connection->getRemoteIp()]);
            $connection->close();
            return;
        }

        $event = $fields['Event'] ?? '';
        $transactionId = isset($fields['TransID']) && preg_match('/^-?\d+$/', $fields['TransID'])
            ? (int) $fields['TransID']
            : -1;

        if ($event === 'KeepAlive') {
            $connection->send(clock_reply('KeptAlive', $transactionId));
            continue;
        }

        if ($event === 'TimeLog') {
            $ok = clock_process_time_log($fields, $connection->getRemoteIp(), $studentDeviceId);
            $connection->send(clock_reply($ok ? 'UploadedLog' : 'Error', $transactionId));
            continue;
        }

        if (in_array($event, ['AdminLog', 'Alarm'], true)) {
            // The school attendance workflow only consumes TimeLog punches.
            $connection->send(clock_reply('UploadedLog', $transactionId));
            continue;
        }

        clock_log('Ignored unknown event type', ['event' => $event, 'serial' => $serial]);
    }
};

$worker->onClose = function ($connection) use (&$buffers, &$lastSeen): void {
    unset($buffers[$connection->id], $lastSeen[$connection->id]);
    clock_log('connection closed');
};

Worker::runAll();
