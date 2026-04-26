<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class BackupService
{
    /**
     * Create a database backup with DROP statements for clean import
     */
    public function createBackup(): string
    {
        $connection = config('database.default');
        $cfg = config("database.connections.{$connection}");

        if (!isset($cfg['driver']) || $cfg['driver'] !== 'mysql') {
            throw new \RuntimeException('Only MySQL is supported for automatic backup.');
        }

        $database = $cfg['database'] ?? '';
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = $cfg['port'] ?? '3306';

        $timestamp = now()->format('Ymd_His');
        $filename = sprintf('%s_%s.sql', $database, $timestamp);
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $target = $backupDir . DIRECTORY_SEPARATOR . $filename;
        
        // Path to mysqldump (XAMPP default)
        $mysqldump = 'D:\\xampp\\mysql\\bin\\mysqldump.exe';

        if (!file_exists($mysqldump)) {
            throw new \RuntimeException('mysqldump not found at: ' . $mysqldump);
        }

        // Build command with ALL DROP flags for clean import
        $command = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction --quick --routines --events --triggers --add-drop-database --add-drop-table --add-drop-trigger --create-options --complete-insert --extended-insert=FALSE --skip-comments --databases "%s" > "%s"',
            $mysqldump,
            $host,
            $port,
            $username,
            $password ? '--password=' . $password : '',
            $database,
            $target
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($target) || filesize($target) === 0) {
            throw new \RuntimeException('Backup failed. Return code: ' . $returnVar);
        }

        // Optional: Add a pre-import cleanup section to the backup file
        $this->addPreImportCleanup($target, $database);

        Log::info('Database backup created with DROP statements', [
            'filename' => $filename,
            'size' => filesize($target)
        ]);

        return $filename;
    }

    /**
     * Add pre-import cleanup statements to the backup file
     * This ensures foreign key checks are disabled during import
     */
    protected function addPreImportCleanup(string $backupPath, string $database): void
    {
        $cleanupSql = "-- ===========================================\n";
        $cleanupSql .= "-- PRE-IMPORT CLEANUP STATEMENTS\n";
        $cleanupSql .= "-- ===========================================\n";
        $cleanupSql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $cleanupSql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $cleanupSql .= "SET AUTOCOMMIT = 0;\n";
        $cleanupSql .= "SET time_zone = '+00:00';\n\n";
        
        // Add post-import re-enable statements at the end
        $postImportSql = "\n-- ===========================================\n";
        $postImportSql .= "-- POST-IMPORT CLEANUP\n";
        $postImportSql .= "-- ===========================================\n";
        $postImportSql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $postImportSql .= "COMMIT;\n";

        // Read existing content
        $content = file_get_contents($backupPath);
        
        // Prepend cleanup statements and append post-import statements
        $newContent = $cleanupSql . $content . $postImportSql;
        
        // Write back to file
        file_put_contents($backupPath, $newContent);
    }

    /**
     * Get all backups with details
     */
    public function getBackups(): array
    {
        $backupDir = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'size_mb' => round(filesize($file) / 1048576, 2),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                    'created_at_timestamp' => filemtime($file)
                ];
            }
            usort($backups, function($a, $b) {
                return $b['created_at_timestamp'] - $a['created_at_timestamp'];
            });
        }

        return $backups;
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(string $filename): bool
    {
        $path = storage_path('app/backups/' . basename($filename));
        if (file_exists($path)) {
            unlink($path);
            Log::info('Backup deleted', ['file' => $filename]);
            return true;
        }
        return false;
    }

    /**
     * Get backup statistics
     */
    public function getStats(): array
    {
        $backups = $this->getBackups();
        $totalSize = array_sum(array_column($backups, 'size'));
        
        return [
            'total_backups' => count($backups),
            'total_size_mb' => round($totalSize / 1048576, 2),
            'oldest_backup' => !empty($backups) ? end($backups)['created_at'] : null,
            'newest_backup' => !empty($backups) ? $backups[0]['created_at'] : null,
            'average_size_mb' => count($backups) > 0 ? round(($totalSize / count($backups)) / 1048576, 2) : 0
        ];
    }
}