<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\OperationSubStatuses;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionSteps;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\AddTransactionRequest;
use App\Http\Requests\Backoffice\OperationRequest;
use App\Models\Account;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CProfile;
use App\Models\Commission;
use App\Models\Limit;
use App\Models\Operation;
use App\Models\PaymentProvider;
use App\Models\Project;
use App\Models\Transaction;
use App\Operations\AmountCalculators\TopUpWireCalculator;
use App\Operations\RefundTopUpCrypto;
use App\Operations\WithdrawCrypto;
use App\Services\AccountService;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\ExchangeInterface;
use App\Services\OperationService;
use App\Services\PdfGeneratorService;
use App\Services\ProjectService;
use App\Services\TransactionService;
use App\Services\Wallester\WallesterPaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    /**
     * @param Request $request
     * @param OperationService $operationService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, OperationService $operationService, ProjectService $projectService)
    {
        $operationsPending = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::PENDING);
        $operationsSuccessful = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::SUCCESSFUL);
        $operationsDeclined = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::DECLINED);
        $operationsReturned = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::RETURNED);

        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.transactions.index', compact(
            'operationsPending',
            'operationsSuccessful',
            'operationsDeclined',
            'operationsReturned',
            'projectNames'
        ));
    }

    /**
     * @param $id
     * @param $operationService
     * @param $complianceService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showTransaction($id, OperationService $operationService, ComplianceService $complianceService, Request $request)
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

        $selectedExchangeProvider = $operation->getSelectedExchangeProvider();

        $operationCalculator = new TopUpWireCalculator($operation);
        return view('backoffice.transactions.show')->with([
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
            'isCardOrderOperation' => in_array($operation->operation_type, OperationOperationType::CARD_ORDER_OPERATIONS),
            'api' => $selectedExchangeProvider->api ?? null,
            'operationCalculator' => $operationCalculator,
        ]);
    }

    //add bank detail
    public function addBankDetail(OperationRequest $request, OperationService $operationService, $id)
    {
        try {
            $account = $operationService->createAccount($request->all(), $id, AccountType::ACCOUNT_EXTERNAL);

            $wireAccountDetail = $operationService->createWireAccountDetail($request->all(), $account);
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_ADDED, ['wire_account_detail_id' => $wireAccountDetail->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_ADDED, $id);
            return redirect()->route('backoffice.show.transaction', $id)->with(['success' => 'Success']);
        } catch (Exception $e) {
            return redirect()->route('backoffice.show.transaction', $id)->with(['warning' => $e->getMessage()]);
        }
    }

    //connect operation with account
    public function confirmTransaction(Request $request, $id)
    {
        $request->validate([
            'account' => ['required']
        ]);

        $operation = Operation::find($id);
        /* @var Operation $operation*/

        if (!$operation->from_account || !$operation->transactions()->where('from_account', $request->account)->first()) {
            $operation->from_account = $request->account;
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

    /**
     * @param $id
     * @param AddTransactionRequest $request
     * @param OperationService $operationService
     * @param CommissionsService $commissionsService
     * @param TransactionService $transactionService
     * @param ExchangeInterface $exchangeService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addTransaction($id, AddTransactionRequest $request, OperationService $operationService,
                                   CommissionsService $commissionsService, TransactionService $transactionService)
    {
        try {
            $response = $operationService->createTransaction($request->except(['_token']), $id, $commissionsService,
                $transactionService);
            if (empty($response['message']) || $response['message'] != 'Success') {
                return redirect()->route('backoffice.show.transaction', $id)->with(['warning' => $response['message']]);
            } else {
                ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESSFULLY, [],  LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED_SUCCESS, $id);
                $operation = Operation::find($id);
                ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_SUCCESS, [
                    'type' => t(TransactionType::NAMES[$request->transaction_type]), 'fromAccountName' => Providers::NAMES[$request->from_type] ?? '', 'toAccountName' => Providers::NAMES[$request->to_type] ?? ''
                ], LogResult::RESULT_SUCCESS, LogType::TRANSACTION_ADDED, $id);
                return redirect()->route('backoffice.show.transaction', $id)->with(['success' => t('transaction_added_successfully')]);
            }
        } catch (Exception $e) {
            logger()->error('Transaction error ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return redirect()->route('backoffice.show.transaction', $id)->with(['warning' => $e->getMessage()]);
        }
    }

    public function addTransactionForCardOrderByWire($id, AddTransactionRequest $request, WallesterPaymentService $wallesterPaymentService)
    {
        try {
            $wallesterPaymentService->addTransactionForCardOrderByWire($id, $request->all());

            $operation = Operation::findOrFail($id);
            if ($operation->step == TransactionSteps::TRX_STEP_TWO && $operation->status != OperationStatuses::SUCCESSFUL) {
                $projectId = $operation->cProfile->cUser->project_id;
                $liqProviderAccount = Account::getProviderAccount($operation->from_currency, Providers::PROVIDER_LIQUIDITY, null, null, $projectId);
                $lastTransaction = $operation->getLastTransactionByType(TransactionType::BANK_TRX);
                if (!$liqProviderAccount) {
                    throw new OperationException(t('provider_account_not_found'));
                }
                if (!$lastTransaction) {
                    throw new OperationException(t('Last bank transaction not found'));
                }

                $wallesterPaymentService->addTransactionForCardOrderByWire($id, [
                    'transaction_type' => TransactionType::BANK_TRX,
                    'from_type' => Providers::PROVIDER_PAYMENT,
                    'to_type' => Providers::PROVIDER_LIQUIDITY,
                    'from_currency' => $operation->from_currency,
                    'from_account' => $operation->providerAccount->id,
                    'to_account' => $liqProviderAccount->id,
                    'currency_amount' => $lastTransaction->recipient_amount,
                ]);

                session()->flash('success', 'Payment success');
                return redirect()->back();
            }
        }catch (\Throwable $exception) {
            throw new OperationException($exception->getMessage());
        }

    }

    //get accounts for the add transaction form
    public function getAccountsByType(Request $request, OperationService $operationService, AccountService $accountService)
    {
        $trxType = $request->trx_type;
        $fromType = $request->from_type;

        $operation = $operationService->getOperationById($request->operation_id);

        if (($trxType == TransactionType::BANK_TRX || $trxType == TransactionType::CARD_TRX) && $fromType == Providers::CLIENT) {
            if (in_array($operation->operation_type, OperationOperationType::FIAT_WALLET_OPERATIONS)) {
                $account = $request->from ? $operation->fromAccount : $operation->toAccount;
            } else {
                $account = $operation->fromAccount;
            }
            if ($request->fromCurrency && $request->fromCurrency != $account->currency) {
                return response()->json([
                    'accounts' => null,
                ]);
            }
            return response()->json([
                'accounts' => $account,
            ]);
        } elseif ($trxType == TransactionType::CRYPTO_TRX && $fromType == Providers::CLIENT) {
            $account = $operation->toAccount;
            $walletAddress = $account->cryptoAccountDetail->address;
            return response()->json([
                'accounts' => $account,
                'walletAddress' => $walletAddress,
            ]);
        } elseif ($trxType == TransactionType::CRYPTO_TRX && $fromType == Providers::PROVIDER_WALLET) {
            $projectId = $operation->cProfile->cUser->project_id ?? null;
            $walletProviderIds = PaymentProvider::query()
                ->where('provider_type', Providers::PROVIDER_WALLET)
                ->queryByProject($projectId)->pluck('id');
            $query = Account::query()->whereIn('payment_provider_id', $walletProviderIds)
                ->where('status', AccountStatuses::STATUS_ACTIVE)
                ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                ->whereNotNull('name');

            $cryptoCurrency = ($request->fromCurrency && in_array($request->fromCurrency, Currency::getList())) ? $request->fromCurrency :
                (($request->toCurrency && in_array($request->toCurrency, Currency::getList())) ? $request->toCurrency : null);
            if ($cryptoCurrency) {
                $query->where('currency', $cryptoCurrency);
            }
            $fromAccounts = $query->get();

            return response()->json([
                'accounts' => $fromAccounts,
            ]);
        } elseif (in_array($trxType, [TransactionType::REFUND, TransactionType::CHARGEBACK]) && $fromType == Providers::CLIENT) {
            return response()->json([
                'accounts' => $operation->fromAccount
            ]);
        } elseif (in_array($trxType, [TransactionType::REFUND, TransactionType::CHARGEBACK, TransactionType::CARD_TRX]) && $fromType == Providers::PROVIDER_CARD) {
            return response()->json([
                'accounts' => $operation->getCardProviderAccount(),
            ]);
        } else {
            $currency = !$request->from && $request->toCurrency ? $request->toCurrency : $request->fromCurrency;
            $fromAccounts = $accountService->getAllowedAccountsByTrxTypeForOperation($fromType, $operation, $currency);

            $paymentProviderAccount = $operation->getProviderAccount();

            return response()->json([
                'vvv' => 555,
                'accounts' => $fromAccounts,
                'paymentProvider' => $paymentProviderAccount
            ]);
        }
    }


    //get commissions for selected account
    public function getCommissions(Request $request, OperationService $operationService)
    {
        $from = $request->from;
        $account = Account::findOrFail($request->account);
        $operation = Operation::findOrFail($request->operation_id);
        if ($request->from_type == Providers::CLIENT) {
            $account = ($from || $request->trx_type == TransactionType::REFUND) ? $operation->fromAccount : $operation->toAccount;
            if ($request->trx_type != TransactionType::REFUND) {
                if (in_array($operation->operation_type, [
                    OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA,
                    OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT,
                    OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO
                ])) {
                    $providerAccount = $operation->fromAccount->provider->accountByCurrency($operation->from_currency, AccountType::TYPE_CRYPTO);
                    if (!$providerAccount || !$providerAccount->childAccount) {
                        throw new OperationException("Provider fee account not found for operation {$operation->id}");
                    }
                    if ($request->trx_type == TransactionType::CRYPTO_TRX) {
                        $walletProviderCommission = $providerAccount->getAccountCommission($from, $request->trx_type, $operation);
                        $commission = $walletProviderCommission;
                    }
                } elseif ($operation->step == 0 && in_array($operation->operation_type, [OperationOperationType::TYPE_TOP_UP_SEPA, OperationOperationType::TYPE_TOP_UP_SWIFT, OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE])) {
                    $commission = $account->getAccountCommission(false, $request->trx_type, $operation);
                } elseif ($operation->step == 1 && in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_CARD_PF])) {
                    $commission = $operation->fromAccount->getAccountCommission($from, $request->trx_type, $operation);
                }
            }
        }
        $commission = $commission ?? $account->getAccountCommission($from, $request->trx_type, $operation);
        return response()->json([
            'transactionType' => $request->trx_type,
            'commission' => $commission,
            'toAddress' => $account->cryptoAccountDetail ? $account->cryptoAccountDetail->address : null,
            'walletProviderCommission' => $walletProviderCommission ?? null,
            'refundAmount' => $operationService->getRefundAvailableAmount($operation),
            'chargebackAmount' => $operationService->getChargebackAvailableAmount($operation),
            'isIndividualCardPayment' => $operation->operation_type == OperationOperationType::TYPE_CARD
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

    public function commissions($rateTemplateId, $request, $type = Commissions::TYPE_OUTGOING)
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

    /**
     * @param $id
     * @param TransactionService $transactionService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveTransaction($id, TransactionService $transactionService)
    {
        $transaction = Transaction::findOrFail($id);
        /* @var Transaction $transaction*/
        $operation = $transaction->operation;
        $data = $transactionService->approveTransaction($transaction);
        $result = $data['result'];
        $success = $data['success'];
        ActivityLogFacade::saveLog(LogMessage::APPROVE_TRANSACTION_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_APPROVE_TRANSACTION_SUCCESS, $operation->id);
        return redirect()->back()->with($success ? ['success' => $result['message'] ?? t('transaction_added_successfully')] : ['warning' => $result['message'] ?? '']);
    }


    public function changeStatus($operationId, Request $request)
    {
        $operation = Operation::findOrFail($operationId);
        $status = $operation->status;

        if ($request->status == OperationStatuses::SUCCESSFUL && in_array($operation->operation_type, [OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA, OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT, OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO])) {
            /* @var WallesterPaymentService $wallesterPaymentService */
            $wallesterPaymentService = resolve(WallesterPaymentService::class);

            $result = $wallesterPaymentService->markWireOperationAsSuccessful($operation);

            if (!$result) {
                session()->flash('error', (t('card_order_wire_change_status_fail')));
                return redirect()->back();
            }
        }

        $operation->status = $request->status;
        $operation->comment = $request->comment;

        if ($operation->status == OperationStatuses::DECLINED ) {
            $operation->substatus = null;
            EmailFacade::sendRejectedSepaSwiftTopUpTransaction($operation);
        } elseif ($operation->status == OperationStatuses::RETURNED) {
            $operation->substatus = $request->substatus;
            if ($operation->substatus == OperationSubStatuses::REFUND) {
                EmailFacade::sendRefundedSepaSwiftTopUpTransaction($operation);
            }
        } else {
            $operation->substatus = null;
        }
        $operation->save();

        $message = t('withdrawal_crypto_change_status_success');
        $logMessage = LogMessage::STATUS_CHANGED_SUCCESSFULLY;
        $logType = LogType::STATUS_CHANGED_SUCCESS;
        $resultType = LogResult::RESULT_SUCCESS;
        ActivityLogFacade::saveLog($logMessage, ['comment' => $operation->comment, 'oldStatus' => OperationStatuses::getName($status), 'newStatus' => OperationStatuses::getName($operation->status)], $resultType, $logType, $operation->id);

        return redirect()->back()->with(['success' => $message]);
    }

    public function getTransactionDetails(Request $request)
    {
        $transaction = Transaction::query()->with(['fromAccount', 'toAccount', 'fromCommission', 'toCommission', 'operation'])->findOrFail($request->transaction_id);
        $fromType = $transaction->fromAccount->getAccountTypeName();
        $toType = $transaction->toAccount->getAccountTypeName();
        $trxType = TransactionType::getName($transaction->type);
        $exchangeFee = $transaction->operation->getExchangeFeeAmount();

        $cryptoToCryptoDetails = $transaction->operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF ? [
            'cryptoToCryptoDetails' => [
                'incomingFee' => $transaction->operation->paymentFormAttempt->incoming_fee,
            ]
        ] : [];

        return response()->json(array_merge([
            'transaction' => $transaction,
            'fromType' => $fromType,
            'toType' => $toType,
            'trxType' => $trxType,
            'exchangeFee' => $exchangeFee ?? null,
            'toCryptoAccountDetail' => $transaction->toAccount ? $transaction->toAccount->cryptoAccountDetail : null,
        ], $cryptoToCryptoDetails));
    }

    /**
     * @param Operation $operation
     * @param TransactionService $transactionService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approveOperation(Operation $operation, TransactionService $transactionService)
    {
        if ($operation->operation_type == OperationOperationType::TYPE_WITHDRAW_CRYPTO) {
            if ($operation->transactions()->exists()) {
                return redirect()->back()->with(['error' => t('operation_has_transaction')]);
            }
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
                logger()->error('WithdrawByCryptoError.', [
                    'operationId' => $operation->id,
                    'message' => $exception->getMessage()
                ]);
                return redirect()->back()->with(['error' => $exception->getMessage()]);
            }
            ActivityLogFacade::saveLog(LogMessage::APPROVE_WITHDRAW_CRYPTO_SUCCESSFULLY, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_APPROVE_WITHDRAW_CRYPTO_SUCCESS, $operation->id);
            return redirect()->back()->with(['success' => t('transaction_added_successfully')]);
        } elseif ($operation->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO) {
            $cryptoTrx = $operation->pendingCrypto();

            if ($cryptoTrx) {
                $transactionService->approveTransaction($cryptoTrx);
                ActivityLogFacade::saveLog(LogMessage::APPROVE_TRANSACTION_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_APPROVE_TOP_UP_CRYPTO_SUCCESS, $operation->id);
            }

            return redirect()->back()->with(['success' => t('transaction_added_successfully')]);
        } elseif ($operation->operation_type == OperationOperationType::TYPE_CARD) {
            $cardTransaction = $operation->transactions()->where('type', TransactionType::CARD_TRX)->first();
            if ($operation->amount != $cardTransaction->trans_amount) {
                $operation->amount = $cardTransaction->trans_amount;
                $operation->save();
            }

            $transactionService->approveTransaction($cardTransaction);
            ActivityLogFacade::saveLog(LogMessage::APPROVE_TRANSACTION_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_APPROVE_TRANSACTION_SUCCESS, $operation->id);

        }

        return redirect()->back()->with(['error' => t('transaction_added_failed')]);
    }


    public function addTopUpCryptoTransaction($id , Request $request)
    {
        try {
            if ($request->transaction_type == TransactionType::REFUND) {
                $operation = Operation::findOrFail($id);
                $operationData = new OperationTransactionData($request->all());
                $refundTopUpCrypto = new RefundTopUpCrypto($operation, $operationData);
                $refundTopUpCrypto->execute();
                EmailFacade::sendUnsuccessfulIncomingCryptocurrencyPayment($operation);
                ActivityLogFacade::saveLog(LogMessage::ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS, [],  LogResult::RESULT_SUCCESS, LogType::TYPE_ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS, $operation->id);
                return redirect()->back()->with(['success' => t('transaction_added_successfully')]);
            }
        }catch (\Exception $exception) {
            return redirect()->route('backoffice.withdraw.crypto.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);
        }
        return redirect()->back()->with(['error' => t('transaction_added_failed')]);
    }

    public function getCsv(OperationService $operationService, Request $request)
    {
        $reportRequest = $operationService->getCsvFile($request->only(['from', 'to', 'project']));
        return response()->json([
            'reportRequestId' => $reportRequest->id,
            'report' => 'operations-report.csv'
        ]);
    }

    public function getProviderAccountsByCurrency(Request $request)
    {
        $providerType = $request->from ? $request->from_type : $request->to_type;
        $accounts = Account::getProviderAccountsQuery($request->currency, $providerType)->get();

        return response()->json([
            'accounts' => $accounts,
            'from' => $request->from,
        ]);
    }

    public function downloadOperationReportPdf(Request $request, OperationService $operationService, PdfGeneratorService $pdfGeneratorService)
    {
        switch ($request->operation) {
            case OperationStatuses::SUCCESSFUL:
                $operations = $operationService->getOperationsByFilter($request->all(), OperationStatuses::SUCCESSFUL);
                break;
            case OperationStatuses::PENDING:
                $operations = $operationService->getOperationsByFilter($request->all(), OperationStatuses::PENDING);
                break;
            case OperationStatuses::DECLINED:
                $operations = $operationService->getOperationsByFilter($request->all(), OperationStatuses::DECLINED);
                break;
            case OperationStatuses::RETURNED:
                $operations = $operationService->getOperationsByFilter($request->all(), OperationStatuses::RETURNED);
                break;
            default:
                $operations = null;
        }

        $profile = auth()->user();

        return $pdfGeneratorService->getHistoryReportPdf($operations, $profile, $request->get('from', null), $request->get('to', null), true);

    }

    public function showMerchantOperationsCsvFilterPage()
    {
        return view('backoffice.partials.generate-report');
    }

    public function generateCsvForMerchantsOperations(Request $request, OperationService $operationService)
    {

        $operationTypes = [OperationOperationType::TYPE_CARD_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF];
        $operationsQuery = $operationService->getFilteredMerchantPaymentOperations($request, $operationTypes);
        $operationService->getCsvFileForMerchantsBackoffice($operationsQuery);
    }


    public function getFromAndToAccountsForOperation(Request $request, OperationService $operationService)
    {
        /* @var Operation $operation */

        $operation = Operation::findOrFail($request->operation_id);

        $response = $operationService->getFromAndToAccountsForOperation($operation, $request->from_type, $request->to_type, $request->trx_type, $request->from_currency, $request->to_currency ?? $request->from_currency);

        return response()->json($response);

    }

    public function getCryptoAccountDetailAddress(Request $request)
    {
        $account = Account::findOrFail($request->account);
        return response()->json([
            'address' => $account->cryptoAccountDetail->address ?? ''
        ]);
    }

    /**
     * @param ProjectService $projectService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reports(ProjectService $projectService)
    {
        /** @var BUser $bUser */
        $bUser = auth()->guard('bUser')->user();

        $projectIds = !$bUser->is_super_admin ? $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions')) : [];

        $projects = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE, $projectIds);

        return view('backoffice.reports', compact('projects'));
    }
}
