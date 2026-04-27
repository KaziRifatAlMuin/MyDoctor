<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\CheckCpanelReadiness::class,
        \App\Console\Commands\AutoBackupCommand::class,
        \App\Console\Commands\ManualBackupCommand::class,
        \App\Console\Commands\CleanBackupsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Existing reminder schedule
        $schedule->command('reminders:send')->everyMinute();
        
        // NEW: Automatic database backup every 12 hours (at 00:00 and 12:00)
        $schedule->command('backup:auto')
                 ->cron('0 */12 * * *')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/backup-scheduler.log'));
        
        // NEW: Clean old backups daily at 2 AM (keep last 30 days)
        $schedule->command('backup:clean --days=30')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}