<?php

namespace App\Console\Commands;

use App\Models\Cabinet\CProfile;
use App\Services\BitGOAPIService;
use App\Services\WalletService;
use Illuminate\Console\Command;

class ImportUserWalletsToNewProd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:user-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create wallet addresses for users';

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
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);

        /* @var BitGOAPIService $bitgoApiService */
        $bitgoApiService = resolve(BitGOAPIService::class);

        $importData = json_decode(file_get_contents(storage_path('prod_user_wallets.txt')), true);

        foreach ($importData as $profileId => $walletCoins) {
            $cProfile = CProfile::find($profileId);
            if (!$cProfile) {
                logger()->error('CProfile not found', ['id' => $profileId]);
                continue;
            }
            echo $profileId, PHP_EOL;
            foreach ($walletCoins as $coin) {
                $account = $cProfile->accounts()->where('currency', $coin)->first();
                if ($account) {
                    continue;
                }
                echo $coin, PHP_EOL;
                $walletService->generateWallet($bitgoApiService, $coin, $cProfile);
                usleep(500000);
            }
        }
        return 0;
    }
}
