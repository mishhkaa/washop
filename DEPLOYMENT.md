# Запуск на проді (покрокова інструкція)

Інструкція для деплою Laravel CRM + магазин + Telegram-бот на продакшен-сервер.

---

## 1. Сервер і залежності

- **PHP** 8.2+ з розширеннями: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `sqlite3` (або `pdo_mysql` для MySQL).
- **Composer** 2.x.
- **Node.js** 20+ або 22+ та **npm** (для збірки фронту; Vite 7 не працює з Node 12/16/18).
- (За бажанням) **MySQL/MariaDB** або залишаємо **SQLite**.

Перевірка PHP:
```bash
php -v
php -m
```

---

## 2. Клонування та встановлення

```bash
cd /var/www   # або ваша папка
git clone https://github.com/mishhkaa/washop.git
cd washop
```

Встановити залежності PHP та Node:
```bash
composer install --no-dev --optimize-autoloader
npm ci
```

---

## 3. Файл середовища (.env)

```bash
cp .env.example .env
```

Відредагуй `.env` для **продакшену**:

```env
APP_NAME="WaShop"
APP_ENV=production
APP_KEY=                    # згенеруй кроком нижче
APP_DEBUG=false
APP_URL=https://твій-домен.com

# База даних (SQLite на проді — ок)
DB_CONNECTION=sqlite
DB_DATABASE=/повний/шлях/до/database/database.sqlite

# Якщо MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=washop
# DB_USERNAME=washop_user
# DB_PASSWORD=надійний_пароль

SESSION_DRIVER=file
SESSION_LIFETIME=120

# Для API бота/магазину (заголовок X-API-Key)
SHOP_API_TOKEN=згенеруй_довгий_випадковий_рядок
```

Згенерувати ключ додатку:
```bash
php artisan key:generate
```

---

## 4. База даних

**SQLite:**
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

**MySQL:** створи БД і користувача, потім у `.env` вкажи `DB_*`.

Міграції:
```bash
php artisan migrate --force
```

**Сиди для товарів магазину та бота (обов’язково, щоб на сайті/в боті були товари):**
```bash
php artisan db:seed --class=BotProductsSeeder --force
```

Якщо не запустити `BotProductsSeeder`, на проді буде **«немає товарів»**: магазин і API бота показують лише товари з `available_in_bot = true` та `quantity > 0`. Цей сидер додає приклад товарів з такими позначками.

(Опційно) інші сиди:
```bash
php artisan db:seed --force
```

---

## 5. Збірка фронту (CRM стилі/скрипти)

```bash
npm run build
```

Переконайся, що в `public/build/` з’явилися файли (manifest.json та assets).

**Якщо на проді виникає помилка `ViteManifestNotFoundException`** — це означає, що збірка не виконана або папка `public/build/` не завантажена на сервер. Зробіть одне з двох:
- На сервері: `cd washop && npm ci && npm run build` (потрібен Node.js 18+).
- Або збери локально: `npm run build`, потім завантаж папку `public/build/` на сервер у той самий шлях (наприклад `public_html/washop/public/build/`).

Якщо збірку не виконувати, застосунок не впаде (підключиться fallback CSS), але стилі CRM будуть спрощені.

**Якщо на сервері старий Node (наприклад v12) і виникає `SyntaxError: Unexpected token '.'` при `npm run build`** — на сервері не потрібно оновлювати Node. Збери ассети **локально** (де вже стоїть Node 20+):

```bash
# На своєму комп’ютері (у папці проєкту)
npm ci
npm run build
```

Потім завантаж на сервер **тільки** папку `public/build/` (разом з усім вмістом) у каталог `.../washop/public/build/`. Наприклад через SCP/SFTP:

```bash
scp -r public/build/* root@сервер:/home/administrator/web/mycrm.hookly.org/public_html/washop/public/build/
```

Або архівуй `public/build`, залий на сервер і розпакуй у `washop/public/build/`.

---

## 6. Права та симлінк storage

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

Якщо `storage:link` повідомляє, що посилання вже є — нічого не змінюй.

---

## 7. Веб-сервер (Nginx / Apache)

**Document root** має бути папка `public` проєкту.

Приклад для **Nginx**:
```nginx
server {
    listen 80;
    server_name твій-домен.com;
    root /var/www/washop/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

Після змін перезапусти Nginx. Якщо використовуєш HTTPS — налаштуй SSL (наприклад, Let's Encrypt) і в `APP_URL` вкажи `https://...`.

---

