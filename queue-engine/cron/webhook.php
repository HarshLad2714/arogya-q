<?php

require dirname(__DIR__).'/bootstrap.php';

$file = $argv[1] ?? '';
$payload = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
@unlink($file);

if (! is_array($payload)) {
    exit(0);
}

$url = arogya_env('LARAVEL_WEBHOOK_URL', 'http://127.0.0.1:8000/webhooks/queue');
$secret = arogya_env('QUEUE_ENGINE_SECRET', 'arogya-queue-secret');
$body = json_encode($payload);

for ($attempt = 0; $attempt < 5; $attempt++) {
    usleep(400000);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\nX-Queue-Secret: {$secret}\r\n",
            'content' => $body,
            'timeout' => 2,
            'ignore_errors' => true,
        ],
    ]);
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        exit(0);
    }
}

exit(0);
