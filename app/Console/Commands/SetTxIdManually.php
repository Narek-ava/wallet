<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\BitGOAPIService;
use Illuminate\Console\Command;

class SetTxIdManually extends Command
{
    public int $transaction_id;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:txid {transaction_id}';

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
    public function handle()
    {
        $bitGOAPIService = resolve(BitGOAPIService::class);
        /* @var BitGOAPIService $bitGOAPIService */

        $transaction = Transaction::query()->where(['transaction_id' => $this->argument('transaction_id')])->firstOrFail();
        /* @var Transaction $transaction*/

        $bitGoTransaction = $bitGOAPIService->sendTransaction(
            $transaction->fromAccount->cryptoAccountDetail,
            $transaction->toAccount->cryptoAccountDetail,
            $transaction->trans_amount,
        );

        logger()->error('ManualTransaction', $bitGoTransaction);


        if (!empty($bitGoTransaction['transfer']['txid'])) {
            $transaction->setTxId($bitGoTransaction['transfer']['txid']);
        }
        return 0;
    }
}
