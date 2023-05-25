<?php


namespace App\Services;


use App\DataObjects\OperationTransactionData;
use App\Enums\AccountStatuses;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\Notification;
use App\Enums\NotificationRecipients;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\OperationType;
use App\Enums\SumsubCrypto;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionSteps;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Http\Requests\Backoffice\AddTransactionRequest;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\Commission;
use App\Models\CryptoAccountDetail;
use App\Models\Limit;
use App\Models\Operation;
use App\Models\Transaction;
use App\Operations\WithdrawCrypto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawCryptoService
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
            ->where('commission_type', CommissionType::TYPE_CRYPTO)
            ->first();

        return $commissions;
    }


    /**
     * @param $fromWallet
     * @param $toWallet
     * @param $leftAmount
     * @param $operation
     * @param $bitGOAPIService
     * @param $activityLogService
     */
    /*public function transactionFromBitGoToExternal(CryptoAccountDetail $fromWallet,
                                                   CryptoAccountDetail $toWallet,
                                                   int $leftAmount,Operation $operation,
                                                   BitGOAPIService $bitGOAPIService,
                                                   ActivityLogService $activityLogService)
    {
        $cProfile = CProfile::find($operation->c_profile_id);
//        try{
            //send from bitgo wallet to external wallet
            $bitGOAPIService->sendTransaction($fromWallet, $toWallet, $leftAmount);

            $notificationId = $this->notificationService->createNotification(
                Notification::SEND_TRANSACTION_SUCCESSFUL_BODY,
                NotificationRecipients::CURRENT_CLIENT, Notification::SEND_TRANSACTION_SUCCESSFUL, [], []);

            $this->notificationUserService->createNotificationUser([
                'title' => Notification::SEND_TRANSACTION_SUCCESSFUL,
                'message' => Notification::SEND_TRANSACTION_SUCCESSFUL_BODY
            ],$cProfile->cUser->id, $notificationId, false, $cProfile);

            $activityLogService->setAction(LogMessage::TRANSACTION_ADDED_SUCCESSFULLY)
                ->setResultType(LogResult::RESULT_SUCCESS)
                ->setType(LogType::TRANSACTION_ADDED_SUCCESS)
                ->setContextId($operation->id)
                ->log();
//        }catch (\Exception $e){
//                $notificationId = $this->notificationService->createNotification(
//                Notification::SEND_TRANSACTION_FAILED_BODY,
//                NotificationRecipients::CURRENT_CLIENT, Notification::SEND_TRANSACTION_FAILED, [], []);
//
//            $this->notificationUserService->createNotificationUser([
//                'title' => Notification::SEND_TRANSACTION_FAILED,
//                'message' => Notification::SEND_TRANSACTION_FAILED_BODY,
//                'body_params' =>  $e->getMessage()
//            ],$cProfile->cUser->id, $notificationId, false, $cProfile);
//
//            $activityLogService->setAction($e->getMessage())
//                ->setResultType(LogResult::RESULT_FAILURE)
//                ->setType(LogType::TRANSACTION_ADDED_FAIL)
//                ->setContextId($operation->id)
//                ->log();
//        }
    }*/
}
