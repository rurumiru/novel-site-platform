<div align="center">

# 📚 ER.IIIBA

### ✦ Платформа для публикации и чтения онлайн-новелл ✦

*Авторский кабинет · чтение по главам · форум · обзоры · монетизация · модерация*

<br/>

[![Открыть демо](https://img.shields.io/badge/▶_ОТКРЫТЬ_ДЕМО-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)
[![Поддержка в Telegram](https://img.shields.io/badge/ПОДДЕРЖКА-Telegram-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
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
> ### 🗄️ Архивный проект — больше не поддерживается
>
> Это **старый исходный код**, который ранее работал на сайте **er.iiiba.ru**. Боевой сайт и проект **больше не функционируют** — код выложен «как есть» (as-is) в ознакомительных целях.
>
> - 🛑 Проект **не развивается** и **не поддерживается**
> - 🛑 **Исправление ошибок, issue и pull-request'ы не предполагаются**
> - 🛑 Часть интеграций (платежи, S3, почта, внешние парсеры) удалена или заменена заглушками
> - 🔒 Все боевые домены, реквизиты и сторонние сервисы вырезаны и заменены на `localhost`/placeholder'ы

<div align="center">

### 📑 Навигация

[Возможности](#-возможности) ·
[Стек](#-технологический-стек) ·
[Требования](#-требования) ·
[Быстрый старт](#-быстрый-старт-локально) ·
[Конфигурация](#️-конфигурация) ·
[Что заливать](#-что-заливать-на-сервер) ·
[Развёртывание](#-развёртывание-на-сервере-production) ·
[Демо-стенд](#-публичный-демо-стенд) ·
[API](#-rest-api) ·
[Поддержка](#-контакты-и-поддержка)

</div>

---

## ✨ Возможности

> Полноценная экосистема для авторов, переводчиков и читателей онлайн-новелл — от публикации и чтения до сообщества, монетизации и модерации.

<table>
<tr>
<td width="50%" valign="top">

### 📖 Чтение и каталог
- **Каталог новелл** — поиск и подборки по жанрам, тегам, статусу, популярности и свежим обновлениям
- **Рейтинги и топы** — чарты за день, месяц и всё время + живая лента новых глав
- **Удобная читалка** — чтение по главам с темами оформления (светлая / тёмная / сепия)
- **Прогресс чтения** — позиция сохраняется автоматически, на любом устройстве
- **Закладки и избранное** — личная библиотека и отметки внутри глав
- **Возрастные метки** — аккуратная фильтрация 18+ и скрытие работ от гостей
- **Экспорт книг** — выгрузка произведений для офлайн-чтения

</td>
<td width="50%" valign="top">

### ✍️ Авторам и переводчикам
- **Кабинет автора** — создание и оформление новелл: обложка, описание, жанры, теги
- **Редактор глав** — насыщенный редактор с форматированием и картинками
- **Тома и расписание** — отложенная публикация с автоматическим открытием глав
- **Импорт глав** — массовая загрузка и пакетная обработка
- **Статистика** — наглядная аналитика просмотров по каждой работе
- **Командная работа** — бета-ридеры, редакторы и переводческие команды
- **Набор в команду** — страницы рекрутинга и заявок

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 💬 Сообщество
- **Комментарии** — обсуждения с лайками, рекомендациями и жалобами
- **Оценки и рейтинги** — честные топы по голосам читателей
- **Обзоры** — рецензии, анонсы и продвижение с витриной на главной
- **Форум** — разделы, темы, теги, реакции и подписки
- **Личные сообщения** — приватная переписка
- **Профили и репутация** — аватары, баннеры, бейджи, уровни доверия
- **Уведомления** — оповещения о важных событиях

</td>
<td width="50%" valign="top">

### 💎 Монетизация
- **Подписки** — на отдельные новеллы и авторские бандлы
- **Платные главы и баланс** — поглавная покупка и разблокировка
- **Plus-подписка** — премиальные тарифы
- **Промокоды** — гибкие промокампании и бонусы

> *В публичной версии приём платежей и вывод средств отключены — остаются логика и интерфейсы.*

</td>
</tr>
<tr>
<td width="50%" valign="top">

### 🛡️ Модерация и админка
- **Админ-панель** — управление новеллами, главами, пользователями, жанрами, баннерами, тарифами, обзорами и форумом
- **Очереди на одобрение** — модерация контента и заявок
- **Роли и права** — иерархия от владельца до читателя
- **Уровни доверия и бейджи** — репутация за активность
- **Модерация контента** — проверка обложек, описаний, обзоров

</td>
<td width="50%" valign="top">

### 🎨 Персонализация и доступ
- **Темы оформления** — светлая, тёмная, сепия
- **Настраиваемая главная** — пользователь сам компонует блоки
- **Витрины и баннеры** — управляемые промо-блоки
- **Гео-ограничения** — доступность контента по регионам
- **Мультидоменность** — несколько витрин на одной базе
- **Возрастная верификация** — корректная работа с 18+

</td>
</tr>
</table>

### 🔌 Платформа
**Публичный REST API** · **адаптивный интерфейс** · **полная русская локализация** · **самоочищающийся демо-режим**

---

## 🛠 Технологический стек

| Слой | Технология |
|------|-----------|
| ⚙️ Backend | PHP 8.2+, Laravel 12 |
| 🧩 Админка | Filament 3 |
| ⚡ Интерактив | Livewire |
| 🎨 Фронтенд | Tailwind CSS 4, Vite 7, Quill, markdown-it |
| 🗄️ База данных | PostgreSQL (основная) или SQLite (для демо) |
| 🚀 Кеш / очереди / сессии | Redis |
| 📦 Хранилище файлов | S3-совместимое (Flysystem) |
| ✉️ Почта | SMTP |

---

## 📦 Требования

- **PHP 8.2+** с расширениями: `pdo_pgsql` (или `pdo_sqlite`), `mbstring`, `gd`, `intl`, `zip`, `bcmath`, `fileinfo`, `curl`, `openssl`, `redis`
- **Composer** 2.2+
- **Node.js** 18+ и npm
- **PostgreSQL** 14+ *(или SQLite для быстрого демо)*
- **Redis** 6+ *(опционально)*
- **S3-совместимое хранилище** *(опционально, для загрузки файлов)*

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

🌐 Приложение: **http://localhost:8000** · 🧩 админ-панель: **http://localhost:8000/admin**

> [!NOTE]
> Это архивный код — запуск «из коробки» без донастройки окружения не гарантируется.

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

# Почта и демо-режим
MAIL_MAILER=smtp
DEMO_MODE=false
```

Полный список — в [.env.example](.env.example).

> [!TIP]
> **Самый простой путь для демо — SQLite:** отдельный сервер БД не нужен, можно обойтись без Redis.
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

## 📤 Что заливать на сервер

Достаточно содержимого этого репозитория. **Заливаются:**

```
app/  bootstrap/  config/  database/  public/  resources/  routes/  storage/  tests/
artisan  composer.json  composer.lock  package.json  package-lock.json
vite.config.js  tailwind.config.js  phpunit.xml
.env.example  .gitignore  .gitattributes  .editorconfig  .htaccess
```

**Создаётся уже на сервере** (не заливать — генерируется командами):

| Папка / файл | Чем создаётся |
|---|---|
| `vendor/` | `composer install --no-dev --optimize-autoloader` |
| `node_modules/` | `npm ci` |
| `public/build/` | `npm run build` *(или соберите локально и залейте)* |
| `.env` | `cp .env.example .env` + правка |
| `database/database.sqlite` | `touch` *(при SQLite)* |
| `storage/**`, `bootstrap/cache/**` | runtime; дать права веб-пользователю |

> [!TIP]
> Не хотите ставить Node на сервер — выполните `npm ci && npm run build` локально и залейте готовую папку `public/build/`.

---

## 🌍 Развёртывание на сервере (production)

<details open>
<summary><b>📋 Пошаговая инструкция (Nginx + PHP-FPM + Redis)</b></summary>

<br/>

**1. Подготовка сервера (пример для Ubuntu)**

```bash
sudo apt update
sudo apt install -y nginx postgresql redis-server \
  php8.2-fpm php8.2-cli php8.2-pgsql php8.2-redis php8.2-mbstring \
  php8.2-gd php8.2-intl php8.2-zip php8.2-bcmath php8.2-curl php8.2-fileinfo
```

**2. Код и зависимости**

```bash
cd /var/www/shiba
git clone <repository-url> .

composer install --no-dev --optimize-autoloader
npm ci && npm run build

cp .env.example .env
php artisan key:generate
# отредактируйте .env: APP_ENV=production, APP_DEBUG=false, доступы к БД/Redis/S3
```

**3. База данных**

```bash
php artisan migrate --force
php artisan db:seed --force        # базовые справочники (жанры, роли, тарифы)
php artisan storage:link
```

**4. Права на запись**

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**5. Кеширование для production**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

**6. Nginx** (`/etc/nginx/sites-available/shiba`) — каталог сайта = `public/`

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

**7. Очередь** (Supervisor) — не нужна при `QUEUE_CONNECTION=sync`

```ini
[program:shiba-worker]
command=php /var/www/shiba/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
stopwaitsecs=3600
```

**8. Планировщик** (cron) — `crontab -e`

```cron
* * * * * cd /var/www/shiba && php artisan schedule:run >> /dev/null 2>&1
```

</details>

> [!TIP]
> Возникли сложности с развёртыванием? Напишите в Telegram — **[@licht_re](https://t.me/licht_re)**.

---

## 🧪 Публичный демо-стенд

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

**Развёртывание демо-стенда:**

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

### 🔑 Демо-доступы

| Роль | E-mail | Логин | Пароль |
|------|--------|-------|--------|
| 👑 Владелец/админ | `admin@demo.iiiba.ru` | `demo_admin` | `demo12345` |
| 🛡️ Модератор | `moderator@demo.iiiba.ru` | `demo_mod` | `demo12345` |
| ✍️ Автор | `author@demo.iiiba.ru` | `demo_author` | `demo12345` |
| 📖 Читатель | `reader@demo.iiiba.ru` | `demo_reader` | `demo12345` |
| 📖 Читатель 2 | `reader2@demo.iiiba.ru` | `demo_reader2` | `demo12345` |

Войти можно по e-mail **или** по логину (в одно поле). Админ-панель — `/admin`. Доступы меняются через `DEMO_ADMIN_EMAIL` / `DEMO_ADMIN_PASSWORD`.

### Команды демо-режима

| Команда | Назначение |
|---------|-----------|
| `php artisan db:seed --class=DemoSeeder` | создать эталонные демо-данные |
| `php artisan demo:snapshot` | пометить текущий контент как эталон |
| `php artisan demo:snapshot --users` | дополнительно пометить всех пользователей как демо |
| `php artisan demo:cleanup --force` | немедленно сбросить стенд к эталону |

Списки очищаемых/защищаемых таблиц — в [config/demo.php](config/demo.php).

> [!CAUTION]
> **`DEMO_MODE=true` включайте только на изолированном демо-окружении.** На сервере с реальными данными очистка безвозвратно удалит весь не-демо контент. По умолчанию режим выключен, а команда защищена флагом `--force`.

---

## ⏱ Планировщик задач

Регулярные задачи — в [routes/console.php](routes/console.php):

| Команда | Расписание | Назначение |
|---------|-----------|-----------|
| `auth:clear-resets` | каждые 15 минут | очистка устаревших токенов сброса пароля |
| `app:auto-unlock-chapters` | ежедневно 06:00 | автоматическая разблокировка глав |
| `eriiba:recalc-trust` | ежедневно 03:30 | пересчёт уровней доверия |
| `demo:cleanup` | каждые 15 минут | сброс демо-стенда *(только при `DEMO_MODE=true`)* |

---

## 🔌 REST API

Публичный REST API доступен по префиксу `/api/v1` — новеллы, главы, авторы, поиск. Подробности — в [routes/api.php](routes/api.php).

---

<div align="center">

## 💬 Контакты и поддержка

Проект архивный и официально не поддерживается, но по вопросам **развёртывания** всегда можно написать:

[![Написать в Telegram](https://img.shields.io/badge/НАПИСАТЬ_В_TELEGRAM-@licht__re-229ED9?style=for-the-badge&logo=telegram&logoColor=white&labelColor=0B1020)](https://t.me/licht_re)
[![Открыть демо](https://img.shields.io/badge/ОТКРЫТЬ_ДЕМО-demo.iiiba.ru-7C3AED?style=for-the-badge&labelColor=0B1020)](https://demo.iiiba.ru)

</div>

---

## 📄 Лицензия

Фреймворк Laravel распространяется по лицензии [MIT](https://opensource.org/licenses/MIT). Код приложения выложен «как есть» в ознакомительных целях; права на исходный код принадлежат автору.

> 🇷🇺 Интерфейс, контент и документация проекта — **полностью на русском языке**. English version: **[readme_en.md](readme_en.md)**.

<div align="center">
<sub>Сделано с ❤️ для сообщества онлайн-новелл · архив проекта er.iiiba.ru</sub>
</div>
