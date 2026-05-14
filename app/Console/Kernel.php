<?php

namespace App\Console;

use App\Console\Commands\CheckDueMaintenanceCommand;
use App\Console\Commands\BackupDatabaseCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        CheckDueMaintenanceCommand::class,
        BackupDatabaseCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('maintenance:check-due')->everyMinute();
        $schedule->command('db:backup')->dailyAt('02:30');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
