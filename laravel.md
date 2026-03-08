# Laravel — Command Cheat Sheet (terminal)

This file lists the most important Laravel terminal commands grouped by purpose. Copy the commands as needed.

## Top Terminal Commands

```
php artisan serve
php artisan make:migration create_posts_table
php artisan make:controller PostController --resource
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
php artisan make:model Post -mcrf
php artisan route:list
php artisan storage:link
```

## Artisan — Essentials

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan` | Show Artisan help and available commands | Quick discovery hub |
| `php artisan list` | List all commands with descriptions | Alternative to `php artisan` |
| `php artisan help <command>` | Show usage for a specific command | e.g. `php artisan help migrate` |
| `php artisan serve --host=127.0.0.1 --port=8000` | Start local dev server | Use during development |
| `php artisan tinker` | REPL for interacting with app | Inspect models, run quick code |
| `php artisan route:list` | Show registered routes | Debug routing issues |
| `php artisan view:clear` | Clear compiled Blade views | After view changes |
| `php artisan cache:clear` | Clear application cache | General troubleshooting |
| `php artisan config:clear` | Clear config cache | When .env/config changes not applied |
| `php artisan config:cache` | Cache config for production | Run on deploy (use with care) |
| `php artisan route:cache` | Cache routes for production | Only for route files without closures |
| `php artisan optimize` | Optimize framework for better performance | Useful before deploy |

## Generators (make:*)

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan make:controller NameController` | Create a controller | Add `--resource` for resource methods |
| `php artisan make:model ModelName` | Create a model | Combine flags: `-m` migration, `-f` factory, `-c` controller |
| `php artisan make:migration create_table_name_table` | Create a migration file | Edit then run migrations |
| `php artisan make:seeder UsersTableSeeder` | Create a seeder class | Run with `db:seed` |
| `php artisan make:factory UserFactory --model=User` | Create a model factory | For generating test/dev data |
| `php artisan make:middleware CheckAge` | Create middleware | Register in kernel after creating |
| `php artisan make:request StoreUserRequest` | Create a form request | Use for validation rules |
| `php artisan make:job ProcessPodcast` | Create a queued job class | Use with queues |
| `php artisan make:event OrderPlaced` | Create an event | Pair with listeners |
| `php artisan make:listener SendOrderNotification --event=OrderPlaced` | Create an event listener | Auto-injects event type |

## Database & Migrations

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan migrate` | Run outstanding migrations | Applies schema changes |
| `php artisan migrate:rollback` | Roll back last migration batch | Revert recent migrations |
| `php artisan migrate:refresh` | Roll back all and re-run migrations | Useful in development |
| `php artisan migrate:fresh --seed` | Drop all tables, migrate, then seed | Clean local DB setup |
| `php artisan db:seed` | Run database seeders | Use `--class=SeederName` to run single seeder |

## Queues & Scheduling

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan queue:work` | Start a queue worker | Use supervisor/horizon in production |
| `php artisan queue:listen` | Listen for jobs and restart after each | Slower, restarts after each job |
| `php artisan queue:retry all` | Retry all failed jobs | Use with care |
| `php artisan queue:flush` | Delete all failed jobs | Permanent removal |
| `php artisan schedule:run` | Run scheduled tasks (call from cron) | Cron: `* * * * * cd /path && php artisan schedule:run` |

## Maintenance & Deployment

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan down` | Put app into maintenance mode | Use before deploy changes |
| `php artisan up` | Bring app out of maintenance mode | After deploy/work complete |
| `php artisan key:generate` | Generate new `APP_KEY` in .env | Run once when creating project |
| `php artisan storage:link` | Create symlink from public/storage to storage/app/public | Required for public storage files |
| `php artisan vendor:publish` | Publish vendor assets/configs | Use `--tag` or `--provider` to narrow |

## Testing & Debugging

| Command | Description | Notes |
| --- | --- | --- |
| `php artisan test` | Run PHPUnit tests (wrapper) | Runs tests defined in `tests/` |
| `php artisan telescope:install` | Install Telescope (if used) | Dev-only debugging tool |

## Composer & Node (project commands)

| Command | Description | Notes |
| --- | --- | --- |
| `composer install` | Install PHP dependencies | Run after clone |
| `composer update` | Update dependencies | Use cautiously in projects |
| `composer require vendor/package` | Add a package | Adds to composer.json |
| `composer dump-autoload -o` | Regenerate optimized autoload files | Useful after creating classes |
| `npm install` | Install Node dependencies | For frontend assets |
| `npm run dev` | Build assets for dev (Vite) | Watch/dev mode |
| `npm run build` | Build assets for production | Run on deploy/build server |