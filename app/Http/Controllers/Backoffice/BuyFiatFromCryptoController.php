<?php


namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BaseTransactionRequest;
use App\Models\Limit;
use App\Models\Operation;
use App\Operations\AmountCalculators\BuyFiatByCryptoCalculator;
use App\Operations\BuyFiatFromCrypto;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\WithdrawWireService;
use Illuminate\Http\Request;

class BuyFiatFromCryptoController extends Controller
{
    public function showTransaction(Operation $operation, CommissionsService $commissionsService,
                                    OperationService $operationService, WithdrawWireService $withdrawWireService,
                                    Request $request)
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
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $operation->from_currency, Commissions::TYPE_OUTGOING);

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

        $operationCalculator = new BuyFiatByCryptoCalculator($operation);

        return view('backoffice.buy-fiat-from-crypto.show')->with([
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
            'operationCalculator' => $operationCalculator,
        ]);
    }


    public function makeTransaction(Operation $operation, BaseTransactionRequest $request)
    {
        try {
            $currentStep = $operation->step;
            $buyCryptoFromFiat = new BuyFiatFromCrypto($operation, new OperationTransactionData($request->all()));
            $buyCryptoFromFiat->execute();
            //@todo fiat
//            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_WIRE_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_WIRE_SUCCESS, $operation->id);
            return redirect()->route('backoffice.buy.fiat.from.crypto.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
        }
        $operation->save();
        return redirect()->route('backoffice.buy.fiat.from.crypto.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);

    }
}
