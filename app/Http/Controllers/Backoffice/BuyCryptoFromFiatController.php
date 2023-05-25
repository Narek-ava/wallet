<?php


namespace App\Http\Controllers\Backoffice;


use App\DataObjects\BaseDataObject;
use App\DataObjects\OperationTransactionData;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BaseTransactionRequest;
use App\Http\Requests\TopUpFiatWireRequest;
use App\Models\Limit;
use App\Models\Operation;
use App\Operations\AbstractOperation;
use App\Operations\AmountCalculators\BuyCryptoFromFiatCalculator;
use App\Operations\AmountCalculators\TopUpWireCalculator;
use App\Operations\BuyCryptoFromFiat;
use App\Services\BuyCryptoFromFiatService;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use Illuminate\Http\Request;



class BuyCryptoFromFiatController extends Controller
{

    public function showTransaction(
        $id, OperationService $operationService, ComplianceService $complianceService,
        Request $request, CommissionsService $commissionsService, BuyCryptoFromFiatService $buyCryptoFromFiatService
    )
    {
        $operation = Operation::findOrFail($id);
        /* @var Operation $operation*/
        $allowedMaxAmount = $operation->calculateOperationMaxAmount();
        $cProfile = $operation->cProfile;
        $accounts = $cProfile->bankDetailAccounts;
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $nextComplianceLevels = $complianceService->getNextComplianceLevels($cProfile);
        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);

        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, Commissions::TYPE_INCOMING);

        if ($limits) {
            $passCompliance = $operation->isLimitsVerified();
            $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableMonthlyAmount < 0) {
                $availableMonthlyAmount = 0;
            }
        }

        $paymentProviderAccount = $operation->getProviderAccount();
        $liquidityAccount = $operation->getLiquidityAccount();
        $credited = $operation->credited;
        $exchangeFee = $operation->getExchangeFeeAmount();

        $txTransactionLink = $operation->getCryptoExplorerUrl();

        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $request->logFrom,
            "logTo" => $request->logTo,
        ]);

        $operationCalculator = new BuyCryptoFromFiatCalculator($operation);
        $recipientAmount =  $buyCryptoFromFiatService->getAllowedAmountForOperationStep($operation);
        return view('backoffice.buy-crypto-from-fiat.show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'operation' => $operation,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'cProfile' => $cProfile,
            'nextComplianceLevels' => $nextComplianceLevels,
            'passCompliance' => $passCompliance ?? false,
            'availableMonthlyAmount' => $availableMonthlyAmount ?? 0,
            'limits' => $limits,
            'commissions' => $commissions,
            'paymentProviderAccount' => $paymentProviderAccount,
            'credited' => $credited,
            'exchangeFee' => $exchangeFee,
            'liquidityAccount' => $liquidityAccount,
            'link' => $txTransactionLink,
            'steps' => $operation->stepInfo(),
            'operationLogs' => $operationLogs,
            'logFrom' => $request->logFrom,
            'logTo' => $request->logTo,
            'operationCalculator' => $operationCalculator,
            'recipientAmount' => $recipientAmount
        ]);
    }

    public function makeTransaction(Operation $operation, BaseTransactionRequest $request)
    {
        try {
            $currentStep = $operation->step;
            $buyCryptoFromFiat = new BuyCryptoFromFiat($operation, new OperationTransactionData($request->all()));
            $buyCryptoFromFiat->execute();
            //@todo fiat
//            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_WIRE_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_WIRE_SUCCESS, $operation->id);
            return redirect()->route('backoffice.buy.crypto.from.fiat.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
        }
        $operation->save();
        return redirect()->route('backoffice.buy.crypto.from.fiat.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);

    }
}
