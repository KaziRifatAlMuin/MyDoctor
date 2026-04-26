<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

class AdminBackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display list of all backups
     */
    public function index()
    {
        $backups = $this->backupService->getBackups();
        $stats = $this->backupService->getStats();
        
        return view('admin.backups', compact('backups', 'stats'));
    }

    /**
     * Create a new backup manually
     */
    public function store(Request $request)
    {
        try {
            $filename = $this->backupService->createBackup();
            return redirect()->route('admin.backups.index')
                ->with('success', "Backup created successfully: {$filename}");
        } catch (\Throwable $e) {
            Log::error('Manual backup failed: ' . $e->getMessage());
            return redirect()->route('admin.backups.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download($file)
    {
        try {
            // Prevent directory traversal attacks
            $basename = basename($file);
            $path = storage_path('app/backups/' . $basename);

            if (!file_exists($path)) {
                abort(404, 'Backup file not found.');
            }

            // Check if it's a valid .sql file
            if (!str_ends_with($basename, '.sql')) {
                abort(400, 'Invalid file type.');
            }

            return response()->download($path, $basename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $basename . '"',
                'Content-Length' => filesize($path),
                'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Throwable $e) {
            Log::error('Backup download failed: ' . $e->getMessage());
            abort(500, 'Download failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup file
     */
    public function destroy($file)
    {
        try {
            $basename = basename($file);
            
            if ($this->backupService->deleteBackup($basename)) {
                return redirect()->route('admin.backups.index')
                    ->with('success', 'Backup deleted successfully: ' . $basename);
            }
            
            return redirect()->route('admin.backups.index')
                ->with('error', 'Backup not found: ' . $basename);
        } catch (\Throwable $e) {
            Log::error('Backup deletion failed: ' . $e->getMessage());
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    /**
     * Download multiple backups as a ZIP file
     */
    public function downloadMultiple(Request $request)
    {
        try {
            $files = $request->input('files', []);
            
            if (empty($files)) {
                return redirect()->route('admin.backups.index')
                    ->with('error', 'No files selected for download.');
            }

            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create ZIP file
            $zipName = 'backups_' . now()->format('Ymd_His') . '.zip';
            $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipName;
            
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                throw new \RuntimeException('Cannot create ZIP file.');
            }

            // Add selected files to ZIP
            foreach ($files as $file) {
                $basename = basename($file);
                $filePath = storage_path('app/backups/' . $basename);
                
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $basename);
                }
            }
            
            $zip->close();

            // Download and delete the ZIP file after sending
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
            
        } catch (\Throwable $e) {
            Log::error('Multiple backup download failed: ' . $e->getMessage());
            return redirect()->route('admin.backups.index')
                ->with('error', 'Failed to create ZIP file: ' . $e->getMessage());
        }
    }

    /**
     * Restore a backup file (optional feature)
     */
    public function restore($file)
    {
        try {
            $basename = basename($file);
            $path = storage_path('app/backups/' . $basename);

            if (!file_exists($path)) {
                return redirect()->route('admin.backups.index')
                    ->with('error', 'Backup file not found.');
            }

            // Get database configuration
            $connection = config('database.default');
            $cfg = config("database.connections.{$connection}");
            
            $database = $cfg['database'] ?? '';
            $username = $cfg['username'] ?? '';
            $password = $cfg['password'] ?? '';
            $host = $cfg['host'] ?? '127.0.0.1';
            $port = $cfg['port'] ?? '3306';
            
            // Path to mysql client
            $mysql = 'D:\\xampp\\mysql\\bin\\mysql.exe';
            
            if (!file_exists($mysql)) {
                throw new \RuntimeException('mysql client not found at: ' . $mysql);
            }
            
            // Build restore command
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s %s --database "%s" < "%s"',
                $mysql,
                $host,
                $port,
                $username,
                $password ? '--password=' . $password : '',
                $database,
                $path
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \RuntimeException('Restore failed with return code: ' . $returnVar);
            }
            
            Log::info('Database restored from backup', ['file' => $basename]);
            
            return redirect()->route('admin.backups.index')
                ->with('success', 'Database restored successfully from: ' . $basename);
                
        } catch (\Throwable $e) {
            Log::error('Restore failed: ' . $e->getMessage());
            return redirect()->route('admin.backups.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Get backup information as JSON (for AJAX)
     */
    public function info($file)
    {
        try {
            $basename = basename($file);
            $path = storage_path('app/backups/' . $basename);

            if (!file_exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            return response()->json([
                'name' => $basename,
                'size' => filesize($path),
                'size_mb' => round(filesize($path) / 1048576, 2),
                'created_at' => date('Y-m-d H:i:s', filemtime($path)),
                'created_at_timestamp' => filemtime($path),
                'path' => $path
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Clean all old backups (force cleanup)
     */
    public function clean(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $deleted = 0;
            $backups = $this->backupService->getBackups();
            $now = time();
            
            foreach ($backups as $backup) {
                if ($backup['created_at_timestamp'] < $now - ($days * 86400)) {
                    if ($this->backupService->deleteBackup($backup['name'])) {
                        $deleted++;
                    }
                }
            }
            
            return redirect()->route('admin.backups.index')
                ->with('success', "Cleaned {$deleted} old backup(s) older than {$days} days.");
                
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }
}