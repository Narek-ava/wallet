<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\ComplianceLevel;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationStatuses;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\OperationRequest;
use App\Http\Requests\Backoffice\WithdrawWireTransactionRequest;
use App\Models\Account;
use App\Models\Limit;
use App\Models\Operation;
use App\Models\PaymentProvider;
use App\Models\Transaction;
use App\Operations\AmountCalculators\WidthrawWireCalculator;
use App\Operations\WithdrawWire;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\WithdrawWireService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;


class WithdrawWireController extends Controller
{
    /**
     * @param Operation $operation
     * @param CommissionsService $commissionsService
     * @param OperationService $operationService
     * @param WithdrawWireService $withdrawWireService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTransaction(Operation $operation, CommissionsService $commissionsService, OperationService $operationService, WithdrawWireService $withdrawWireService, Request $request)
    {
        $showCompliance = false;
        //$allowedMaxAmount = $operation->calculateOperationMaxAmount();  // @ToDo
        $allowedMaxAmount = null;
        if ($operation->step == 0) {
            $allowedMaxAmount = $operation->amount;
        }
        $cProfile = $operation->cProfile;
        $bankAccount = $operation->toAccount;
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $nextComplianceLevels = (new ComplianceService())->getNextComplianceLevels($cProfile);
        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO,  $operation->from_currency, Commissions::TYPE_OUTGOING);

        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        if ($limits) {
            $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableMonthlyAmount < 0) {
                $availableMonthlyAmount = 0;
            }
            $passCompliance = $operation->isLimitsVerified();
        }

        $cryptoTransaction = $operation->transactions()
            ->where('type', TransactionType::CRYPTO_TRX)
            ->first();
        $transactionDetails = $withdrawWireService->getToAccounts($operation);
        $exchangeRate = $transactionDetails['exchangeRate'] ?? null;
        $withdrawalFee = $operation->getWithdrawalFeeAttribute();

        $accounts = $operation->cProfile->bankDetailAccounts()->get();

        $txTransactionLink = $operation->getCryptoExplorerUrl();

        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $request->logFrom,
            "logTo" => $request->logTo,
        ]);

        $exchangeProvider = $operation->getSelectedExchangeProvider();

        $operationCalculator = new WidthrawWireCalculator($operation);

        return view('backoffice.withdraw-wire-transactions.show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'operation' => $operation,
            'bankAccount' => $bankAccount,
            'transactions' => $transactions,
            'cProfile' => $cProfile,
            'nextComplianceLevels' => $nextComplianceLevels,
            'passCompliance' => $passCompliance ?? false,
            'availableMonthlyAmount' => $availableMonthlyAmount ?? 0,
            'limits' => $limits,
            'commissions' => $commissions ?? null,
            'paymentProviderAccount' => $operation->getProviderAccount() ?? null,
            'cryptoTransaction' => $cryptoTransaction,
            'toAccounts' => $transactionDetails['to'] ?? null,
            'toProviders' => $transactionDetails['toProviders'] ?? null,
            'fromProviders' => $transactionDetails['fromProviders'] ?? null,
            'fromAccounts' => $transactionDetails['from'] ?? null,
            'exchangeRate' => $exchangeRate,
            'cryptocurrencyAmount' => $exchangeRate ? $operation->amount * $exchangeRate : null,
            'allCurrencies' => $operation->step >= 2 ? Currency::FIAT_CURRENCY_NAMES : Currency::getList(),
            'fromCurrency' => $transactionDetails['fromCurrency'] ?? null,
            'recipientAmount' => $transactionDetails['recipientAmount'] ?? null,
            'withdrawalFee' => $withdrawalFee ?? null,
            'accounts' => $accounts,
            'link' => $txTransactionLink,
            'steps' => $operation->stepInfo(),
            'operationLogs' => $operationLogs,
            'logFrom' => $request->logFrom,
            'logTo' => $request->logTo,
            'api' => $exchangeProvider->api ?? null,
            'operationCalculator' => $operationCalculator,
        ]);
    }

    //connect operation with account
    public function confirmTransaction(Request $request, $id)
    {
        $request->validate([
            'account' => ['required']
        ]);

        $operation = Operation::find($id);
        /* @var Operation $operation*/

