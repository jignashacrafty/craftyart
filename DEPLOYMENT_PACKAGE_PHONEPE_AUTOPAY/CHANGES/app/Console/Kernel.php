<?php

namespace App\Console;

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Jobs\ExportDesignCampaignController;
use App\Http\Controllers\Jobs\ProcessCheckoutDropController;
use App\Http\Controllers\Jobs\RecentExpiredController;
use App\Jobs\ProcessCheckoutDropJob;
use App\Jobs\RecentExpiredJob;
use App\Jobs\SendBulkCampaignJob;
use App\Models\Automation\CampaignSendLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        // PhonePe AutoPay - Process recurring payments daily at 9 AM
        $schedule->command('phonepe:process-autopay')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                Log::info('PhonePe AutoPay processing completed successfully');
            })
            ->onFailure(function () {
                Log::error('PhonePe AutoPay processing failed');
            });

    	// $schedule->call(function () {
     //        NotificationController::sendMorningNotification();
     //    })->dailyAt('9:00');

     //    $schedule->call(function () {
     //        NotificationController::sendAfternoonNotification();
     //    })->dailyAt('13:00');

     //    $schedule->call(function () {
     //        NotificationController::sendEveningNotification();
     //    })->dailyAt('16:30');

        // $schedule->call(function () {
        //     NotificationController::sendQuoteNotification();
        // })->dailyAt('21:00');

        // $schedule->call(function () {
        //     NotificationController::sendNightNotification();
        // })->dailyAt('22:00');


        /*$schedule->call(function () {
            Template::query()->update(array('trending_views' => 0));
        })->weekly();

        $schedule->call(function () {
            $from= Carbon::now()->subDays(10);
            Template::where("created_at","<", $from)->update(array('latest' => '0'));
        })->hourly();

        $schedule->call(function () {
            $singleDatas = TransactionLog::where("status", "1")->get();
            if($singleDatas != null) {
                foreach ($singleDatas as $singleDataRow) {
                    $user_data = UserData::where("uid", $singleDataRow->user_id)->first();
                    $toDate = Carbon::parse($singleDataRow->created_at)->addDays($user_data->total_validity);
                    $diffInDays = $singleDataRow->created_at->diffInDays(Carbon::now());
                    $daysLeft = $user_data->total_validity - $diffInDays;
                    UserData::where('uid', $user_data->uid)->update(['validity' => $daysLeft]);
                    if($daysLeft < 1) {
                        TransactionLog::where("user_id", $user_data->uid)->update(array('status' => '0'));
                        UserData::where("uid", $user_data->uid)->update(array('is_premium' => '0', 'validity' => '0'));
                        $user_data = UserData::where("uid", $user_data->uid)->first();
                    }
                }
            }
        })->hourly();


        */

//        $schedule->job(new ProcessCheckoutDropJob)->dailyAt('17:13');
//        $schedule->job(new RecentExpiredJob())->dailyAt('17:13');

        $schedule->call(function () {
            $logs = CampaignSendLog::whereStatus('paused')
                ->wherePauseType('auto')
                ->whereAutoResume(1)
                ->get();

            foreach ($logs as $log) {
                $log->update([
                    'status' => 'pending',
                    'pause_type' => null,
                    'sent_since_last_pause' => $log->total_processed
                ]);

                Log::info("BulkEmailJob: Auto-resumed next day for log {$log->id}");
                SendBulkCampaignJob::dispatch($log->id);
            }
        })->dailyAt('9:47');

        $schedule->call(function () {
            app(ProcessCheckoutDropController::class)->startProcess();
        })->dailyAt('10:40');

        $schedule->call(function (){
            app(RecentExpiredController::class)->startProcess();
        })->dailyAt('11:52');

        $schedule->call(function () {
            app(ExportDesignCampaignController::class)->startProcess();
        })->dailyAt('15:18');

        // PhonePe AutoPay: Send pre-debit notifications at 10 AM daily
        $schedule->command('phonepe:send-predebit-notifications')
                 ->dailyAt('10:00')
                 ->timezone('Asia/Kolkata');

        // PhonePe AutoPay: Process recurring payments at 12 PM daily
        $schedule->command('phonepe:process-recurring-payments')
                 ->dailyAt('12:00')
                 ->timezone('Asia/Kolkata');

        // PhonePe: Check pending payments and create orders every 5 minutes
        $schedule->command('phonepe:check-pending')
                 ->everyFiveMinutes();

        // $schedule->command(NotificationController::sendMorningNotification())->everyMinute();

        // $schedule->command(NotificationController::sendAfternoonNotification())->dailyAt('13:00');

        // $schedule->command(NotificationController::sendEveningNotification())->dailyAt('16:30');

        // $schedule->command(NotificationController::sendQuoteNotification())->dailyAt('21:00');

        // $schedule->command(NotificationController::sendNightNotification())->dailyAt('22:00');
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
