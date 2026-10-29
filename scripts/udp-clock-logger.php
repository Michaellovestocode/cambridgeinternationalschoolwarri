<?php

declare(strict_types=1);

/**
 * Temporary diagnostic logger for Realand clock UDP packets.
 * Run from the Laravel project root: php scripts/udp-clock-logger.php start
 * This logger does not reply to packets or write to the database.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/vendor/autoload.php';

use Workerman\Worker;

$port = 5055;
$logFile = dirname(__DIR__) . '/storage/logs/clock-udp.log';

if (! is_dir(dirname($logFile)) || ! is_writable(dirname($logFile))) {
    fwrite(STDERR, "Log directory is missing or not writable: " . dirname($logFile) . PHP_EOL);
    exit(1);
}

$worker = new Worker('udp://0.0.0.0:' . $port);
$worker->name = 'student-clock-udp-logger';
$worker->count = 1;

$worker->onMessage = static function ($connection, string $data) use ($logFile): void {
    $printable = preg_replace('/[^\x20-\x7E]/', '.', $data) ?? '';
    $entry = sprintf(
        "[%s] source=%s:%s length=%d hex=%s ascii=%s\n",
        date('Y-m-d H:i:s'),
        $connection->getRemoteIp(),
        $connection->getRemotePort(),
        strlen($data),
        bin2hex($data),
        $printable
    );

    if (file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX) === false) {
        fwrite(STDERR, "Could not append UDP packet to {$logFile}" . PHP_EOL);
        return;
    }

    echo rtrim($entry) . PHP_EOL;
};

Worker::runAll();
