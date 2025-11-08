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
        // load commands if necessary
    }
}
