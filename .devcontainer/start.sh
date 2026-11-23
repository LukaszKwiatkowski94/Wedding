#!/usr/bin/env bash
set -euo pipefail

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist
php artisan config:clear

if ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --no-interaction
fi

npm ci
npm run build

exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
