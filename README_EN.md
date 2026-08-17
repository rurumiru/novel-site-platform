<div align="center">

# 📚 ER.IIIBA

### ✦ A Platform for Publishing and Reading Online Novels ✦

*Author dashboard · chapter reading · forum · reviews · monetization · moderation*

<br/>

[![OPEN DEMO](https://img.shields.io/badge/▶_ОТКРЫТЬ_ДЕМО-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)
[![SUPPORT в Telegram](https://img.shields.io/badge/ПОДДЕРЖКА-Telegram-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![English README](https://img.shields.io/badge/README-English-2B2B2B?style=for-the-badge&logo=googletranslate&logoColor=white&labelColor=0B1020)](readme_en.md)

<br/>

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3-FDAE4B?style=flat-square)
![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square&logo=livewire&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind-4-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?style=flat-square&logo=postgresql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-3-003B57?style=flat-square&logo=sqlite&logoColor=white)

</div>

<br/>

> [!WARNING]
> ### 🗄️ Archived Project — No Longer Maintained
>
> This is the **old source code** that previously powered **er.iiiba.ru**. The production website and project **are no longer operational** — the code is provided **as-is** for reference and educational purposes.
>
> - 🛑 The project is **no longer developed or maintained**
> - 🛑 **Bug fixes, issues, and pull requests are not expected**
> - 🛑 Some integrations (payments, S3, email, external parsers) have been removed or replaced with placeholders
> - 🔒 All production domains, credentials, and third-party services have been removed and replaced with `localhost`/placeholders

<div align="center">

### 📑 Navigation

[Features](#-возможности) ·
[Tech Stack](#-технологический-стек) ·
[Requirements](#-требования) ·
[Quick Start](#-быстрый-старт-locally) ·
[Configuration](#️-конфигурация) ·
[Что заливать](#-что-заливать-на-сервер) ·
[Deployment](#-развёртывание-на-сервере-production) ·
[Demo Environment](#-публичный-демо-стенд) ·
[API](#-rest-api) ·
[SUPPORT](#-контакты-и-поддержка)

</div>

---

## ✨ Features

> A complete ecosystem for online novel authors, translators, and readers — from publishing and reading to community features, monetization, and moderation.

<table>
<tr>
<td width="50%" valign="top">

### 📖 Reading & Catalog
- **Novel catalog** — поиск и подборки по жанрам, тегам, статусу, популярности и свежим обновлениям
- **Ratings & charts** — чарты за день, месяц и всё время + живая лента новых глав
- **Convenient reader** — чтение по главам с темами оформления (светлая / тёмная / сепия)
- **Reading progress** — позиция сохраняется автоматически, на любом устройстве
- **Bookmarks & favorites** — личная библиотека и отметки внутри глав
- **Age ratings** — аккуратная фильтрация 18+ и скрытие работ от гостей
- **Book export** — выгрузка произведений для офлайн-чтения

</td>
<td width="50%" valign="top">

### ✍️ Authors & Translators
- **Author dashboard** — создание и оформление новелл: обложка, описание, жанры, теги
- **Chapter editor** — насыщенный редактор с форматированием и картинками
- **Volumes & scheduling** — отложенная публикация с автоматическим открытием глав
- **Chapter import** — массовая загрузка и пакетная обработка
- **Statistics** — наглядная аналитика просмотров по каждой работе
- **Team collaboration** — бета-ридеры, редакторы и переводческие команды
- **Team recruitment** — страницы рекрутинга и заявок

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 💬 Community
- **Comments** — обсуждения с лайками, рекомендациями и жалобами
- **Ratings & reviews** — честные топы по голосам читателей
- **Reviews** — рецензии, анонсы и продвижение с витриной на главной
- **Forum** — разделы, темы, теги, реакции и подписки
- **Private messages** — приватная переписка
- **Profiles & reputation** — аватары, баннеры, бейджи, уровни доверия
- **Notifications** — оповещения о важных событиях

</td>
<td width="50%" valign="top">

### 💎 Monetization
- **Subscriptions** — на отдельные новеллы и авторские бандлы
- **Paid chapters & balance** — поглавная покупка и разблокировка
- **Plus subscription** — премиальные тарифы
- **Promo codes** — гибкие промокампании и бонусы

> *В публичной версии приём платежей и вывод средств отключены — остаются логика и интерфейсы.*

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🛡️ Moderation & Administration
- **Admin panel** — управление новеллами, главами, пользователями, жанрами, баннерами, тарифами, обзорами и форумом
- **Approval queues** — модерация контента и заявок
- **Roles & permissions** — иерархия от владельца до читателя
- **Trust levels & badges** — репутация за активность
- **Content moderation** — проверка обложек, описаний, обзоров

</td>
<td width="50%" valign="top">

### 🎨 Personalization & Access
- **Themes** — светлая, тёмная, сепия
- **Customizable homepage** — пользователь сам компонует блоки
- **Showcases & banners** — управляемые промо-блоки
- **Geo restrictions** — доступность контента по регионам
- **Multi-domain support** — несколько витрин на одной базе
- **Age verification** — корректная работа с 18+

</td>
</tr>
</table>

### 🔌 Platform
**Public REST API** · **responsive interface** · **full Russian localization** · **self-cleaning demo mode**

---

## 🛠 Technology Stack

| Layer | Technology |
|------|-----------|
| ⚙️ Backend | PHP 8.2+, Laravel 12 |
| 🧩 Admin Panel | Filament 3 |
| ⚡ Interactivity | Livewire |
| 🎨 Frontend | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| 🗄️ Database | PostgreSQL (основная) или SQLite (для демо) |
| 🚀 Cache / Queues / Sessions | Redis |
| 📦 File Storage | S3-совместимое (Flysystem) |
| ✉️ Email | SMTP |

---

## 📦 Requirements

- **PHP 8.2+** с расширениями: `pdo_pgsql` (или `pdo_sqlite`), `mbstring`, `gd`, `intl`, `zip`, `bcmath`, `fileinfo`, `curl`, `openssl`, `redis`
- **Composer** 2.2+
- **Node.js** 18+ и npm
- **PostgreSQL** 14+ *(или SQLite для быстрого демо)*
- **Redis** 6+ *(опционально)*
- **S3-совместимое хранилище** *(опционально, для загрузки файлов)*

---

## 🚀 Quick Start (locally)

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

# 4. Указать в .env доступы к БД и Redis (см. ниже)

# 5. Миграции и базовые сидеры
php artisan migrate
php artisan db:seed

# 6. Символьная ссылка на хранилище
php artisan storage:link

# 7. Собрать фронтенд и запустить dev-окружение
npm run build
composer dev      # сервер + очередь + логи + Vite одной командой
```

🌐 Application: **http://localhost:8000** · 🧩 Admin panel: **http://localhost:8000/admin**

> [!NOTE]
> This is archived code — running it out of the box without additional environment configuration is not guaranteed.

---

## ⚙️ Configuration

Main variables `.env`:

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

# Почта и демо-режим
MAIL_MAILER=smtp
DEMO_MODE=false
```

Complete list — в [.env.example](.env.example).

> [!TIP]
> **The simplest option for a demo is SQLite:** no separate database server is required, and Redis can be omitted.
> ```dotenv
> DB_CONNECTION=sqlite
> DB_DATABASE=/var/www/shiba/database/database.sqlite
> CACHE_STORE=file
> SESSION_DRIVER=file
> QUEUE_CONNECTION=sync   # задачи выполняются сразу, воркер не нужен
> ```
> ```bash
> touch database/database.sqlite && php artisan migrate --force
> ```

---

## 📤 What to Upload

The contents of this repository are sufficient. **Upload:**

```
app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/  tests/
artisan  composer.json  composer.lock  package.json  package-lock.json
vite.config.js  tailwind.config.js  phpunit.xml
.env.example  .gitignore  .gitattributes  .editorconfig  .htaccess
```

**Create these on the server** (не заливать — генерируется командами):

| Directory / File | Created with |
|---|---|
| `vendor/` | `composer install --no-dev --optimize-autoloader` |
| `node_modules/` | `npm ci` |
| `public/build/` | `npm run build` *(или соберите locally и залейте)* |
| `.env` | `cp .env.example .env` + правка |
| `database/database.sqlite` | `touch` *(при SQLite)* |
| `storage/**`, `bootstrap/cache/**` | runtime; grant permissions to the web user |

> [!TIP]
> Don't want to install Node.js on the server — run `npm ci && npm run build` locally и upload the resulting directory `public/build/`.

---

## 🌍 Deployment на сервере (production)

<details open>
<summary><b>📋 Step-by-Step Guide (Nginx + PHP-FPM + Redis)</b></summary>

<br/>

**1. Prepare the server (Ubuntu example)**

```bash
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl php8.2-fileinfo
```

**2. Application code and dependencies**

```bash
cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# отредактируйте .env: APP_ENV=production, APP_DEBUG=false, доступы к БД/Redis/S3
```

**3. Database**

```bash
php artisan migrate --force
php artisan db:seed --force        # базовые справочники (жанры, роли, тарифы)
php artisan storage:link
```

**4. Write permissions**

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**5. Production caching**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

**6. Nginx** (`/etc/nginx/sites-available/shiba`) — site root = `public/`

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
sudo certbot --nginx -d demo.iiiba.ru          # HTTPS
```

**7. Queue** (Supervisor) — not required when using `QUEUE_CONNECTION=sync`

```ini
[program:shiba-worker]
command=php /var/www/shiba/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
stopwaitsecs=3600
```

**8. Scheduler** (cron) — `crontab -e`

```cron
* * * * * cd /var/www/shiba && php artisan schedule:run >> /dev/null 2>&1
```

</details>

> [!TIP]
> Having trouble with deployment? Напишите в Telegram — **[@licht_re](https://t.me/licht_re)**.

---

## 🧪 Public Demo Environment

Проект умеет работать в режиме **самоочищающегося демо**: посетители свободно тестируют сайт и админку, а стенд автоматически возвращается к эталонному состоянию.

```
╭──────────────────────────────────────────────────────────────╮
│  ✅ Эталон (is_demo = true)  →  сохраняется всегда             │
│     • демо-аккаунты (админ, модератор, автор, читатели)        │
│     • демо-новеллы, тома, главы, обзоры                        │
├──────────────────────────────────────────────────────────────┤
│  🗑️ Контент посетителей (is_demo = false)  →  удаляется        │
│     • новеллы / тома / главы / обзоры                          │
│     • комментарии, оценки, избранное, закладки                 │
│     • форум, личные сообщения, уведомления                     │
│     • аккаунты посетителей (кроме staff)                       │
│          ⟳  автоматически раз в 15 минут                       │
╰──────────────────────────────────────────────────────────────╯
```

**Deployment демо-стенда:**

```bash
# 1) в .env: DEMO_MODE=true
# 2) залить эталонные данные
php artisan migrate --force
php artisan db:seed --force                    # справочники
php artisan db:seed --class=DemoSeeder         # демо-аккаунты, новеллы, главы, обзоры
# 3) зафиксировать эталон
php artisan demo:snapshot
# 4) cron schedule:run сам запустит очистку каждые 15 минут

# ручной сброс в любой момент:
php artisan demo:cleanup --force
```

### 🔑 Demo Credentials

| Role | E-mail | Login | Password |
|------|--------|-------|--------|
| 👑 Owner/Admin | `admin@demo.iiiba.ru` | `demo_admin` | `demo12345` |
| 🛡️ Moderator | `moderator@demo.iiiba.ru` | `demo_mod` | `demo12345` |
| ✍️ Author | `author@demo.iiiba.ru` | `demo_author` | `demo12345` |
| 📖 Reader | `reader@demo.iiiba.ru` | `demo_reader` | `demo12345` |
| 📖 Reader 2 | `reader2@demo.iiiba.ru` | `demo_reader2` | `demo12345` |

Войти можно по e-mail **или** по логину (в одно поле). Admin panel — `/admin`. Credentials can be changed using `DEMO_ADMIN_EMAIL` / `DEMO_ADMIN_PASSWORD`.

### Demo Mode Commands

| Команда | Purpose |
|---------|-----------|
| `php artisan db:seed --class=DemoSeeder` | create the reference demo data |
| `php artisan demo:snapshot` | mark the current content as the reference state |
| `php artisan demo:snapshot --users` | additionally mark all users as demo users |
| `php artisan demo:cleanup --force` | immediately reset the environment to the reference state |

The lists of tables to be cleaned/protected — в [config/demo.php](config/demo.php).

> [!CAUTION]
> **`DEMO_MODE=true` enable only in an isolated demo environment.** On a server containing real data, cleanup will permanently delete all non-demo content. The mode is disabled by default, а команда защищена флагом `--force`.

---

## ⏱ Scheduler задач

Scheduled tasks — в [routes/console.php](routes/console.php):

| Команда | Schedule | Purpose |
|---------|-----------|-----------|
| `auth:clear-resets` | every 15 minutes | remove expired password reset tokens |
| `app:auto-unlock-chapters` | ежедневно 06:00 | automatically unlock chapters |
| `eriiba:recalc-trust` | ежедневно 03:30 | recalculate trust levels |
| `demo:cleanup` | every 15 minutes | reset the demo environment *(only when `DEMO_MODE=true`)* |

---

## 🔌 REST API

Public REST API is available under the prefix `/api/v1` — novels, chapters, authors, and search. Details — в [routes/api.php](routes/api.php).

---

<div align="center">

## 💬 Contact & Support

The project is archived and officially unsupported, but deployment-related questions can still be sent to:

[![Написать в Telegram](https://img.shields.io/badge/НАПИСАТЬ_В_TELEGRAM-@licht__re-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![OPEN DEMO](https://img.shields.io/badge/ОТКРЫТЬ_ДЕМО-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)

</div>

---

## 📄 License

The Laravel framework is distributed under the [MIT](https://opensource.org/licenses/MIT). The application code is provided "as-is" for reference and educational purposes; the rights to the source code belong to its author.

> 🇷🇺 The project's interface, content, and documentation are **entirely in Russian**. English version: **[readme_en.md](readme_en.md)**.

<div align="center">
<sub>Made with ❤️ for the online novel community · archived er.iiiba.ru project</sub>
</div>
