#!/usr/bin/env bash
# إدارة طابور Laravel + إشعارات واتساب (Green API) على cPanel
# الاستخدام:
#   bash deploy/manage-whatsapp.sh setup
#   bash deploy/manage-whatsapp.sh start
#   bash deploy/manage-whatsapp.sh stop
#   bash deploy/manage-whatsapp.sh status
#   bash deploy/manage-whatsapp.sh test
#   bash deploy/manage-whatsapp.sh cron
#   bash deploy/manage-whatsapp.sh logs
set -euo pipefail

export PATH="${HOME}/bin:/usr/local/bin:/usr/bin:${PATH}"

APP_DIR="${DEPLOY_PATH:-${APP_DIR:-$HOME/elattar}}"
PID_FILE="${APP_DIR}/storage/framework/queue-worker.pid"
LOG_FILE="${APP_DIR}/storage/logs/queue-worker.log"
ARTISAN_LOG="${APP_DIR}/storage/logs/laravel.log"

cd "$APP_DIR"

red()    { printf '\033[0;31m%s\033[0m\n' "$*"; }
green()  { printf '\033[0;32m%s\033[0m\n' "$*"; }
yellow() { printf '\033[0;33m%s\033[0m\n' "$*"; }
info()   { printf '==> %s\n' "$*"; }

need_env() {
  if [[ ! -f .env ]]; then
    red "ملف .env غير موجود في $APP_DIR"
    exit 1
  fi
}

set_env_var() {
  local key="$1"
  local val="$2"
  if grep -qE "^${key}=" .env; then
    # استبدال السطر الحالي
    sed -i.bak -E "s|^${key}=.*|${key}=${val}|" .env
  else
    printf '\n%s=%s\n' "$key" "$val" >> .env
  fi
}

get_env_var() {
  local key="$1"
  grep -E "^${key}=" .env 2>/dev/null | tail -1 | cut -d= -f2- | tr -d '"' | tr -d "'" || true
}

worker_running() {
  if [[ -f "$PID_FILE" ]]; then
    local pid
    pid="$(cat "$PID_FILE" 2>/dev/null || true)"
    if [[ -n "${pid:-}" ]] && kill -0 "$pid" 2>/dev/null; then
      return 0
    fi
  fi
  # fallback: ابحث عن process
  pgrep -f "artisan queue:work" >/dev/null 2>&1
}

cmd_setup() {
  need_env
  info "إعداد إشعارات واتساب (Green API) في .env"
  echo ""
  yellow "اترك القيمة فارغة للإبقاء على الحالية."
  echo ""

  local cur_enabled cur_instance cur_token cur_wa cur_base cur_timeout
  cur_enabled="$(get_env_var WHATSAPP_NOTIFY_ENABLED)"
  cur_instance="$(get_env_var GREEN_API_INSTANCE_ID)"
  cur_token="$(get_env_var GREEN_API_TOKEN)"
  cur_wa="$(get_env_var STOREFRONT_WHATSAPP)"
  cur_base="$(get_env_var GREEN_API_BASE_URL)"
  cur_timeout="$(get_env_var GREEN_API_TIMEOUT)"

  read -r -p "تفعيل الإشعار؟ true/false [${cur_enabled:-false}]: " in_enabled
  read -r -p "رقم واتساب المتجر بدون + [${cur_wa:-2010…}]: " in_wa
  read -r -p "GREEN_API_BASE_URL [${cur_base:-https://api.green-api.com}]: " in_base
  read -r -p "GREEN_API_INSTANCE_ID [${cur_instance}]: " in_instance
  read -r -p "GREEN_API_TOKEN [${cur_token:+****}]: " in_token
  read -r -p "GREEN_API_TIMEOUT [${cur_timeout:-15}]: " in_timeout
  read -r -p "QUEUE_CONNECTION [database]: " in_queue

  set_env_var WHATSAPP_NOTIFY_ENABLED "${in_enabled:-${cur_enabled:-false}}"
  [[ -n "${in_wa:-}" ]] && set_env_var STOREFRONT_WHATSAPP "$in_wa"
  set_env_var GREEN_API_BASE_URL "${in_base:-${cur_base:-https://api.green-api.com}}"
  [[ -n "${in_instance:-}" ]] && set_env_var GREEN_API_INSTANCE_ID "$in_instance"
  [[ -n "${in_token:-}" ]] && set_env_var GREEN_API_TOKEN "$in_token"
  set_env_var GREEN_API_TIMEOUT "${in_timeout:-${cur_timeout:-15}}"
  set_env_var QUEUE_CONNECTION "${in_queue:-database}"

  php artisan config:clear
  php artisan config:cache

  green "تم تحديث .env وتخزين الإعدادات."
  echo ""
  cmd_status
  echo ""
  yellow "الخطوة التالية: شغّل الطابور ثم ثبّت cron"
  echo "  bash deploy/manage-whatsapp.sh start"
  echo "  bash deploy/manage-whatsapp.sh cron"
}

