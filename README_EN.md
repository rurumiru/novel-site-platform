
<div align="center">

# 📚 ER.IIIBA

### ✦ Platform for publishing and reading online novels ✦

*Author's dashboard · chapter-by-chapter reading · forum · reviews · monetization · moderation*

<br/>

[![Open Demo](https://img.shields.io/badge/▶_OPEN_DEMO-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)
[![Telegram Support](https://img.shields.io/badge/SUPPORT-Telegram-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![Russian README](https://img.shields.io/badge/README-Русский-2B2B2B?style=for-the-badge&logo=googletranslate&logoColor=white&labelColor=0B1020)](readme.md)

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
> ### 🗄️ Archived Project — No Longer Supported
>
> This is the **old source code** that previously ran on **er.iiiba.ru**. The production site and project are **no longer functioning** — the code is provided "as-is" for informational purposes.
>
> - 🛑 The project is **not being developed** and is **not supported**
> - 🛑 **Bug fixes, issues, and pull requests are not expected**
> - 🛑 Some integrations (payments, S3, mail, external parsers) have been removed or replaced with stubs
> - 🔒 All production domains, credentials, and third-party services have been stripped and replaced with `localhost`/placeholders

<div align="center">

### 📑 Navigation

[Features](#-features) ·
[Tech Stack](#-tech-stack) ·
[Requirements](#-requirements) ·
[Quick Start](#-quick-start-local) ·
[Configuration](#️-configuration) ·
[What to Upload](#-what-to-upload-to-the-server) ·
[Deployment](#-server-deployment-production) ·
[Demo Stand](#-public-demo-stand) ·
[API](#-rest-api) ·
[Support](#-contacts-and-support)

</div>

---

## ✨ Features

> A complete ecosystem for authors, translators, and readers of online novels — from publishing and reading to community, monetization, and moderation.

<table>
<tr>
<td width="50%" valign="top">

### 📖 Reading and Catalog
- **Novel Catalog** — search and collections by genres, tags, status, popularity, and recent updates
- **Ratings and Tops** — daily, monthly, and all-time charts + live feed of new chapters
- **Convenient Reader** — chapter-by-chapter reading with themes (light / dark / sepia)
- **Reading Progress** — position is saved automatically across all devices
- **Bookmarks and Favorites** — personal library and in-chapter bookmarks
- **Age Tags** — careful 18+ filtering and hiding mature works from guests
- **Book Export** — downloading works for offline reading

</td>
<td width="50%" valign="top">

### ✍️ For Authors and Translators
- **Author's Dashboard** — creating and designing novels: cover, description, genres, tags
- **Chapter Editor** — rich text editor with formatting and image support
- **Volumes and Schedule** — delayed publishing with automatic chapter unlocking
- **Chapter Import** — mass upload and batch processing
- **Statistics** — visual analytics of views for each work
- **Teamwork** — beta readers, editors, and translation teams
- **Recruitment** — team recruiting and application pages

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 💬 Community
- **Comments** — discussions with likes, recommendations, and reports
- **Ratings and Reviews** — honest tops based on reader votes
- **Reviews** — critiques, announcements, and promotion with a showcase on the homepage
- **Forum** — sections, topics, tags, reactions, and subscriptions
- **Private Messages** — private user-to-user chats
- **Profiles and Reputation** — avatars, banners, badges, trust levels
- **Notifications** — alerts for important events

</td>
<td width="50%" valign="top">

### 💎 Monetization
- **Subscriptions** — to individual novels and author bundles
- **Paid Chapters and Balance** — pay-per-chapter purchases and unlocking
- **Plus Subscription** — premium tiers
- **Promo Codes** — flexible promo campaigns and bonuses

> *In the public version, payment processing and withdrawals are disabled — only the logic and interfaces remain.*

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🛡️ Moderation and Admin Panel
- **Admin Panel** — management of novels, chapters, users, genres, banners, plans, reviews, and the forum
- **Approval Queues** — content and application moderation
- **Roles and Permissions** — hierarchy from owner to reader
- **Trust Levels and Badges** — reputation for activity
- **Content Moderation** — checking covers, descriptions, and reviews

</td>
<td width="50%" valign="top">

### 🎨 Personalization and Access
- **Themes** — light, dark, sepia
- **Customizable Homepage** — users can arrange blocks themselves
- **Showcases and Banners** — manageable promo blocks
- **Geo-restrictions** — content availability by region
- **Multi-domain** — multiple storefronts on a single database
- **Age Verification** — proper handling of 18+ content

</td>
</tr>
</table>

### 🔌 Platform
**Public REST API** · **responsive interface** · **full Russian localization** · **self-cleaning demo mode**

---

## 🛠 Tech Stack

| Layer | Technology |
|------|-----------|
| ⚙️ Backend | PHP 8.2+, Laravel 12 |
| 🧩 Admin Panel | Filament 3 |
| ⚡ Interactive | Livewire |
| 🎨 Frontend | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| 🗄️ Database | PostgreSQL (primary) or SQLite (for demo) |
| 🚀 Cache / Queues / Sessions | Redis |
| 📦 File Storage | S3-compatible (Flysystem) |
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

## 🚀 Quick Start (Local)

```bash
# 1. Clone the repository
git clone <repository-url> shiba
cd shiba

# 2. Install dependencies
composer install
npm install

# 3. Create .env and generate application key
cp .env.example .env
php artisan key:generate

# 4. Specify DB and Redis credentials in .env (see below)

# 5. Migrations and basic seeders
php artisan migrate
php artisan db:seed

# 6. Create storage symlink
php artisan storage:link

# 7. Build frontend and start dev environment
npm run build
composer dev      # server + queue + logs + Vite in one command

🌐 Application: http://localhost:8000 · 🧩 Admin panel:
http://localhost:8000/admin

[!NOTE] This is archived code — running "out of the box" without environment
configuration is not guaranteed.

⚙️ Configuration

Main .env variables:

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

# Redis (cache / sessions / queues)
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

Full list is available in .env.example.

[!TIP] The easiest way for a demo is SQLite: no separate DB server is needed,
and you can skip Redis.

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/shiba/database/database.sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync   # tasks run immediately, no worker needed

touch database/database.sqlite && php artisan migrate --force

📤 What to Upload to the Server

The contents of this repository are sufficient. Upload:

app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/  tests/
artisan  composer.json  composer.lock  package.json  package-lock.json
vite.config.js  tailwind.config.js  phpunit.xml
.env.example  .gitignore  .gitattributes  .editorconfig  .htaccess

Created on the server (do not upload — generated by commands):

| Folder / File                      | Created by                                        |
| ---------------------------------- | ------------------------------------------------- |
| `vendor/`                          | `composer install --no-dev --optimize-autoloader` |
| `node_modules/`                    | `npm ci`                                          |
| `public/build/`                    | `npm run build` *(or build locally and upload)*   |
| `.env`                             | `cp .env.example .env` + editing                  |
| `database/database.sqlite`         | `touch` *(if using SQLite)*                       |
| `storage/**`, `bootstrap/cache/**` | runtime; grant permissions to web user            |

[!TIP] If you don't want to install Node on the server, run npm ci && npm run
build locally and upload the generated public/build/ folder.

🌍 Server Deployment (Production)

1. Server preparation (Ubuntu example)

sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl php8.2-fileinfo

2. Code and dependencies

cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# edit .env: APP_ENV=production, APP_DEBUG=false, DB/Redis/S3 credentials

3. Database

php artisan migrate --force
php artisan db:seed --force        # basic dictionaries (genres, roles, plans)
php artisan storage:link

4. Write permissions

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

5. Caching for production

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

6. Nginx (/etc/nginx/sites-available/shiba) — site root = public/

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

sudo ln -s /etc/nginx/sites-available/shiba /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d demo.iiiba.ru          # HTTPS

7. Queue (Supervisor) — not needed if QUEUE_CONNECTION=sync

[program:shiba-worker]
command=php /var/www/shiba/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
stopwaitsecs=3600

8. Scheduler (cron) — crontab -e

* * * * * cd /var/www/shiba && php artisan schedule:run >> /dev/null 2>&1

[!TIP] Having trouble with deployment? Write to me on Telegram — @licht_re.

🧪 Public Demo Stand

The project can run in a self-cleaning demo mode: visitors can freely test the
site and admin panel, and the stand automatically reverts to its baseline state.

╭──────────────────────────────────────────────────────────────╮
│  ✅ Baseline (is_demo = true)  →  always preserved             │
│     • demo accounts (admin, moderator, author, readers)        │
│     • demo novels, volumes, chapters, reviews                  │
├──────────────────────────────────────────────────────────────┤
│  🗑️ Visitor Content (is_demo = false)  →  deleted              │
│     • novels / volumes / chapters / reviews                    │
│     • comments, ratings, favorites, bookmarks                  │
│     • forum, private messages, notifications                   │
│     • visitor accounts (except staff)                          │
│          ⟳  automatically every 15 minutes                     │
╰──────────────────────────────────────────────────────────────╯

Deploying the demo stand:

# 1) in .env: DEMO_MODE=true
# 2) seed baseline data
php artisan migrate --force
php artisan db:seed --force                    # dictionaries
php artisan db:seed --class=DemoSeeder         # demo accounts, novels, chapters, reviews
# 3) snapshot the baseline
php artisan demo:snapshot
# 4) cron schedule:run will automatically trigger cleanup every 15 minutes

# manual reset at any time:
php artisan demo:cleanup --force

🔑 Demo Access

| Role          | E-mail                    | Login          | Password    |
| ------------- | ------------------------- | -------------- | ----------- |
| 👑 Owner/Admin | `admin@demo.iiiba.ru`     | `demo_admin`   | `demo12345` |
| 🛡️ Moderator  | `moderator@demo.iiiba.ru` | `demo_mod`     | `demo12345` |
| ✍️ Author     | `author@demo.iiiba.ru`    | `demo_author`  | `demo12345` |
| 📖 Reader      | `reader@demo.iiiba.ru`    | `demo_reader`  | `demo12345` |
| 📖 Reader 2    | `reader2@demo.iiiba.ru`   | `demo_reader2` | `demo12345` |

You can log in using e-mail or login (in the same field). Admin panel — /admin.
Credentials can be changed via DEMO_ADMIN_EMAIL / DEMO_ADMIN_PASSWORD.

Demo Mode Commands

| Command                                  | Purpose                                 |
| ---------------------------------------- | --------------------------------------- |
| `php artisan db:seed --class=DemoSeeder` | create baseline demo data               |
| `php artisan demo:snapshot`              | mark current content as baseline        |
| `php artisan demo:snapshot --users`      | additionally mark all users as demo     |
| `php artisan demo:cleanup --force`       | immediately reset the stand to baseline |

Lists of cleaned/protected tables are in config/demo.php.

[!CAUTION] Enable DEMO_MODE=true only on an isolated demo environment. On a
server with real data, the cleanup will permanently delete all non-demo content.
By default, the mode is disabled, and the command is protected by the --force
flag.

⏱ Task Scheduler

Regular tasks are located in routes/console.php:

| Command                    | Schedule         | Purpose                                       |
| -------------------------- | ---------------- | --------------------------------------------- |
| `auth:clear-resets`        | every 15 minutes | clear expired password reset tokens           |
| `app:auto-unlock-chapters` | daily at 06:00   | automatically unlock scheduled chapters       |
| `eriiba:recalc-trust`      | daily at 03:30   | recalculate user trust levels                 |
| `demo:cleanup`             | every 15 minutes | reset demo stand *(only if `DEMO_MODE=true`)* |

🔌 REST API

A public REST API is available under the /api/v1 prefix — novels, chapters,
authors, search. Details are in routes/api.php.

💬 Contacts and Support

The project is archived and not officially supported, but if you have questions
about deployment, you can always reach out:

Write on Telegram Open Demo

📄 License

The Laravel framework is open-sourced software licensed under the MIT license.
The application code is provided "as-is" for informational purposes; source code
rights belong to the author.

🇷🇺 The interface, content, and documentation of the project are fully in
Russian. Russian version: readme.md.
