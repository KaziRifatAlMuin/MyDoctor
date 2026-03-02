# Laravel Quick Reference & Full Guide

This document collects the most important Laravel terminal commands, common workflows, and a concise guide to help you build, test, and deploy Laravel applications.

---

## Table of Contents

- Introduction
- Installation & Setup
- Creating Projects
- Development Server & Basic Commands
- Artisan (common commands)
- Composer (common commands)
- Environment & Configuration
- Database: Migrations, Seeding, Factories
- Eloquent (Models & Relationships)
- Routing & Controllers
- Requests, Validation & Form Requests
- Middleware
- Views, Blade & Assets (Vite)
- Testing (PHPUnit & Pest)
- Queues, Jobs & Events
- Mail, Notifications & Broadcasting
- Caching, Sessions & Filesystems
- Scheduling & Task Scheduler
- Debugging & Common Troubleshooting
- Deployment checklist
- Useful Resources

---

## Introduction

Laravel is a PHP web framework that emphasizes expressive, elegant syntax. This guide focuses on terminal commands and practical workflows for day-to-day development.

## Installation & Setup

1. System requirements: PHP 8.1+ (or higher), Composer, MySQL/Postgres/SQLite, Node.js (for frontend assets).
2. Install Composer (if not installed):

```
composer --version
```

3. Global Laravel installer (optional):

```
composer global require laravel/installer
laravel --version
```

4. Create a new project (via Composer):

```
composer create-project laravel/laravel my-project
```

Or using the Laravel installer:

```
laravel new my-project
```

## Creating Projects & Common Project Tasks

- Create a project: `composer create-project laravel/laravel app-name`
- Open project folder: `cd app-name`
- Install Node dependencies: `npm install` or `pnpm install`
- Build assets (development): `npm run dev`
- Build assets (production): `npm run build`

## Development Server & Basic Commands

- Start Laravel development server: `php artisan serve --host=127.0.0.1 --port=8000`
- Access at: `http://127.0.0.1:8000`

## Artisan — Core CLI (most-used commands)

General help:

```
php artisan
php artisan list
php artisan help migrate
```

Key commands:

- Clear application cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Cache config (production): `php artisan config:cache`
- Clear route cache: `php artisan route:clear`
- Cache routes: `php artisan route:cache`
- Clear compiled views: `php artisan view:clear`
- Optimize for production: `php artisan optimize`

Key generators:

- Make controller: `php artisan make:controller NameController`
- Make controller with resource methods: `php artisan make:controller PhotoController --resource`
- Make model: `php artisan make:model ModelName`
- Make model with migration, factory, resource controller: `php artisan make:model Post -mfr`
- Make migration: `php artisan make:migration create_posts_table`
- Make middleware: `php artisan make:middleware CheckAge`
- Make request (form request validation): `php artisan make:request StoreUserRequest`
- Make job: `php artisan make:job ProcessPodcast`
- Make event/listener: `php artisan make:event OrderPlaced` / `php artisan make:listener SendOrderNotification --event=OrderPlaced`
- Make seeder: `php artisan make:seeder UsersTableSeeder`
- Run Tinker (REPL): `php artisan tinker`

Database

- Run migrations: `php artisan migrate`
- Rollback last migration batch: `php artisan migrate:rollback`
- Reset all migrations: `php artisan migrate:reset`
- Refresh (rollback + migrate): `php artisan migrate:refresh`
- Fresh (drop all, migrate): `php artisan migrate:fresh`
- Fresh with seed: `php artisan migrate:fresh --seed`

Queues & workers

- Start queue worker: `php artisan queue:work`
- Start queue listener (restarts after each job): `php artisan queue:listen`
- Retry failed job: `php artisan queue:retry all` or `php artisan queue:retry {id}`
- Clear failed jobs: `php artisan queue:flush` or `php artisan queue:forget {id}`

Scheduling

- Run scheduled tasks (local): `php artisan schedule:run`

Testing

- Run tests: `php artisan test` (wrapper around PHPUnit)

Maintenance

- Put application into maintenance mode: `php artisan down`
- Bring application out of maintenance: `php artisan up`

## Composer — dependency management

- Install dependencies: `composer install`
- Update dependencies: `composer update`
- Add a package: `composer require vendor/package`
- Add a dev package: `composer require --dev vendor/package`
- Remove a package: `composer remove vendor/package`
- Dump autoload: `composer dump-autoload -o`

## Environment & Configuration

- Copy `.env.example` to `.env` and set database, mail, queue, cache, and other settings.

```
cp .env.example .env
php artisan key:generate
```

- Useful `.env` variables:

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_DRIVER`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `MAIL_MAILER`

## Database: Migrations, Seeding & Factories

Migrations:

- Create migration: `php artisan make:migration create_table_name_table`
- Add columns using schema builder inside migration file.

Seeding & Factories:

- Create factory (Laravel 8+ syntax): `php artisan make:factory UserFactory --model=User`
- Create seeder: `php artisan make:seeder UsersTableSeeder`
- Run seeders: `php artisan db:seed`
- Seed specific class: `php artisan db:seed --class=UsersTableSeeder`

Database refresh with seed:

```
php artisan migrate:fresh --seed
```

## Eloquent (Models & Relationships)

- Create a model with migration: `php artisan make:model Post -m`
- Common relationships: `hasOne`, `hasMany`, `belongsTo`, `belongsToMany`, `morphMany`, `morphTo`.
- Use guarded/fillable properties to protect mass assignment.

Eloquent tips:

- Use `->with()` for eager loading to avoid N+1 queries.
- Use query scopes for reusable query filters.

## Routing & Controllers

- Basic route in `routes/web.php`:

```
Route::get('/', [HomeController::class, 'index']);
```

- Route resource (conventional REST):

```
Route::resource('photos', PhotoController::class);
```

- Route grouping and middleware:

```
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