cmd_start() {
  need_env
  mkdir -p "$(dirname "$PID_FILE")" "$(dirname "$LOG_FILE")"

  if worker_running; then
    yellow "الطابور شغال بالفعل (PID $(cat "$PID_FILE" 2>/dev/null || pgrep -f 'artisan queue:work' | head -1))"
    return 0
  fi

  # تأكد من جدول jobs
  php artisan migrate --force >/dev/null 2>&1 || true

  nohup php artisan queue:work database \
    --sleep=3 \
    --tries=3 \
    --timeout=90 \
    --max-time=3600 \
    --memory=128 \
    >>"$LOG_FILE" 2>&1 &

  echo $! > "$PID_FILE"
  sleep 1

  if worker_running; then
    green "تم تشغيل queue:work — PID $(cat "$PID_FILE")"
    echo "اللوق: $LOG_FILE"
    yellow "ملاحظة: على الاستضافة المشتركة يُفضَّل أيضًا cron كل دقيقة (manage-whatsapp.sh cron)"
  else
    red "فشل تشغيل الـ worker — راجع $LOG_FILE"
    exit 1
  fi
}

cmd_stop() {
  if [[ -f "$PID_FILE" ]]; then
    local pid
    pid="$(cat "$PID_FILE")"
    if kill -0 "$pid" 2>/dev/null; then
      kill "$pid" 2>/dev/null || true
      sleep 1
      kill -9 "$pid" 2>/dev/null || true
      green "تم إيقاف worker PID $pid"
    fi
    rm -f "$PID_FILE"
  fi

  # أوقف أي queue:work متبقي لهذا المشروع
  pkill -f "$APP_DIR/artisan queue:work" 2>/dev/null || true
  green "تم إيقاف طابور واتساب/الـ queue."
}

cmd_restart() {
  cmd_stop || true
  cmd_start
}

cmd_status() {
  need_env
  echo "── المسار: $APP_DIR"
  echo "── الإعدادات:"
  printf "   WHATSAPP_NOTIFY_ENABLED = %s\n" "$(get_env_var WHATSAPP_NOTIFY_ENABLED)"
  printf "   STOREFRONT_WHATSAPP     = %s\n" "$(get_env_var STOREFRONT_WHATSAPP)"
  printf "   GREEN_API_INSTANCE_ID   = %s\n" "$(get_env_var GREEN_API_INSTANCE_ID)"
  printf "   GREEN_API_TOKEN         = %s\n" "$( [[ -n "$(get_env_var GREEN_API_TOKEN)" ]] && echo 'set' || echo 'empty' )"
  printf "   QUEUE_CONNECTION        = %s\n" "$(get_env_var QUEUE_CONNECTION)"
  echo ""

  if worker_running; then
    green "queue worker: RUNNING (PID $(cat "$PID_FILE" 2>/dev/null || pgrep -f 'artisan queue:work' | head -1))"
  else
    yellow "queue worker: STOPPED"
  fi

  echo ""
  echo "── طابور قاعدة البيانات:"
  php artisan tinker --execute="
    echo 'pending jobs: ' . DB::table('jobs')->count() . PHP_EOL;
    echo 'failed jobs:  ' . DB::table('failed_jobs')->count() . PHP_EOL;
  " 2>/dev/null || yellow "تعذّر قراءة جداول jobs (شغّل migrate إن لزم)."
}