        if (!$operation->to_account || !$operation->transactions()->where('to_account', $request->account)->first()) {
            $operation->to_account = $request->account;
            $operation->save();
            ActivityLogFacade::saveLog(LogMessage::CONFIRM_TRANSACTION_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_CONFIRM_TRANSACTION_SUCCESS, $operation->id);

            return back()->with([
                'success' => t('account_connected_successfully')
            ]);
        }
        return back()->with([
            'warning' => t('account_already_connected')
        ]);
    }

    public function addBankDetail(OperationRequest $request, OperationService $operationService, $id)
    {
        try {
            $account = $operationService->createAccount($request->all(), $id, AccountType::ACCOUNT_EXTERNAL);

            $operationService->createWireAccountDetail($request->all(), $account);

            return redirect()->route('backoffice.withdraw.wire.transaction',$id)->with(['success' => 'Success']);
        } catch (Exception $e) {
            return redirect()->route('backoffice.withdraw.wire.transaction',$id)->with(['warning' => $e->getMessage()]);
        }
    }

    /**
     * @param Operation $operation
     * @param WithdrawWireTransactionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeTransaction(Operation $operation, WithdrawWireTransactionRequest $request)
    {


        try {
            $currentStep = $operation->step;
            $withdrawWire = new WithdrawWire($operation, new OperationTransactionData($request->all()));
            $withdrawWire->execute();
            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_WIRE_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_WIRE_SUCCESS, $operation->id);
            return redirect()->route('backoffice.withdraw.wire.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
            $operation->save();
            return redirect()->route('backoffice.withdraw.wire.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);
        }

    }

    public function getAccountsByType(Request $request, WithdrawWireService $withdrawWireService)
    {
        $operation = Operation::findOrFail($request->operation_id);
        /* @var Operation $operation*/

        $projectId = $operation->cProfile->cUser->project_id ?? null;
        $fromType = $request->from_type;
        $toType = $request->to_type;
        $trxType = $request->trx_type;
        $liquidityProviderIds = PaymentProvider::where([
            'provider_type' => Providers::PROVIDER_LIQUIDITY,
            'status' => AccountStatuses::STATUS_ACTIVE
        ])->queryByProject($projectId)->pluck('id');

        $query = Account::whereIn('payment_provider_id', $liquidityProviderIds)->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM);

        $paymentProviderIds = PaymentProvider::where([
            'provider_type' => Providers::PROVIDER_PAYMENT,
            'status' => AccountStatuses::STATUS_ACTIVE
        ])->queryByProject($projectId)->pluck('id');

        if ($trxType == TransactionType::CRYPTO_TRX && $fromType == Providers::CLIENT && $toType == Providers::PROVIDER_LIQUIDITY) {
            $account = $operation->fromAccount;

            if ($request->fromCurrency) {
                $query->where('currency', $request->fromCurrency);
            }
            $liquidityAccounts = $query->get();
            return response()->json([
                'account' => $account,
                'liquidityAccounts' => $liquidityAccounts,
            ]);
        }

        if ($trxType == TransactionType::EXCHANGE_TRX && $fromType == Providers::PROVIDER_LIQUIDITY && $toType == Providers::PROVIDER_LIQUIDITY) {
            if ($request->fromCurrency) {
                $liquidityCryptoAccounts = Account::whereIn('payment_provider_id', $liquidityProviderIds)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $request->fromCurrency)
                    ->where('status', AccountStatuses::STATUS_ACTIVE);
            }

            if ($request->toCurrency) {
                $liquidityFiatAccounts = Account::whereIn('payment_provider_id', $liquidityProviderIds)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $request->toCurrency)
                    ->where('status', AccountStatuses::STATUS_ACTIVE);
            }

            $liquidityCryptoAccounts = !empty($liquidityCryptoAccounts) ? $liquidityCryptoAccounts->get() : $query->get();
            $liquidityFiatAccounts = !empty($liquidityFiatAccounts) ? $liquidityFiatAccounts->get() : $query->get();

            return response()->json([
                'liquidityCryptoAccounts' => $liquidityCryptoAccounts,
                'liquidityFiatAccounts' => $liquidityFiatAccounts,
            ]);
        }

        if ($trxType == TransactionType::BANK_TRX && $fromType == Providers::PROVIDER_LIQUIDITY && $toType == Providers::PROVIDER_PAYMENT) {
            if ($request->fromCurrency) {
                $liquidityFiatAccounts = Account::whereIn('payment_provider_id', $liquidityProviderIds)
                    ->where('currency', $request->fromCurrency)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('status', AccountStatuses::STATUS_ACTIVE)
                    ->get();
                $paymentProviders = Account::whereIn('payment_provider_id', $paymentProviderIds)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $request->fromCurrency)
                    ->where('status', AccountStatuses::STATUS_ACTIVE)->get();
            }

            $selectedPaymentProvider = Account::find($operation->provider_account_id);

            return response()->json([
                'liquidityFiatAccounts' => $liquidityFiatAccounts ?? [],
                'paymentProviders' => $paymentProviders ?? [],
                'selectedPaymentProvider' => $selectedPaymentProvider,
            ]);
        }

        if ($trxType == TransactionType::REFUND || ($trxType == TransactionType::BANK_TRX && $fromType == Providers::PROVIDER_PAYMENT && $toType == Providers::CLIENT)) {
            if ($request->fromCurrency) {
                $paymentProviders = $withdrawWireService->getAllowedFromAccounts($operation, $request->fromCurrency, $paymentProviderIds, $fromType);
            }
            $account = $operation->toAccount;

            return response()->json([
                'paymentProviders' => $paymentProviders ?? [],
                'account' => $account,
            ]);
        }

    }


    //get commissions for selected account
    public function getCommissions(Request $request)
    {
        $from = $request->from;
        $commissionType = false;
        $account = Account::findOrFail($request->account);
        if ($request->from_type == Providers::CLIENT) {
            $operation = Operation::findOrFail($request->operation_id);
            $account = $from ? $operation->fromAccount : $operation->toAccount;

        }
        $commission = $account->getAccountCommission($from, $request->trx_type);
        return response()->json([
            'commission' => $commission,
        ]);
    }

    public function getAccountsByCurrency(Request $request)
    {
        if ($request->transactionType == TransactionType::EXCHANGE_TRX) {
            if ($request->from == 'from') {
                $fromAccounts = Account::whereIn('id', array_filter($request->fromAccountIds))
                    ->where('currency', $request->fromCurrency)
                    ->get();

                return response()->json([
                    'fromAccounts' => $fromAccounts
                ]);
            }

            if ($request->from == 'to') {
                $toAccounts = Account::whereIn('id', array_filter($request->toAccountIds))
                    ->where('currency', $request->toCurrency)
                    ->get();

                return response()->json([
                    'toAccounts' => $toAccounts
                ]);
            }
        } else {
            $fromAccounts = Account::whereIn('id', array_filter($request->fromAccountIds))
                ->where('currency', $request->fromCurrency)
                ->get();

            $toAccounts = Account::whereIn('id', array_filter($request->toAccountIds))
                ->where('currency', $request->fromCurrency)
                ->get();

            return response()->json([
                'fromAccounts' => $fromAccounts,
                'toAccounts' => $toAccounts
            ]);
        }
    }

    public function showBankDetail(Request $request)
    {
        $bankAccount = Account::query()->with('wire')->findOrFail($request->account_id);

        return response()->json([
            'bankAccount' => $bankAccount ?? null
            ]);
    }
}
