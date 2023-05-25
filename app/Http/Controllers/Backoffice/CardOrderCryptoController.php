<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\TransactionType;
use App\Enums\WallesterCardBlockTypes;
use App\Enums\WallesterCardStatuses;
use App\Enums\WallesterCardTypes;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BaseTransactionRequest;
use App\Http\Requests\Backoffice\WallesterCardRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Commission;
use App\Models\Limit;
use App\Models\Operation;
use App\Models\WallesterAccountDetail;
use App\Operations\BuyFiatFromCrypto;
use App\Operations\OrderCardByCrypto;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\Wallester\Api;
use App\Services\Wallester\WallesterPaymentService;
use App\Services\WithdrawWireService;
use Illuminate\Http\Request;
use App\Services\Wallester\Api as WallesterApi;

class CardOrderCryptoController extends Controller
{
    /**
     * @param $id
     * @param $operationService
     * @param $exchangeService
     * @param $complianceService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showTransaction($id, WithdrawWireService $withdrawWireService, OperationService $operationService, ComplianceService $complianceService, Request $request)
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

        $cryptoTransaction = $operation->transactions()
            ->where('type', TransactionType::CRYPTO_TRX)
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

        $selectedExchangeProvider = $operation->getSelectedExchangeProvider();
        $transactionDetails = $withdrawWireService->getToAccounts($operation);
        $exchangeRate = $transactionDetails['exchangeRate'] ?? null;


        return view('backoffice.cards.card-order-crypto.show')->with([
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
            'api' => $selectedExchangeProvider->api ?? null,
            'isCardOrderOperation' => in_array($operation->operation_type, OperationOperationType::CARD_ORDER_OPERATIONS),
            'exchangeRate' => KrakenFacade::ticker($operation->from_currency, $operation->to_currency),
            'cryptoTransaction' => $cryptoTransaction,
            'allCurrencies' => $operation->step >= 2 ? Currency::FIAT_CURRENCY_NAMES : Currency::getList(),
            'fromCurrency' => $transactionDetails['fromCurrency'] ?? null,
            'recipientAmount' => $transactionDetails['recipientAmount'] ?? null,
            'cryptocurrencyAmount' => $exchangeRate ? $operation->amount * $exchangeRate : null,
        ]);
    }

    public function addTransaction($id, BaseTransactionRequest $request)
    {
        $operation = Operation::findOrFail($id);
        try {
            $currentStep = $operation->step;
            $buyCryptoFromFiat = new OrderCardByCrypto($operation, new OperationTransactionData($request->all()));
            $buyCryptoFromFiat->execute();
            return redirect()->route('backoffice.card.order.crypto.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
        }
        $operation->save();
        return redirect()->route('backoffice.card.order.crypto.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);

    }

    protected function commissions($rateTemplateId, $request, $type = Commissions::TYPE_OUTGOING)
    {
        $operationType = $request['operationType'];
        $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$operationType] ?? null;
        $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$accountType];

        $commissions = Commission::where('rate_template_id', $rateTemplateId)
            ->where('type', $type)
            ->where('commission_type', $commissionType)
            ->where('currency', $request['currency'])
            ->where('is_active', Commissions::COMMISSION_ACTIVE)
            ->first();
        return $commissions;
    }

}
