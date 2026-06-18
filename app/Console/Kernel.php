<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Send subscription reminders daily at 9am
        $schedule->command('subscriptions:reminders')->dailyAt('09:00');

        // Process queued jobs
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
