#!/usr/bin/env bash
# إعداد أول مرة على cPanel — شغّله مرة واحدة بعد إنشاء .env
set -euo pipefail

export PATH="${HOME}/bin:${PATH}"

APP_DIR="${DEPLOY_PATH:-${1:-$HOME/elattar}}"
cd "$APP_DIR"

echo "==> Bootstrap: $APP_DIR"

# قاعدة البيانات والصلاحيات
php artisan migrate --force
php artisan shield:generate --all --no-interaction

php artisan storage:link --force 2>/dev/null || true

# مستخدم المدير (تفاعلي — أدخل الاسم والبريد وكلمة المرور)
echo ""
echo "── إنشاء مستخدم Filament (مدير) ──"
php artisan make:filament-user

# اجعل آخر مستخدم أو المستخدم #1 سوبر أدمن
USER_ID="${ADMIN_USER_ID:-1}"
php artisan shield:super-admin --user="$USER_ID" --no-interaction

# صلاحيات المجلدات
: > storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "==> Bootstrap finished."
echo "    سجّل الدخول: https://elattar.gawasem.com/admin"
echo "    لو ظهر 500: tail -50 storage/logs/laravel.log"
