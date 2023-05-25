<?php

namespace App\Console;

use App\Console\Commands\{AccountBalanceLimitCheck,
    CardPaymentWebhookSenderCommand,
    CheckCompliance,
    CheckExpiredCardOperations,
    CheckTxIdByRefId,
    CryptoToCryptoMonitor,
    CryptoWebhookQueue,
    DocumentsAutoDelete,
    ExistingUsersMigration,
    GenerateReports,
    MonitorCrypto,
    NotifyBeforeSuspend,
    RiskScore,
    SendCUserVerifyPhoneNotification,
    SendNotificationEmails,
    SumsubMigration,
    SuspendUser,
    TransactionAmountReceived};
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
        DocumentsAutoDelete::class,
        NotifyBeforeSuspend::class,
        SuspendUser::class,
        CheckCompliance::class,
        CheckTxIdByRefId::class,
        TransactionAmountReceived::class,
        MonitorCrypto::class,
        RiskScore::class,
        SendNotificationEmails::class,
        CryptoWebhookQueue::class,
        AccountBalanceLimitCheck::class,
        CheckExpiredCardOperations::class,
        CardPaymentWebhookSenderCommand::class,
        CryptoToCryptoMonitor::class,
        SumsubMigration::class,
        ExistingUsersMigration::class,
        SendCUserVerifyPhoneNotification::class,
        GenerateReports::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('documents:auto-delete')->dailyAt('10:00');
        $schedule->command('notify:before-suspend')->dailyAt('12:00');
        $schedule->command('suspend:user')->dailyAt('14:00');
        $schedule->command('check:compliance')->dailyAt('10:00');
        $schedule->command('check:txId-by-refId')->everyThreeMinutes();
        $schedule->command('crypto-webhook:queue')->everyFiveMinutes();
        $schedule->command('transaction:check-if-amount-received')->everyTenMinutes();
        $schedule->command('monitor:crypto')->everyThirtyMinutes();
        $schedule->command('risk:score')->dailyAt('16:00');
        $schedule->command('notification:send')->everyFifteenMinutes();
        $schedule->command('monitor:provider-account-balance')->everyTenMinutes();
        $schedule->command('check:expired-card-operations')->everyTenMinutes();
        $schedule->command('payment-form:send-webhook')->everyFiveMinutes();
        $schedule->command('monitor:crypto-to-crypto')->everyFiveMinutes();
        $schedule->command('notification:c-user-verify-phone')->everyMinute();
//        $schedule->command('reports:generate-report')->everyMinute()->runInBackground();
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
