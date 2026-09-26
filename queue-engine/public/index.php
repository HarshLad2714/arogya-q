<?php

require dirname(__DIR__).'/bootstrap.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, X-Queue-Secret, Accept');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $repo = new QueueRepository(db());
    $body = read_json();

    if ($method === 'GET' && $path === '/health') {
        json_out(['ok' => true, 'service' => 'arogyaq-queue-engine']);
    }

    if ($method === 'GET' && $path === '/queue/status') {
        $doctorId = (int) ($_GET['doctor_id'] ?? 0);
        $date = (string) ($_GET['date'] ?? date('Y-m-d'));
        json_out($repo->status($doctorId, $date) ?: shape_row(null, $doctorId, $date));
    }

    if ($method === 'POST' && $path === '/queue/next') {
        require_secret();
        $row = $repo->next((int) ($body['doctor_id'] ?? 0), (string) ($body['date'] ?? date('Y-m-d')));
        dispatch_webhook($row);
        json_out($row);
    }

    if ($method === 'POST' && $path === '/queue/sync') {
        require_secret();
        json_out($repo->sync($body));
    }

    if ($method === 'POST' && $path === '/queue/reset') {
        require_secret();
        $date = (string) ($body['date'] ?? date('Y-m-d'));
        json_out(['reset' => $date, 'rows' => $repo->reset($date)]);
    }

    json_out(['message' => 'Not found'], 404);
} catch (Throwable $exception) {
    $status = str_contains($exception->getMessage(), 'not initialised') ? 404 : 500;
    json_out(['message' => $exception->getMessage()], $status);
}