## Requests, Validation & Form Requests

- Inline validation in controllers:

```
$request->validate([
    'title' => 'required|string|max:255',
    'email' => 'required|email',
]);
```

- Use Form Request classes for complex validation:

```
php artisan make:request StorePostRequest
```

Then type-hint `StorePostRequest` in controller methods.

## Middleware

- Create middleware: `php artisan make:middleware EnsureUserIsAdmin`
- Register middleware in `app/Http/Kernel.php` (route or global middleware).

## Views, Blade & Assets (Vite)

- Blade files live in `resources/views` with `.blade.php` suffix.
- Escaping: `{{ $var }}`; raw: `{!! $html !!}`.

Vite (Laravel mix replacement):

- Install frontend deps: `npm install`
- Dev: `npm run dev`
- Prod build: `npm run build`

Include assets in Blade via `@vite(['resources/js/app.js', 'resources/css/app.css'])`.

## Testing (PHPUnit & `artisan test`)

- Run tests: `php artisan test`
- Run a specific test file: `php artisan test --filter TestClassName`
- Create test: `php artisan make:test UserTest` (Feature) or `php artisan make:test ExampleTest --unit` (Unit)

Factories & testing DB:

- Use in-memory SQLite for fast tests or use `RefreshDatabase` trait to migrate.

```
php artisan test --testsuite=Feature
```

## Queues, Jobs & Events

- Set queue connection in `.env`: `QUEUE_CONNECTION=database|redis|sqs`.
- Create job: `php artisan make:job SendWelcomeEmail`
- Dispatch a job: `SendWelcomeEmail::dispatch($user)` or `dispatch(new SendWelcomeEmail($user))`

Run worker:

```
php artisan queue:work --tries=3
```

Use horizon for Redis queue management: `laravel/horizon` package.

## Mail, Notifications & Broadcasting

- Send mail via Mailable classes: `php artisan make:mail OrderShipped`
- Send notification: `php artisan make:notification InvoicePaid`

Configure mail driver in `.env` (`MAIL_MAILER=smtp|sendmail|log|mailgun`).

Broadcasting:

- Use Pusher or Redis and configure `config/broadcasting.php`.

## Caching, Sessions & Filesystems

- Cache clear: `php artisan cache:clear`
- Cache driver in `.env`: `CACHE_DRIVER=redis|file|database|memcached`.
- Filesystems: local, s3, etc. Use `php artisan storage:link` to symlink `storage/app/public` to `public/storage`.

```
php artisan storage:link
```

## Scheduling & Task Scheduler

- Define scheduled tasks in `app/Console/Kernel.php`'s `schedule()` method.
- On server, run cron every minute to trigger scheduler:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Windows note: use Task Scheduler to run `php artisan schedule:run` every minute.

## Debugging & Common Troubleshooting

- Check logs: `storage/logs/laravel.log`.
- Clear caches if config or route changes not reflecting: `php artisan cache:clear`, `config:clear`, `route:clear`, `view:clear`.
- If class not found after Composer install: `composer dump-autoload`.
- Permissions: ensure `storage` and `bootstrap/cache` are writable by web server.

## Deployment Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
- Ensure `APP_KEY` exists: `php artisan key:generate` (local only—keep same key on deployed envs).
- Run migrations: `php artisan migrate --force` (use `--force` in production).
- Cache config & routes: `php artisan config:cache && php artisan route:cache`
- Build frontend assets: `npm run build` and upload or build on server.
- Set up queue workers or Horizon as needed.
- Configure supervisor (Linux) to keep queue workers running.

## Useful Artisan Command Summary (cheat sheet)

- `php artisan serve` — start dev server
- `php artisan tinker` — REPL
- `php artisan migrate` — run migrations
- `php artisan migrate:fresh --seed` — fresh install + seed
- `php artisan make:model Model -mcrf` — model, migration, controller, factory
- `php artisan make:controller NameController --resource --model=Model`
- `php artisan route:list` — show registered routes
- `php artisan vendor:publish` — publish vendor assets/configs
- `php artisan storage:link` — link storage

## Common Troubleshooting Scenarios

- 500 errors in production: check `storage/logs/laravel.log` and `APP_DEBUG=false`.
- `.env` changes not applied: run `php artisan config:clear` and `php artisan config:cache`.
- Missing compiled classes: `composer dump-autoload` and `php artisan optimize:clear`.

## Security Essentials

- Never commit `.env` to source control.
- Keep `APP_DEBUG=false` on production.
- Use HTTPS in production and set `SESSION_SECURE_COOKIE=true` and `SESSION_COOKIE` settings accordingly.
- Sanitize inputs and use validation rules.

## Helpful Packages

- Authentication scaffolding: `laravel/breeze`, `laravel/jetstream`, `laravel/ui`.
- Debugging: `barryvdh/laravel-debugbar` (dev only).
- Horizon: `laravel/horizon` for Redis queues.
- Telescope: `laravel/telescope` for app monitoring (dev only).

## Resources

- Official docs: https://laravel.com/docs
- Laracasts: https://laracasts.com
- Laravel News: https://laravel-news.com