<?php

require __DIR__.'/src/QueueRepository.php';

function arogya_env(string $key, ?string $default = null): ?string
{
    $fromProcess = getenv($key);
    if (is_string($fromProcess) && $fromProcess !== '') {
        return $fromProcess;
    }

    static $vars = null;

    if ($vars === null) {
        $vars = [];
        $path = dirname(__DIR__).'/.env';

        if (is_file($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }
                [$name, $value] = explode('=', $line, 2);
                $vars[trim($name)] = trim($value, " \t\"'");
            }
        }
    }

    return $vars[$key] ?? $default;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $driver = arogya_env('DB_CONNECTION', 'sqlite');

    if ($driver === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            arogya_env('DB_HOST', '127.0.0.1'),
            arogya_env('DB_PORT', '3306'),
            arogya_env('DB_DATABASE', 'arogya_q'),
        );
        $pdo = new PDO($dsn, arogya_env('DB_USERNAME', 'root'), arogya_env('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }

    $database = arogya_env('DB_DATABASE');
    if (! $database || $database === 'laravel') {
        $database = dirname(__DIR__).'/database/database.sqlite';
    } elseif (! str_starts_with($database, '/')) {
        $database = dirname(__DIR__).'/'.$database;
    }

    $pdo = new PDO('sqlite:'.$database, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA busy_timeout=3000');
    $pdo->exec('PRAGMA foreign_keys=ON');

    return $pdo;
}

function read_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);

    return is_array($data) ? $data : $_POST;
}

function json_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function require_secret(): void
{
    $given = $_SERVER['HTTP_X_QUEUE_SECRET'] ?? '';
    $expected = arogya_env('QUEUE_ENGINE_SECRET', 'arogya-queue-secret');

    if (! hash_equals((string) $expected, (string) $given)) {
        json_out(['message' => 'Unauthorized'], 401);
    }
}

function dispatch_webhook(array $row): void
{
    $script = __DIR__.'/cron/webhook.php';
    $tmp = tempnam(sys_get_temp_dir(), 'aqwh');
    file_put_contents($tmp, json_encode([
        'doctor_id' => (int) $row['doctor_id'],
        'date' => $row['date'],
        'current_token' => (int) $row['current_token'],
    ]));
    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg($script).' '.escapeshellarg($tmp).' > /dev/null 2>&1 &';
    exec($command);
}

function shape_row(?array $row, int $doctorId, string $date): array
{
    $current = (int) ($row['current_token'] ?? 0);
    $total = (int) ($row['total_booked'] ?? 0);
    $avg = (int) ($row['avg_consultation_minutes'] ?? 12);

    return [
        'doctor_id' => $doctorId,
        'date' => $date,
        'current_token' => $current,
        'total_booked' => $total,
        'avg_consultation_minutes' => $avg,
        'avg_wait_time' => max(0, $total - $current) * $avg,
        'room' => $row['room'] ?? null,
        'doctor_name' => $row['doctor_name'] ?? null,
    ];
}
