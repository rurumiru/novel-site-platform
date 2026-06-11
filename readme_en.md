<div align="center">

# 📚 ER.IIIBA

### ✦ A platform for publishing and reading web novels ✦

*Author dashboard · chapter reading · forum · reviews · monetization · moderation*

<br/>

[![Open demo](https://img.shields.io/badge/▶_OPEN_DEMO-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)
[![Telegram support](https://img.shields.io/badge/SUPPORT-Telegram-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![Russian README](https://img.shields.io/badge/README-Русский-2B2B2B?style=for-the-badge&logo=googletranslate&logoColor=white&labelColor=0B1020)](README.md)

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

> [!IMPORTANT]
> ### 🇷🇺 The project is entirely in Russian
> The interface, all content and the in-app texts are **fully in Russian**. This file is an English description of the project; the application itself is **not localized to English**.

> [!WARNING]
> ### 🗄️ Archived project — no longer maintained
>
> This is **legacy source code** that previously powered the site **er.iiiba.ru**. The production site and the project **no longer operate** — the code is published **as-is** for reference purposes only.
>
> - 🛑 The project is **not developed** and **not maintained**
> - 🛑 **Bug fixes, issues and pull requests are not expected**
> - 🛑 Some integrations (payments, S3, mail, external parsers) are removed or replaced with stubs
> - 🔒 All production domains, credentials and third-party services are stripped and replaced with `localhost`/placeholders

<div align="center">

### 📑 Navigation

[Features](#-features) ·
[Stack](#-tech-stack) ·
[Requirements](#-requirements) ·
[Quick start](#-quick-start-local) ·
[Configuration](#️-configuration) ·
[What to upload](#-what-to-upload-to-the-server) ·
[Deployment](#-server-deployment-production) ·
[Demo stand](#-public-demo-stand) ·
[API](#-rest-api) ·
[Support](#-contacts--support)

</div>

---

## ✨ Features

> A complete ecosystem for authors, translators and readers of web novels — from publishing and reading to community, monetization and moderation.

<table>
<tr>
<td width="50%" valign="top">

### 📖 Reading & catalog
- **Novel catalog** — search and curated lists by genre, tags, status, popularity and fresh updates
- **Ratings & charts** — daily, monthly and all-time charts + a live feed of new chapters
- **Comfortable reader** — chapter reading with display themes (light / dark / sepia)
- **Reading progress** — position saved automatically, on any device
- **Bookmarks & favorites** — a personal library and in-chapter marks
- **Age labels** — accurate 18+ filtering and hiding works from guests
- **Book export** — download works for offline reading

</td>
<td width="50%" valign="top">

### ✍️ For authors & translators
- **Author dashboard** — create and style novels: cover, description, genres, tags
- **Chapter editor** — a rich editor with formatting and images
- **Volumes & scheduling** — delayed publishing with automatic chapter release
- **Chapter import** — bulk upload and batch processing
- **Statistics** — clear view analytics per work
- **Team collaboration** — beta readers, editors and translation teams
- **Recruitment** — recruitment and application pages

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 💬 Community
- **Comments** — discussions with likes, recommendations and reports
- **Ratings** — honest charts driven by reader votes
- **Reviews** — reviews, announcements and promotion with a homepage showcase
- **Forum** — sections, threads, tags, reactions and subscriptions
- **Private messages** — private conversations
- **Profiles & reputation** — avatars, banners, badges, trust levels
- **Notifications** — alerts about what matters

</td>
<td width="50%" valign="top">

### 💎 Monetization
- **Subscriptions** — to individual novels and author bundles
- **Paid chapters & balance** — per-chapter purchases and unlocking
- **Plus subscription** — premium tiers
- **Promo codes** — flexible campaigns and bonuses

> *In this public version payment intake and withdrawals are disabled — the logic and interfaces remain.*

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🛡️ Moderation & admin
- **Admin panel** — manage novels, chapters, users, genres, banners, plans, reviews and the forum
- **Approval queues** — moderation of content and requests
- **Roles & permissions** — a hierarchy from owner to reader
- **Trust levels & badges** — reputation for activity
- **Content moderation** — review of covers, descriptions, reviews

</td>
<td width="50%" valign="top">

### 🎨 Personalization & access
- **Display themes** — light, dark, sepia
- **Customizable homepage** — users arrange home blocks themselves
- **Showcases & banners** — managed promo blocks
- **Geo-restrictions** — content availability by region
- **Multi-domain** — several storefronts on one codebase
- **Age verification** — proper handling of an 18+ audience

</td>
</tr>
</table>

### 🔌 Platform
**Public REST API** · **responsive interface** · **full Russian localization** · **self-resetting demo mode**

---

## 🛠 Tech stack

| Layer | Technology |
|------|-----------|
| ⚙️ Backend | PHP 8.2+, Laravel 12 |
| 🧩 Admin | Filament 3 |
| ⚡ Interactivity | Livewire |
| 🎨 Frontend | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| 🗄️ Database | PostgreSQL (primary) or SQLite (for the demo) |
| 🚀 Cache / queue / sessions | Redis |
| 📦 File storage | S3-compatible (Flysystem) |
| ✉️ Mail | SMTP |

---

## 📦 Requirements

- **PHP 8.2+** with extensions: `pdo_pgsql` (or `pdo_sqlite`), `mbstring`, `gd`, `intl`, `zip`, `bcmath`, `fileinfo`, `curl`, `openssl`, `redis`
- **Composer** 2.2+
- **Node.js** 18+ and npm
- **PostgreSQL** 14+ *(or SQLite for a quick demo)*
- **Redis** 6+ *(optional)*
- **S3-compatible storage** *(optional, for file uploads)*

---

## 🚀 Quick start (local)

```bash
# 1. Clone the repository
git clone <repository-url> shiba
cd shiba

# 2. Install dependencies
composer install
npm install

# 3. Create .env and generate the app key
cp .env.example .env
php artisan key:generate

# 4. Set DB and Redis credentials in .env (see below)

# 5. Migrations and base seeders
php artisan migrate
php artisan db:seed

# 6. Symbolic link to storage
php artisan storage:link

# 7. Build the frontend and start the dev environment
npm run build
composer dev      # server + queue + logs + Vite in one command
```

🌐 App: **http://localhost:8000** · 🧩 admin panel: **http://localhost:8000/admin**

> [!NOTE]
> This is archived code — running it out of the box without tuning the environment is not guaranteed.

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

# Mail and demo mode
MAIL_MAILER=smtp
DEMO_MODE=false
```

Full list — in [.env.example](.env.example).

> [!TIP]
> **The simplest path for a demo is SQLite:** no separate database server, and you can skip Redis too.
> ```dotenv
> DB_CONNECTION=sqlite
> DB_DATABASE=/var/www/shiba/database/database.sqlite
> CACHE_STORE=file
> SESSION_DRIVER=file
> QUEUE_CONNECTION=sync   # jobs run immediately, no worker needed
> ```
> ```bash
> touch database/database.sqlite && php artisan migrate --force
> ```

---

## 📤 What to upload to the server

The contents of this repository are enough. **Upload:**

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
| `public/build/` | `npm run build` *(or build locally and upload)* |
| `.env` | `cp .env.example .env` + editing |
| `database/database.sqlite` | `touch` *(when using SQLite)* |
| `storage/**`, `bootstrap/cache/**` | runtime; grant write access to the web user |

> [!TIP]
> Don't want Node on the server — run `npm ci && npm run build` locally and upload the resulting `public/build/` folder.

---

## 🌍 Server deployment (production)

<details open>
<summary><b>📋 Step-by-step (Nginx + PHP-FPM + Redis)</b></summary>

<br/>

**1. Prepare the server (Ubuntu example)**

```bash
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl php8.2-fileinfo
```

**2. Code and dependencies**

```bash
cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# edit .env: APP_ENV=production, APP_DEBUG=false, DB/Redis/S3 credentials
```

**3. Database**

```bash
php artisan migrate --force
php artisan db:seed --force        # base reference data (genres, roles, plans)
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

**7. Queue** (Supervisor) — not needed when `QUEUE_CONNECTION=sync`

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
> Having trouble with deployment? Reach out on Telegram — **[@licht_re](https://t.me/licht_re)**.

---

## 🧪 Public demo stand

The project can run in a **self-resetting demo** mode: visitors freely test the site and admin panel, while the stand automatically returns to its reference state.

```
╭──────────────────────────────────────────────────────────────╮
│  ✅ Reference (is_demo = true)  →  always preserved            │
│     • demo accounts (admin, moderator, author, readers)        │
│     • demo novels, volumes, chapters, reviews                  │
├──────────────────────────────────────────────────────────────┤
│  🗑️ Visitor content (is_demo = false)  →  deleted              │
│     • novels / volumes / chapters / reviews                    │
│     • comments, ratings, favorites, bookmarks                  │
│     • forum, private messages, notifications                   │
│     • visitor accounts (except staff)                          │
│          ⟳  automatically every 15 minutes                     │
╰──────────────────────────────────────────────────────────────╯
```

**Deploying the demo stand:**

```bash
# 1) in .env: DEMO_MODE=true
# 2) load the reference data
php artisan migrate --force
php artisan db:seed --force                    # reference data
php artisan db:seed --class=DemoSeeder         # demo accounts, novels, chapters, reviews
# 3) lock the reference state
php artisan demo:snapshot
# 4) cron schedule:run runs the cleanup every 15 minutes

# manual reset at any time:
php artisan demo:cleanup --force
```

### 🔑 Demo credentials

| Role | E-mail | Login | Password |
|------|--------|-------|----------|
| 👑 Owner/admin | `admin@demo.iiiba.ru` | `demo_admin` | `demo12345` |
| 🛡️ Moderator | `moderator@demo.iiiba.ru` | `demo_mod` | `demo12345` |
| ✍️ Author | `author@demo.iiiba.ru` | `demo_author` | `demo12345` |
| 📖 Reader | `reader@demo.iiiba.ru` | `demo_reader` | `demo12345` |
| 📖 Reader 2 | `reader2@demo.iiiba.ru` | `demo_reader2` | `demo12345` |

You can log in with the e-mail **or** the login (single field). Admin panel — `/admin`. Credentials are configurable via `DEMO_ADMIN_EMAIL` / `DEMO_ADMIN_PASSWORD`.

### Demo-mode commands

| Command | Purpose |
|---------|---------|
| `php artisan db:seed --class=DemoSeeder` | create reference demo data |
| `php artisan demo:snapshot` | mark current content as the reference |
| `php artisan demo:snapshot --users` | also mark all users as demo |
| `php artisan demo:cleanup --force` | immediately reset the stand to the reference |

Cleanup/protected table lists — in [config/demo.php](config/demo.php).

> [!CAUTION]
> **Enable `DEMO_MODE=true` only on an isolated demo environment.** On a server with real data the cleanup will irreversibly delete all non-demo content. The mode is disabled by default, and the command is protected by the `--force` flag.

---

## ⏱ Task scheduler

Recurring tasks — in [routes/console.php](routes/console.php):

| Command | Schedule | Purpose |
|---------|----------|---------|
| `auth:clear-resets` | every 15 minutes | clear stale password-reset tokens |
| `app:auto-unlock-chapters` | daily 06:00 | automatic chapter unlocking |
| `eriiba:recalc-trust` | daily 03:30 | recalculate user trust levels |
| `demo:cleanup` | every 15 minutes | reset the demo stand *(only when `DEMO_MODE=true`)* |

---

## 🔌 REST API

A public REST API is available under the `/api/v1` prefix — novels, chapters, authors, search. Details — in [routes/api.php](routes/api.php).

---

<div align="center">

## 💬 Contacts & support

The project is archived and officially unsupported, but for **deployment** questions you can always reach out:

[![Message on Telegram](https://img.shields.io/badge/MESSAGE_ON_TELEGRAM-@licht__re-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![Open demo](https://img.shields.io/badge/OPEN_DEMO-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)

</div>

---

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). The application code is published as-is for reference purposes; the rights to the source code belong to the author.

> 🇷🇺 The project's interface, content and documentation are **entirely in Russian**. Русская версия: **[README.md](README.md)**.

<div align="center">
<sub>Made with ❤️ for the web-novel community · archive of er.iiiba.ru</sub>
</div>
