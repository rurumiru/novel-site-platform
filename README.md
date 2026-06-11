<div align="center">

# 📚 ER.IIIBA

**Платформа для публикации и чтения онлайн-новелл**

Авторский кабинет · чтение по главам · форум · обзоры · модерация · админ-панель

<br>

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3-FDAE4B)
![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9)
![Tailwind](https://img.shields.io/badge/Tailwind-4-38BDF8?logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-4169E1?logo=postgresql&logoColor=white)
![License](https://img.shields.io/badge/license-archive-lightgrey)

### 🌐 Демо-стенд: **[demo.iiiba.ru](https://demo.iiiba.ru)**

</div>

---

> ## ⚠️ Архивный проект — больше не поддерживается
>
> Это **старый исходный код**, который ранее работал на сайте **er.iiiba.ru**.
> Боевой сайт и проект **больше не функционируют**. Код выложен «как есть» (as-is) в ознакомительных целях.
>
> | | |
> |---|---|
> | 🛑 | Проект **не развивается** и **не поддерживается** |
> | 🛑 | **Исправление ошибок, ответы на issue и pull-request'ы не предполагаются** |
> | 🛑 | Работоспособность не гарантируется: часть интеграций (платежи, S3, почта, внешние парсеры) удалена или заменена заглушками |
> | 🔒 | Все упоминания боевых доменов, реквизитов и сторонних сервисов из кода удалены и заменены на `localhost`/placeholder'ы |
>
> Используйте только как образец архитектуры Laravel-приложения.

---

## 📑 Содержание

- [Возможности](#-возможности)
- [Технологический стек](#-технологический-стек)
- [Требования](#-требования)
- [Быстрый старт (локально)](#-быстрый-старт-локально)
- [Конфигурация](#-конфигурация)
- [Развёртывание на сервере (production)](#-развёртывание-на-сервере-production)
- [🧪 Публичный демо-стенд (demo.iiiba.ru)](#-публичный-демо-стенд-demoiiibaru)
- [Планировщик задач](#-планировщик-задач)
- [REST API](#-rest-api)
- [Структура проекта](#-структура-проекта)
- [Лицензия](#-лицензия)

---

## ✨ Возможности

- **Каталог и чтение** — новеллы, тома и главы, прогресс чтения, закладки, избранное.
- **Авторский кабинет** — создание и редактирование новелл, импорт глав, статистика.
- **Монетизация** — подписки на новеллы, поглавная покупка контента, промокоды. *(Приём платежей и вывод средств в этой публичной версии удалены.)*
- **Социальные функции** — комментарии, рейтинги, обзоры, форум.
- **Коллаборация** — бета-ридеры, редакторы новелл, переводческие команды.
- **Модерация и роли** — система ролей и прав на базе `spatie/laravel-permission`, уровни доверия, бейджи.
- **Админ-панель** — Filament.
- **Гео-ограничения** — middleware блокировки доступа к ограниченному контенту по региону.
- **Мультидоменность** — выбор базы данных по домену через middleware.
- **Публичный REST API** — `/api/v1` (новеллы, главы, авторы, поиск).

## 🛠 Технологический стек

| Слой | Технология |
|------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Админка | Filament 3 |
| Интерактив | Livewire |
| Фронтенд | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| База данных | PostgreSQL (основная), опциональная синхронизация в MySQL |
| Кеш / очереди / сессии | Redis |
| Хранилище файлов | S3-совместимое (Flysystem) |
| Почта | SMTP |

## 📦 Требования

- **PHP 8.2+** с расширениями: `pdo_pgsql`, `mbstring`, `gd`, `intl`, `zip`, `bcmath`, `curl`, `openssl`, `redis`
- **Composer** 2+
- **Node.js** 18+ и npm
- **PostgreSQL** 14+
- **Redis** 6+
- **S3-совместимое хранилище** (опционально, для загрузки файлов)

---

## 🚀 Быстрый старт (локально)

```bash
# 1. Клонировать репозиторий
git clone <repository-url> shiba
cd shiba

# 2. Установить зависимости
composer install
npm install

# 3. Создать .env и сгенерировать ключ приложения
cp .env.example .env
php artisan key:generate

# 4. Указать в .env доступы к PostgreSQL и Redis (см. ниже)

# 5. Выполнить миграции и базовые сидеры
php artisan migrate
php artisan db:seed

# 6. Символьная ссылка на хранилище
php artisan storage:link

# 7. Собрать фронтенд и запустить dev-окружение
npm run build
composer dev      # сервер + очередь + логи + Vite одной командой
```

Приложение: **http://localhost:8000** · админ-панель: **http://localhost:8000/admin**

> ⚠️ Это архивный код — запуск «из коробки» без донастройки окружения не гарантируется.

---

## ⚙️ Конфигурация

Основные переменные `.env`:

```dotenv
APP_NAME=ER.IIIBA
APP_ENV=production
APP_DEBUG=false
APP_URL=https://demo.iiiba.ru

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=forge
DB_PASSWORD=secret

# Redis (кеш / сессии / очереди)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

# Файловое хранилище (S3-совместимое; необязательно)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_ENDPOINT=

# Почта
MAIL_MAILER=smtp

# Демо-режим (см. раздел про демо-стенд)
DEMO_MODE=false
```

Полный список — в [.env.example](.env.example).

---

## 🌍 Развёртывание на сервере (production)

<details open>
<summary><b>Пошаговая инструкция</b></summary>

### 1. Подготовка сервера

```bash
# Пакеты (пример для Ubuntu)
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl
```

### 2. Код и зависимости

```bash
cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# отредактируйте .env: APP_ENV=production, APP_DEBUG=false, доступы к БД/Redis/S3
```

### 3. База данных

```bash
php artisan migrate --force
php artisan db:seed --force        # базовые справочники (жанры, роли, тарифы)
php artisan storage:link
```

### 4. Права на запись

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 5. Кеширование для production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### 6. Nginx (`/etc/nginx/sites-available/shiba`)

```nginx
server {
    listen 80;
    server_name demo.iiiba.ru;
    root /var/www/shiba/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/shiba /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
# HTTPS:
sudo certbot --nginx -d demo.iiiba.ru
```

### 7. Очередь (Supervisor) — `/etc/supervisor/conf.d/shiba-worker.conf`

```ini
[program:shiba-worker]
command=php /var/www/shiba/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/shiba/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start shiba-worker:*
```

### 8. Планировщик (cron) — `crontab -e`

```cron
* * * * * cd /var/www/shiba && php artisan schedule:run >> /dev/null 2>&1
```

</details>

---

## 🧪 Публичный демо-стенд (demo.iiiba.ru)

Проект умеет работать в режиме **самоочищающегося демо**: посетители свободно тестируют сайт и админку, а стенд автоматически возвращается к эталонному состоянию.

### Как это устроено

```
┌──────────────────────────────────────────────────────────────┐
│  Эталонный контент (is_demo = true)  →  сохраняется всегда   │
│  ────────────────────────────────────────────────────────────│
│  • тестовый админ и автор                                    │
│  • демо-новеллы, тома, главы, обзоры                         │
├──────────────────────────────────────────────────────────────┤
│  Контент посетителей (is_demo = false)  →  удаляется         │
│  ────────────────────────────────────────────────────────────│
│  • новеллы / тома / главы / обзоры                           │
│  • комментарии, оценки, избранное, закладки                  │
│  • форум-треды и сообщения, личные сообщения, уведомления    │
│  • аккаунты посетителей (кроме staff)                        │
│         ⟳  раз в 15 минут командой demo:cleanup             │
└──────────────────────────────────────────────────────────────┘
```

- Весь контент, добавленный посетителями, создаётся с `is_demo = false` и удаляется командой **`demo:cleanup`** (по расписанию, раз в 15 минут; в планировщике активна только при `DEMO_MODE=true`).
- Вся пользовательская активность (комментарии, оценки, форум-сообщения, ЛС, заявки и т.д.) сбрасывается **полностью** при каждом цикле.
- Эталон помечается флагом `is_demo = true` командой **`demo:snapshot`** и не удаляется. Аккаунты со staff-ролями и защищённый e-mail администратора при очистке **не трогаются**.

### Демо-доступы

| Роль | E-mail | Пароль |
|------|--------|--------|
| Администратор | `admin@localhost` | `demo12345` |
| Автор | `author@localhost` | `demo12345` |

Вход в админ-панель: **`/admin`**. Доступы меняются через `DEMO_ADMIN_EMAIL` / `DEMO_ADMIN_PASSWORD`.

> ⚠️ **`DEMO_MODE=true` включайте только на изолированном демо-окружении.** На сервере с реальными данными очистка безвозвратно удалит весь не-демо контент. По умолчанию режим выключен, а команда защищена флагом `--force`.

### Команды демо-режима

| Команда | Назначение |
|---------|-----------|
| `php artisan db:seed --class=DemoSeeder` | создать эталонные демо-данные |
| `php artisan demo:snapshot` | пометить текущий контент как эталон (`is_demo=true`) |
| `php artisan demo:snapshot --users` | дополнительно пометить всех текущих пользователей как демо |
| `php artisan demo:cleanup --force` | немедленно сбросить стенд к эталону |

Списки очищаемых/защищаемых таблиц настраиваются в [config/demo.php](config/demo.php).

---

## ⏱ Планировщик задач

Регулярные задачи — в [routes/console.php](routes/console.php):

| Команда | Расписание | Назначение |
|---------|-----------|-----------|
| `auth:clear-resets` | каждые 15 минут | очистка устаревших токенов сброса пароля |
| `app:auto-unlock-chapters` | ежедневно 06:00 | автоматическая разблокировка глав |
| `eriiba:recalc-trust` | ежедневно 03:30 | пересчёт уровней доверия пользователей |
| `demo:cleanup` | каждые 15 минут | сброс демо-стенда *(только при `DEMO_MODE=true`)* |

---

## 🔌 REST API

Публичный REST API доступен по префиксу `/api/v1` (новеллы, главы, авторы, поиск). Подробности — в [routes/api.php](routes/api.php).

---

## 🗂 Структура проекта

```
app/
├── Console/Commands/   — Artisan-команды (в т.ч. demo:cleanup, demo:snapshot)
├── Filament/           — ресурсы и страницы админ-панели
├── Forum/              — модуль форума
├── Http/               — контроллеры, middleware, реквесты
├── Livewire/           — Livewire-компоненты (каталог, кабинет автора, редакторы)
├── Models/             — Eloquent-модели
├── Notifications/      — уведомления
├── Observers/          — обсерверы моделей
├── Policies/           — политики авторизации
├── Providers/          — сервис-провайдеры
└── Services/           — доменные сервисы (изображения, импорт, рендеринг)

config/demo.php         — настройки демо-режима (списки таблиц очистки)
routes/
├── web.php             — публичные страницы, чтение, кабинет автора, модерация
├── api.php             — публичный JSON API /api/v1
└── console.php         — планировщик задач
database/
├── migrations/         — схема БД
├── seeders/            — сидеры (DatabaseSeeder, DemoSeeder, …)
└── factories/          — фабрики
resources/
├── views/              — Blade-шаблоны
├── js/                 — фронтенд-скрипты (редакторы глав)
└── css/                — стили
```

---

## 📄 Лицензия

Фреймворк Laravel распространяется по лицензии [MIT](https://opensource.org/licenses/MIT). Код приложения выложен «как есть» в ознакомительных целях; права на исходный код принадлежат автору.
