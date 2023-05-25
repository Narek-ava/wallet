<?php

namespace App\Console\Commands;

use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Models\Operation;
use App\Services\BitGOAPIService;
use App\Services\CryptoAccountService;
use Illuminate\Console\Command;

class CryptoToCryptoMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:crypto-to-crypto';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor crypto to crypto operations.';

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
        $operations = Operation::query()->where([
            'status' => OperationStatuses::PENDING,
        ])
            ->where('operation_type', OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
            ->whereDoesntHave('transactions')->get();

        $bitGOAPIService = resolve(BitGOAPIService::class);
        /* @var BitGOAPIService $bitGOAPIService */

        $cryptoAccountService = resolve(CryptoAccountService::class);
        /* @var CryptoAccountService $cryptoAccountService */

        foreach ($operations as $operation) {
            $toAccount = $operation->toAccount;
            $transfers = $bitGOAPIService->listTransfers($operation->to_currency, $toAccount->cryptoAccountDetail->wallet_id, $operation->address);
            foreach ($transfers['transfers'] as $transfer) {
                $cryptoTransferData = $bitGOAPIService->getCryptoTransferData($transfer, $operation->address);
                if ($cryptoTransferData->is_received && $operation->address == $cryptoTransferData->to_address) {
                    $cryptoAccountService->cryptoToCryptoPFIncomingTrx(
                        $operation->toAccount,
                        $operation->address,
                        $cryptoTransferData->tx_id,
                        $operation,
                        $cryptoTransferData->value,
                        $cryptoTransferData->is_approved
                    );
                }
            }
        }
    }
}
