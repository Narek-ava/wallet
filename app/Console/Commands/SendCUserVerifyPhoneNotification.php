<?php

namespace App\Console\Commands;

use App\Services\CUserTemporaryRegisterDataService;
use Illuminate\Console\Command;

class SendCUserVerifyPhoneNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:c-user-verify-phone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CUserTemporaryRegisterDataService $cUserTemporaryRegisterDataService)
    {
        $cUserTemporaryRegisterDataService->removeCompletedRegistration();
        $toBeNotified = $cUserTemporaryRegisterDataService->getToBeNotified();

        foreach ($toBeNotified as $cUserTemporaryRegisterData) {
            $cUserTemporaryRegisterDataService->notify($cUserTemporaryRegisterData);
        }

        return 0;
    }
}
