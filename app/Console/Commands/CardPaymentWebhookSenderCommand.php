<?php

namespace App\Console\Commands;

use App\Models\MerchantWebhookAttempt;
use App\Services\WebhookService;
use Illuminate\Console\Command;

class CardPaymentWebhookSenderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-form:send-webhook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send merchant card payment webhook';

    protected WebhookService $webhookService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->webhookService = resolve(WebhookService::class);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        MerchantWebhookAttempt::query()
            ->where('status', MerchantWebhookAttempt::STATUS_PENDING)
            ->chunk(100, function ($merchantWebhookAttempts) {
                foreach ($merchantWebhookAttempts as $merchantWebhookAttempt) {
                    $this->webhookService->sendMerchantCardPaymentWebhook($merchantWebhookAttempt);
                }
            });

        return 0;
    }
}
