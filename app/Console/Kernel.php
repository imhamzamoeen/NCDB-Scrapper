<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('app:scrap-ncm')->weekly()->withoutOverlapping()->appendOutputTo(storage_path('app/public/ConsoleCommandOutput.txt'))
        ->onSuccess(function (Stringable $output) {
            // The task succeeded...
            Log::info("Task completed successfully: {$output}");
            
        })
        ->onFailure(function (Stringable $output) {
            Log::info("Task Failed : {$output}");            
            // The task failed...
        });


    

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
