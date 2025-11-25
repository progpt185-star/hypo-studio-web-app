<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // no scheduled commands by default
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        // Load custom Artisan commands from app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        $consoleRoutes = base_path('routes/console.php');
        if (file_exists($consoleRoutes)) {
            require $consoleRoutes;
        }
    }
}
