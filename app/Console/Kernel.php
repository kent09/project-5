<?php

namespace App\Console;

use App\Console\Commands\SyncInfusionsoft;
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
        // Commands\Inspire::class,
        Commands\ApplyTagOnCron::class,
        // Commands\ImportCsv::class,
        // Commands\CsvProcess::class,
        Commands\SyncInfusionsoft::class,
        Commands\SyncFailed::class,
        Commands\CreatePlanSubscription::class,
        //Commands\UpdateMonthlyToken::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call('App\Http\Controllers\InfusionSoftAuthController@refreshTokenCron')->cron('* * * * * *')->runInBackground();

        $schedule->call('App\Http\Controllers\BillingUsageController@monitorSubscriptions')->twiceDaily(1, 12)->runInBackground();

        $schedule->call('App\Http\Controllers\BillingUsageController@resetAllowance')->twiceDaily(1, 12)->runInBackground();

        $schedule->command('command:applyTagOnCron')->everyMinute()->runInBackground();

        $schedule->call('App\Http\Controllers\InfusionSoftController@processOwnerAssignmentCron')->cron('* * * * * *')->runInBackground();

        $schedule->call('App\Http\Controllers\DocAuthDocusignController@refreshDocusignAccountCron')->cron('* * * * * *')->runInBackground();

        
        // $schedule->command('csv:import')->everyMinute()->runInBackground();

        // $schedule->command('sync:infusionsoft')->everyFiveMinutes()->runInBackground();

//        $schedule->command('fusedsoftware:importcsv');

        // $schedule->command('csv:process')->everyMinute()->runInBackground();
        // temporarily set to one minute, default should be 5
        $schedule->command('sync:infusionsoft')->everyMinute()->runInBackground();

        $schedule->command('sync:failed')->everyMinute()->runInBackground();

        //$schedule->command('update:token')->daily()->runInBackground();

    }
}
