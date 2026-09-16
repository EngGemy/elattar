#!/usr/bin/env bash
# نشر روتيني — يُشغَّل من GitHub Actions أو يدويًا على السيرفر
set -euo pipefail

export PATH="${HOME}/bin:${PATH}"

APP_DIR="${DEPLOY_PATH:-${1:-$HOME/elattar}}"
cd "$APP_DIR"

echo "==> Deploy: $APP_DIR ($(git rev-parse --short HEAD 2>/dev/null || echo 'no-git'))"

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

php artisan storage:link --force 2>/dev/null || true

php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# أعد تشغيل طابور واتساب إن كان السكربت موجودًا
if [[ -f deploy/manage-whatsapp.sh ]]; then
  bash deploy/manage-whatsapp.sh restart 2>/dev/null || true
fi

echo "==> Deploy finished OK"
echo "    واتساب/طابور: bash deploy/manage-whatsapp.sh status"
