<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\ComplianceLevel;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Enums\PaymentFormTypes;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionSteps;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Cabinet\TransferController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BaseTransactionRequest;
use App\Models\Operation;
use App\Operations\AmountCalculators\TopUpCardCalculator;
use App\Operations\ChargebackTopUpCard;
use App\Operations\TopUpCard;
use App\Operations\TopUpCardPF;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use Exception;
use Illuminate\Http\Request;

class CardTransactionController extends Controller
{
    public function show(Request $request,Operation $operation, OperationService $operationService, CommissionsService $commissionsService, ComplianceService $complianceService)
    {
        $cProfile = $operation->cProfile;
        $toAccount = $operation->toAccount;

        $amountCalculator = new TopUpCardCalculator($operation);
        $cryptoAccountDetail = $toAccount->cryptoAccountDetail;
        $allowedMaxAmount = $amountCalculator->getCurrentStepAmount();
        $toWallet = $cryptoAccountDetail;

        //change commissions
        $commissionForBlockChainFee = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $cryptoAccountDetail->coin, Commissions::TYPE_OUTGOING);
        $blockChainFee = ($commissionForBlockChainFee->blockchain_fee * OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[OperationOperationType::TYPE_CARD]) . ' ' . $operation->to_currency;

        $commissionForTopUpFee = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CARD, $operation->from_currency, Commissions::TYPE_INCOMING);
        $topUpFee = $commissionForTopUpFee->percent_commission ?? null;

        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);

        $complianceLevel = null;
        if ($operation->operation_type == OperationOperationType::TYPE_CARD_PF) {
            if (in_array($operation->paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
                $complianceLevel = $operation->paymentForm->kyc ? ComplianceLevel::VERIFICATION_LEVEL_1 : ComplianceLevel::VERIFICATION_LEVEL_3;
            }
        }
        $limits = TransferController::getLimits($cProfile, $complianceLevel);
        $availableMonthlyAmount = 0;
        if ($limits) {
            $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableMonthlyAmount < 0) {
                $availableMonthlyAmount = 0;
            }
        }
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $cardTransaction = $operation->getCardTransaction();
        $isCardTransactionDeclined = isset($cardTransaction) && ($cardTransaction->status == TransactionStatuses::DECLINED);

        $exchangeTransaction = $operation->getExchangeTransaction();
        $liquidityProviderAccount = $exchangeTransaction->fromAccount ?? null;


        $credited = $operation->transactions()->where('to_account', $operation->to_account)->first()->recipient_amount ?? null;

        switch ($operation->step) {
            case TransactionSteps::TRX_STEP_ONE:
                $transactionType = TransactionType::CARD_TRX;
                $fromType = Providers::CLIENT;
                $toType = Providers::PROVIDER_CARD;
                $fromCurrency = $operation->from_currency;

                break;
            case TransactionSteps::TRX_STEP_TWO:
                $transactionType = TransactionType::EXCHANGE_TRX;
                $fromType = Providers::PROVIDER_LIQUIDITY;
                $toType = Providers::PROVIDER_LIQUIDITY;
                $fromCurrency = $operation->from_currency;
//                $toCurrency = $operation->to_currency;

                break;
            case TransactionSteps::TRX_STEP_THREE:
                $transactionType = TransactionType::CRYPTO_TRX;
                $fromType = Providers::PROVIDER_LIQUIDITY;
                $toType = Providers::PROVIDER_WALLET;
                $fromCurrency = $operation->to_currency;

                break;
            default:
                $transactionType = TransactionType::CRYPTO_TRX;
                $fromType = Providers::PROVIDER_WALLET;
                $toType = Providers::CLIENT;
                $fromCurrency = $operation->to_currency;
                break;
        }

        $operationCalculator = new TopUpCardCalculator($operation);
        $nextComplianceLevels = $complianceService->getNextComplianceLevels($operation->cProfile);
        $cProfile = $operation->cProfile;
        $steps = $operation->stepInfo();

        $payerDetails = $operation->merchantOperationsInformation;

        $logFrom = $request->logFrom;
        $logTo = $request->logTo;
        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $logFrom,
            "logTo" => $logTo,
        ]);

        $selectedProvider = $operation->getSelectedExchangeProvider();
        $api = $selectedProvider->api ?? null;

        return view('backoffice.topup-card-transactions.index',
            compact('operation', 'steps', 'api',
                'transactionType',
                'payerDetails',
                'cryptoAccountDetail',
                'fromType',
                'toType',
                'fromCurrency',
                'transactions',
                'availableMonthlyAmount',
                'blockChainFee',
                'limits',
                'allowedMaxAmount',
                'topUpFee',
                'nextComplianceLevels',
                'cardTransaction',
                'operationCalculator',
                'isCardTransactionDeclined',
                'cProfile',
                'credited',
                'liquidityProviderAccount',
                'toWallet',
                'operationLogs',
                'logFrom',
                'logTo'));
    }


    public function makeTransaction(Operation $operation, BaseTransactionRequest $request)
    {
        $routeName = 'backoffice.card.transaction';
        try {
            if ($request->transaction_type == TransactionType::CHARGEBACK) {
                $chargebackTopUpCard = new ChargebackTopUpCard($operation, new OperationTransactionData($request->all()));
                $chargebackTopUpCard->execute();
            }else {
                if ($request->transaction_type == TransactionType::REFUND) {
                    $operation->step = TransactionSteps::TRX_STEP_REFUND;
                }
                $topUpCard = $operation->operation_type == OperationOperationType::TYPE_CARD ? new TopUpCard($operation, new OperationTransactionData($request->all()))
                    : new TopUpCardPF($operation, new OperationTransactionData($request->all()));
                $topUpCard->execute();
            }
            ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESSFULLY, [],  LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED_SUCCESS, $operation->id);
            ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESS, [
                'type' => t(TransactionType::NAMES[$request->transaction_type]), 'fromAccountName' => Providers::NAMES[$request->from_type]  ?? '', 'toAccountName' => Providers::NAMES[$request->to_type] ?? ''
            ], LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED, $operation->id);

            return redirect()->route($routeName, $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (Exception $exception) {
            logger()->error('TopUpCardManualError', ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            return redirect()->route($routeName, $operation->id)->with(['warning' => $exception->getMessage()]);
        }

    }

}