## 8. Кеш конфігурації (прод)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Після зміни `.env` або коду знову виконай ці команди.

---

## 9. Telegram-бот

Бот відкриває магазин у WebApp і відправляє замовлення в CRM через API.

У `.env` на сервері **не обов’язково** додавати змінні для бота — бот запускається окремо і читає свій `.env` або змінні середовища.

На сервері (або окремому VPS/контейнері), де буде працювати бот:

1. Встанови Python 3.10+ та залежності:
```bash
cd /var/www/washop
python3 -m venv venv
source venv/bin/activate   # Linux/macOS
pip install aiogram
```

2. Створи `.env` для бота (або експортуй змінні):
```env
TELEGRAM_BOT_TOKEN=токен_від_@BotFather
SHOP_WEBAPP_URL=https://твій-домен.com
```

3. Запуск бота (вручну або через systemd/supervisor):
```bash
python3 bot.py
```

**Важливо:** у боті в коді вказаний URL WebApp — він має збігатися з твоїм доменом (`SHOP_WEBAPP_URL`). У BotFather у кнопці Web App вкажи той самий URL (наприклад `https://твій-домен.com`).

---

## 10. API для бота (перевірка)

Замовлення з WebApp/бота йдуть у Laravel так:

- **З браузера (форма на сайті):** POST на твій домен `/checkout` (звичайний веб-маршрут, CSRF).
- **З зовнішнього клієнта (наприклад, бот):** POST на `https://твій-домен.com/api/orders` з заголовком:
  - `X-API-Key: твій_SHOP_API_TOKEN`
  або
  - `Authorization: Bearer твій_SHOP_API_TOKEN`

У `.env` має бути вказаний той самий `SHOP_API_TOKEN`, який використовує клієнт.

---

## 11. Швидкий чеклист

| Крок | Команда / дія |
|------|----------------|
| 1 | `git clone`, `composer install --no-dev`, `npm ci` |
| 2 | `cp .env.example .env` → відредагувати `APP_*`, `DB_*`, `APP_URL`, `SHOP_API_TOKEN` |
| 3 | `php artisan key:generate` |
| 4 | SQLite: `touch database/database.sqlite` або налаштувати MySQL |
| 5 | `php artisan migrate --force` |
| 5a | Товари: `php artisan db:seed --class=BotProductsSeeder --force` |
| 6 | `npm run build` |
| 7 | `chown/chmod` для `storage` і `bootstrap/cache`, `php artisan storage:link` |
| 8 | Nginx/Apache: root = `.../public` |
| 9 | `php artisan config:cache` та `route:cache`, `view:cache` |
| 10 | Запустити бота: `TELEGRAM_BOT_TOKEN=... SHOP_WEBAPP_URL=... python3 bot.py` |

---

## Типові помилки

- **`ViteManifestNotFoundException` (manifest not found at …/public/build/manifest.json)** — на сервері не виконано `npm run build` або папка `public/build/` відсутня. Виконай на сервері `npm ci && npm run build` (потрібен Node 20+) або збери локально і завантаж папку `public/build/`.
- **`SyntaxError: Unexpected token '.'` при `npm run build` на сервері** — на сервері занадто старий Node (наприклад v12). Не оновлюй Node на проді: збери локально (`npm run build`) і завантаж папку `public/build/` на сервер (див. розділ 5 вище).
- **Стилі CRM не завантажуються** — переконайся, що є папка `public/build/` (збірка на сервері або завантажена з локальної машини) і document root веб-сервера вказує на `public`.
- **`PHP Warning: Module "pdo_sqlite" is already loaded`** — у php.ini модуль pdo_sqlite підключено двічі (наприклад і в основному файлі, і в додатковому .ini). Видали один з рядків `extension=pdo_sqlite` або `extension=sqlite`. На роботу сидерів це не впливає.
- **На проді немає товарів (магазин / бот пустий)** — запусти сидер: `php artisan db:seed --class=BotProductsSeeder --force`. Товари мають мати `available_in_bot = true` та `quantity > 0`; це налаштовується в адмінці в розділі «Товари».
- **The image failed to upload** — перевір права на `storage/app/public` (775) і наявність `public/storage` → `storage/app/public`.
- **419 / CSRF** — на веб-формах має бути `@csrf`; для API використовуй `X-API-Key`, не cookie.
- **Бот не відкриває магазин** — перевір `SHOP_WEBAPP_URL` у боті і URL Web App у BotFather.

Якщо щось падає — переглянь логи: `storage/logs/laravel.log` та вивід бота в консолі.
