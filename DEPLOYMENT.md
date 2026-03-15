# Запуск на проді (покрокова інструкція)

Інструкція для деплою Laravel CRM + магазин + Telegram-бот на продакшен-сервер.

---

## Швидкі команди (вже є код, треба оновити і запустити бота)

Виконай на сервері **по черзі** (шлях заміни на свій, якщо проєкт не в `washop`):

```bash
cd /home/administrator/web/mycrm.hookly.org/public_html/washop

# Скинути локальні зміни і підтягнути код з git (.env не перезапишеться)
git fetch origin main
git reset --hard origin/main

# Laravel
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache

# Віртуальне середовище і бот (якщо venv немає — створюється)
python3 -m venv venv 2>/dev/null || true
source venv/bin/activate
pip install -r requirements.txt 2>/dev/null || pip install "aiogram>=3.0" "python-dotenv>=1.0"

# Запуск бота вручну (перевірка)
python3 bot.py
```

Якщо бот у консолі запустився (бачиш "Бот запускається...") — зупини його Ctrl+C і запусти через systemd:

```bash
sudo cp deploy/washop-bot.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable washop-bot
sudo systemctl start washop-bot
sudo systemctl status washop-bot
```

Переконайся, що в `.env` у корені проєкту є: `TELEGRAM_BOT_TOKEN`, `SHOP_WEBAPP_URL`, `TELEGRAM_ORDERS_CHAT_ID`. Файл `.env` в git не потрапляє — його не перезапише `git reset --hard`.

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

**При оновленні проду (`git pull`):** не перезаписуй і не видаляй папку `storage/` (зокрема `storage/app/public/`). Там зберігаються завантажені фото товарів і категорій; якщо їх перезаписати, посилання в БД перестануть знаходити файли. При деплої з іншого сервера — скопіюй `storage/app/public/` з проду або не чіпай її після клону.

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
APP_NAME="CloudCity"
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

# Telegram (один .env для Laravel і бота; заявки летять у чат)
TELEGRAM_BOT_TOKEN=токен_від_BotFather
TELEGRAM_ORDERS_CHAT_ID=-1003698957698
SHOP_WEBAPP_URL=https://mycrm.hookly.org
```
**Важливо:** бот має бути доданий у чат (групу) з ID `TELEGRAM_ORDERS_CHAT_ID` як учасник, інакше Laravel не зможе відправляти туди повідомлення про замовлення.

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
chmod 775 database
```
Якщо з’являється **«attempt to write a readonly database»** — веб-сервер не може писати в БД. Виконай (підстав свого користувача веб-сервера, напр. `www-data` або `nginx`):
```bash
chown -R www-data:www-data database
chmod 664 database/database.sqlite
chmod 775 database
```
Якщо проєкт під юзером `administrator`, а PHP-FPM/nginx працюють під ним же — достатньо `chmod 664 database/database.sqlite` та `chmod 775 database`.

**MySQL:** створи БД і користувача, потім у `.env` вкажи `DB_*`.

Міграції:
```bash
php artisan migrate --force
```
(Міграція додає колонку `delivery_method` у `sales` — Paczkomat / Osobisty odbiór.)

**Сиди для товарів магазину та бота (обов’язково, щоб на сайті/в боті були товари):**
```bash
php artisan db:seed --class=BotProductsSeeder --force
```

Якщо не запустити `BotProductsSeeder`, на проді буде **«немає товарів»**: магазин і API бота показують лише товари з `available_in_bot = true` та `quantity > 0`. Цей сидер додає приклад товарів з такими позначками.

**Категорії магазину (Pody, Jednorazówki, Liquidy, Kartridże, мова — польська):** їх додає/оновлює `ShopCategoriesSeeder`. Він викликається при повному сиді:
```bash
php artisan db:seed --force
```
Якщо на проді категорій немає — виконай цю команду (або `php artisan db:seed --class=ShopCategoriesSeeder --force`).

(Опційно) інші сиди:
```bash
php artisan db:seed --force
```

**Логін у CRM (після `php artisan db:seed --force`):**

| Роль    | Email             | Пароль   |
|---------|-------------------|----------|
| Адмін   | `admin@example.com` | `password` |
| Менеджер | `test@example.com`  | `password` |

