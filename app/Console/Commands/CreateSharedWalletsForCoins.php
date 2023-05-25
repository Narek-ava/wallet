<?php

namespace App\Console\Commands;

use App\Enums\Currency;
use App\Models\ClientSystemWallet;
use App\Services\ClientSystemWalletService;
use Illuminate\Console\Command;

class CreateSharedWalletsForCoins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:shared-wallets {project_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create shared wallets for enabled currencies.';

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
    public function handle()
    {
        $projectId = $this->argument('project_id');
        $clientSystemWalletService = resolve(ClientSystemWalletService::class);
        /* @var ClientSystemWalletService $clientSystemWalletService */
        foreach (Currency::getList() as $currency) {

            $sharedWallet = $clientSystemWalletService->getSystemWalletByCurrency($currency, $projectId);
            if (!$sharedWallet) {
                $clientWallet = new ClientSystemWallet();
                $clientWallet->currency = $currency;
                $clientWallet->project_id = $projectId;
                $clientWallet->save();
            }
        }
        return 0;
    }
}