cmd_test() {
  need_env
  info "إرسال Job تجريبي لإشعار طلب أونلاين أخير (إن وُجد)"

  php artisan tinker --execute="
    \$order = \\App\\Domain\\Sales\\Models\\Order::query()
      ->where('channel', 'online')
      ->latest('id')
      ->first();
    if (! \$order) {
      echo 'NO_ORDER' . PHP_EOL;
      exit(1);
    }
    \\App\\Jobs\\SendNewOrderWhatsAppNotificationJob::dispatch(\$order->id);
    echo 'DISPATCHED order_id=' . \$order->id . ' number=' . \$order->number . PHP_EOL;
  "

  yellow "لو الـ worker واقف: bash deploy/manage-whatsapp.sh start"
  yellow "أو نفّذ مرة واحدة فورًا:"
  echo "  php artisan queue:work --once"
}

cmd_failed() {
  need_env
  php artisan queue:failed
  echo ""
  yellow "إعادة محاولة كل الفاشل: php artisan queue:retry all"
  yellow "مسح الفاشل:          php artisan queue:flush"
}

cmd_cron() {
  local php_bin
  php_bin="$(command -v php)"
  cat <<EOF

════════════════════════════════════════════════════════
ثبّت هذه الأسطر في cPanel → Cron Jobs  (كل دقيقة)
════════════════════════════════════════════════════════

# 1) Scheduler Laravel (اختياري لكن مفيد)
* * * * * cd ${APP_DIR} && ${php_bin} artisan schedule:run >> /dev/null 2>&1

# 2) معالجة الطابور — يعمل حتى لو الـ worker الخلفية توقف
* * * * * cd ${APP_DIR} && ${php_bin} artisan queue:work database --stop-when-empty --max-time=50 --tries=3 --timeout=90 >> ${APP_DIR}/storage/logs/queue-cron.log 2>&1

════════════════════════════════════════════════════════
بعد الإضافة:
  bash deploy/manage-whatsapp.sh status
════════════════════════════════════════════════════════

EOF
}

cmd_logs() {
  echo "── آخر سطور queue-worker.log"
  if [[ -f "$LOG_FILE" ]]; then
    tail -n 40 "$LOG_FILE"
  else
    yellow "لا يوجد $LOG_FILE بعد"
  fi
  echo ""
  echo "── آخر سطور laravel.log (whatsapp)"
  if [[ -f "$ARTISAN_LOG" ]]; then
    grep -i 'whatsapp' "$ARTISAN_LOG" | tail -n 30 || yellow "لا رسائل whatsapp في اللوق بعد"
  else
    yellow "لا يوجد laravel.log"
  fi
}

cmd_help() {
  cat <<EOF
إدارة واتساب + الطابور — $APP_DIR

الأوامر:
  setup     ضبط متغيرات Green API ورقم المتجر في .env
  start     تشغيل queue:work في الخلفية (nohup)
  stop      إيقاف الـ worker
  restart   إعادة تشغيل
  status    حالة الإعداد + الطابور + عدد الـ jobs
  test      إرسال إشعار تجريبي لآخر طلب أونلاين
  failed    عرض الـ jobs الفاشلة
  cron      طباعة أسطر cron لـ cPanel
  logs      عرض لوقات الطابور وواتساب

مثال أول مرة:
  cd ~/elattar
  bash deploy/manage-whatsapp.sh setup
  bash deploy/manage-whatsapp.sh start
  bash deploy/manage-whatsapp.sh cron   # انسخ الأسطر لـ cPanel Cron Jobs
  bash deploy/manage-whatsapp.sh test
EOF
}

case "${1:-help}" in
  setup)   cmd_setup ;;
  start)   cmd_start ;;
  stop)    cmd_stop ;;
  restart) cmd_restart ;;
  status)  cmd_status ;;
  test)    cmd_test ;;
  failed)  cmd_failed ;;
  cron)    cmd_cron ;;
  logs)    cmd_logs ;;
  help|-h|--help) cmd_help ;;
  *)
    red "أمر غير معروف: $1"
    cmd_help
    exit 1
    ;;
esac
