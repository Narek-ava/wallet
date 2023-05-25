<?php


namespace App\Http\Controllers\Backoffice;


use App\DataObjects\BaseDataObject;
use App\DataObjects\OperationTransactionData;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\OperationRequest;
use App\Http\Requests\Backoffice\WithdrawWireTransactionRequest;
use App\Http\Requests\TopUpFiatWireRequest;
use App\Models\Commission;
use App\Models\Limit;
use App\Models\Operation;
use App\Operations\AbstractOperation;
use App\Operations\AmountCalculators\TopUpFiatByWireCalculator;
use App\Operations\AmountCalculators\TopUpWireCalculator;
use App\Operations\TopUpFiatWire;
use App\Services\ComplianceService;
use App\Services\ExchangeInterface;
use App\Services\OperationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TopUpFiatWireController extends Controller
{
    /**
     * @param $id
     * @param $operationService
     * @param $exchangeService
     * @param $complianceService
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     */
    public function showTransaction($id, ExchangeInterface $exchangeService, OperationService $operationService, ComplianceService $complianceService, Request $request)
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

        $commissions = $this->commissions($cProfile->rate_template_id, [
            'operationType' => $operation->operation_type,
            'currency' => $operation->from_currency
        ], Commissions::TYPE_INCOMING);

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

        $operationCalculator = new TopUpFiatByWireCalculator($operation);

        return view('backoffice.top-up-fiat-wire.show')->with([
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
        ]);
    }

    public function commissions($rateTemplateId, $request, $type = Commissions::TYPE_OUTGOING)
    {
        $commissionType = CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE;
        $commissions = Commission::where('rate_template_id', $rateTemplateId)
            ->where('type', $type)
            ->where('commission_type', $commissionType)
            ->where('currency', $request['currency'])
            ->where('is_active', Commissions::COMMISSION_ACTIVE)
            ->first();
        return $commissions;
    }

    /**
     * @param Operation $operation
     * @param TopUpFiatWireRequest $request
     * @return RedirectResponse
     */
    public function makeTransaction(Operation $operation, TopUpFiatWireRequest $request)
    {
        try {
            $currentStep = $operation->step;
            $topUpFiatWire = new TopUpFiatWire($operation, new OperationTransactionData($request->all()));
            $topUpFiatWire->execute();
            //@todo fiat
//            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_WIRE_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_WIRE_SUCCESS, $operation->id);
            return redirect()->route('backoffice.top.up.fiat.wire.show.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
        }
        $operation->save();
        return redirect()->route('backoffice.top.up.fiat.wire.show.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);

    }

    public function addBankDetail(OperationRequest $request, OperationService $operationService, $id)
    {
//        try {
            $account = $operationService->createAccount($request->all(), $id, AccountType::ACCOUNT_EXTERNAL);
            $wireAccountDetail = $operationService->createWireAccountDetail($request->all(), $account);
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_ADDED, ['wire_account_detail_id' => $wireAccountDetail->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_ADDED, $id);
            return redirect()->route('backoffice.top.up.fiat.wire.show.transaction', $id)->with(['success' => 'Success']);
//        } catch (\Exception $e) {
//            return redirect()->route('backoffice.top.up.fiat.wire.show.transaction', $id)->with(['warning' => $e->getMessage()]);
//        }
    }

}
