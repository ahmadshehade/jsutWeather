#!/usr/bin/env bash
set -e

# تأكد من وجود APP_KEY في environment (يجب إضافته في لوحة Render)
if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY is not set. Generate it locally and add to Render env."
  exit 1
fi

# تنظيف وتهيئة الكاش بطريقة آمنة (runtime)
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# تشغيل المهاجرات (إذا أردت تشغيلها آليًا)
if [ "$RUN_MIGRATIONS" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force
fi

# ربط التخزين إن لم يكن مرتبطًا
if [ ! -L public/storage ]; then
  php artisan storage:link || true
fi

# الآن شغّل Apache في foreground
exec "$@"
