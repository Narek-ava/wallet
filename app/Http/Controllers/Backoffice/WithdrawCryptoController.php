<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Facades\EmailFacade;
use App\Operations\AmountCalculators\WidthrawCryptoCalculator;
use App\Operations\WithdrawCrypto;
use App\Rules\BCHAddressFormat;
use App\Enums\{Commissions,
    CommissionType,
    LogMessage,
    LogResult,
    LogType,
    OperationOperationType,
    OperationStatuses,
    OperationSubStatuses,
    Providers,
    TransactionType};
use App\Facades\ActivityLogFacade;
use App\Http\{Controllers\Controller,
    Requests\Backoffice\AddTransactionRequest,
    Requests\Backoffice\WithdrawCryptoRequest};
use App\Models\{Cabinet\CProfile, Cabinet\CUser, Commission, CryptoAccountDetail, Operation};
use App\Services\{AccountService,
    CommissionsService,
    ComplianceService,
    OperationService,
    PaymentFormsService,
    TransactionService,
    WithdrawCryptoService};
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WithdrawCryptoController extends Controller
{
    /**
     * Withdraw and Top up crypto transactions view page
     * @param $id
     * @param $commissionsService
     * @param $operationService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTransaction($id, CommissionsService $commissionsService, OperationService $operationService, PaymentFormsService $paymentFormsService, Request $request)
    {
        $passCompliance = true;
        $operation = $operationService->getOperationById($id);

        $allowedMaxAmount = $operation->calculateOperationMaxAmount();
        $cProfile = $operation->cProfile;
        $accounts = $cProfile->accounts;
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $nextComplianceLevels = (new ComplianceService())->getNextComplianceLevels($cProfile);
        $operationIds = $cProfile->operations()->pluck('id');

        if ($operationIds) {
            $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);
        } else {
            $receivedAmountForCurrentMonth = 0;
        }

        //get limits of transaction
        $commissionType = CommissionType::TYPE_CRYPTO;
        $comType = ($operation->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO) ? Commissions::TYPE_INCOMING : Commissions::TYPE_OUTGOING;
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $operation->from_currency, $comType);
        $complianceLevel = $paymentFormsService->getComplianceLevel($cProfile);
        $limits = $commissionsService->limits($cProfile->rate_template_id, $complianceLevel);

        if (!$commissions || !$limits) {
            // @todo correct error view
            return response()->json([
                'message' => 'failed'
            ]);
        } else {
            $availableAmountForMonth = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableAmountForMonth < 0) {
                $availableAmountForMonth = 0;
            }
            $withdrawFee = $operation->getWithdrawalFeeAttribute();
        }

        $passCompliance = $operation->isLimitsVerified($complianceLevel);

        $transactionAccount = $operation->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO ? $operation->fromAccount : $operation->toAccount;
        $toWallet = $transactionAccount->cryptoAccountDetail;
        $pendingCryptoTransaction = $operation->pendingCrypto();

        $txTransactionLink = $operation->getCryptoExplorerUrl();
        $steps = $operation->stepInfo();

       $payerDetails =  $operation->merchantOperationsInformation;

        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $request->logFrom,
            "logTo" => $request->logTo,
        ]);

        $operationCalculator = new WidthrawCryptoCalculator($operation);

        return view('backoffice.withdraw-crypto-transactions.show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'operation' => $operation,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'cProfile' => $cProfile,
            'nextComplianceLevels' => $nextComplianceLevels,
            'passCompliance' => $passCompliance,
            'availableMonthlyAmount' => $availableAmountForMonth ?? '-',
            'limits' => $limits,
            'toWallet' => $toWallet,
            'withdrawFee' => $withdrawFee,
            'commissions' => $commissions,
            'pendingCryptoTransaction' => $pendingCryptoTransaction,
            'link' => $txTransactionLink,
            'steps' => $steps,
            'payerDetails' => $payerDetails,
            'operationLogs' => $operationLogs,
            'logFrom' => $request->logFrom,
            'logTo' => $request->logTo,
            'operationCalculator' => $operationCalculator,
        ]);
    }



    public function addTransaction($id, AddTransactionRequest $request, WithdrawCryptoService $withdrawCryptoService, CommissionsService $commissionsService, TransactionService $transactionService)
    {
        try {
            $result = $withdrawCryptoService->createTransaction($request->except(['_token']), $id, $commissionsService, $transactionService, $request);
            if ($result['message'] && $result['message'] != 'success') {
                return redirect()->route('backoffice.withdraw.crypto.transaction', $id)->with(['warning' => $result['message']]);
            }
            ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESSFULLY, [], LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED_SUCCESS, $id);
            $operation = Operation::find($id);
            ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESS, [
                'type' => t(TransactionType::NAMES[$request->transaction_type]) , 'fromAccountName' => Providers::NAMES[$request->from_type] ?? '', 'toAccountName' => Providers::NAMES[$request->to_type] ?? ''
            ], LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED, $id);
            return redirect()->route('backoffice.withdraw.crypto.transaction', $id)->with(['success' => t('transaction_added_successfully')]);
        } catch (Exception $e) {
            return redirect()->route('backoffice.withdraw.crypto.transaction', $id)->with(['warning' => $e->getMessage()]);
        }
    }

    /**
     * @param $operationId
     * @param Request $request
     * change status of operation
     * @return mixed
     */
    public function changeStatus($operationId, Request $request)
    {
        $operation = Operation::findOrFail($operationId);
        /* @var Operation $operation*/

        $status = $operation->status;
        $operation->status = $request->status;
        $operation->comment = $request->comment;
        $operation->save();

        $message  = t('withdrawal_crypto_change_status_success');
        $logMessage = LogMessage::STATUS_CHANGED_SUCCESSFULLY;
        $logType = LogType::STATUS_CHANGED_SUCCESS;
        $resultType = LogResult::RESULT_SUCCESS;


        ActivityLogFacade::saveLog($logMessage, ['comment' => $operation->comment, 'oldStatus' => OperationStatuses::getName($status), 'newStatus' => OperationStatuses::getName($operation->status)],$resultType, $logType, $operation->id);

        return redirect()->route('backoffice.withdraw.crypto.transaction', $operationId)
            ->with(['success' => $message]);
    }

    public function withdrawCrypto(WithdrawCryptoRequest $request, WithdrawCryptoService $sendCryptoService, AccountService $accountService)
    {

        $cProfile = CProfile::find($request->cProfile_id);

        $withdrawalRequest = $request->validated();

        $fromCryptoAccount = $cProfile->cryptoAccountDetail()->findOrFail($withdrawalRequest['crypto_account_detail_id']);
        /* @var CryptoAccountDetail $fromCryptoAccount */
        $fromAccount = $fromCryptoAccount->account;
        $validator = $fromAccount->amountValidator($withdrawalRequest['amount']);
        if ($validator && $validator->fails()) {
            logger()->error('withdrawalPostValidationFail', $withdrawalRequest);
            return redirect()->back()->withInput($withdrawalRequest)->withErrors($validator);
        }

        $toAccount = $accountService->addWalletToClient($withdrawalRequest['to_wallet'],
            $fromCryptoAccount->coin, $cProfile, false);

        if (!$toAccount) {
            logger()->error('WithdrawNoAccount', $withdrawalRequest);
            return redirect()->back()->withInput($withdrawalRequest)->withErrors(['to_wallet' => t('send_crypto_wallet_fail')]);
        }
        if (!$toAccount->cryptoAccountDetail->isAllowedRisk()) {
            logger()->error('withdrawalPostRisk', $withdrawalRequest);
            return redirect()->back()->withInput($withdrawalRequest)->withErrors(['to_wallet' => t('high_score_failure')]);
        }

        $operation = $sendCryptoService->createOperation($cProfile, $withdrawalRequest, $fromCryptoAccount, $toAccount->cryptoAccountDetail, $fromAccount, $toAccount);

        if ($operation->isLimitsVerified()) {
            $operationData = new OperationTransactionData([
                'date' => date('Y-m-d'),
                'transaction_type' => TransactionType::CRYPTO_TRX,
                'from_type' => Providers::CLIENT,
                'to_type' => Providers::CLIENT,
                'from_currency' => $operation->from_currency,
                'from_account' => $operation->from_account,
                'to_account' => $operation->to_account,
                'currency_amount' => $operation->amount
            ]);
            try {
                $withdrawCrypto = new WithdrawCrypto($operation, $operationData);
                $withdrawCrypto->execute();
            } catch (\Exception $exception) {
                $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                $operation->error_message = $exception->getMessage();
                $operation->save();
                logger()->error('WithdrawByCryptoErrorBackOffice.', [
                    'operationId' => $operation->id,
                    'message' => $exception->getMessage()
                ]);
                return redirect()->back()->withInput($request->all())->with(['error' => t('withdraw_crypto_error_message')]);
            }
            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_CRYPTO_SUCCESS, ['amount' => $operation->amount, 'currency' => $operation->from_currency, 'to_address' => $toAccount->cryptoAccountDetail->address, 'from_address' => $fromAccount->cryptoAccountDetail->address], LogResult::RESULT_SUCCESS, LogType::TYPE_WITHDRAW_CRYPTO_SUCCESS, $operation->id);

            return redirect()->route('backoffice.profile', $cProfile->id)->with([
                'success' => t('operation_added_successfully')
            ]);

        } else {
            return back()->with([
                'warning' => t('withdrawal_crypto_fail')
            ]);
        }
    }

    public function getWithdrawalFee(Request $request)
    {
        $cProfile = CProfile::find($request->cProfileId);
        $cryptoAccount = CryptoAccountDetail::find($request->cryptoAccountId);

        $commissions = Commission::where('rate_template_id', $cProfile->rate_template_id)
            ->where('type', Commissions::TYPE_OUTGOING)
            ->where('commission_type', CommissionType::TYPE_CRYPTO)
            ->where('currency', $cryptoAccount->coin)
            ->where('is_active', Commissions::COMMISSION_ACTIVE)
            ->first();

        $commissionPercent = $commissions->percent_commission;
        $commissionFixed = $commissions->fixed_commission;
        $commissionMin = $commissions->min_commission;
        $commissionMax = $commissions->max_commission;

        $result = $request->amount * $commissionPercent/100 + ($commissionFixed ?? 0);

        if($commissionMax && $result >= $commissionMax){
            return response()->json([
                'result' => $commissionMax,
                'commissionPercent' => $commissionPercent,
            ]);
        }else if($commissionMin && $result <= $commissionMin){
            return response()->json([
                'result' => $commissionMin,
                'commissionPercent' => $commissionPercent,
            ]);
        }

        return response()->json([
            'result' => $result,
            'commissionPercent' => $commissionPercent,
        ]);
    }
}
