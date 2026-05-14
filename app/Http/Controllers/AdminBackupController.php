<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminBackupController extends Controller
{
    public function index(): View
    {
        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);

        $backups = collect(File::glob($backupDirectory.DIRECTORY_SEPARATOR.'database_*.sqlite'))
            ->map(function (string $path) {
                return [
                    'name' => basename($path),
                    'path' => $path,
                    'size' => File::size($path),
                    'modified_at' => date('Y-m-d H:i:s', (int) filemtime($path)),
                ];
            })
            ->sortByDesc('modified_at')
            ->values();

        return view('admin.backups.index', compact('backups'));
    }

    public function create(): RedirectResponse
    {
        Artisan::call('db:backup');
        AuditLogger::log('db_backup_created', 'system', null, 'Veritabani yedegi olusturuldu.');

        return back()->with('success', 'Yeni veritabani yedegi olusturuldu.');
    }

    public function restore(string $file): RedirectResponse
    {
        $sanitized = basename($file);
        $backupPath = storage_path('app/backups'.DIRECTORY_SEPARATOR.$sanitized);
        $databasePath = (string) config('database.connections.sqlite.database');

        if (! File::exists($backupPath)) {
            return back()->withErrors(['backup' => 'Secilen yedek dosyasi bulunamadi.']);
        }

        DB::disconnect('sqlite');
        File::copy($backupPath, $databasePath);

        AuditLogger::log('db_backup_restored', 'system', null, 'Veritabani yedekten geri yuklendi.', null, null, [
            'backup_file' => $sanitized,
        ]);

        return back()->with('success', 'Veritabani yedekten geri yuklendi: '.$sanitized);
    }
}
