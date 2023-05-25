<?php

namespace App\Services;

use App\Enums\{AccountStatuses,
    AccountType,
    Enum,
    LogMessage,
    LogResult,
    LogType,
    Notification,
    NotificationRecipients,
    OperationOperationType,
    OperationStatuses,
    OperationSubStatuses,
    OperationType,
    ProjectStatuses,
    Providers,
    ReportStatuses,
    ReportTypes,
    TransactionStatuses,
    TransactionSteps,
    TransactionType};
use App\Models\{Account,
    Cabinet\CProfile,
    Commission,
    Country,
    CryptoAccountDetail,
    Limit,
    Log,
    Operation,
    OperationFee,
    ReportRequestTemporary,
    PaymentProvider,
    Project,
    Transaction,
    WireAccountDetail};
use App\DataObjects\OperationTransactionData;
use App\Exceptions\OperationException;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Facades\KrakenFacade;
use App\Operations\AmountCalculators\BuyCryptoFromFiatCalculator;
use App\Operations\AmountCalculators\BuyFiatByCryptoCalculator;
use App\Operations\AmountCalculators\CryptoToCryptoCalculator;
use App\Operations\AmountCalculators\TopUpCardCalculator;
use App\Operations\AmountCalculators\TopUpFiatByWireCalculator;
use App\Operations\AmountCalculators\TopUpWireCalculator;
use App\Operations\AmountCalculators\WidthrawCryptoCalculator;
use App\Operations\AmountCalculators\WidthrawFromFiatCalculator;
use App\Operations\AmountCalculators\WidthrawWireCalculator;
use App\Operations\TopUpCard;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NunoMaduro\Collision\Provider;

class OperationService
{
    public function getCurrentMonthOperationsAmountSum($cProfile)
    {
        return $cProfile->operations()
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');
    }

    /**
     * @param string $id
     * @return Operation|null
     */
    public function getOperationById(string $id): ?Operation
    {
        return Operation::query()->findOrFail($id);
    }

    /**
     * @param $request
     * @param $isExternal
     * @param $operationId
     * @return Account
     */
    public function createAccount(array $request, string $operationId, ?bool $isExternal = null)
    {
        $operation = $this->getOperationById($operationId);
        if ($operation->operation_type == OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE) {
            $operationData = json_decode($operation->additional_data, true);
            $operationType = $operationData['payment_method'] ?? null;
        } else {
            $operationType = $operation->operation_type;
        }

        $account = new Account([
            'id' => Str::uuid(),
            'name' => $request['template_name'],
            'country' => $request['country'],
            'currency' => $request['currency'],
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
            'c_profile_id' => $operation->c_profile_id,
            'account_type' => OperationOperationType::ACCOUNT_OPERATION_TYPES[$operationType],
            'is_external' => $isExternal ?? false
        ]);

        $account->save();

        return $account;
    }


    /**
     * @param $request
     * @param $cProfileId
     * @return Account
     */
    public function createBankDetailAccount(array $request, string $cProfileId): Account

    {
        return Account::create([
            'id' => Str::uuid(),
            'name' => $request['template_name'],
            'country' => $request['country'],
            'currency' => $request['currency'],
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
            'c_profile_id' => $cProfileId,
            'account_type' => $request['type'],
            'is_external' => AccountType::ACCOUNT_EXTERNAL,

        ]);
    }

    /**
     * @param $data
     * @param $account
     * @return WireAccountDetail
     */
    public function createWireAccountDetail(array $data, Account $account): WireAccountDetail
    {

        $wireAccountDetail = new WireAccountDetail([
            'id' => Str::uuid(),
            'iban' => $data['iban'],
            'swift' => $data['swift'],
            'bank_name' => $data['bank_name'],
            'bank_address' => $data['bank_address'],
            'account_beneficiary' => $data['account_holder'],
            'account_number' => $data['account_number'],
            'correspondent_bank' => $data['correspondent_bank'] ?? null,
            'correspondent_bank_swift' => $data['correspondent_bank_swift'] ?? null,
            'intermediary_bank' => $data['intermediary_bank'] ?? null,
            'intermediary_bank_swift' => $data['intermediary_bank_swift'] ?? null,
            'account_id' => $account->id,
        ]);

        $wireAccountDetail->save();

        return $wireAccountDetail;
    }

    /**
     * @param ExchangeInterface $exchangeService
     * @param array $data
     * @param string $operationId
     * @param CommissionsService $commissionsService
     * @param TransactionService $transactionService
     * @return array
     * @throws \Exception
     */
    public function createTransaction(array $data, string $operationId, CommissionsService $commissionsService, TransactionService $transactionService)
    {
        try {
            $operation = Operation::findOrFail($operationId);
            $fromAccountModel = Account::findOrFail($data['from_account']);
            $toAccountModel = Account::findOrFail($data['to_account']);
            if ($data['transaction_type'] == TransactionType::BANK_TRX &&
                !($fromAccountModel->currency == ($operation->received_amount_currency ?? $operation->from_currency) &&
                    $fromAccountModel->currency == $toAccountModel->currency && $fromAccountModel->currency == $data['from_currency'] && $toAccountModel->currency == $data['from_currency'])) {
                return ['message' => t('different_currency')];
            }

            if ($data['transaction_type'] == TransactionType::EXCHANGE_TRX && ($data['from_currency'] != $operation->received_amount_currency || $data['to_currency'] != $operation->to_currency)) {
                return ['message' => t('different_currency')];
            }

            if ($data['transaction_type'] == TransactionType::BANK_TRX && $fromAccountModel->checkIfClientAccount()) {
                $operation->received_amount = $data['currency_amount'];
                $operation->received_amount_currency = $data['from_currency'];
                $operation->payment_provider_id = $provider->id ?? null;
                $operation->provider_account_id = $toAccountModel->id;
                //step 1
                $response = $this->addTransactionStepOne($data, $operation, $fromAccountModel, $toAccountModel, $commissionsService, $transactionService);
                if (!empty($response['message']) && $response['message'] != 'Success') {
                    return ['message' => $response['message']];
                } else {
                    //update operation
                    $operation->save();
                    return ['message' => 'Success'];
                }
            } elseif ($data['transaction_type'] == TransactionType::BANK_TRX && $fromAccountModel->checkIfPaymentProviderAccount()) {
                //step 2
                $response = $this->addTransactionStepTwo($data, $operation, $fromAccountModel, $toAccountModel, $commissionsService, $transactionService);

            } elseif ($data['transaction_type'] == TransactionType::REFUND && $operation->step == TransactionSteps::TRX_STEP_ONE) {
                //refund transaction
                $response = $this->refundTransaction($data, $operation, $fromAccountModel, $toAccountModel, $commissionsService, $transactionService);

            } elseif ($data['transaction_type'] == TransactionType::EXCHANGE_TRX) {
                //step 3
                $response = $this->addTransactionStepThree($data, $operation, $fromAccountModel, $toAccountModel, $transactionService);
            } else {
                throw new OperationException('Invalid chosen options!');
            }

            if (!empty($response['message']) && $response['message'] != 'Success') {
                return ['message' => $response['message']];
            }
            return ['message' => 'Success'];
        } catch (\Exception $e) {
            $activityLogService = resolve(ActivityLogService::class);
            $activityLogService->setAction(LogMessage::TRANSACTION_ADDED_FAILED)
                ->setReplacements(['message' => $e->getMessage(), 'data' => json_encode($data), 'operation' => $operation->operation_id])
                ->setResultType(LogResult::RESULT_FAILURE)
                ->setType(LogType::TRANSACTION_ADDED_FAIL)
                ->log();

            if (!($e instanceof OperationException)) {
                if (strpos($e->getMessage(), OperationSubStatuses::getName(OperationSubStatuses::INSUFFICIENT_FUNDS)) !== false) {
                    $operation->substatus = OperationSubStatuses::INSUFFICIENT_FUNDS;
                } else {
                    $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                }
                $operation->error_message = $e->getMessage();
                $operation->save();
            }

            throw $e;

        }
    }


    /**
     * First transaction from client bank to payment provider wire account
     * @param array $data
     * @param Operation $operation
     * @param Account $fromAccountModel
     * @param Account $toAccountModel
     * @param CommissionsService $commissionsService
     * @param TransactionService $transactionService
     * transactions step one
     * @return array
     */
    public function addTransactionStepOne(array $data, Operation $operation, Account $fromAccountModel, Account $toAccountModel,
                                          CommissionsService $commissionsService, TransactionService $transactionService)
    {
        $fromCommission = $fromAccountModel->getAccountCommission(false, $data['transaction_type']);
        $toCommission = $toAccountModel->getAccountCommission(false, $data['transaction_type']);
        $systemAccount = Account::getSystemAccount($data['from_currency'], $toAccountModel->account_type); //TODO log when no system account

        if (!$fromCommission || !$toCommission) {
            return ['message' => t('transaction_message_commissions_failed')];
        }

        if (!$systemAccount) {
            logger()->error('NoSystemAccount '.$operation->id. ' - ' . json_encode($data));
            return ['message' => t('transaction_message_system_account_failed')];
        }

        $providerFeeAccount = $toAccountModel->childAccount;
        if (!$providerFeeAccount) {
            return ['message' => t('transaction_message_provider_fee_account_failed')];
        }

        //@todo uncomment and fix commission edit
        //$fromCommission = $commissionsService->updateCommission($fromCommission, $data, 'from');
        //$toCommission = $commissionsService->updateCommission($toCommission, $data, 'to');
        $transactionAmount = $commissionsService->calculateCommissionAmount($fromCommission, $operation->received_amount);

        //step 1 transaction 1 from client to valter
        $bankTransaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $data['currency_amount'],
            $fromAccountModel, $toAccountModel, $data['date'], TransactionStatuses::SUCCESSFUL,
            $data['exchange_rate'], $operation, $fromCommission->id, $toCommission->id,
            'Client - ' . $fromAccountModel->name, 'Payment provider - ' . $toAccountModel->name, TransactionSteps::TRX_STEP_ONE
        );

