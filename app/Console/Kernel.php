<?php

namespace App\Console;

use App\BaiduAccount;
use App\Http\Controllers\ApiController;
use App\Jobs\SignTieba;
use App\SignJob;
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
        $schedule->call(function () {
            $bdusses = BaiduAccount::all();
            foreach ($bdusses as $bduss) {
                $api = new ApiController;
                $api->ApiBDUSSSign($bduss->id);
            }
        })->twiceDaily([1, 13]);
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
