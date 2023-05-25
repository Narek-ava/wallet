<?php

namespace App\Console\Commands;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Facades\KrakenFacade;
use App\Models\Account;
use App\Services\BitGOAPIService;
use Illuminate\Console\Command;

class WithdrawAmountFromClients extends Command
{

    const EXPECTED_MIN_BALANCE_IN_EURO = 1;
    const EXPECTED_MIN_BALANCE_LEFT_IN_BITGO_IN_EURO = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'withdraw-amount:from-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Withdraw amount from clients';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $accounts = Account::query()
            ->where(function ($q) {
                return $q->whereHas('cryptoAccountDetail')
                    ->where('is_external', '!=', AccountType::ACCOUNT_EXTERNAL)
                    ->where([
                        'status' => AccountStatuses::STATUS_ACTIVE,
                        'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
                        'account_type' => AccountType::TYPE_CRYPTO
                    ]);
            })
            ->where(function ($q) {
                return $q->whereHas('incomingTransactions')->orWhereHas('outgoingTransactions');
            })
            ->get();

        /* @var BitGOAPIService $bitGOAPIService */
        $bitGOAPIService = resolve(BitGOAPIService::class);

        foreach ($accounts as $account) {
            config()->set('projects.project', $account->cProfile->cUser->project);

            $balanceInEuro = KrakenFacade::getRateCryptoFiat($account->currency, Currency::CURRENCY_EUR, $account->balance);

            if ($balanceInEuro < self::EXPECTED_MIN_BALANCE_IN_EURO) {

                $availableBalance = $account->cryptoAccountDetail->getWalletBalance();
                $availableBalanceInEuro = KrakenFacade::getRateCryptoFiat($account->currency, Currency::CURRENCY_EUR, $availableBalance);

                if ($availableBalanceInEuro > self::EXPECTED_MIN_BALANCE_LEFT_IN_BITGO_IN_EURO) {
                    $leftAmountInBitgo = $availableBalance - $account->balance;

                    $projectId = $account->cProfile->cUser->project_id ?? null;

                    $liquidityProviderAccount = Account::getProviderAccount($account->currency, Providers::PROVIDER_LIQUIDITY, null, null, $projectId);
                    $walletProviderAccount = Account::getProviderAccount($account->currency, Providers::PROVIDER_WALLET, null, null, $projectId);

                    if ($liquidityProviderAccount && $liquidityProviderAccount->cryptoAccountDetail) {

                        $commission = $walletProviderAccount->getAccountCommission(true, TransactionType::CRYPTO_TRX);
                        $blockchainFee = $commission->blockchain_fee ?? 0;

                        $leftAmount = $leftAmountInBitgo - $blockchainFee;

                        if ($leftAmount > 0) {
//                            $bitGOAPIService->sendTransaction($account->cryptoAccountDetail, $walletProviderAccount->cryptoAccountDetail, $leftAmount, 'Withdraw to corporate');

                            print_r([
                                'accountName' => $account->name,
                                'accountBalance' => $account->balance,
                                'availableBalance' => $availableBalance,
                                'leftAmountInBitgo' => $leftAmountInBitgo,
                                'blockchainFee' => $blockchainFee,
                                'leftAmount' => $leftAmount
                            ]);
                            echo PHP_EOL;
                        }
                    }

                }
            }

        }
    }
}
