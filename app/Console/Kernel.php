<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// Import your command
use App\Console\Commands\SyncYouTubeStreams;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string|string>
     */
    protected $commands = [
        // Register your command here
        SyncYouTubeStreams::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run the YouTube sync every 30 minutes
        $schedule->command('streams:sync')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Loads commands in the Commands directory automatically
        $this->load(__DIR__ . '/Commands');

        // Optional: include routes/console.php if needed
        require base_path('routes/console.php');
    }
}
