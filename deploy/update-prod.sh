#!/bin/bash
# Оновлення проду: pull, Laravel, бот. Запускати з кореня проєкту. .env не чіпає (в gitignore).
set -e
cd "$(dirname "$0")/.."
echo "=== Оновлення проєкту в $(pwd) ==="

# 1. Скинути локальні зміни і підтягнути код (.env залишається — він у .gitignore)
git fetch origin main
git reset --hard origin/main

# 2. Laravel
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader 2>/dev/null || true
php artisan migrate --force 2>/dev/null || true
php artisan config:cache
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# 3. Бот: venv і залежності
if [ ! -d venv ]; then
  python3 -m venv venv
fi
set +e
source venv/bin/activate
pip install -q -r requirements.txt 2>/dev/null || pip install -q "aiogram>=3.0" "python-dotenv>=1.0"
set -e

echo "=== Готово. Перезапусти бота: systemctl restart washop-bot (або python3 bot.py) ==="
