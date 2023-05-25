<?php


namespace App\Http\Controllers\Cabinet;


use App\Enums\{Commissions, CommissionType, OperationOperationType, OperationStatuses};
use App\Facades\{EmailFacade, ExchangeRatesBitstampFacade};
use App\Http\Controllers\Controller;
use App\Http\Requests\WithdrawToFiatRequest;
use App\Models\{Account, Cabinet\CProfile, Cabinet\CUser, CryptoAccountDetail, Limit, Operation, Transaction};
use App\Services\{CommissionsService, ComplianceService, OperationService, TwoFAService, WalletService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Auth, Facades\Session, Str};

class WithdrawToFiatController extends Controller
{
    public function showWithdrawToFiat(CryptoAccountDetail $cryptoAccountDetail, Request $request, CommissionsService $commissionsService)
    {
        $currentId = Str::uuid()->toString();
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $currentId;
        $request->session()->put('operationIds', $operationIds);

        $cUser = auth()->user();
        $cProfile = $cUser->cProfile;

        $fiatWallets = $cProfile->getFiatWallets();

        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        $receivedAmountForCurrentMonth = Operation::where('c_profile_id', $cProfile->id)
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->where('from_currency', $cryptoAccountDetail->coin)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');

        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        if ($availableMonthlyAmount < 0) {
            $availableMonthlyAmount = 0;
        }
        $account = $cProfile->accounts()->findOrFail($cryptoAccountDetail->account_id);
        /* @var Account $account*/
        $availableCurrentAmount = formatMoney($account->getAvailableBalance(), $account->currency);

        $cryptoCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $cryptoAccountDetail->coin, Commissions::TYPE_OUTGOING);
        $blockChainFee = $cryptoCommissions->blockchain_fee ?? null;
        $blockChainFee *= OperationOperationType::BLOCKCHAIN_FEE_COUNT_WITHDRAW_FIAT;

        return view('cabinet.wallets.withdraw-to-fiat', compact(
            'cUser', 'cryptoAccountDetail', 'currentId',
            'limits', 'availableMonthlyAmount', 'availableCurrentAmount', 'fiatWallets', 'blockChainFee'
        ));

    }


    public function withdrawToFiatOperation(WalletService $walletService, TwoFAService $twoFAService, WithdrawToFiatRequest $request, OperationService $operationService, ComplianceService $complianceService, $id)
    {

        $cUser = Auth::user();

        $cProfile = $cUser->cProfile;
        /* @var CProfile $cProfile */

        $cryptoAccountDetail = $cProfile->cryptoAccountDetail()->findOrFail($id);
        /* @var  CryptoAccountDetail $cryptoAccountDetail */

        $account = $cryptoAccountDetail->account;
        $validator = $account->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($validator);
        }
        $operationIds = $request->session()->pull('operationIds', []);

        $fiatWallet = $walletService->getFiatWallet($cProfile, $request->currency);
        /* @var Account $fiatWallet */

        /* @var CUser $cUser*/
        if (in_array($request->operation_id, $operationIds) && (!$cUser->two_fa_type || $twoFAService->checkSession())) {
            $operation = $operationService->createOperation(
                $cProfile->id,
                OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO,
                $request->amount,
                $cryptoAccountDetail->coin,
                $fiatWallet->currency,
                $cryptoAccountDetail->account->id,
                $request->currency,
                OperationStatuses::PENDING,
                $request->bank_detail,
                null,
                $request->operation_id,
                $request->provider_account_id,
                $cUser->project->id
            );

            if ($operation->isLimitsVerified()) {
                EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
            } else {
                $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
            }

            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('operationCreated', t('withdraw_wire_operation_created_successfully'));

        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
        }
        return redirect()->route('cabinet.wallets.show', $id);
    }

    public function getLimits(Request $request, CommissionsService $commissionsService)
    {
        $cProfile = Auth::user()->cProfile;
        /* @var CProfile $cProfile*/

        //get transaction in some period of time
        $transactionsPerDay = null;
        $transactionsPerMonth = null;

        // @todo change query with relations and auth user id
        $operationIds = $cProfile->operations()->pluck('id');
        if ($operationIds) {
            $transactions = Transaction::whereIn('operation_id', $operationIds);
            $dailyTransactions = $transactions->whereDate('creation_date', Carbon::today())->get()->pluck('id');
            $monthlyTransactions = $transactions->whereMonth('creation_date', '=', Carbon::now()->month)->pluck('id');

            if ($dailyTransactions || $monthlyTransactions) {
                $transactionsPerDay = count($dailyTransactions);
                $transactionsPerMonth = count($monthlyTransactions);
            }
            //available transactions for month
            $receivedAmountForCurrentMonth = Operation::where('c_profile_id', $cProfile->id)
                ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount_in_euro');
        } else {
            $transactionsPerDay = 0;
            $transactionsPerMonth = 0;
            $receivedAmountForCurrentMonth = 0;
        }

        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET, $request->currency, Commissions::TYPE_INCOMING);
        $limits = $commissionsService->limits($cProfile->rate_template_id, $cProfile->compliance_level);
        $availableAmountForMonth = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        $availableAmountForMonth = $availableAmountForMonth > 0 ? $availableAmountForMonth : 0;
        $rate = ExchangeRatesBitstampFacade::rate();


        return response()->json([
            'limits' => $limits,
            'transactionsPerDay' => eur_format($transactionsPerDay),
            'transactionsPerMonth' => eur_format($transactionsPerMonth),
            'availableAmountForMonth' => eur_format($availableAmountForMonth),
            'transactionLimit' => eur_format($limits->transaction_amount_max),
            'cProfile' => $cProfile,
            'toAccountCommissions' => $toAccountCommissions ?? null,
            'commissions' => $commissions ?? null,
            'rate' => $rate,
            'blockChainFee' => number_format($blockChainFee ?? null, 8, '.', ''),
            'blockChainFeeCurrency' => $blockChainFeeCurrency ?? null,
            'exchangeCommissions' => $exchangeCommissions ?? null,
            'liquidityProviderFee' => $liquidityProviderFee ?? 0
        ]);
    }
}