Сторінка входу: **`/crm/login`** (наприклад `https://твій-домен.com/crm/login`). Після першого входу зміни пароль в профілі або через адмінку.

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

Бот відкриває магазин у WebApp. Замовлення зберігаються в CRM, а **усі заявки автоматично відправляються в один чат** (див. `TELEGRAM_ORDERS_CHAT_ID` у Laravel `.env`). Для цього в Laravel мають бути задані `TELEGRAM_BOT_TOKEN` та `TELEGRAM_ORDERS_CHAT_ID` (розділ 3). Токен бота — той самий, що й для запуску `bot.py`.

Бот і Laravel використовують **один і той самий `.env`** (TELEGRAM_BOT_TOKEN, SHOP_WEBAPP_URL, TELEGRAM_ORDERS_CHAT_ID).

На сервері (або окремому VPS/контейнері), де буде працювати бот:

1. Встанови Python 3.10+ та залежності (у корені проєкту, де є `bot.py` і `.env`):
```bash
cd /var/www/washop
python3 -m venv venv
source venv/bin/activate   # Linux/macOS
pip install -r requirements.txt
```

2. У **одному** `.env` (розділ 3) мають бути `TELEGRAM_BOT_TOKEN`, `SHOP_WEBAPP_URL`, `TELEGRAM_ORDERS_CHAT_ID`. Окремий файл `.env.bot` не потрібен.

3. Запуск бота (вручну або через systemd):
```bash
python3 bot.py
```

**Важливо:** у боті в коді вказаний URL WebApp — він має збігатися з твоїм доменом (`SHOP_WEBAPP_URL`). У BotFather у кнопці Web App вкажи той самий URL (наприклад `https://твій-домен.com`).

### Як запустити бота (покроково)

