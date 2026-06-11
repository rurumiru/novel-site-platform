<div align="center">

# 📚 ER.IIIBA

**A platform for publishing and reading web novels**

Author dashboard · chapter-by-chapter reading · forum · reviews · moderation · admin panel

<br>

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3-FDAE4B)
![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9)
![Tailwind](https://img.shields.io/badge/Tailwind-4-38BDF8?logo=tailwindcss&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16+-4169E1?logo=postgresql&logoColor=white)
![License](https://img.shields.io/badge/license-archive-lightgrey)

### 🌐 Live demo: **[demo.iiiba.ru](https://demo.iiiba.ru)**

💬 Deployment questions — Telegram: **[@licht_re](https://t.me/licht_re)**

🇷🇺 *Russian README:* **[README.md](README.md)**

</div>

---

> ## 🇷🇺 The project is entirely in Russian
>
> The user interface, all content, comments in code and documentation are **fully in Russian**.
> This file is an English description of the project; the application itself is **not localized to English**.

---

> ## ⚠️ Archived project — no longer maintained
>
> This is **legacy source code** that previously powered the site **er.iiiba.ru**.
> The production site and the project **no longer operate**. The code is published **as-is** for reference purposes only.
>
> | | |
> |---|---|
> | 🛑 | The project is **not developed** and **not maintained** |
> | 🛑 | **Bug fixes, issue responses and pull requests are not expected** |
> | 🛑 | Functionality is not guaranteed: some integrations (payments, S3, mail, external parsers) have been removed or replaced with stubs |
> | 🔒 | All references to production domains, credentials and third-party services have been stripped from the code and replaced with `localhost`/placeholders |
>
> Use it only as a sample of Laravel application architecture.

---

## 📑 Table of contents

- [Features](#-features)
- [Tech stack](#-tech-stack)
- [Requirements](#-requirements)
- [Quick start (local)](#-quick-start-local)
- [Configuration](#-configuration)
- [What to upload to the server](#-what-to-upload-to-the-server)
- [Server deployment (production)](#-server-deployment-production)
- [🧪 Public demo stand (demo.iiiba.ru)](#-public-demo-stand-demoiiibaru)
- [Task scheduler](#-task-scheduler)
- [REST API](#-rest-api)
- [Project structure](#-project-structure)
- [Contacts & support](#-contacts--support)
- [License](#-license)

---

## ✨ Features

- **Catalog & reading** — novels, volumes and chapters, reading progress, bookmarks, favorites.
- **Author dashboard** — create and edit novels, import chapters, statistics.
- **Monetization** — novel subscriptions, per-chapter purchases, promo codes. *(Payment intake and withdrawals are removed in this public version.)*
- **Social features** — comments, ratings, reviews, forum.
- **Collaboration** — beta readers, novel editors, translation teams.
- **Moderation & roles** — roles and permissions via `spatie/laravel-permission`, trust levels, badges.
- **Admin panel** — Filament.
- **Geo-restrictions** — middleware that blocks access to restricted content by region.
- **Multi-domain** — database selection per domain via middleware.
- **Public REST API** — `/api/v1` (novels, chapters, authors, search).

## 🛠 Tech stack

| Layer | Technology |
|------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Admin | Filament 3 |
| Interactivity | Livewire |
| Frontend | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| Database | PostgreSQL (primary), optional sync to MySQL |
| Cache / queue / sessions | Redis |
| File storage | S3-compatible (Flysystem) |
| Mail | SMTP |

## 📦 Requirements

- **PHP 8.2+** with extensions: `pdo_pgsql` (or `pdo_sqlite` for the demo), `mbstring`, `gd`, `intl`, `zip`, `bcmath`, `curl`, `openssl`, `redis`
- **Composer** 2+
- **Node.js** 18+ and npm
- **PostgreSQL** 14+ *(or SQLite for a quick demo)*
- **Redis** 6+ *(optional — see SQLite section)*
- **S3-compatible storage** (optional, for file uploads)

---

## 🚀 Quick start (local)

```bash
# 1. Clone the repository
git clone <repository-url> shiba
cd shiba

# 2. Install dependencies
composer install
npm install

# 3. Create .env and generate the application key
cp .env.example .env
php artisan key:generate

# 4. Set PostgreSQL and Redis credentials in .env (see below)

# 5. Run migrations and base seeders
php artisan migrate
php artisan db:seed

# 6. Symbolic link to storage
php artisan storage:link

# 7. Build the frontend and start the dev environment
npm run build
composer dev      # server + queue + logs + Vite in a single command
```

App: **http://localhost:8000** · admin panel: **http://localhost:8000/admin**

> ⚠️ This is archived code — running it out of the box without tuning the environment is not guaranteed.

---

## ⚙️ Configuration

Key `.env` variables:

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

# Redis (cache / sessions / queue)
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1

# File storage (S3-compatible; optional)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_ENDPOINT=

# Mail
MAIL_MAILER=smtp

# Demo mode (see the demo stand section)
DEMO_MODE=false
```

Full list — in [.env.example](.env.example).

### SQLite option (quick demo launch, no PostgreSQL)

For a demo stand SQLite is the simplest choice — no separate database server is required:

```dotenv
DB_CONNECTION=sqlite
# absolute path to the DB file (for sqlite this is a path, not a database name)
DB_DATABASE=/var/www/shiba/database/database.sqlite

# You can also do without Redis:
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync   # jobs run immediately, no queue worker needed
```

```bash
# create the DB file and run migrations
touch database/database.sqlite
php artisan migrate --force
```

> With `QUEUE_CONNECTION=sync` a separate `queue:work`/Supervisor process is not needed — that is the simplest path for a demo.

---

## 📤 What to upload to the server

The contents of this repository are enough. **Upload** (folders and files):

```
app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/  tests/
artisan  composer.json  composer.lock  package.json  package-lock.json
vite.config.js  tailwind.config.js  phpunit.xml
.env.example  .gitignore  .gitattributes  .editorconfig  .htaccess
```

**Created on the server** (do not upload — generated by commands):

| Folder / file | Generated by |
|---|---|
| `vendor/` | `composer install --no-dev --optimize-autoloader` |
| `node_modules/` | `npm ci` |
| `public/build/` | `npm run build` *(or build locally and upload this folder)* |
| `.env` | `cp .env.example .env` + editing |
| `database/database.sqlite` | `touch` *(when using SQLite)* |
| `storage/**`, `bootstrap/cache/**` | runtime; grant write permissions to the web user |

> If you don't want to install Node on the server — run `npm ci && npm run build` locally and upload the resulting `public/build/` folder along with the rest.

---

## 🌍 Server deployment (production)

<details open>
<summary><b>Step-by-step</b></summary>

### 1. Prepare the server

```bash
# Packages (Ubuntu example)
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl
```

### 2. Code and dependencies

```bash
cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# edit .env: APP_ENV=production, APP_DEBUG=false, DB/Redis/S3 credentials
```

### 3. Database

```bash
php artisan migrate --force
php artisan db:seed --force        # base reference data (genres, roles, plans)
php artisan storage:link
```

### 4. Write permissions

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 5. Caching for production

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

### 7. Queue (Supervisor) — `/etc/supervisor/conf.d/shiba-worker.conf`

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

> Not needed when `QUEUE_CONNECTION=sync` (SQLite demo).

### 8. Scheduler (cron) — `crontab -e`

```cron
* * * * * cd /var/www/shiba && php artisan schedule:run >> /dev/null 2>&1
```

</details>

---

> 💬 Having trouble with deployment? Reach out on Telegram: **[@licht_re](https://t.me/licht_re)**

---

## 🧪 Public demo stand (demo.iiiba.ru)

The project can run in a **self-resetting demo** mode: visitors freely test the site and admin panel, while the stand automatically returns to its reference state.

### How it works

```
┌──────────────────────────────────────────────────────────────┐
│  Reference content (is_demo = true)  →  always preserved       │
│  ───────────────────────────────────────────────────────────  │
│  • test admin and author                                       │
│  • demo novels, volumes, chapters, reviews                     │
├──────────────────────────────────────────────────────────────┤
│  Visitor content (is_demo = false)  →  deleted                 │
│  ───────────────────────────────────────────────────────────  │
│  • novels / volumes / chapters / reviews                       │
│  • comments, ratings, favorites, bookmarks                     │
│  • forum threads and posts, private messages, notifications    │
│  • visitor accounts (except staff)                             │
│         ⟳  every 15 minutes via demo:cleanup                   │
└──────────────────────────────────────────────────────────────┘
```

- All content added by visitors is created with `is_demo = false` and removed by the **`demo:cleanup`** command (scheduled every 15 minutes; in the scheduler it runs only when `DEMO_MODE=true`).
- All user activity (comments, ratings, forum posts, private messages, applications, etc.) is **fully** reset on every cycle.
- The reference state is marked with the `is_demo = true` flag via the **`demo:snapshot`** command and is never deleted. Accounts with staff roles and the protected admin e-mail are **not touched** during cleanup.

### Deploying the demo stand

```bash
# 1. Enable demo mode in .env
DEMO_MODE=true
# optionally set your own test-admin credentials:
# DEMO_ADMIN_EMAIL=admin@localhost
# DEMO_ADMIN_PASSWORD=demo12345

# 2. Load the reference data
php artisan migrate --force
php artisan db:seed --force                    # reference data
php artisan db:seed --class=DemoSeeder         # test admin, author, novels, chapters, reviews
# (optionally also: php artisan db:seed --class=ForumSeeder)

# 3. Lock the current state as the reference
php artisan demo:snapshot

# 4. Make sure cron runs the scheduler (see the server section)
#    the demo cleanup will then run automatically every 15 minutes

# Manual reset at any time:
php artisan demo:cleanup --force
```

### Demo credentials

| Role | E-mail | Password |
|------|--------|----------|
| Administrator | `admin@localhost` | `demo12345` |
| Author | `author@localhost` | `demo12345` |

Admin panel login: **`/admin`**. Credentials are configurable via `DEMO_ADMIN_EMAIL` / `DEMO_ADMIN_PASSWORD`.

> ⚠️ **Enable `DEMO_MODE=true` only on an isolated demo environment.** On a server with real data the cleanup will irreversibly delete all non-demo content. The mode is disabled by default, and the command is protected by the `--force` flag.

### Demo-mode commands

| Command | Purpose |
|---------|---------|
| `php artisan db:seed --class=DemoSeeder` | create reference demo data |
| `php artisan demo:snapshot` | mark current content as reference (`is_demo=true`) |
| `php artisan demo:snapshot --users` | additionally mark all current users as demo |
| `php artisan demo:cleanup --force` | immediately reset the stand to the reference state |

The lists of tables to clean/protect are configured in [config/demo.php](config/demo.php).

---

## ⏱ Task scheduler

Recurring tasks are in [routes/console.php](routes/console.php):

| Command | Schedule | Purpose |
|---------|----------|---------|
| `auth:clear-resets` | every 15 minutes | clear stale password-reset tokens |
| `app:auto-unlock-chapters` | daily 06:00 | automatic chapter unlocking |
| `eriiba:recalc-trust` | daily 03:30 | recalculate user trust levels |
| `demo:cleanup` | every 15 minutes | reset the demo stand *(only when `DEMO_MODE=true`)* |

---

## 🔌 REST API

A public REST API is available under the `/api/v1` prefix (novels, chapters, authors, search). Details — in [routes/api.php](routes/api.php).

---

## 🗂 Project structure

```
app/
├── Console/Commands/   — Artisan commands (incl. demo:cleanup, demo:snapshot)
├── Filament/           — admin panel resources and pages
├── Forum/              — forum module
├── Http/               — controllers, middleware, requests
├── Livewire/           — Livewire components (catalog, author dashboard, editors)
├── Models/             — Eloquent models
├── Notifications/      — notifications
├── Observers/          — model observers
├── Policies/           — authorization policies
├── Providers/          — service providers
└── Services/           — domain services (images, import, rendering)

config/demo.php         — demo-mode settings (cleanup table lists)
routes/
├── web.php             — public pages, reading, author dashboard, moderation
├── api.php             — public JSON API /api/v1
└── console.php         — task scheduler
database/
├── migrations/         — DB schema
├── seeders/            — seeders (DatabaseSeeder, DemoSeeder, …)
└── factories/          — factories
resources/
├── views/              — Blade templates
├── js/                 — frontend scripts (chapter editors)
└── css/                — styles
```

---

## 💬 Contacts & support

The project is archived and officially unsupported, but for **deployment** questions you can reach out on Telegram:

**→ [@licht_re](https://t.me/licht_re)**

---

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). The application code is published as-is for reference purposes; the rights to the source code belong to the author.

> 🇷🇺 The project's interface, content and documentation are **entirely in Russian**. Russian README: [README.md](README.md).
