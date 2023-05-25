<?php

namespace App\Console\Commands;

use App\Enums\AccountType;
use App\Enums\OperationStatuses;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Models\CollectedCryptoFee;
use App\Models\Transaction;
use App\Services\CollectedCryptoFeeService;
use Illuminate\Console\Command;

class AddMissedSystemTransactionsToNotCollectedTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collect:missed-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missed transactions to not collected transactions list';

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
        $transactions = Transaction::query()->where([
            'type' => TransactionType::SYSTEM_FEE,
            'status' => TransactionStatuses::SUCCESSFUL,
        ])->whereHas('toAccount', function ($q) {
            return $q->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'account_type' => AccountType::TYPE_CRYPTO,
            ])
            ->whereNull('c_profile_id')->whereNull('payment_provider_id');
        })->whereHas('fromAccount', function ($q) {
            return $q->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
                'account_type' => AccountType::TYPE_CRYPTO
            ])->whereNotNull('c_profile_id');
        })->whereHas('operation', function ($q) {
            return $q->where('status', '!=', OperationStatuses::DECLINED);
        })->whereDoesntHave('collectedCryptoFee')->get();


        /* @var CollectedCryptoFeeService $collectedCryptoFeeService */
        $collectedCryptoFeeService = resolve(CollectedCryptoFeeService::class);

        foreach ($transactions as $transaction) {
            $collectedCryptoFeeExistingTransaction = CollectedCryptoFee::query()->where([
                'currency' => $transaction->fromAccount->currency,
                'amount' => $transaction->trans_amount,
                'wallet_id' => $transaction->fromAccount->cryptoAccountDetail->wallet_id,
                'client_account_id' => $transaction->from_account,
                'system_account_id' => $transaction->to_account,
            ])->whereNull('transaction_id')->first();



            if ($collectedCryptoFeeExistingTransaction) {

                $collectedCryptoFeeExistingTransaction->transaction_id = $transaction->id;
                $collectedCryptoFeeExistingTransaction->save();
            } else {
                $collectedCryptoFeeService->saveCollectedCryptoFee($transaction->trans_amount, $transaction->fromAccount, $transaction->toAccount, $transaction);
            }

        }

        return 0;
    }
}