1. **Отримай токен бота:** у Telegram знайди [@BotFather](https://t.me/BotFather), відправ `/newbot`, придумай ім’я — отримаєш токен на кшталт `123456:ABC-DEF...`.

2. **Налаштуй Web App у BotFather:**  
   `/mybots` → вибери свого бота → **Bot Settings** → **Menu Button** або **Configure inline button** → вкажи URL магазину, наприклад `https://mycrm.hookly.org/` (без `/crm` — це головна сторінка магазину).

3. **На сервері (або на своєму ПК для тесту):**
```bash
cd /home/administrator/web/mycrm.hookly.org/public_html/washop

# Віртуальне середовище (один раз)
python3 -m venv venv
source venv/bin/activate   # Linux/macOS
# Windows:  venv\Scripts\activate

pip install -r requirements.txt
```

4. **Запуск вручну** (бот читає `.env` з кореня проєкту):
```bash
cd /home/administrator/web/mycrm.hookly.org/public_html/washop
source venv/bin/activate
python3 bot.py
```

5. **Щоб бот сам працював на VPS (systemd — автозапуск при перезавантаженні):**

   - У папці проєкту вже є **один** `.env` (з `TELEGRAM_BOT_TOKEN`, `SHOP_WEBAPP_URL`). Unit-файл підключає саме його (`EnvironmentFile=.../.env`). Файл `.env.bot` не потрібен.

   - Скопіюй unit-файл і увімкни сервіс (заміни шлях на свій, якщо проєкт в іншій папці):
   ```bash
   cd /home/administrator/web/mycrm.hookly.org/public_html/washop
   sudo cp deploy/washop-bot.service /etc/systemd/system/
   # Якщо проєкт не в цій папці — відредагуй: sudo nano /etc/systemd/system/washop-bot.service
   # (WorkingDirectory, EnvironmentFile, ExecStart)
   sudo systemctl daemon-reload
   sudo systemctl enable washop-bot
   sudo systemctl start washop-bot
   ```

   - Перевірка:
   ```bash
   sudo systemctl status washop-bot   # має бути active (running)
   sudo journalctl -u washop-bot -f    # логи в реальному часі
   ```

   Після цього бот запускається разом із сервером і перезапускається при падінні.

   Якщо проєкт у `/home/administrator/...`, у unit-файлі можливо треба змінити `User=` та `Group=` на `administrator` (або твого користувача), щоб сервіс мав доступ до папки та venv.

**Альтернатива (без systemd)** — бот у фоні вручну (з папки проєкту читає `.env`):
```bash
cd /шлях/до/проєкту && source venv/bin/activate && nohup python3 bot.py > bot.log 2>&1 &
```

У Telegram напиши боту `/start` — з’явиться кнопка «Відкрити магазин», по натисканню відкриється твій сайт магазину.

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
| 2 | `cp .env.example .env` → відредагувати `APP_*`, `DB_*`, `APP_URL`, `SHOP_API_TOKEN`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_ORDERS_CHAT_ID` |
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

- **`attempt to write a readonly database` (SQLite)** — веб-сервер не має прав на запис у БД. **Діагностика на сервері:** `php artisan db:check-writable` — покаже шлях до БД, чи є права на запис, власника файлу і які команди виконати. **Виправлення:** зайди на сервер по SSH з того юзера, під яким крутиться сайт (або root), перейди в корінь проєкту і виконай:
  ```bash
  cd /home/administrator/web/mycrm.hookly.org/public_html/washop
  chmod 775 database
  chmod 664 database/database.sqlite
  ```
  Далі **папку database має володіти той самий користувач, під яким працює PHP-FPM/nginx.** Дізнайся його: `ps aux | grep php-fpm` або у конфігу nginx/php-fpm (часто `www-data`). Потім:
  ```bash
  sudo chown -R WWW_USER:WWW_USER database
  ```
  (замість `WWW_USER` підстав `www-data`, `nginx` або того, хто в процесі). Якщо проєкт і PHP працюють під одним юзером (наприклад `administrator`) — достатньо `chmod` вище. Перезавантаж PHP-FPM після змін: `sudo systemctl reload php8.3-fpm` (або твій варіант). Після виправлення прав додай категорії з консолі: `php artisan db:seed --class=ShopCategoriesSeeder --force`.

- **В CRM немає категорій, а на сайті є** — обидва беруть дані з таблиці `shop_categories`. Якщо на сайті категорії є, в БД вони є. В CRM зайди: «Магазин / ТГ-бот» → вкладка «Категорії». Якщо список порожній — `php artisan view:clear && php artisan cache:clear`. Якщо треба створити категорії — спочатку виправ права на БД (пункт вище), потім `php artisan db:seed --class=ShopCategoriesSeeder --force`.

- **`ViteManifestNotFoundException` (manifest not found at …/public/build/manifest.json)** — на сервері не виконано `npm run build` або папка `public/build/` відсутня. Виконай на сервері `npm ci && npm run build` (потрібен Node 20+) або збери локально і завантаж папку `public/build/`.
- **`SyntaxError: Unexpected token '.'` при `npm run build` на сервері** — на сервері занадто старий Node (наприклад v12). Не оновлюй Node на проді: збери локально (`npm run build`) і завантаж папку `public/build/` на сервер (див. розділ 5 вище).
- **Стилі CRM не завантажуються** — переконайся, що є папка `public/build/` (збірка на сервері або завантажена з локальної машини) і document root веб-сервера вказує на `public`.
- **`PHP Warning: Module "pdo_sqlite" is already loaded`** — у php.ini модуль pdo_sqlite підключено двічі (наприклад і в основному файлі, і в додатковому .ini). Видали один з рядків `extension=pdo_sqlite` або `extension=sqlite`. На роботу сидерів це не впливає.
- **На проді немає товарів (магазин / бот пустий)** — запусти сидер: `php artisan db:seed --class=BotProductsSeeder --force`. Товари мають мати `available_in_bot = true` та `quantity > 0`; це налаштовується в адмінці в розділі «Товари».
- **The image failed to upload** — перевір права на `storage/app/public` (775) і наявність `public/storage` → `storage/app/public`.
- **Після оновлення (git pull / деплой) пропали фото товарів** — фото лежать у `storage/app/public/` (шляхи в БД у полі `image_path`). Не перезаписуй і не видаляй цю папку при деплої; якщо клонуєш репо заново — скопіюй `storage/app/public/` з проду або не чіпай її.
- **419 / CSRF** — на веб-формах має бути `@csrf`; для API використовуй `X-API-Key`, не cookie.
- **Бот не відкриває магазин** — перевір `SHOP_WEBAPP_URL` у боті і URL Web App у BotFather.

Якщо щось падає — переглянь логи: `storage/logs/laravel.log` та вивід бота в консолі.
