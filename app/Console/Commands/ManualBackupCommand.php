<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class ManualBackupCommand extends Command
{
    protected $signature = 'backup:manual {--download : Download backup after creation}';
    protected $description = 'Create manual database backup';

    public function handle(BackupService $backupService)
    {
        $this->info('Creating manual backup...');
        
        try {
            $filename = $backupService->createBackup();
            $this->info('✓ Backup created: ' . $filename);
            
            $stats = $backupService->getStats();
            $this->info('✓ Total backups: ' . $stats['total_backups']);
            $this->info('✓ Total size: ' . $stats['total_size_mb'] . ' MB');
            
            if ($this->option('download')) {
                $this->info('Download link: ' . url('/admin/backups/download/' . $filename));
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}