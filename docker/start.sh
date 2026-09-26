#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

php artisan storage:link --force >/dev/null 2>&1 || true

echo "Waiting for MySQL..."
ready=0
for _ in $(seq 1 40); do
  if php -r '
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    Illuminate\Support\Facades\DB::select("select 1");
  ' >/dev/null 2>&1; then
    ready=1
    break
  fi
  sleep 2
done

if [[ "$ready" != "1" ]]; then
  echo "MySQL did not become ready." >&2
  exit 1
fi

php artisan migrate --force

users="$(php -r '
  require "vendor/autoload.php";
  $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  echo App\Models\User::query()->count();
')"

if [[ "$users" == "0" ]]; then
  php artisan db:seed --force
fi

echo "Laravel  http://0.0.0.0:8000"
echo "Queue    http://0.0.0.0:8081"
echo "Socket   ws://0.0.0.0:8082"

php artisan serve --host=0.0.0.0 --port=8000 &
php -S 0.0.0.0:8081 -t queue-engine/public queue-engine/public/index.php &
exec php queue-engine/websocket/server.php
