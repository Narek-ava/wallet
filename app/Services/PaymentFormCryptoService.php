<?php

namespace App\Services;

use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\CProfileStatuses;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationStatuses;
use App\Enums\PaymentFormTypes;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Http\Requests\PaymentFormRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\PaymentForm;
use App\Models\PaymentFormAttempt;

class PaymentFormCryptoService
{
    protected SmsCodeService $smsCodeService;
    protected CUserService $cUserService;

    public function __construct()
    {
        $this->smsCodeService = resolve(SmsCodeService::class);
        $this->cUserService = resolve(CUserService::class);
    }

    public function getPaymentFormAttemptById($id): ?PaymentFormAttempt
    {
        return PaymentFormAttempt::find($id);
    }

    public function saveInitialDataInAttempt(PaymentForm $paymentForm, array $dataArray, ?PaymentFormAttempt $paymentFormAttempt = null)
    {
        $paymentFormAttempt = $paymentFormAttempt ?? new PaymentFormAttempt();

        $paymentFormAttempt->fill([
            'payment_form_id' => $paymentForm->id,
            'from_currency' => $dataArray['currency'],
            'to_currency' => $dataArray['cryptoCurrency'],
            'amount' => $dataArray['paymentFormAmount'],
        ]);


        $validated = $this->validateOperationAmount($paymentFormAttempt);
        if (empty($validated['error'])) {
            $paymentFormAttempt->save();
        }

        return $validated;
    }

    public function validateOperationAmount(PaymentFormAttempt $paymentFormAttempt)
    {
        $recipientCProfile = $paymentFormAttempt->paymentForm->cProfile;

        if (!$recipientCProfile) {
            return [
                'error' => t('operation_limit_validation_failed'),
            ];
        }

        $profileCommission = $recipientCProfile->operationCommission(CommissionType::TYPE_CRYPTO, Commissions::TYPE_INCOMING, $paymentFormAttempt->from_currency);
        if ($paymentFormAttempt->amount < $profileCommission->min_amount) {
            return [
                'error' => t('payment_form_lower_amount', [ 'minAmount' => $profileCommission->min_amount, 'currency' => $paymentFormAttempt->from_currency]),
            ];
        }

        return [
            'success' => true,
            'paymentFormAttempt' => $paymentFormAttempt
        ];
    }

    public function verifyByPhoneNumber($phone, PaymentFormAttempt $paymentFormAttempt)
    {
        $paymentFormAttempt->phone = $phone;
        $paymentFormAttempt->save();

//        if (!$this->smsCodeService->generateConfirmForMerchantPayment($phone, false)) {
//           return false;
//        }

        return true;
    }

    public function savePayerData(PaymentFormAttempt $paymentFormAttempt, array $dataArray)
    {
        $paymentFormAttempt->fill([
            'first_name' => $dataArray['first_name'],
            'last_name' => $dataArray['last_name'],
            'phone' => $dataArray['phone_cc_part'] . $dataArray['phone_no_part'],
            'email' => $dataArray['email'],
        ]);

        $paymentFormAttempt->save();

    }

    public function verifyEmail(PaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $response = $paymentFormsService->verifyByEmail($request->email, $paymentFormAttempt);

        if (!empty($response['error'])) {
            logger()->error('verifyEmailFailure', $response);
            ActivityLogFacade::saveLog($response['error'], ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId), 'email' => $request->email], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_VERIFY_EMAIL_ERROR);
            return response()->json($response, 403);
        }

        return response()->json($response);
    }

    public function getCryptoPaymentMinAmount(PaymentForm $paymentForm, string $cryptocurrency)
    {
        $merchant = $paymentForm->cProfile;
        $profileCommission = $merchant->operationCommission(CommissionType::TYPE_CRYPTO, Commissions::TYPE_INCOMING, $cryptocurrency);

        return $profileCommission->min_amount ?? 0;
    }


    public function checkPayment(PaymentFormAttempt $paymentFormAttempt)
    {
        $operation = $paymentFormAttempt->operation;
        $cryptoTransaction = $operation->getLastTransactionByType(TransactionType::CRYPTO_TRX);

        return $cryptoTransaction && $cryptoTransaction->status == TransactionStatuses::SUCCESSFUL;
    }

    public function calculateFee(PaymentForm $paymentForm, $amount)
    {
        if ($paymentForm->type == PaymentFormTypes::TYPE_CRYPTO_TO_CRYPTO_FORM) {
            $incomingFeePercent = $paymentForm->incoming_fee;
            $feeAmount = ($amount * $incomingFeePercent)/100;
            return [
                'feePercent' => $incomingFeePercent,
                'feeAmount' => $feeAmount,
                'leftAmount' => $amount - $feeAmount,
            ];
        }

        return null;
    }
}
