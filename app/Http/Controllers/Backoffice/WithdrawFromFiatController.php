<?php


namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\OperationRequest;
use App\Http\Requests\Backoffice\WithdrawWireTransactionRequest;
use App\Models\Limit;
use App\Models\Operation;
use App\Operations\AmountCalculators\WidthrawFromFiatCalculator;
use App\Operations\AmountCalculators\WidthrawWireCalculator;
use App\Operations\WithdrawFromFiatWire;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\FiatWithdrawWireService;
use App\Services\OperationService;
use Illuminate\Http\Request;

class WithdrawFromFiatController extends Controller
{

    public function showTransaction(Operation $operation, CommissionsService $commissionsService, OperationService $operationService, FiatWithdrawWireService $withdrawWireService, Request $request)
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

        $transactionDetails = $withdrawWireService->getToAccounts($operation);

        $withdrawalFee = $operation->getWithdrawalFeeAttribute();

        $accounts = $operation->cProfile->bankDetailAccounts()->where(['currency' => $operation->from_currency])->get();

        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $request->logFrom,
            "logTo" => $request->logTo,
        ]);

        $operationCalculator = new WidthrawFromFiatCalculator($operation);

        $preferredMethod = '-';
        if ($operation->additional_data) {
            $additionalData = json_decode($operation->additional_data, true);
            $accountOperationType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$additionalData['payment_method']];
            $preferredMethod = $additionalData['payment_method'] ? AccountType::ACCOUNT_WIRE_TYPES[$accountOperationType] : '-';
        }

        return view('backoffice.withdraw-from-fiat-transactions.show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'preferredMethod' => $preferredMethod,
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
            'toAccounts' => $transactionDetails['to'] ?? null,
            'toProviders' => $transactionDetails['toProviders'] ?? null,
            'fromProviders' => $transactionDetails['fromProviders'] ?? null,
            'fromAccounts' => $transactionDetails['from'] ?? null,
            'allCurrencies' => $operation->step >= 2 ? Currency::FIAT_CURRENCY_NAMES : Currency::getList(),
            'fromCurrency' => $transactionDetails['fromCurrency'] ?? null,
            'recipientAmount' => $transactionDetails['recipientAmount'] ?? null,
            'withdrawalFee' => $withdrawalFee ?? null,
            'accounts' => $accounts,
            'steps' => $operation->stepInfo(),
            'operationLogs' => $operationLogs,
            'logFrom' => $request->logFrom,
            'logTo' => $request->logTo,
            'operationCalculator' => $operationCalculator,
        ]);
    }


    public function makeTransaction(Operation $operation, WithdrawWireTransactionRequest $request)
    {
        try {
            $currentStep = $operation->step;
            $withdrawWire = new WithdrawFromFiatWire($operation, new OperationTransactionData($request->all()));
            $withdrawWire->execute();
            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_WIRE_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_WIRE_SUCCESS, $operation->id); //@todo fiat
            return redirect()->route('backoffice.withdraw.from.fiat.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
            $operation->save();
            return redirect()->route('backoffice.withdraw.from.fiat.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);
        }
    }

    public function addBankDetail(OperationRequest $request, OperationService $operationService, $id)
    {
        try {
            $account = $operationService->createAccount($request->all(), $id, AccountType::ACCOUNT_EXTERNAL);
            $operationService->createWireAccountDetail($request->all(), $account);
            return redirect()->route('backoffice.withdraw.from.fiat.transaction', $id)->with(['success' => 'Success']);
        } catch (\Exception $e) {
            return redirect()->route('backoffice.withdraw.from.fiat.transaction', $id)->with(['warning' => $e->getMessage()]);
        }
    }
}
