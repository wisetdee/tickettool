<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        
        // this line must be commented to be able to php artisan route:list

        // // Error shows in CMD : "Call to a member function everyMinute() on null"
        // app('App\Http\Controllers\ImapController')->fetch_mail($_ENV['SERVER_PATH'])->everyMinute();
        // app('App\Http\Controllers\TicketsController')->send_notification_for_overdue()->everyMinute();

        // app('App\Http\Controllers\ImapController')->fetch_mail($_ENV['SERVER_PATH']);
        // app('App\Http\Controllers\TicketsController')->send_notification_for_overdue();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
