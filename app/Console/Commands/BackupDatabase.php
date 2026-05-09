<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * =============================================================================
 * Automated Database Backup Command
 * =============================================================================
 *
 * This command creates automatic backups of the database.
 * It supports both SQLite (file copy) and MySQL (mysqldump).
 *
 * USAGE:
 *   php artisan backup:database              # Create a backup now
 *   php artisan backup:database --cleanup    # Create backup + delete old ones
 *
 * SCHEDULED (automatic):
 *   Runs daily at 2:00 AM via Laravel Scheduler (see routes/console.php)
 *
 * BACKUP LOCATION:
 *   storage/backups/
 *
 * =============================================================================
 */
class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     * --cleanup flag enables automatic deletion of old backups.
     */
    protected $signature = 'backup:database
                            {--cleanup : Delete backups older than retention period}';

    /**
     * The console command description (shown in 'php artisan list').
     */
    protected $description = 'Create an automated backup of the database';

    /**
     * How many days to keep backups before deleting them.
     */
    protected int $retentionDays = 7;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🗄️  Starting database backup...');

        // Create backup directory if it doesn't exist
        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            $this->info("📁 Created backup directory: {$backupDir}");
        }

        // Generate backup filename with timestamp
        // Example: backup_2024-01-15_02-00-00.sql.gz
        $timestamp = now()->format('Y-m-d_H-i-s');
        $connection = config('database.default');

        try {
            // Choose backup method based on database type
            match ($connection) {
                'sqlite' => $this->backupSqlite($backupDir, $timestamp),
                'mysql'  => $this->backupMysql($backupDir, $timestamp),
                'pgsql'  => $this->backupPostgres($backupDir, $timestamp),
                default  => throw new \Exception("Unsupported database connection: {$connection}"),
            };

            $this->info('✅ Database backup completed successfully!');
            Log::info("Database backup completed: {$connection} at {$timestamp}");

            // Clean up old backups if --cleanup flag is set
            if ($this->option('cleanup')) {
                $this->cleanupOldBackups($backupDir);
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Backup failed: {$e->getMessage()}");
            Log::error("Database backup failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Backup SQLite database (simple file copy).
     *
     * SQLite is just a single file, so we copy it directly.
     */
    private function backupSqlite(string $backupDir, string $timestamp): void
    {
        $dbPath = config('database.connections.sqlite.database');

        // Handle relative path (Laravel default: database/database.sqlite)
        if ($dbPath === ':memory:') {
            $this->warn('⚠️  Cannot backup in-memory SQLite database.');
            return;
        }

        if (!$dbPath || !file_exists($dbPath)) {
            // Try default path
            $dbPath = database_path('database.sqlite');
        }

        if (!file_exists($dbPath)) {
            throw new \Exception("SQLite database file not found at: {$dbPath}");
        }

        $backupFile = "{$backupDir}/backup_{$timestamp}.sqlite";

        copy($dbPath, $backupFile);

        $size = $this->formatBytes(filesize($backupFile));
        $this->info("📦 SQLite backup saved: {$backupFile} ({$size})");
    }

    /**
     * Backup MySQL database using mysqldump.
     *
     * mysqldump creates a SQL file containing all CREATE TABLE
     * and INSERT statements needed to recreate the database.
     */
    private function backupMysql(string $backupDir, string $timestamp): void
    {
        $host     = config('database.connections.mysql.host');
        $port     = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $backupFile = "{$backupDir}/backup_{$timestamp}.sql.gz";

        // Build mysqldump command with gzip compression
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s | gzip > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        // Execute the backup command
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("mysqldump failed with exit code: {$exitCode}");
        }

        $size = $this->formatBytes(filesize($backupFile));
        $this->info("📦 MySQL backup saved: {$backupFile} ({$size})");
    }

    /**
     * Backup PostgreSQL database using pg_dump.
     */
    private function backupPostgres(string $backupDir, string $timestamp): void
    {
        $host     = config('database.connections.pgsql.host');
        $port     = config('database.connections.pgsql.port', 5432);
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        $backupFile = "{$backupDir}/backup_{$timestamp}.sql.gz";

        // Set password via environment variable (pg_dump doesn't accept --password)
        $command = sprintf(
            'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s %s | gzip > %s',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("pg_dump failed with exit code: {$exitCode}");
        }

        $size = $this->formatBytes(filesize($backupFile));
        $this->info("📦 PostgreSQL backup saved: {$backupFile} ({$size})");
    }

    /**
     * Delete backup files older than the retention period.
     *
     * This prevents the backup directory from growing infinitely.
     */
    private function cleanupOldBackups(string $backupDir): void
    {
        $this->info("🧹 Cleaning up backups older than {$this->retentionDays} days...");

        $cutoff = now()->subDays($this->retentionDays)->timestamp;
        $deleted = 0;

        foreach (glob("{$backupDir}/backup_*") as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
                $this->line("  🗑️  Deleted: " . basename($file));
            }
        }

        if ($deleted > 0) {
            $this->info("🧹 Cleaned up {$deleted} old backup(s).");
            Log::info("Backup cleanup: deleted {$deleted} old backup(s).");
        } else {
            $this->info("✨ No old backups to clean up.");
        }
    }

    /**
     * Convert bytes to human-readable format.
     * Example: 1048576 → "1.00 MB"
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
