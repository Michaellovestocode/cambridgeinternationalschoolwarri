<?php

declare(strict_types=1);

// Protocol smoke test only: verifies NUL framing and acknowledgements without
// writing attendance data. Start the listener first, then run this script.

$host = $argv[1] ?? '127.0.0.1';
$port = isset($argv[2]) ? (int) $argv[2] : 5055;
$serial = getenv('CLOCK_TEST_SERIAL') ?: 'TEST-ONLY-SERIAL';
$socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $error, 5);

if (! $socket) {
    fwrite(STDERR, "Could not connect to {$host}:{$port}: {$error} ({$errno})\n");
    exit(1);
}

stream_set_timeout($socket, 5);
$replyBuffer = '';

$message = static fn (int $id): string => '<?xml version="1.0"?><Message>'
    . '<TerminalType>F500</TerminalType><TerminalID>1</TerminalID>'
    . '<DeviceSerialNo>' . htmlspecialchars($serial, ENT_XML1) . '</DeviceSerialNo>'
    . '<TransID>' . $id . '</TransID><Event>KeepAlive</Event></Message>' . "\0";

$readReply = static function (int $id) use ($socket, &$replyBuffer): bool {
    while (! str_contains($replyBuffer, "\0") && ! feof($socket)) {
        $chunk = fread($socket, 4096);
        if ($chunk === false || $chunk === '') {
            break;
        }
        $replyBuffer .= $chunk;
    }

    $end = strpos($replyBuffer, "\0");
    if ($end === false) {
        return false;
    }

    $reply = substr($replyBuffer, 0, $end);
    $replyBuffer = substr($replyBuffer, $end + 1);

    return str_contains($reply, '<Request>KeptAlive</Request>')
        && str_contains($reply, '<TransID>' . $id . '</TransID>');
};

// Split one XML frame between writes to exercise TCP packet fragmentation.
$split = $message(9001);
$half = intdiv(strlen($split), 2);
fwrite($socket, substr($split, 0, $half));
usleep(150000);
fwrite($socket, substr($split, $half));
if (! $readReply(9001)) {
    fwrite(STDERR, "Split-frame acknowledgement failed.\n");
    exit(1);
}

// Put two complete XML frames in one write to exercise coalesced messages.
fwrite($socket, $message(9002) . $message(9003));
if (! $readReply(9002) || ! $readReply(9003)) {
    fwrite(STDERR, "Coalesced-frame acknowledgements failed.\n");
    exit(1);
}

fclose($socket);
fwrite(STDOUT, "PASS: split and coalesced TCP frames received the expected acknowledgements.\n");
