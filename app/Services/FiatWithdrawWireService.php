<?php


namespace App\Services;


use App\DataObjects\OperationTransactionData;

use App\Enums\{AccountStatuses,
    AccountType,
    Commissions,
    CommissionType,
    LogMessage,
    LogResult,
    LogType,
    OperationOperationType,
    OperationStatuses,
    Providers,
    TransactionType};

use App\Facades\ActivityLogFacade;
use App\Http\Requests\Backoffice\AddTransactionRequest;
use App\Models\{Account, Cabinet\CProfile, Commission, CryptoAccountDetail, Limit, Operation, PaymentProvider};

use App\Operations\WithdrawCrypto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class FiatWithdrawWireService
{
    protected $notificationService;
    protected $notificationUserService;


    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->notificationUserService = new NotificationUserService();
    }

    /**
     * @param CProfile $cProfile
     * @param array $data
     * @param CryptoAccountDetail $fromCryptoAccountDetail
     * @param CryptoAccountDetail $toCryptoAccountDetail
     * @param Account $fromAccount
     * @param Account $toAccount
     * @return Operation
     */
    public function createOperation(CProfile $cProfile, array $data, CryptoAccountDetail $fromCryptoAccountDetail, CryptoAccountDetail $toCryptoAccountDetail, Account $fromAccount, Account $toAccount): Operation
    {
        /* @var OperationService $operationService*/
        $operationService = resolve(OperationService::class);

        $operation = $operationService->createOperation(
            $cProfile->id,
            OperationOperationType::TYPE_WITHDRAW_CRYPTO,
            $data['amount'],
            $fromCryptoAccountDetail->coin,
            $toCryptoAccountDetail->coin,
            $fromAccount->id,
            $toAccount->id
        );

        return $operation;
    }

    public function createTransaction(array $data, string $operationId, CommissionsService $commissionsService, TransactionService $transactionService, AddTransactionRequest $request)
    {
        try {
            $operation = Operation::findOrFail($operationId);
            DB::beginTransaction();
//            $withdrawCrypto = new WithdrawCrypto($operation, $data['currency_amount'], $data['date'] );
            $withdrawCrypto = new WithdrawCrypto($operation, new OperationTransactionData($request->all()));
            $withdrawCrypto->execute();


            DB::commit();
            return ['message' => 'Success'];

        } catch (\Exception $e) {
            DB::rollBack();
            ActivityLogFacade::saveLog(LogMessage::TRANSACTION_ADDED_FAILED, ['message' => $e->getMessage()],
                LogResult::RESULT_FAILURE, LogType::TRANSACTION_ADDED_FAIL);
            return ['message' => $e->getMessage()];
        }
    }




    /**
     * @param array $data
     * @param Operation $operation
     * @param Account $fromAccountModel
     * @param Account $toAccountModel
     * @param TransactionService $transactionService
     * @param CommissionsService $commissionsService
     * @return array
     */


    /**
     * @param $cProfile
     * @return mixed
     */
    public function getLimits(CProfile $cProfile)
    {
        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        return $limits;
    }


    /**
     * @param $rateTemplateId
     * @param $coin
     * @return mixed
     */
    public function getCommissions(string $rateTemplateId, $coin)
    {
        $commissions = Commission::where('rate_template_id', $rateTemplateId)
            ->where('type', Commissions::TYPE_OUTGOING)
            ->where('commission_type', CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE)
            ->first();

        return $commissions;
    }


    public function getToAccounts(Operation $operation)
    {
        // @todo fiat get only fiat payment providers

        $projectId = $operation->cProfile->cUser->project_id ?? null;
        config()->set('projects.project', $operation->cProfile->cUser->project);

        $allPaymentProviders = PaymentProvider::where(['provider_type' => Providers::PROVIDER_PAYMENT])
            ->queryByProject($projectId)
            ->where('status', AccountStatuses::STATUS_ACTIVE)->get();
        foreach ($allPaymentProviders as $aPaymentProvider) {
            if ($operation->to_currency) {
                $query = $aPaymentProvider->accounts()->where(['status' => AccountStatuses::STATUS_ACTIVE, 'fiat_type' => AccountType::PAYMENT_PROVIDER_FIAT_TYPE_FIAT]);
                if ($operation->cProfile->account_type == CProfile::TYPE_INDIVIDUAL){
                    $query->whereHas('accountClientPolicy', function (Builder $q) use ($operation) {
                        if (in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)) {
                            $q->where('type', AccountType::WIRE_PROVIDER_C2B);
                        } elseif (in_array($operation->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
                            $q->where('type', AccountType::WIRE_PROVIDER_B2C);
                        }
                    });
                }
                if ($operation->cProfile->account_type == CProfile::TYPE_CORPORATE &&
                    in_array($operation->operation_type, array_merge(OperationOperationType::TYPES_WIRE_LAST, OperationOperationType::TYPES_TOP_UP))){
                    $query->whereHas('accountClientPolicy', function (Builder $q) {
                        $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                    });
                }

                $accountsOfProviderToCurrency = $query
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->where('currency', $operation->to_currency)->get();
                foreach ($accountsOfProviderToCurrency as $providerAccount) {
                    $paymentProviders[$providerAccount->id] = $providerAccount;
                    $paymentProvidersAccounts[$providerAccount->id] = $providerAccount->provider;
                }

            }
        }

        switch ($operation->step) {
            case 0:
                $selectedPaymentProvider = $operation->getProviderAccount();
                return
                    [
                        'from' => [$operation->fromAccount],
                        'toProviders' => $paymentProvidersAccounts,
                        'to' => $paymentProviders,
                        'selectedPaymentProvider' => $selectedPaymentProvider,
                        'fromCurrency' => $operation->from_currency,
                    ];
                break;
            case 1:
                $account = $operation->toAccount;
                $systemCommission = $operation->transactions->where('type', TransactionType::SYSTEM_FEE)
                    ->where('from_account', $operation->from_account)
                    ->first();

                $systemCommissionAmount = $systemCommission->recipient_amount ?? 0;

                return [
                    'from' => $paymentProviders,
                    'fromProviders' => $paymentProvidersAccounts,
                    'to' => [$account],
                    'fromCurrency' => $operation->to_currency,
                    'recipientAmount' => $operation->amount -  $systemCommissionAmount,
                ];
                break;
        }
    }

    public function getAllowedFromAccounts(Operation $operation, $fromCurrency = null, $paymentProviderIds = null, $fromProviderType = Providers::PROVIDER_PAYMENT)
    {
        $query = Account::query()
            ->where([
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                'currency' => $fromCurrency ?? $operation->from_currency,
                'status' => AccountStatuses::STATUS_ACTIVE
            ]);

        if ($fromProviderType == Providers::PROVIDER_PAYMENT) {
            if ($operation->cProfile->account_type == CProfile::TYPE_INDIVIDUAL){
                $type = null;

                if (in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)) {
                    $type = AccountType::WIRE_PROVIDER_C2B;
                } elseif (in_array($operation->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
                    $type = AccountType::WIRE_PROVIDER_B2C;
                }

                $query->whereHas('accountClientPolicy', function (Builder $q) use ($type) {
                    if ($type) {
                        $q->where('type', $type);
                    }
                    return $q;
                });

            } elseif ($operation->cProfile->account_type == CProfile::TYPE_CORPORATE &&
                in_array($operation->operation_type, array_merge(OperationOperationType::TYPES_WIRE_LAST, OperationOperationType::TYPES_TOP_UP))){
                $query->whereHas('accountClientPolicy', function (Builder $q) {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                });
            }
        }

        if ($paymentProviderIds){
            $query->whereIn('payment_provider_id', $paymentProviderIds);
        }

        return $query->get();
    }

}
