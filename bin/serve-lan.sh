#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_BIN="${PHP_BIN:-/opt/homebrew/opt/php@8.4/bin/php}"

if [[ ! -x "$PHP_BIN" ]]; then
  PHP_BIN="$(command -v php)"
fi

cd "$ROOT"

echo "Laravel  http://0.0.0.0:8000"
echo "Queue    http://0.0.0.0:8081"
echo "Socket   ws://0.0.0.0:8082"

"$PHP_BIN" artisan serve --host=0.0.0.0 --port=8000 &
"$PHP_BIN" -S 0.0.0.0:8081 -t queue-engine/public queue-engine/public/index.php &
"$PHP_BIN" queue-engine/websocket/server.php &
wait
