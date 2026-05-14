<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--keep=14 : Saklanacak yedek gun sayisi}';

    protected $description = 'SQLite veritabaninin yedegini alir';

    public function handle(): int
    {
        $databasePath = (string) config('database.connections.sqlite.database');
        if (! File::exists($databasePath)) {
            $this->error('SQLite dosyasi bulunamadi: '.$databasePath);
            return 1;
        }

        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);

        $timestamp = now()->format('Ymd_His');
        $backupFile = $backupDirectory.DIRECTORY_SEPARATOR.'database_'.$timestamp.'.sqlite';
        File::copy($databasePath, $backupFile);

        $keepDays = max(1, (int) $this->option('keep'));
        $threshold = now()->subDays($keepDays)->timestamp;
        $deletedCount = 0;

        foreach (File::glob($backupDirectory.DIRECTORY_SEPARATOR.'database_*.sqlite') as $filePath) {
            if (filemtime($filePath) !== false && filemtime($filePath) < $threshold) {
                File::delete($filePath);
                $deletedCount++;
            }
        }

        $this->info('Yedek olusturuldu: '.basename($backupFile));
        if ($deletedCount > 0) {
            $this->info($deletedCount.' eski yedek silindi.');
        }

        return 0;
    }
}
