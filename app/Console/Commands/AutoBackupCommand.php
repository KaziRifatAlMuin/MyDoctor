<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AutoBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:auto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create automatic database backup';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Starting automatic backup...');
        
        try {
            $startTime = microtime(true);
            
            // Create backup
            $filename = $backupService->createBackup();
            
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            $this->info('Backup created successfully: ' . $filename);
            $this->info('Execution time: ' . $executionTime . ' seconds');
            
            // Log success
            Log::channel('daily')->info('Auto backup successful', [
                'filename' => $filename,
                'execution_time' => $executionTime
            ]);
            
            // Optional: Send email notification
            if (config('app.backup_email_notifications', false)) {
                $this->sendNotificationEmail($filename);
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            
            Log::channel('daily')->error('Auto backup failed', [
                'error' => $e->getMessage()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Send email notification for backup
     */
    protected function sendNotificationEmail($filename)
    {
        // Uncomment and configure if you want email notifications
        /*
        Mail::raw("Database backup completed successfully at " . now() . "\nFile: " . $filename, function($message) {
            $message->to(config('app.admin_email'))
                    ->subject('Database Backup Completed - ' . config('app.name'));
        });
        */
    }
}