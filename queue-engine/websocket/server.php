<?php

require dirname(__DIR__).'/bootstrap.php';

$host = '0.0.0.0';
$port = (int) (arogya_env('QUEUE_ENGINE_WS_PORT', '8082') ?? 8082);
$server = stream_socket_server("tcp://{$host}:{$port}", $errno, $error);

if (! $server) {
    fwrite(STDERR, "WebSocket failed: {$error}\n");
    exit(1);
}

stream_set_blocking($server, false);
echo "ArogyaQ queue socket on {$host}:{$port}\n";

$clients = [];
$lastHash = '';

while (true) {
    $read = [$server];
    foreach ($clients as $client) {
        $read[] = $client['socket'];
    }

    $write = null;
    $except = null;

    if (@stream_select($read, $write, $except, 0, 400000) === false) {
        continue;
    }

    if (in_array($server, $read, true)) {
        $connection = @stream_socket_accept($server, 0);
        if ($connection) {
            stream_set_blocking($connection, false);
            $clients[(int) $connection] = ['socket' => $connection, 'handshake' => false, 'buffer' => ''];
        }
    }

    foreach ($clients as $id => $client) {
        if (! in_array($client['socket'], $read, true)) {
            continue;
        }

        $data = fread($client['socket'], 8192);
        if ($data === '' || $data === false) {
            fclose($client['socket']);
            unset($clients[$id]);
            continue;
        }

        if (! $clients[$id]['handshake']) {
            $clients[$id]['buffer'] .= $data;
            if (preg_match('/Sec-WebSocket-Key:\s*(.+)\r\n/', $clients[$id]['buffer'], $matches)) {
                $accept = base64_encode(sha1(trim($matches[1]).'258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
                $response = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: {$accept}\r\n\r\n";
                fwrite($clients[$id]['socket'], $response);
                $clients[$id]['handshake'] = true;
                $clients[$id]['buffer'] = '';
            }
        }
    }

    try {
        $snapshot = (new QueueRepository(db()))->snapshot(date('Y-m-d'));
    } catch (Throwable) {
        $snapshot = [];
    }

    $hash = md5(json_encode($snapshot));
    if ($hash === $lastHash) {
        continue;
    }

    $lastHash = $hash;
    $frame = ws_encode(json_encode(['type' => 'queue', 'doctors' => $snapshot]));

    foreach ($clients as $id => $client) {
        if (! $client['handshake']) {
            continue;
        }
        $written = @fwrite($client['socket'], $frame);
        if ($written === false) {
            fclose($client['socket']);
            unset($clients[$id]);
        }
    }
}

function ws_encode(string $payload): string
{
    $length = strlen($payload);
    $header = chr(129);

    if ($length < 126) {
        $header .= chr($length);
    } elseif ($length < 65536) {
        $header .= chr(126).pack('n', $length);
    } else {
        $header .= chr(127).pack('J', $length);
    }

    return $header.$payload;
}
