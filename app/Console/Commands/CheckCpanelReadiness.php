<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CheckCpanelReadiness extends Command
{
    protected $signature = 'check:cpanel';

    protected $description = 'Run quick readiness checks for cPanel/shared hosting (DB, tables, writable dirs, activity_logs)';

    public function handle(): int
    {
        $this->info('Starting cPanel readiness checks...');

        $this->checkDirectories();
        $this->checkDatabaseConnection();
        $this->checkActivityLogsTable();
        $this->checkSessionDriver();
        $this->info('Checks completed. Interpret results above.');

        return 0;
    }

    private function checkDirectories(): void
    {
        $this->line('');
        $this->info('1) Checking writable directories');
        $paths = [
            storage_path(),
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        $fs = new Filesystem();
        foreach ($paths as $p) {
            try {
                $exists = $fs->exists($p);
                $writable = $exists ? $fs->isWritable($p) : false;
                $this->line(sprintf(' - %s : exists=%s writable=%s', $p, $exists ? 'yes' : 'no', $writable ? 'yes' : 'no'));
            } catch (Throwable $e) {
                $this->line(sprintf(' - %s : error (%s)', $p, $e->getMessage()));
            }
        }
    }

    private function checkDatabaseConnection(): void
    {
        $this->line('');
        $this->info('2) Checking database connection');
        try {
            DB::connection()->getPdo();
            $this->line(' - Database connection: OK');
        } catch (Throwable $e) {
            $this->error(' - Database connection failed: ' . $e->getMessage());
        }
    }

    private function checkActivityLogsTable(): void
    {
        $this->line('');
        $this->info('3) Checking activity_logs table and probe insert');
        try {
            $has = Schema::hasTable('activity_logs');
            $this->line(' - activity_logs table present: ' . ($has ? 'yes' : 'no'));

            if ($has) {
                // Probe insert inside transaction and rollback to avoid persisting sample rows
                DB::beginTransaction();
                try {
                    ActivityLog::query()->create([
                        'user_id' => null,
                        'category' => 'probe',
                        'action' => 'probe_insert',
                        'description' => 'probe',
                        'subject_type' => null,
                        'subject_id' => null,
                        'context' => ['probe' => true],
                        'created_at' => now(),
                    ]);
                    DB::rollBack();
                    $this->line(' - activity_logs insert probe: OK (rolled back)');
                } catch (Throwable $e) {
                    DB::rollBack();
                    $this->error(' - activity_logs insert probe failed: ' . $e->getMessage());
                }
            }
        } catch (Throwable $e) {
            $this->error(' - activity_logs check error: ' . $e->getMessage());
        }
    }

    private function checkSessionDriver(): void
    {
        $this->line('');
        $this->info('4) Checking session driver and sessions table if applicable');
        $driver = config('session.driver');
        $this->line(' - SESSION_DRIVER: ' . $driver);

        if ($driver === 'database') {
            try {
                $has = Schema::hasTable(config('session.table', 'sessions'));
                $this->line(' - sessions table present: ' . ($has ? 'yes' : 'no'));
            } catch (Throwable $e) {
                $this->error(' - sessions table check failed: ' . $e->getMessage());
            }
        }
    }
}
