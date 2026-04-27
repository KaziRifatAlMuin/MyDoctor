<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class CleanBackupsCommand extends Command
{
    protected $signature = 'backup:clean {--days=30 : Delete backups older than X days}';
    protected $description = 'Clean old database backups';

    public function handle(BackupService $backupService)
    {
        $days = $this->option('days');
        $this->info("Cleaning backups older than {$days} days...");
        
        $backups = $backupService->getBackups();
        $now = time();
        $deleted = 0;
        
        foreach ($backups as $backup) {
            if ($backup['created_at_timestamp'] < $now - ($days * 86400)) {
                if ($backupService->deleteBackup($backup['name'])) {
                    $deleted++;
                    $this->line("Deleted: " . $backup['name']);
                }
            }
        }
        
        $this->info("✓ Deleted {$deleted} old backup(s)");
        
        return Command::SUCCESS;
    }
}