        //step 1  transaction 2 from valter to system
        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $toAccountModel, $systemAccount,
            $data['date'], TransactionStatuses::SUCCESSFUL,
            $data['exchange_rate'], $operation,
            null, null,
            'Payment provider - ' . $toAccountModel->name, 'System - ' . $systemAccount->name, TransactionSteps::TRX_STEP_ONE, null, $bankTransaction
        );

        // step 1  transaction 3 from system to valter sepa fee
        $transactionAmount = $commissionsService->calculateCommissionAmount($toCommission, $operation->received_amount);

        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $providerFeeAccount,
            $data['date'], TransactionStatuses::SUCCESSFUL,
            $data['exchange_rate'], $operation,
            null, null,
            'System - ' . $systemAccount->name, 'Payment provider fee - ' . $providerFeeAccount->name, TransactionSteps::TRX_STEP_ONE, null, $bankTransaction
        );
        return ['message' => 'Success'];
    }

    /**
     * Bank transaction from payment provider to liquidity wire account
     * @param array $data
     * @param Operation $operation
     * @param Account $fromAccountModel
     * @param Account $toAccountModel
     * @param CommissionsService $commissionsService
     * @param TransactionService $transactionService
     * transactions step two
     * @return array
     * @throws OperationException
     */
    public function addTransactionStepTwo(
        array $data, Operation $operation, Account $fromAccountModel, Account $toAccountModel,
        CommissionsService $commissionsService, TransactionService $transactionService
    )
    {
        $systemAccount = $operation->getOperationSystemAccount();
        if (!$systemAccount) {
            throw new OperationException(t('transaction_message_system_account_failed'));
        }

        $fromCommission = $fromAccountModel->getAccountCommission(true, $data['transaction_type']);
        $toCommission = $toAccountModel->getAccountCommission(false, $data['transaction_type']);
        if (!$fromCommission || !$toCommission) {
            throw new OperationException(t('transaction_message_commissions_failed'));

        }
        $providerFeeAccount = $toAccountModel->providerFeeAccount;
        if (!$toAccountModel->childAccount || !$fromAccountModel->childAccount) {
            throw new OperationException(t('transaction_message_provider_fee_account_failed'). ' L#303');
        }

        // update commission if one of fields was edited from the form
        $fromCommission = $commissionsService->updateCommission($fromCommission, $data, 'from');
        $toCommission = $commissionsService->updateCommission($toCommission, $data, 'to');
        $transactionAmount = $commissionsService->calculateCommissionAmount($fromCommission, $data['currency_amount']);

        //transaction 1 from walter to kraken
        $bankTransaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $data['currency_amount'],
            $fromAccountModel, $toAccountModel, $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation, $fromCommission->id, $toCommission->id,
            'Payment provider - ' . $fromAccountModel->name, 'Liquidity - ' . $toAccountModel->name, TransactionSteps::TRX_STEP_TWO
        );


        //transaction 2 from system fee to valter sepa fee
        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $fromAccountModel->providerFeeAccount, $data['date'], TransactionStatuses::SUCCESSFUL,
            $data['exchange_rate'], $operation, null, null,
            'System fee - ' . $systemAccount->name, 'Payment provider fee - ' . $providerFeeAccount->name, TransactionSteps::TRX_STEP_TWO, null, $bankTransaction
        );


        //transaction 3 from system to bitstamp sepa fee
        $transactionAmount = $commissionsService->calculateCommissionAmount($toCommission, $data['currency_amount']);
        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $toAccountModel->providerFeeAccount, $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation, null, null,
            'System - ' . $systemAccount->name, 'Liquidity provider fee - ' . $toAccountModel->name, TransactionSteps::TRX_STEP_TWO, null, $bankTransaction
        );

        return ['message' => 'Success'];
    }

    /**
     *
     * Step from liquidity provider to cratos wallet provider (exchange transaction)
     * @param array $data
     * @param Operation $operation
     * @param Account $fromAccountModel
     * @param Account $toAccountModel
     * @param TransactionService $transactionService
     * @return array
     * @throws \Exception
     */
    public function addTransactionStepThree(
        array $data, Operation $operation, Account $fromAccountModel, Account $toAccountModel, TransactionService $transactionService
    )
    {
        $systemAccount = $operation->getOperationSystemAccount();
        if (!$systemAccount) {
            throw new OperationException(t('transaction_message_system_account_failed'));
        }
        $fromCurrency = $data['from_currency'];
        $toCurrency = $data['to_currency'];
        $walletProviderToCommission = $toAccountModel->getAccountCommission(0, TransactionType::EXCHANGE_TRX);

        $liquidityCryptoAccount = $fromAccountModel->getLiquidityCryptoAccount($toCurrency);
        if (!$liquidityCryptoAccount) {
            return ['message' => t('transaction_message_liquidity_crypto_account_failed', ['crypto' => $toCurrency])];
        }

        config()->set('projects.project', $operation->cProfile->cUser->project);
        /* @var ExchangeInterface $exchangeService */
        $exchangeService = resolve(ExchangeInterface::class);


        $exchangeData = $exchangeService->executeExchange($fromCurrency, $toCurrency, $data['currency_amount'],  $operation->id);

        $fromFeeAccount = $fromAccountModel->providerFeeAccount;
        if(!$fromFeeAccount){
            return ['message' => t('transaction_message_provider_fee_failed')];
        }

        // step 3 transaction 2 from Liquidity provider account to Liquidity crypto account
        $exchangeTransaction = $transactionService->createTransactions(
            TransactionType::EXCHANGE_TRX, $exchangeData->costAmount, $fromAccountModel, $liquidityCryptoAccount,
            $data['date'], TransactionStatuses::SUCCESSFUL, $exchangeData->rateAmount, $operation,
            $exchangeData->fromCommission->id, null,
            'Liquidity provider - ' . $fromAccountModel->name, 'Liquidity crypto account - ' . $liquidityCryptoAccount->name, TransactionSteps::TRX_STEP_THREE,
            $exchangeData->transactionAmount
        );

        // step 3 transaction 1 from Liquidity provider account to Liquidity provider commission account
        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $exchangeData->feeAmount, $systemAccount, $fromFeeAccount,
            $data['date'], TransactionStatuses::SUCCESSFUL, $exchangeData->rateAmount, $operation, $exchangeData->fromCommission->id,
            null, 'Liquidity provider - ' . $fromAccountModel->name, 'Liquidity crypto account - ' . $fromFeeAccount->name, TransactionSteps::TRX_STEP_THREE, null, $exchangeTransaction
        );



        $operation->exchange_rate = $exchangeData->rateAmount;
        $operation->save();

        // TODO check
        $refId = $exchangeService->withdraw($toAccountModel->currency, $toAccountModel->cryptoAccountDetail->label_in_kraken, $exchangeData->transactionAmount);
        if (!$refId) {
            ActivityLogFacade::saveLog(LogMessage::WITHDRAW_FAILED, ['message' => $exchangeData->transactionAmount], LogResult::RESULT_FAILURE, LogType::WITHDRAW_FAILED, $operation->id);
            return ['message' => 'Withdraw failed'];

        }


        $exchangeCurrency  = config('app.env') != 'local' ? $toAccountModel->currency : 'LTC';
        $trxs = $exchangeService->withdrawStatus($exchangeCurrency);
        if (!empty($trxs['result'])) {
            sleep(2);
            $withdrawTransaction = $exchangeService->getTransactionByRefId($trxs, $refId) ?? null;
            if (!$withdrawTransaction) {
                throw new OperationException('Failed to check withdraw status from Kraken!');
            }
        }

        $liquidityCryptoFeeAccount = $liquidityCryptoAccount->providerFeeAccount;
        if(!$liquidityCryptoFeeAccount){
            return ['message' => t('transaction_message_liquidity_crypto_fee_account_failed')];
        }

        // step 3 transaction 5 from  Liquidity crypto account to  Cratos wallet provider account

        $trx =  $transactionService->createTransactions(
            TransactionType::CRYPTO_TRX, $withdrawTransaction['amount'],
            $liquidityCryptoAccount, $toAccountModel,
            $data['date'], TransactionStatuses::PENDING,
            $data['exchange_rate'], $operation,
            $exchangeData->fromCommission->id,
            $walletProviderToCommission->id ?? null,
            'Liquidity crypto account', 'wallet provider account', TransactionSteps::TRX_STEP_THREE
        );
        $trx->setRefId($refId);

        if (!empty($withdrawTransaction['fee'])) {
            // step 3 transaction 3  outgoing fee after withdraw (from liquidity crypto account to liquidity crypto fee account)
            $transactionService->createTransactions(
                TransactionType::SYSTEM_FEE, $withdrawTransaction['fee'],
                $liquidityCryptoAccount, $liquidityCryptoFeeAccount,
                $data['date'], TransactionStatuses::SUCCESSFUL,
                null, $operation,
                $liquidityCryptoAccount->from_commission_id, null,
                'Liquidity crypto account - ' . $liquidityCryptoAccount->name, 'Liquidity crypto fee account - ' . $liquidityCryptoFeeAccount->name,
                TransactionSteps::TRX_STEP_THREE, null, $trx
            );
        }

        return ['message' => t('success')];
    }

    /**
     * transaction from corp wallet to client wallet
     * @param $data
     * @param $operation
     * @param $fromAccountModel
     * @param $toAccountModel
     * @param $transactionService
     * @param $transactionAmount
     * @return array
     */
    public function addTransactionStepFour(array $data, Operation $operation,
                                           Account $fromAccountModel, Account $toAccountModel,
                                           TransactionService $transactionService,
                                           float $transactionAmount)
    {


        $providerFeeAccount = $fromAccountModel->providerFeeAccount;
        if (!$providerFeeAccount) {
            return ['message' => t('transaction_message_provider_fee_account_failed')];
        }
        $blockChainFeeAmount = $fromAccountModel->cryptoBlockChainFee();

        $transactionAmount = $transactionAmount - ($blockChainFeeAmount ?? 0);

        //transaction 2 from corporate wallet to client wallet
        $transaction = $transactionService->createTransactions(
            TransactionType::CRYPTO_TRX, $transactionAmount,
            $fromAccountModel, $toAccountModel,
            $data['date'], TransactionStatuses::PENDING,
            null, $operation,
            null, null,
            'Corporate eWallet', 'Client eWallet',
            TransactionSteps::TRX_STEP_FOUR
        );


        // transaction 1 from corporate wallet to corporate wallet fee;
        if ($blockChainFeeAmount) {
            $transactionService->createTransactions(
                TransactionType::SYSTEM_FEE, $blockChainFeeAmount ?? 0,
                $fromAccountModel, $providerFeeAccount,
                $data['date'], TransactionStatuses::SUCCESSFUL,
                null, $operation,
                null, null,
                'Corporate eWallet', 'Corporate eWallet Fee',
                TransactionSteps::TRX_STEP_FOUR, null, $transaction
            );
        }

        $fromCryptoAccount = $fromAccountModel->cryptoAccountDetail;
        $toCryptoAccount = $toAccountModel->cryptoAccountDetail;
        $this->transactionFromBitGoToExternal($fromCryptoAccount, $toCryptoAccount, $transactionAmount, $operation, $transaction);

        return ['message' => 'Success'];
    }


    /**
     * @param $request
     * @param $account_id
     * @param $cProfile
     * @return mixed
     */
    public function getClientOperationsPaginationWithFilter($request, string $account_id = null, $status = null, ?CProfile $cProfile = null)
    {
        if ($account_id) {
            $query = Operation::where('c_profile_id', $cProfile->id ?? auth()->user()->cProfile->id)->
            where(function ($q) use ($account_id) {
                $q->where('from_account', $account_id)
                    ->orWhere('to_account', $account_id);
            });
        } elseif ($request->has('wallet') && $request->wallet) {
            $wallet = $request->wallet;
            $query = Operation::where(function ($q) use ($wallet) {
                $q->where('from_account', $wallet)
                    ->orWhere('to_account', $wallet);
            });
        } else {
            $cProfile = getCProfile();
            $query = Operation::where('c_profile_id', $cProfile->id);
            $timezone = $cProfile->timezone;
        }

        if ($status !== null && in_array($status, OperationStatuses::VALUES)) {
            $query = $query->where('status', $status);
        }

        if ($request->has('number') && $request->number) {
            $query->where('operation_id', $request->number);
        }
        if ($request->has('transaction_type') && $request->transaction_type) {
            if (array_key_exists($request->transaction_type, OperationType::VALUES)
                && (int)$request->transaction_type !== OperationType::ALL) {
                if (is_array(OperationType::VALUES[$request->transaction_type])) {
                    $query->whereIn('operation_type', OperationType::VALUES[$request->transaction_type]);
                } else {
                    $query->where('operation_type', OperationType::VALUES[$request->transaction_type]);
                }
            }
        }
        if ($request->has('substatus')) {
            $query->where('substatus', $request->get('substatus'));
        }
        if ($request->has('from') && $request->from) {
            $query->where('created_at', '>=', dateFromUserToUTC($request->from . ' 00:00:00', 'Y-m-d H:i:s', $cProfile->timezone));
        }
        if ($request->has('to') && $request->to) {
            $query->where('created_at', '<=', dateFromUserToUTC($request->to . ' 23:59:59', 'Y-m-d H:i:s', $cProfile->timezone));
        }

        $query->orderByDesc('operation_id');

        return $query->paginate(config('cratos.pagination.operations'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getClientOperationsHistoryWithFilter($request)
    {
        if ($request->has('wallet') && $request->wallet) {
            $wallet = $request->wallet;
            $query = Operation::where(function ($q) use ($wallet) {
                $q->where('from_account', $wallet)
                    ->orWhere('to_account', $wallet);
            });
        } else {
            $query = Operation::where('c_profile_id', auth()->user()->cProfile->id);
        }


        if ($request->has('number') && $request->number) {
            $query->where('operation_id', $request->number);
        }
        if ($request->has('transaction_type') && $request->transaction_type) {
            if (array_key_exists($request->transaction_type, OperationType::VALUES)
                && (int)$request->transaction_type !== OperationType::ALL) {
                if (is_array(OperationType::VALUES[$request->transaction_type])) {
                    $query->whereIn('operation_type', OperationType::VALUES[$request->transaction_type]);
                } else {
                    $query->where('operation_type', OperationType::VALUES[$request->transaction_type]);
                }
            }
        }
        if ($request->has('substatus')) {
            $query->where('substatus', $request->get('substatus'));
        }
        if ($request->has('from') && $request->from) {
            $query->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->has('to') && $request->to) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $query->orderByDesc('operation_id');

        return $query->paginate(config('cratos.pagination.operations'), ['*'], 'page', $request->page);
    }

    public function getRefundAvailableAmount(Operation $operation): ?float
    {
        if (in_array($operation->operation_type, OperationOperationType::TOP_UP_OPERATIONS)) {
            $systemAccount = $operation->getOperationSystemAccount();
            if (!$systemAccount) {
                throw new OperationException(t('transaction_message_system_account_failed'));
            }
            $otherSystemTransactionAmounts = (float) $operation->transactions()
                ->where('type', TransactionType::SYSTEM_FEE)
                ->where('to_account', '!=', $systemAccount->id)
                ->where('trans_amount', '<>', 0)
                ->pluck('trans_amount')
                ->sum();
            return ($operation->received_amount ?? $operation->amount) - $otherSystemTransactionAmounts;
        }

        return null;
    }

    public function getChargebackAvailableAmount(Operation $operation): ?float
    {
        if (in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_CARD_PF])) {
            $chargebackAmount = $operation->getCardTransaction()->trans_amount;
        }

        return $chargebackAmount ?? null;
    }

    /**
     * @param $data
     * @param $operationId
     * @param $fromAccountModel
     * @param $toAccountModel
     * @param $commissionsService
     * @param $transactionService
     * @param $operationService
     * @param $operation
     * @return array
     */
    public function refundTransaction(array $data, Operation $operation,
                                      Account $fromAccountModel, Account $toAccountModel,
                                      CommissionsService $commissionsService,
                                      TransactionService $transactionService)
    {
        $systemAccount = $operation->getOperationSystemAccount();
        if (!$systemAccount) {
            return ['message' => t('transaction_message_system_account_failed')];
        }

        $fromCommission = $fromAccountModel->getAccountCommission(1, TransactionType::BANK_TRX);
        $clientRefundCommission = $toAccountModel->getAccountCommission(1, TransactionType::REFUND);
        if (!$fromCommission || !$clientRefundCommission) {
            return ['message' => t('transaction_message_commissions_failed')];
        }

        $providerFeeAccount = $fromAccountModel->providerFeeAccount;
        if (!$providerFeeAccount) {
            return ['message' => t('transaction_message_provider_fee_account_failed')];
        }

        // update commission if one of fields was edited from the form
        $fromCommission = $commissionsService->updateCommission($fromCommission, $data, 'from');
        $clientRefundCommission = $commissionsService->updateCommission($clientRefundCommission, $data, 'to');

        //system account amount
        $systemTransaction = $operation->transactions()
            ->where('type', TransactionType::SYSTEM_FEE)
            ->where('to_account', $systemAccount->id)
            ->first();

        if (!$systemTransaction) {
            return ['message' => t('transaction_message_system_trx_failed')];
        }
        $systemTransactionAmount = $systemTransaction->trans_amount;

        $otherSystemTransactionAmounts =  $operation->transactions()
            ->where('type', TransactionType::SYSTEM_FEE)
            ->where('to_account', '!=', $systemAccount->id)
            ->pluck('trans_amount')
            ->sum();

        $leftAmountInCratosSystemAccount = $systemTransactionAmount - $otherSystemTransactionAmounts;

        $valterAmount = $this->getRefundAvailableAmount($operation);

        //from system to valter  without any commission
        $transactionService->createTransactions(
            $data['transaction_type'], $leftAmountInCratosSystemAccount,
            $systemAccount, $fromAccountModel, //from system to valter
            $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation,
            null, null,
            'System fee', 'Valter Sepa',
            TransactionSteps::TRX_STEP_REFUND
        );

        //refund transaction 2 from valter sepa to system
        $clientRefundCommissionAmount = $commissionsService->calculateCommissionAmount($clientRefundCommission, $operation->received_amount, true); //?????
        $valterAmount -= $clientRefundCommissionAmount;

        //refund transaction 4 from valter sepa to client

        $refundTransaction = $transactionService->createTransactions(
            $data['transaction_type'], $valterAmount,
            $fromAccountModel, $toAccountModel,
            $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation,
            null, null,
            'Payment provider', 'Client',
            TransactionSteps::TRX_STEP_REFUND
        );


        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $clientRefundCommissionAmount,
            $fromAccountModel, $systemAccount,
            $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation,
            null, null,
            'Payment provider', 'System',
            TransactionSteps::TRX_STEP_REFUND, null, $refundTransaction
        );

        //refund transaction 3 from system to valter sepa fee
        $transactionAmount = $commissionsService->calculateCommissionAmount($fromCommission, $valterAmount);

        $transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $transactionAmount,
            $systemAccount, $providerFeeAccount, $data['date'], TransactionStatuses::SUCCESSFUL,
            null, $operation, null, null, 'System fee', 'Payment provider fee',
            TransactionSteps::TRX_STEP_REFUND, null, $refundTransaction
        );

        return ['message' => 'Success'];
    }

    /**
     * @param $request
     * @param $status
     * @param null $profileId
     * @param null $paymentFormId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getOperationsByFilterPaginate($request, $status, $profileId = null, $paymentFormId = null)
    {
        $query = Operation::query();
        if ($profileId) {
            $query->where('c_profile_id', $profileId);
        } else {
            $query->whereNotIn('operation_type', [OperationOperationType::TYPE_PROVIDER_TOP_UP, OperationOperationType::TYPE_PROVIDER_WITHDRAW]);
        }
        $query = $query->where('status', $status);
        if ($request->profile_id) {
            $cProfile = (new CProfileService)->getCProfileByProfileId($request->profile_id);
            if ($cProfile) {
                $query->where('c_profile_id', $cProfile->id);
            }
        }
        if($paymentFormId) {
             $query->where('payment_form_id', $paymentFormId);
        }
        if ($request->number) {
            $query->where('operation_id', $request->number);
        }
        if ($request->transaction_type) {
            if (array_key_exists($request->transaction_type, OperationType::VALUES) && (int)$request->transaction_type !== OperationType::ALL) {

                if (is_array(OperationType::VALUES[$request->transaction_type])) {
                    $query->whereIn('operation_type', OperationType::VALUES[$request->transaction_type]);
                } else {
                    $query->where('operation_type', OperationType::VALUES[$request->transaction_type]);
                }
            }
        }
        if ($request->substatus) {
            $query->where('substatus', $request->get('substatus'));
        }
        if ($request->from) {
            $query->where('updated_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->to) {
            $query->where('updated_at', '<=', $request->to . ' 23:59:59');
        }
        if ($profile_id = $request->get('profile_id')) {
            $query->whereHas('cProfile', function ($q) use($profile_id) {
                return $q->where('profile_id', $profile_id);
            });
        }

        $bUser = auth()->guard('bUser')->user();
        if ($project_id = $request->get('project')) {
            $query->where(function ($query) use ($project_id) {
                return  $query->where('project_id', $project_id)->orWhereHas('cProfile', function ($q) use ($project_id) {
                    $q->whereHas('cUser', function ($qw) use ($project_id) {
                        $qw->whereHas('project', function ($qv) use ($project_id) {
                            $qv->where('id', $project_id);
                        });
                    });
                });
            });
        } else if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $query->where(function ($query) use ($projectIds) {
                return  $query->whereIn('project_id', $projectIds)->orWhereHas('cProfile', function ($q) use ($projectIds) {
                    $q->whereHas('cUser', function ($qw) use ($projectIds) {
                        $qw->whereHas('project', function ($qv) use ($projectIds) {
                            $qv->whereIn('id', $projectIds);
                        });
                    });
                });
            });
        }

        return $query->whereNotNull('amount')
            ->whereNotNull('status')
            ->orderByDesc('operation_id')
            ->paginate(config('cratos.pagination.operations'));
    }

    /**
     * @param $params
     * @param $status
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getOperationsByFilter($params, $status)
    {
        $query = Operation::query();

        $query = $query->where('status', $status);
        if (!empty($params['profile_id'])) {
            $query->whereHas('cProfile', function ($q) use($params) {
                return $q->where('profile_id', $params['profile_id']);
            });
        }
        if (!empty($params['number'])) {
            $query->where('operation_id', $params['number']);
        }
        if (!empty($params['transaction_type'])) {
            if (array_key_exists($params['transaction_type'], OperationType::VALUES)
                && (int)$params['transaction_type'] !== OperationType::ALL) {
                if (is_array(OperationType::VALUES[$params['transaction_type']])) {
                    $query->whereIn('operation_type', OperationType::VALUES[$params['transaction_type']]);
                } else {
                    $query->where('operation_type', OperationType::VALUES[$params['transaction_type']]);
                }
            }
        }
        if (!empty($params['substatus'])) {
            $query->where('substatus', $params['substatus']);
        }
        if (!empty($params['from'])) {
            $query->where('updated_at', '>=', $params['from'] . ' 00:00:00');
        }
        if (!empty($params['to'])) {
            $query->where('updated_at', '<=', $params['to'] . ' 23:59:59');
        }

        if(!empty($params['payment_form_id'])) {
            $query->where('payment_form_id', $params['payment_form_id']);
        }

        return $query->whereNotNull('amount')
            ->whereNotNull('status')
            ->orderByDesc('operation_id')
            ->paginate(config('cratos.pagination.operations'), ['*'], 'page', $params['page']);
    }


    /**
     * @param Transaction $transaction
     * @param Operation $operation
     * @param TransactionService $transactionService
     * @return array
     */
    public function approveTopUpStepThree(Transaction $transaction, Operation $operation,
                                          TransactionService $transactionService)
    {
        $transaction->markAsSuccessful();
        //transaction step 4(from corporate wallet to client wallet)
        $fromAccount = $transaction->toAccount; // wallet provider corporative crypto account
        $toAccount = Account::getActiveAccountById($operation->to_account);

        $errorMessage = '';
        if (!$fromAccount) {
            $errorMessage = $errorMessage . ' ' . t('transaction_message_from_account_failed');
        }

        if (!$toAccount) {
            $errorMessage = $errorMessage . ' ' . t('transaction_message_to_account_failed');
        }

        if (!$fromAccount || !$toAccount) {
            return ['message' => $errorMessage];
        }
        $commissionsService = new CommissionsService();
        /*$walletProviderToCommission = $fromAccount->getAccountCommission(0);
        if (!$walletProviderToCommission) {
            return ['message' => t('transaction_message_commissions_failed')];
        }*/

        // @todo
        //$commissionTransactionAmount = $commissionsService->calculateCommissionAmount($walletProviderToCommission, $transaction->trans_amount);

        // step 3 transaction 4  incoming fee (from wallet provider account to wallet provider fee account)
        // doing this transaction only after crypto transaction was approved
        /*$transactionService->createTransactions(
            TransactionType::SYSTEM_FEE, $commissionTransactionAmount,
            $fromAccount, $fromAccount->providerFeeAccount,
            $transaction->commit_date, TransactionStatuses::SUCCESSFUL,
            null, $operation,
            $activityLogService, null, $walletProviderToCommission->id,
            'wallet  provider crypto account', 'wallet provider fee account',
            TransactionSteps::TRX_STEP_THREE
        );*/

        $transactionStepFour = $this->addTransactionStepFour(['date' => $transaction->creation_date, 'transaction_type' => TransactionType::CRYPTO_TRX],
            $operation, $fromAccount, $toAccount, $transactionService, $transaction->trans_amount);

        if ($transactionStepFour['message'] != 'Success') {
            return ['message' => $transactionStepFour['message']];
        }

        return ['message' => 'Success'];
    }


    /**
     * @param CryptoAccountDetail $fromWallet
     * @param CryptoAccountDetail $toWallet
     * @param float $leftAmount
     * @param Operation $operation
     * @param Transaction $transaction
     * @throws OperationException
     */
    public function transactionFromBitGoToExternal(CryptoAccountDetail $fromWallet, CryptoAccountDetail $toWallet,
                                                   float $leftAmount, Operation $operation, Transaction $transaction)
    {
        $bitgoService = new BitGOAPIService();
        $bitGoTransaction = $bitgoService->sendTransaction($fromWallet, $toWallet, $leftAmount);
        if (!empty($bitGoTransaction['transfer']['txid'])) {
            $transaction->setTxId($bitGoTransaction['transfer']['txid']);
        }
    }

    /**
     * @param $cProfileId
     * @param $operationType
     * @param $amount
     * @param $fromCurrency
     * @param $toCurrency
     * @param $fromAccountId
     * @param $toAccountId
     * @param int $status
     * @param null $paymentProviderId
     * @param null $amountInEuro
     * @param null $operationId
     * @param null $paymentProviderAccountId
     * @return Operation
     */
    public function createOperation(?string $cProfileId, $operationType, $amount, $fromCurrency, $toCurrency,
                                    $fromAccountId, $toAccountId,
                                    $status = OperationStatuses::PENDING,
                                    $paymentProviderId = null,
                                    $amountInEuro = null, $operationId = null, $paymentProviderAccountId = null,
                                    $projectId = null): Operation
    {
        if (!$operationId) {
            $operationId = \Illuminate\Support\Str::uuid();
        }
        $operation = new Operation([
            'id' => $operationId,
            'c_profile_id' => $cProfileId,
            'operation_type' => $operationType,
            'amount' => $amount,
            'amount_in_euro' => $amountInEuro,
            'from_currency' => $fromCurrency,
            'to_currency' => $toCurrency,
            'from_account' => $fromAccountId,
            'to_account' => $toAccountId,
            'confirm_date' => null,
            'confirm_doc' => null,
            'exchange_rate' => null,
            'client_rate' => null,
            'created_by' => null,
            'status' => $status,
            'payment_provider_id' => $paymentProviderId,
            'provider_account_id' => $paymentProviderAccountId,
            'project_id' => $projectId,
        ]);
        if (!$amountInEuro) {
            $operation->calculateAmountInEuro();
        }

        $operation->save();

        $operation->refresh();

        ActivityLogFacade::saveLog(
            LogMessage::NEW_OPERATION_CREATED,
            [
                'operationNumber' => $operation->operation_id,
                'operationType' => OperationOperationType::getName($operationType),
                'operationAmount' => $operation->amount,
                'fromCurrency' => $operation->from_currency,
                'toCurrency' => $operation->to_currency,
            ],
            LogResult::RESULT_SUCCESS,
            LogType::TYPE_NEW_OPERATION_CREATED,
            $operationId ,
            $cProfileId ? (auth()->id()) : null
        );

        return $operation;
    }

    private function getOperationsForExcel($params) : Builder
    {
        $queryBuilder = Operation::with(['toAccount', 'fromAccount', 'cProfile', 'transactions'])
            ->with('operationFee');

        $projectIds = [];
        $bUser = auth()->guard('bUser')->user();
        if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
        }

        if (!empty($params['project'])) {
            $projectIds = $bUser->is_super_admin ? [$params['project']] : (in_array($params['project'], $projectIds) ? [$params['project']] : $projectIds);
        }

        if(!empty($projectIds)) {
            $queryBuilder->where(function ($query) use ($projectIds) {
                return  $query->whereIn('project_id', $projectIds)->orWhereHas('cProfile', function ($q) use ($projectIds) {
                    $q->whereHas('cUser', function ($qw) use ($projectIds) {
                        $qw->whereHas('project', function ($qv) use ($projectIds) {
                            $qv->whereIn('id', $projectIds)->where('status', ProjectStatuses::STATUS_ACTIVE);
                        });
                    });
                });
            });
        } else {
            $queryBuilder->whereHas('cProfile');
        }

        if (!empty($params['from'])) {
            $queryBuilder->where('created_at', '>=', $params['from'] . ' 00:00:00');
        }

        if (!empty($params['to'])) {
            $queryBuilder->where('created_at', '<=', $params['to'] . ' 23:59:59');
        }

        return $queryBuilder->orderBy('operation_id');

    }

    public function availableMonthlyAmount($cProfile)
    {
        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        $receivedAmountForCurrentMonth = (new OperationService())->getCurrentMonthOperationsAmountSum($cProfile);
        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        if ($availableMonthlyAmount < 0) {
            $availableMonthlyAmount = 0;
        }

        return $availableMonthlyAmount ?? null;
    }

    private function getCsvHeaders($isForMerchantOperations = false)
    {
        if ($isForMerchantOperations) {
            return [
                '#', 'Date', 'Type', 'Status','From Wallet', 'Wallet verified', 'Credited', 'Top up fee',
                'Cryptocurrency', 'Amount in EUR', 'Initial amount', 'Payer Name', 'Payer email', 'Payer phone'
            ];
        }
        $operationHeaders = ['Operation ID', 'Date', 'Project bank','Bank country', 'Type', 'From Currency', 'To Currency',
            'Card Number',
            'Client Name', 'Referral', 'Project Name', 'Amount in EUR', 'Operation Status', 'Operation Substatus',

            'Client Fee fiat', 'Client Fee crypto',
            'Project Income fiat', 'Project Income crypto', 'Total Project Fee',
            'Card Provider Provider Fee', 'Payment Provider Provider Fee','Liquidity Provider Provider Fee', 'Wallet Provider Provider Fee',
        ];

        $operationTransactionsHeaders = [ 'Transaction id',
            'Transaction type', 'Transaction date', 'Transaction amount', 'Transaction currency', 'Recipient amount',
            'Transaction from', 'Transaction to', 'Operation/Transaction status'];

        return array_merge($operationHeaders, $operationTransactionsHeaders);
    }

    private function getCsvHeadersMerchantsBackoffice()
    {
        return [
            '#', 'Date', 'Type', 'Card Number', 'Operation step', 'Status', 'Substatus',
            'From Account', 'From Currency', 'Amount', 'Amount in EUR', 'To currency', 'To Account', 'Parent Operation Id',
            'Top up fee', 'Blockchain Fee', 'Exchange rate', 'Credited',
            'Payment form', 'Payer Name', 'Payer email', 'Payer phone'
        ];
    }

    private function getCsvOperationRow($operation, $isForMerchantOperations = false)
    {

        if ($isForMerchantOperations) {

            $commissionForBlockChainFee = (new CommissionsService())->commissions(auth()->user()->cProfile->rate_template_id, \App\Enums\CommissionType::TYPE_CRYPTO, $operation->to_currency, \App\Enums\Commissions::TYPE_OUTGOING);
            $blockChainFee = ($commissionForBlockChainFee->blockchain_fee * \App\Enums\OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[\App\Enums\OperationOperationType::TYPE_CARD]);

            $row = [
                'operation_id' => $operation->operation_id,
                'operation_date' => $operation->created_at->format('d-m-Y'),
                'operation_type' => OperationOperationType::getName($operation->operation_type),
                'status' => OperationStatuses::getName($operation->status),
                'from_wallet' => $operation->fromAccount->cryptoAccountDetail->address ?? null,
                'wallet_verified' => $operation->isVerified ? 'Yes' : 'No',
                'send_to_client' => $operation->credited,
                'top_up_fee' => $operation->getCryptoFeeFormatted() . '+' . $blockChainFee . $operation->to_currency,
                'cryptocurrency' => $operation->to_currency,
                'amount_in_eur' => $operation->amount_in_euro ?? '-',
            ];

            if ($operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
                return array_merge($row, [
                    'initial_amount' => '',
                    'clientName' => $operation->paymentFormAttempt ? $operation->paymentFormAttempt->getPayerFullName() : null,
                    'clientEmail' => $operation->paymentFormAttempt->email ?? null,
                    'clientPhone' => $operation->paymentFormAttempt->phone ?? null
                ]);
            }
            $topUpCardPFOperation = $operation->parent->parent;

            return array_merge($row, [
                'initial_amount' => $topUpCardPFOperation->amount . ' ' . $topUpCardPFOperation->from_currency,
                'clientName' => $topUpCardPFOperation->cProfile->getFullName() ?? null,
                'clientEmail' => $topUpCardPFOperation->cProfile->cUser->email ?? null,
                'clientPhone' => $topUpCardPFOperation->cProfile->cUser->phone ?? null,
            ]);
        }
        $row = [
            'operation_id' => $operation->operation_id,
            'operation_date' => $operation->created_at->format('d-m-Y'),
            'client_bank_name' => $operation->providerAccount->provider->name ?? null,
            'client_bank_country' => ($operation->fromAccount && $operation->fromAccount->country) ? Country::getCountryNameByCode($operation->fromAccount->country) : null,
            'operation_type' => OperationOperationType::getName($operation->operation_type),
            'operation_from_currency' => $operation->from_currency,
            'operation_to_currency' => $operation->to_currency,
            'operation_card_number' => $operation->fromAccount->cardAccountDetail->card_number ?? null,
            'client_full_name' => $operation->cProfile ? $operation->cProfile->getFullName() : '',
            'referral' => $operation->cProfile ? $operation->cProfile->getReferralName() : '',
            'project' => $operation->cProfile->cUser->project->name ?? '' ,
            'amount_in_eur' => $operation->amount_in_euro,
        ];

        return array_merge($row, [
            OperationStatuses::getName($operation->status),
            OperationSubStatuses::getName($operation->substatus)
        ]);
    }

    private function getCsvOperationRowMerchantsBackoffice($operation)
    {

        $rowData = [
            'operation_id' => $operation->operation_id,
            'operation_date' => $operation->created_at->format('d-m-Y'),
            'operation_type' => OperationOperationType::getName($operation->operation_type),
            'card_number' => '',
            'operation_step' => $operation->step,
            'operation_status' => OperationStatuses::getName($operation->status),
            'operation_substatus' => OperationSubStatuses::getName($operation->substatus),
            'from_account' => $operation->fromAccount->name ?? '',
            'from_currency' => $operation->from_currency,
            'amount' => $operation->amount,
            'amount_in_euro' => $operation->amount_in_euro,
            'to_currency' => $operation->to_currency,
            'to_account' => $operation->toAccount->name ?? '',
            'parent_operation_id' => $operation->parent->operation_id ?? '',
            'top_up_fee' => $operation->top_up_fee,
            'blockchain_fee' => '',
            'exchange_rate' => $operation->exchange_rate,
            'credited' => $operation->credited,
            'payment_form' => $operation->paymentForm->name ?? '',
            'payer_name' => '',
            'payer_email' => '',
            'payer_phone' => '',
        ];

        if ($operation->operation_type == OperationOperationType::TYPE_CARD_PF) {
            $commissionForBlockChainFee = (new CommissionsService())->commissions($operation->cProfile->rate_template_id, \App\Enums\CommissionType::TYPE_CRYPTO, $operation->to_currency, \App\Enums\Commissions::TYPE_OUTGOING);
            $blockChainFee = ($commissionForBlockChainFee->blockchain_fee * \App\Enums\OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[\App\Enums\OperationOperationType::TYPE_CARD]);
            $rowData['card_number'] = $operation->fromAccount->cardAccountDetail->card_number ?? '';
            $rowData['top_up_fee'] = $operation->top_up_fee;
            $rowData['blockchain_fee'] = $blockChainFee;
            $rowData['payer_name'] = $operation->cProfile->getFullName();
            $rowData['payer_email'] = $operation->cProfile->cUser->email;
            $rowData['payer_phone'] = $operation->cProfile->cUser->phone;
        }

        if ($operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
            $rowData['top_up_fee'] = $operation->crypto_fee;
            $rowData['payer_name'] = !empty($operation->paymentFormAttempt) ? $operation->paymentFormAttempt->getPayerFullName() : '';
            $rowData['payer_email'] = $operation->paymentFormAttempt->email ?? null;
            $rowData['payer_phone'] = $operation->paymentFormAttempt->phone ?? null;
        }

        if (in_array($operation->operation_type, [OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF])) {
            $rowData['operation_step'] = $operation->status == OperationStatuses::SUCCESSFUL ? ++$operation->step : $operation->step;
        }

        return $rowData;
    }


    private function getCsvTransactionRow($transaction)
    {
        $row = array_fill(0, 23, null);
        $rowTransactions = [
            $transaction->transaction_id ?? '-',
            \App\Enums\TransactionType::getName($transaction->type) ?? '-',
            $transaction->commit_date ? date('d.m.Y', strtotime($transaction->commit_date)) : '-',
            $transaction->trans_amount ?? '-',
            $transaction->fromAccount->currency ?? '-',
            $transaction->recipient_amount ?? '-',
            $transaction->fromAccount->name ?? '-',
            $transaction->toAccount->name ?? '-',
            \App\Enums\TransactionStatuses::getName($transaction->status) ?? '-'
        ];
        $row = array_merge($row, $rowTransactions);
        return $row;
    }

    private function getCsvOperationFeeRow(?Operation $operation)
    {
        $providerCalculator =null;
        $row = array_fill(0,9, 0);

        if (!empty($operation) && in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::TYPE_TOP_UP_SEPA, OperationOperationType::TYPE_TOP_UP_SWIFT, OperationOperationType::TYPE_WITHDRAW_CRYPTO, OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT, OperationOperationType::TYPE_CARD_PF, OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF, OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT, OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET, OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO, OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE]) && $operation->status !== OperationStatuses::PENDING) {

            switch ($operation->operation_type) {
                case OperationOperationType::TYPE_CARD:
                case OperationOperationType::TYPE_CARD_PF:
                    $providerCalculator = new TopUpCardCalculator($operation);
                    break;
                case OperationOperationType::TYPE_WITHDRAW_CRYPTO:
                case OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF:
                    $providerCalculator = new WidthrawCryptoCalculator($operation);
                    break;
                case OperationOperationType::TYPE_TOP_UP_SEPA:
                case OperationOperationType::TYPE_TOP_UP_SWIFT:
                    $providerCalculator = new TopUpWireCalculator($operation);
                    break;
                case OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA:
                case OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT:
                    $providerCalculator = new WidthrawWireCalculator($operation);
                    break;
                case OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF:
                    $providerCalculator = new CryptoToCryptoCalculator($operation);
                    break;
                case OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT:
                    $providerCalculator = new BuyCryptoFromFiatCalculator($operation);
                    break;
                case OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO:
                    $providerCalculator = new BuyFiatByCryptoCalculator($operation);
                    break;
                case OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE:
                    $providerCalculator = new TopUpFiatByWireCalculator($operation);
                    break;
                case OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET:
                    $providerCalculator = new WidthrawFromFiatCalculator($operation);
                    break;

            }

            $row = [
                'client_fee_fiat' => generalMoneyFormat($providerCalculator->getClientFeeFiatAmount(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency(),
                'client_fee_crypto' => generalMoneyFormat($providerCalculator->getClientFeeCryptoAmount(), $operation->getOperationCryptoCurrency()) . ' ' . $operation->getOperationCryptoCurrency(),
                'cratos_income_fiat' => generalMoneyFormat($providerCalculator->getCratosFeeAmountFiat(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency(),
                'cratos_income_crypto' => generalMoneyFormat($providerCalculator->getCratosFeeAmountCrypto(), $operation->getOperationCryptoCurrency()) . ' ' . $operation->getOperationCryptoCurrency(),
                'cratos_income_total' => generalMoneyFormat($providerCalculator->getCratosFeeAmountFiat(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency() . ' + ' . generalMoneyFormat($providerCalculator->getCratosFeeAmountCrypto(), $operation->getOperationCryptoCurrency()) . ' ' . $operation->getOperationCryptoCurrency(),
                'card_provider_provider_fee' => generalMoneyFormat($providerCalculator->getCardProviderFeeAmount(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency(),
                'liquidity_provider_provider_fee' => generalMoneyFormat($providerCalculator->getLiquidityProviderFeeAmountFiat(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency() . ' + ' . generalMoneyFormat($providerCalculator->getLiquidityProviderFeeAmountCrypto(), $operation->getOperationCryptoCurrency()) . ' ' . $operation->getOperationCryptoCurrency(),
                'wallet_provider_provider_fee' => generalMoneyFormat($providerCalculator->getWalletProviderFeeAmount(), $operation->getOperationCryptoCurrency()) . ' ' . $operation->getOperationCryptoCurrency(),
                'payment_provider_provider_fee' => generalMoneyFormat($providerCalculator->getPaymentProviderFeeAmount(), $operation->getOperationFiatCurrency()) . ' ' . $operation->getOperationFiatCurrency(),
            ];
        }

        return $row;
    }

    public function getCsvFile($params)
    {
        $reportRequestModel = new ReportRequestTemporary();
        /** @var ReportRequestTemporary  $reportRequestModel */

        $reportRequestModel->status = ReportStatuses::REPORT_NEW;
        $reportRequestModel->parameters = json_encode($params);
        $reportRequestModel->report_type = ReportTypes::REPORT_OPERATIONS;
        $reportRequestModel->save();

        return $reportRequestModel;
    }

    public function generateCsvReport($params, $reportRequestId, $report_type)
    {
        $file = fopen('php://temp', "w");
        fputcsv($file, $this->getCsvHeaders());
        $operations = $this->getOperationsForExcel($params)->get();
        foreach ($operations as $operation) {
            /**@var Operation $operation*/
            fputcsv($file, array_merge($this->getCsvOperationRow($operation), $this->getCsvOperationFeeRow($operation)));
            if ($operation->transactions->isNotEmpty()) {
                foreach($operation->transactions as $transaction) {
                    fputcsv($file,  $this->getCsvTransactionRow($transaction));
                }
            }
        }
        rewind($file);
        $output = stream_get_contents($file);
        fclose($file);

        if (!file_exists(storage_path('reports'))) {
            mkdir(storage_path('reports'));
        }

        $link = "reports/{$reportRequestId}_{$report_type}.csv";

        Storage::put($link, $output);
    }


    public function getCsvFileForMerchants($operationsQuery)
    {
        header("Content-disposition: attachment; filename=merchant-operations-report.csv");
        header("Content-Type: text/csv");
        header('Set-Cookie: fileLoading=true');
        $file = fopen('php://temp', "w");
        fputcsv($file, $this->getCsvHeaders(true));
        $operationsQuery->chunk(config('cratos.chunk.report'), function ($operations) use ($file) {
            foreach ($operations as $operation) {
                /**@var Operation $operation*/
                fputcsv($file, $this->getCsvOperationRow($operation, true));
            }
        });
        rewind($file);
        print(stream_get_contents($file));
        fclose($file);
        exit();
    }

    public function getCsvFileForMerchantsBackoffice($params)
    {
        $reportRequestModel = new ReportRequestTemporary();
        /** @var ReportRequestTemporary  $reportRequestModel */

        $reportRequestModel->status = ReportStatuses::REPORT_NEW;
        $reportRequestModel->parameters = json_encode($params);
        $reportRequestModel->report_type = ReportTypes::REPORT_MERCHANT;
        $reportRequestModel->save();

        return $reportRequestModel;
    }

    public function getOperationReportPdf($params)
    {
        $reportRequestModel = new ReportRequestTemporary();
        /** @var ReportRequestTemporary  $reportRequestModel */

        $reportRequestModel->status = ReportStatuses::REPORT_NEW;
        $reportRequestModel->parameters = json_encode($params);
        $reportRequestModel->report_type = ReportTypes::REPORT_OPERATIONS_PDF;
        $reportRequestModel->save();

        return $reportRequestModel;
    }

    public function generateOperationReportPdf($params, $reportRequestId, $report_type)
    {

        switch ($params['operation']) {
            case OperationStatuses::SUCCESSFUL:
                $operations = $this->getOperationsByFilter($params, OperationStatuses::SUCCESSFUL);
                break;
            case OperationStatuses::PENDING:
                $operations = $this->getOperationsByFilter($params, OperationStatuses::PENDING);
                break;
            case OperationStatuses::DECLINED:
                $operations = $this->getOperationsByFilter($params, OperationStatuses::DECLINED);
                break;
            case OperationStatuses::RETURNED:
                $operations = $this->getOperationsByFilter($params, OperationStatuses::RETURNED);
                break;
            default:
                $operations = null;
        }

        app(PdfGeneratorService::class)->generateOperationReportPdf($operations, $params, $reportRequestId, $report_type);
    }

    public function generateCsvFileForMerchantsBackoffice($operationsQuery, $reportRequestId, $report_type)
    {

        header("Content-disposition: attachment; filename=merchant-operations-report.csv");
        header("Content-Type: text/csv");
        header('Set-Cookie: fileLoading=true');
        $file = fopen('php://temp', "w");
        fputcsv($file, $this->getCsvHeadersMerchantsBackoffice());
        fputcsv($file, []);

        $operations = $operationsQuery->get();

        foreach ($operations as $operation) {
            /**@var Operation $operation*/
            while ($operation) {
                fputcsv($file, $this->getCsvOperationRowMerchantsBackoffice($operation));
                $operation = $operation->child;
            }
            fputcsv($file, []);
        }
        rewind($file);
        $output = stream_get_contents($file);
        fclose($file);

        if (!file_exists(storage_path('reports'))) {
            mkdir(storage_path('reports'));
        }

        $link = "reports/{$reportRequestId}_{$report_type}.csv";

        Storage::put($link, $output);
    }


    public function addTransactionFromProviderToClientWallet(array $data, Operation $operation,
                                           Account $fromAccountModel, Account $toAccountModel,
                                           float $transactionAmount)
    {

        $transactionService = new TransactionService();
        /* @var TransactionService $transactionService */

        $providerFeeAccount = $fromAccountModel->providerFeeAccount;
        if (!$providerFeeAccount) {
            return ['message' => t('transaction_message_provider_fee_account_failed')];
        }
        $blockChainFeeAmount = $fromAccountModel->cryptoBlockChainFee();

        $transactionAmount = $transactionAmount - ($blockChainFeeAmount ?? 0);

        $nextStep = ++$operation->step;

        // transaction from corporate wallet to corporate wallet fee;
        if ($blockChainFeeAmount) {
            $transactionService->createTransactions(
                TransactionType::SYSTEM_FEE, $blockChainFeeAmount ?? 0,
                $fromAccountModel, $providerFeeAccount,
                $data['date'], TransactionStatuses::SUCCESSFUL,
                null, $operation,
                null, null,
                'Corporate eWallet', 'Corporate eWallet Fee',
                $nextStep
            );
        }


        //transaction from corporate wallet to client wallet
        $transaction = $transactionService->createTransactions(
            TransactionType::CRYPTO_TRX, $transactionAmount,
            $fromAccountModel, $toAccountModel,
            $data['date'], TransactionStatuses::PENDING,
            null, $operation,
            null, null,
            'Corporate eWallet', 'Client eWallet',
            $nextStep
        );

        $fromCryptoAccount = $fromAccountModel->cryptoAccountDetail;
        $toCryptoAccount = $toAccountModel->cryptoAccountDetail;
        $this->transactionFromBitGoToExternal($fromCryptoAccount, $toCryptoAccount, $transactionAmount, $operation, $transaction);

        return ['message' => 'Success'];
    }


    public function getProviderAccountByProviderType($currency, $providerType ,$ownerType = AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
    {
        return Account::query()->where('owner_type', $ownerType)
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->where('currency', $currency)->whereHas('provider', function ($q) use($providerType){
                $q->where(['provider_type' => $providerType]);
            })->first();
    }

    public function makeProviderOperation(int $operationType,array $dataArray)
    {
        $transactionService = resolve(TransactionService::class);
        /* @var TransactionService $transactionService */

        $commissionsService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionsService */

        $operation = $this->createOperation(null, $operationType, $dataArray['amount'], $dataArray['currency'], $dataArray['currency'], $dataArray['from_account'], $dataArray['to_account'], OperationStatuses::SUCCESSFUL);

        $fromProviderCommission = $operation->fromAccount->getAccountCommission(true);
        $toProviderCommission = $operation->toAccount->getAccountCommission(false);

        $formProviderFeeAccount = $operation->fromAccount->childAccount ?? null;
        $toProviderFeeAccount = $operation->toAccount->childAccount ?? null;

        $fromProviderFeeAmount = $fromProviderCommission ? $commissionsService->calculateCommissionAmount($fromProviderCommission, $operation->amount) : 0;
        $transAmount = $operation->amount - $fromProviderFeeAmount;

        $parentTransaction = $transactionService->createTransactions($dataArray['transaction_type'], $transAmount, $operation->fromAccount, $operation->toAccount, $operation->created_at, TransactionStatuses::SUCCESSFUL, $operation->exchange_rate, $operation, $fromProviderCommission->id ?? null, $toProviderCommission->id ?? null);

        if ($fromProviderFeeAmount && $formProviderFeeAccount) {
            $transactionService->createTransactions(TransactionType::SYSTEM_FEE, $fromProviderFeeAmount, $operation->fromAccount, $formProviderFeeAccount, $operation->created_at, TransactionStatuses::SUCCESSFUL, $operation->exchange_rate, $operation, null, null, null, null, null, null, $parentTransaction);
        }

        $toProviderFeeAmount = $toProviderCommission ? $commissionsService->calculateCommissionAmount($toProviderCommission, $transAmount) : 0;

        if ($toProviderFeeAmount && $toProviderFeeAccount) {
            $transactionService->createTransactions(TransactionType::SYSTEM_FEE, $toProviderFeeAmount, $operation->toAccount, $toProviderFeeAccount, $operation->created_at, TransactionStatuses::SUCCESSFUL, $operation->exchange_rate, $operation, null, null, null, null, null, null, $parentTransaction);
        }
    }

    public function getFilteredMerchantPaymentOperations(Request $request, array $operationTypes,?CProfile $profile = null)
    {

        $queryBuilder = Operation::query()->whereIn('operation_type', $operationTypes);

        $projectIds = [];
        $bUser = auth()->guard('bUser')->user();
        if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
        }

        if ($request->has('project')) {
            $projectIds = $bUser->is_super_admin ? [$request->get('project')] : (in_array($request->get('project'), $projectIds) ? [$request->get('project')] : $projectIds);
        }

        if(!empty($projectIds)) {
            $queryBuilder->where(function ($query) use ($projectIds) {
                return  $query->whereIn('project_id', $projectIds)->orWhereHas('cProfile', function ($q) use ($projectIds) {
                    $q->whereHas('cUser', function ($qw) use ($projectIds) {
                        $qw->whereHas('project', function ($qv) use ($projectIds) {
                            $qv->whereIn('id', $projectIds)->where('status', ProjectStatuses::STATUS_ACTIVE);
                        });
                    });
                });
            });
        } else {
            $queryBuilder->whereHas('cProfile');
        }

        if ($profile) {
            $queryBuilder->where('c_profile_id', $profile->id);
        }

        if ($request->has('from') && $request->from) {
            $queryBuilder->where('created_at', '>=', $request->from . ' 00:00:00');
        }
        if ($request->has('to') && $request->to) {
            $queryBuilder->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        return $queryBuilder;
    }

    public function getFilteredMerchantOperations($params, array $operationTypes,?CProfile $profile = null)
    {

        $query = Operation::query()->whereIn('operation_type', $operationTypes);

        if (!empty($params['from'])) {
            $query->where('created_at', '>=', $params['from'] . ' 00:00:00');
        }
        if (!empty($params['to'])) {
            $query->where('created_at', '<=', $params['to'] . ' 23:59:59');
        }

        return $query;
    }

    /**
     * @param array $filterData
     * @return void
     */
    public function generateCryptoToCryptoMerchantReport(array $filterData)
    {
        $query = $this->getCryptoToCryptoMerchantOperationQuery($filterData);
        $this->getCsvFileForCryptoToCryptoMerchants($query);
    }

    /**
     * @param array $filterData
     * @return Builder
     */
    public function getCryptoToCryptoMerchantOperationQuery(array $filterData)
    {
        $query = Operation::query()->where(['payment_form_id' => $filterData['payment_form_id'], 'status' => $filterData['operation']]);

        if ($filterData['number']) {
            $query->where('operation_id', $filterData['number']);
        }
        if ($filterData['transaction_type']) {
            if (array_key_exists($filterData['transaction_type'], OperationType::VALUES)
                && (int)$filterData['transaction_type'] !== OperationType::ALL) {
                if (is_array(OperationType::VALUES[$filterData['transaction_type']])) {
                    $query->whereIn('operation_type', OperationType::VALUES[$filterData['transaction_type']]);
                } else {
                    $query->where('operation_type', OperationType::VALUES[$filterData['transaction_type']]);
                }
            }
        }
        if ($filterData['substatus']) {
            $query->where('substatus', $filterData['substatus']);
        }

        if ($filterData['profile_id']) {
            $query->where('c_profile_id',$filterData['profile_id']);
        }

        if ($filterData['from']) {
            $query->where('created_at', '>=', $filterData['from'] . ' 00:00:00');
        }
        if ($filterData['to']) {
            $query->where('created_at', '<=', $filterData['to'] . ' 23:59:59');
        }

        return $query;
    }

    /**
     * @param $operationsQuery
     * @return void
     */
    public function getCsvFileForCryptoToCryptoMerchants($operationsQuery)
    {
        header("Content-disposition: attachment; filename=crypto-to-crypto-operations-report.csv");
        header("Content-Type: text/csv");
        header('Set-Cookie: fileLoading=true');
        $file = fopen('php://temp', "w");
        fputcsv($file, $this->getCsvHeadersCryptoToCryptoMerchants());

        $operationsQuery->chunk(config('cratos.chunk.report'), function ($operations) use ($file) {
            foreach ($operations as $operation) {
                /**@var Operation $operation*/
                fputcsv($file, $this->getCsvCryptoToCryptoOperationRow($operation));
            }
        });
        rewind($file);
        print(stream_get_contents($file));
        fclose($file);
    }

    public function getCsvCryptoToCryptoOperationRow($operation)
    {

        return [
            'operation_id' => $operation->operation_id,
            'operation_date' => $operation->created_at->toDateTimeString(),
            'clientId' =>  $operation->paymentFormAttempt->cProfile->profile_id ?? null,
            'clientEmail' =>  $operation->paymentFormAttempt->cProfile->cUser->email ?? null,
            'clientPhone' =>  $operation->paymentFormAttempt->cProfile->cUser->phone ?? null,
            'wallet' => $operation->toAccount->name,
            'amount' => $operation->amount,
            'commission' => $operation->paymentFormAttempt->incoming_fee ?? 0,
            'credited' => $operation->credited,
            'cryptoExplorerUrl' => $operation->getCryptoExplorerUrl() ?? '',
            'status' => OperationStatuses::getName($operation->status),
        ];
    }

    private function getCsvHeadersCryptoToCryptoMerchants()
    {
        return  [
            '#', 'Date', 'Payer ID', 'Payer email', 'Payer phone', 'Merchant Wallet', 'Amount', 'Commission', 'Credited', 'Crypto Explorer Link', 'Status'
        ];
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function searchOperationLogs(array $params)
    {
        $query = Log::query()->where('context_id', $params['operationId'])
            ->whereIn('type', LogType::MANAGER_HISTORY_LOG_TYPES)
            ->whereNotNull('b_user_id')->orderBy('created_at', 'desc');

        if (!empty($params['logFrom'])) {
            $query->where('created_at', '>=', $params['logFrom'] . ' 00:00:00');
        }
        if (!empty($params['logTo'])) {
            $query->where('created_at', '<=', $params['logTo'] . ' 23:59:59');
        }

        return $query->paginate(10, ['*'], Enum::OPERATION_PAGE_NAME);
    }

    public function calculateFeeWithLiqProviderCommission(string $currency, ?string $exchangeToCurrency, float $operationAmount, int $operationType, ?Commission $clientCommission = null)
    {
        $commissionService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionService */

        $project = Project::getCurrentProject();
        config()->set('projects.project', $project);

        if ($operationType == OperationType::TOP_UP_WIRE) {
            $systemFeeInFiat = $commissionService->calculateCommissionAmount($clientCommission, $operationAmount);
            $leftAmountFiat = $operationAmount - $systemFeeInFiat;
            $rate = KrakenFacade::getRateCryptoFiat($currency, $exchangeToCurrency, 1);
            $leftAmount = $leftAmountFiat / $rate;

        } elseif ($operationType == OperationType::WITHDRAW_WIRE) {
            $leftAmount = $operationAmount;
        }

        $walletProviderAccount = Account::getProviderAccount($currency, Providers::PROVIDER_WALLET, null, null, $project->id);
        $walletProviderCommission = $walletProviderAccount->getAccountCommission(true);
        $leftAmount -= $walletProviderCommission->blockchain_fee;


        return $commissionService->getLiqProviderCommissionFee($currency, $leftAmount, $project->id);
    }


    public function getFromAndToAccountsForOperation(Operation $operation,?int $fromType, ?int $toType, ?int $transactionType, ?string $fromCurrency, ?string $toCurrency)
    {
        $projectId = $operation->cProfile->cUser->project_id ?? null;
        $api = null;


        switch ($transactionType) {
            case TransactionType::EXCHANGE_TRX:
                $provider = $operation->getSelectedExchangeProvider();
                if ($provider) {
                    $api = $provider->api;
                    if ($fromType == Providers::PROVIDER_LIQUIDITY && $fromCurrency) {
                        $fromAccounts = $provider->accounts()->where([
                            'currency' => $fromCurrency,
                            'status' => AccountStatuses::STATUS_ACTIVE
                        ])->get();
                    }
                    if ($toType == Providers::PROVIDER_LIQUIDITY && $toCurrency) {
                        $toAccounts = $provider->accounts()->where([
                            'currency' => $toCurrency,
                            'status' => AccountStatuses::STATUS_ACTIVE
                        ])->get();
                    }
                    if ($toType == Providers::PROVIDER_WALLET && $toCurrency) {
                        $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_WALLET, $projectId, $toCurrency)->get();
                    }

                } else {
                    if ($fromType == Providers::PROVIDER_LIQUIDITY && $fromCurrency) {
                        $fromAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_LIQUIDITY, $projectId, $fromCurrency)->get();
                    }
                    if ($toType == Providers::PROVIDER_LIQUIDITY && $toCurrency) {
                        $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_LIQUIDITY, $projectId, $toCurrency)->get();
                    }
                    if ($toType == Providers::PROVIDER_WALLET && $toCurrency) {
                        $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_WALLET, $projectId, $toCurrency)->get();
                    }
                }
               break;
            case TransactionType::CRYPTO_TRX:
                switch ($fromType) {
                    case Providers::CLIENT:
                        $operationFromAccount = $operation->fromAccount;
                        if ($operationFromAccount && $operationFromAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationFromAccount->currency == $fromCurrency) {
                            $fromAccounts = [
                                $operationFromAccount
                            ];
                        }
                        break;

                    case Providers::PROVIDER_LIQUIDITY:
                        $provider = $operation->getSelectedExchangeProvider();
                        if ($provider && $fromCurrency) {
                            $api = $provider->api;
                            $fromAccounts = $provider->accounts()->where([
                                'currency' => $fromCurrency,
                                'status' => AccountStatuses::STATUS_ACTIVE
                            ])->get();
                        }
                        break;

                    case Providers::PROVIDER_WALLET:
                        if ($fromCurrency) {
                            $fromAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_WALLET, $projectId, $fromCurrency)->get();
                        }
                        break;
                }
                switch ($toType) {
                    case Providers::CLIENT:
                        $operationToAccount = $operation->toAccount;
                        if ($operationToAccount && $operationToAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationToAccount->currency == $toCurrency) {
                            $toAccounts = [
                                $operationToAccount
                            ];
                        }
                        break;
                    case Providers::PROVIDER_LIQUIDITY:
                        if ($toCurrency) {
                            $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_LIQUIDITY, $projectId, $toCurrency)->get();
                        }
                        break;

                    case Providers::PROVIDER_WALLET:
                        if ($toCurrency) {
                            $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_WALLET, $projectId, $toCurrency)->get();
                        }
                        break;
                }
                break;

            case  TransactionType::BANK_TRX:
                switch ($fromType) {
                    case Providers::CLIENT:
                        $operationFromAccount = $operation->fromAccount;
                        if ($operationFromAccount && $operationFromAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationFromAccount->currency == $fromCurrency) {
                            $fromAccounts = [
                                $operationFromAccount
                            ];
                        }
                        break;

                    case Providers::PROVIDER_LIQUIDITY:
                        $provider = $operation->getSelectedExchangeProvider();
                        if ($provider && $fromCurrency) {
                            $api = $provider->api;
                            $fromAccounts = $provider->accounts()->where([
                                'currency' => $fromCurrency,
                                'status' => AccountStatuses::STATUS_ACTIVE
                            ])->get();
                        }
                        break;

                    case Providers::PROVIDER_PAYMENT:
                        if ($fromCurrency) {
                            $fromAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_PAYMENT, $projectId, $fromCurrency)->get();
                        }
                        break;
                }

                switch ($toType) {
                    case Providers::CLIENT:
                        $operationToAccount = $operation->toAccount;
                        if ($operationToAccount && $operationToAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationToAccount->currency == $toCurrency) {
                            $toAccounts = [
                                $operationToAccount
                            ];
                        }
                        break;

                    case Providers::PROVIDER_LIQUIDITY:
                        if ($toCurrency) {
                            $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_LIQUIDITY, $projectId, $toCurrency)->get();
                        }
                        break;

                    case Providers::PROVIDER_PAYMENT:
                        if ($toCurrency) {
                            $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_PAYMENT, $projectId, $toCurrency)->get();
                        }
                        break;
                }
                break;

            case TransactionType::CARD_TRX:
                switch ($fromType) {
                    case Providers::CLIENT:
                        $operationFromAccount = $operation->fromAccount;
                        if ($operationFromAccount && $operationFromAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationFromAccount->currency == $fromCurrency) {
                            $fromAccounts = [
                                $operationFromAccount
                            ];
                        }
                        break;
                }

                switch ($toType) {
                    case Providers::PROVIDER_CARD:
                        if ($toCurrency) {
                            $toAccounts = $this->getProviderAccountsByProjectAndType(Providers::PROVIDER_CARD, $projectId, $toCurrency)->get();
                        }
                        break;
                }
                break;

            case TransactionType::REFUND:
                switch ($fromType) {
                    case Providers::CLIENT:
                        $operationToAccount = $operation->toAccount;
                        if ($operationToAccount && $operationToAccount->owner_type === AccountType::ACCOUNT_OWNER_TYPE_CLIENT && $operationToAccount->currency == $fromCurrency) {
                            $fromAccounts = [
                                $operationToAccount
                            ];
                        }
                        break;

                    case Providers::PROVIDER_PAYMENT:
                        $bankTransaction = $operation->getLastTransactionByType(TransactionType::BANK_TRX);
                        if ($bankTransaction) {
                            $fromAccounts = [
                                $bankTransaction->toAccount
                            ];
                        }

                        break;

                    case Providers::PROVIDER_CARD:
                        $cardTransaction = $operation->getLastTransactionByType(TransactionType::CARD_TRX);
                        if ($cardTransaction) {
                            $fromAccounts = [
                                $cardTransaction->toAccount
                            ];
                        }
                        break;
                }

                switch ($toType) {
                    case Providers::CLIENT:
                            $toAccounts = [
                                $operation->fromAccount
                            ];
                        break;
                    case Providers::PROVIDER_PAYMENT:
                        $bankTransaction = $operation->getLastTransactionByType(TransactionType::BANK_TRX);
                        if ($bankTransaction) {
                            $toAccounts = [
                                $bankTransaction->fromAccount
                            ];
                        } else {
                            $toAccounts = [];
                        }
                    break;
                }
            break;
        }

        return [
            'fromAccounts' => $fromAccounts ?? [],
            'toAccounts' => $toAccounts ?? [],
            'api' => $api,
        ];
    }

    public function getProviderAccountsByProjectAndType(int $type, string $projectId, string $currency)
    {
        return Account::query()->where([
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
            'status' => AccountStatuses::STATUS_ACTIVE,
            'currency' => $currency
        ])->whereNotNull('name')->whereHas('provider', function ($q) use ($projectId, $type) {
            return $q->where([
                'provider_type' => $type,
                'status' => AccountStatuses::STATUS_ACTIVE
            ])->queryByProject($projectId);
        });
    }

}
