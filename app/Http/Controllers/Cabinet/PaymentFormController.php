<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\AccountStatuses;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\ComplianceRequest as ComplianceRequestEnum;
use App\Enums\ComplianceLevel;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Enums\PaymentFormStatuses;
use App\Enums\PaymentFormTypes;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantPaymentRequest;
use App\Http\Requests\PaymentFormRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Operation;
use App\Models\PaymentForm;
use App\Models\PaymentFormAttempt;
use App\Models\Project;
use App\Services\AccountService;
use App\Services\BitGOAPIService;
use App\Services\CardProviders\TrustPaymentService;
use App\Services\ComplianceService;
use App\Services\PaymentFormsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\TopUpCardService;
use App\Services\WalletService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;


class PaymentFormController extends Controller
{
    public function index(PaymentForm $paymentForm, PaymentFormsService $paymentFormsService)
    {
        if ($paymentForm->status !== PaymentFormStatuses::STATUS_ACTIVE) {
            abort(404);
        }

        $project = $paymentForm->project ?? null;

        if(!$project){
            abort(404);
        }

        $this->setProject($project);

        $merchant = $paymentForm->cProfile;
        $limits = $paymentFormsService->getLimitsForOperation($paymentForm);

        $maxPaymentAmount = $limits->transaction_amount_max ?? 0;

        $minPaymentAmount = $paymentFormsService->getMinAmount($paymentForm, Currency::CURRENCY_EUR);

        $termsAndConditions = t('terms_and_conditions_for_payment_form');

        return view('cabinet.payment-forms.form', compact('maxPaymentAmount', 'termsAndConditions', 'merchant', 'paymentForm', 'minPaymentAmount', 'project'));
    }

    public function getMinPaymentAmount(Request $request, PaymentForm $paymentForm, PaymentFormsService $paymentFormsService)
    {
        if (!$request->fromCurrency || !in_array($request->fromCurrency, Currency::FIAT_CURRENCY_NAMES)) {
            return response()->json(['minAmount' => 0]);
        }

        $minAmount = $paymentFormsService->getMinAmount($paymentForm, $request->fromCurrency);

        return response()->json(['minAmount' => $minAmount]);
    }

    public function createOperation(PaymentForm $paymentForm, MerchantPaymentRequest $request, TopUpCardService $topUpCardService, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        $project = $paymentForm->project ?? null;
        /* @var Project $project */

        $this->setProject($project);

        if (!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND,['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)],LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            throw (new HttpResponseException(response(t('error_unknown'), 404)));
        }
        $paymentFormAttempt->fill([
            'from_currency' => $request->currency,
            'to_currency' => $request->cryptoCurrency,
        ]);
        $paymentFormAttempt->save();



        $cProfile = $paymentFormAttempt->cProfile;

        $toAccount = $paymentFormsService->resolveToAccountForAttempt($paymentFormAttempt, $cProfile);

        $recipientCProfile = $paymentFormsService->getRecipientCProfile($paymentFormAttempt);
        $paymentFormAttempt->setRecipientAccount($recipientCProfile, $request->cryptoCurrency);

        if (!$toAccount) {
            logger()->error('MissingToAccountPF', $paymentFormAttempt->toArray());
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_MISSING_TO_ACCOUNT,['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)],LogResult::RESULT_FAILURE,LogType::TYPE_PAYMENT_FORM_MISSING_TO_ACCOUNT);
            throw (new HttpResponseException(response(t('error_unknown'), 403)));
        }

        if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $recipientCProfile->rate_template_id = $paymentForm->rate_template_id;
            $recipientCProfile->save();
        } else {
            $cProfile->rate_template_id = $cProfile->rate_template_id ?? $paymentForm->cProfile->rate_template_id;
            $cProfile->save();
        }

        //account limit validation
        $complianceLevel = $paymentFormsService->getComplianceLevel($cProfile);
        if (!$topUpCardService->validateCardOperationLimits($recipientCProfile, $request->currency, $request->amount, $complianceLevel)) {
            logger()->error(t('ValidateCardOperationLimitsPF', [$recipientCProfile->id, $request->currency, $request->amount, $complianceLevel]));
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_VALIDATE_CARD_OPERATION_LIMITS,['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)],LogResult::RESULT_FAILURE,LogType::TYPE_PAYMENT_FORM_VALIDATE_CARD_OPERATION_LIMITS, null, $cProfile->cUser->id);
            throw (new HttpResponseException(response(t('ui_card_transfer_limit_fail_validation'), 403)));
        }

        $profileCommission = $recipientCProfile->operationCommission(CommissionType::TYPE_CARD, Commissions::TYPE_INCOMING, $request->currency);


        if ($request->paymentFormAmount < $profileCommission->min_amount) {
            logger()->error('PaymentFormAmountLowerAmount', [$request->paymentFormAmount, $profileCommission->min_amount]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_AMOUNT_LOWER_AMOUNT, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)],LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_AMOUNT_LOWER_AMOUNT,null, $cProfile->cUser->id);
            throw (new HttpResponseException(response(t('error_unknown'), 403)));
        }

        $operation = $topUpCardService->createTopUpCardOperation(
            $cProfile->id,
            $request->paymentFormAmount,
            $request->currency,
            $request->cryptoCurrency,
            null,
            $toAccount->id,
            null,
            null,
            null,
            OperationOperationType::TYPE_CARD_PF,
            $complianceLevel
        );

        if (!$operation) {
            logger()->error('OperationSaveErrorPF', [ $cProfile->id, $request->paymentFormAmount, $request->currency, $request->cryptoCurrency]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_OPERATION_SAVE_ERROR, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_OPERATION_SAVE_ERROR, null, $cProfile->cUser->id);
            throw (new HttpResponseException(response(t('error_unknown'), 403)));
        }

        $operation->payment_form_id = $paymentForm->id;
        $operation->save();

        ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_OPERATION_SAVE_SUCCESS, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_SUCCESS, LogType::TYPE_PAYMENT_FORM_OPERATION_SAVE_SUCCESS, null, $cProfile->cUser->id);

        $paymentFormAttempt->operation_id = $operation->id;

        $paymentFormAttempt->save();

        return view('cabinet.payment-forms.pay', compact('operation', 'project'));
    }

    public function redirectToTrustPaymentsPage($operationId, TrustPaymentService $trustPaymentService, ProviderService $providerService)
    {
        $operation = Operation::findOrFail($operationId);
        $userId = $operation->cProfile->cUser->id;
        $purpose = t('operation_type_top_up_card'). $operation->amount . ' ' .  $operation->from_currency;
        $trustPaymentService->setTransactionDetails($operation->id, ' ', $operation->amount, $operation->from_currency, $purpose);
        $formData = $trustPaymentService->getPaymentFormData();

        $projectService = resolve(ProjectService::class);
        /* @var ProjectService $projectService */

        $apiSettings = $projectService->getCardApiSettings($operation->cProfile->cUser->project);
        $configKey = 'cardproviders.' . $apiSettings['api'] . '.' . $apiSettings['api_account'] . '.sitereference';
        $siteReference = config($configKey);

        return view('cabinet.wallets.payment-forms.trustpayment', compact('formData', 'operation', 'siteReference', 'userId'));
    }

    public function saveInitialData(PaymentForm $paymentForm, PaymentFormsService $paymentFormsService, PaymentFormRequest $request)
    {
        $response = $paymentFormsService->saveInitialDataInAttempt($paymentForm, $request->validated());

        if (!empty($response['error'])) {
            return response()->json($response, 403);
        }

        $paymentFormAttempt = $response['paymentFormAttempt'];

        return response()->json([
            'paymentFormAttemptId' => encrypt($paymentFormAttempt->id),
        ]);
    }

    public function verifyPhoneNumber(PaymentFormsService $paymentFormsService, PaymentFormRequest $request)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            throw (new HttpResponseException(response(t('something_went_wrong'), 403)));
        }

        $phone = $request->input('phone_cc_part') . $request->input('phone_no_part');
        $response = $paymentFormsService->verifyByPhoneNumber($phone, $paymentFormAttempt);
        if (!empty($response['error'])) {
            logger()->error('verifyPhoneNumberFailure', $response);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_VERIFY_PHONE_ERROR, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId), 'phone' => $phone] , LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_VERIFY_PHONE_ERROR);
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    public function confirmCode(PaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $phone = $paymentFormAttempt->phone;

        $response = $paymentFormsService->confirmVerifyCode($phone, $request->get('code'), $paymentFormAttempt);

        if (!empty($response['error'])) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_CONFIRM_VERIFY_CODE_ERROR, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId), 'code' => $request->get('code')], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_CONFIRM_VERIFY_CODE_ERROR);
            return response()->json($response, 403);
        }
        return response()->json([
            'success' => true
        ]);
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

    public function verifyEmailCode(PaymentFormRequest $request, PaymentFormsService $paymentFormsService, ComplianceService $complianceService)
    {
        $email = $request->get('email');
        $code = $request->get('code');
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $response = $paymentFormsService->confirmVerifyEmailCode($email, $code, $paymentFormAttempt);
        if (!empty($response['error'])) {
            logger()->error('verifyEmailCodeFailure', $response);
            ActivityLogFacade::saveLog($response['error'], ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId), 'email' => $email, 'code' => $code], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_VERIFY_EMAIL_CODE_ERROR);
            return response()->json($response, 403);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function verifyComplianceStatus(PaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        try {
            /** @var CProfile $cProfile */
            $cProfile = $paymentFormAttempt->cProfile;
            $complianceStatus = $cProfile->complianceRequest()->orderBy('created_at', 'desc')->first()->status ?? null;

            switch ($complianceStatus) {
                case ComplianceRequestEnum::STATUS_PENDING:
                    $step = null;
                    break;
                case ComplianceRequestEnum::STATUS_APPROVED:
                    $step = 6;
                    break;
                case ComplianceRequestEnum::STATUS_DECLINED:
                    $step = 5;
                    break;
                default:
                    $step = 2;
            }

            return response()->json(['step' => $step]);

        } catch (\Exception $exception) {
            logger()->error('loginUserFailure', ['error' => $exception->getMessage()]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_VERIFY_COMPLIANCE_STATUS_ERROR, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_VERIFY_COMPLIANCE_STATUS_ERROR);
            return response()->json(['error' => $exception->getMessage()]);
        }

    }

    public function verifyWalletAddress(PaymentFormRequest $request, AccountService $accountService, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $cProfile = $paymentFormAttempt->cProfile;

        $account = $accountService->addWalletToClient($request->wallet_address, $request->currency, $cProfile);

        $paymentFormAttempt->recipient_account_id = $account->id;
        $paymentFormAttempt->save();

        $response = [
            'wallet_checking_passed' => $account->status == AccountStatuses::STATUS_ACTIVE,
        ];
        $code = $account->status == AccountStatuses::STATUS_ACTIVE ? 200 : 403;

        if($account->status != AccountStatuses::STATUS_ACTIVE) {
            EmailFacade::sendWalletVerificationFailedEmail($cProfile, $request->wallet_address);
        }

        return response()->json($response, $code);
    }

    /**
     * @param Request $request
     * @param PaymentFormsService $paymentFormsService
     * @param ComplianceService $complianceService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComplianceData(PaymentFormRequest $request, PaymentFormsService $paymentFormsService, ComplianceService $complianceService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $paymentForm = $paymentFormAttempt->paymentForm;
        /** @var PaymentForm $paymentForm */

        $complianceData = [
            'currentFormAttempt' => $paymentFormAttempt,
            'kyc' => $paymentForm->kyc
        ];

        if(!$paymentForm->kyc) {
            return response()->json($complianceData);
        }

        $project = $paymentForm->project;
        /** @var Project $project */

        if(!$project->complianceProvider()) {
            return response()->json([
                'currentFormAttempt' => $paymentFormAttempt,
                'kyc' => false
            ]);
        }

        $cProfile = $paymentFormAttempt->cProfile;
        /** @var CProfile $cProfile */

        \C\c_user_login($cProfile->cUser);

        $complianceData = array_merge($complianceData ,$complianceService->getCProfileComplianceData($cProfile));

        $complianceData['cProfileId'] = $cProfile->id;
        $complianceData['phone'] = $cProfile->cUser->phone;
        $complianceData['email'] = $cProfile->cUser->email;

        return response()->json($complianceData);

    }

    public function loginUser(PaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $response = $paymentFormsService->loginUser($paymentFormAttempt, $request->paymentFormUserPassword);
        if (!is_array($response)) {
            return $response;
        }
        if (!empty($response['error'])) {
            logger()->error('loginUserFailure', $response);
            ActivityLogFacade::saveLog($response['error'], ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_USER_LOGIN_ERROR);
            return response()->json(['error' => $response['error']], 403);
        }

        return response()->json($response);

    }

    public function verifyPaymentForm(PaymentFormRequest $request, PaymentFormsService $paymentFormsService, ComplianceService $complianceService)
    {

        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $paymentForm = $paymentFormAttempt->paymentForm;
        /* @var PaymentForm $paymentForm */

        /** @var CProfile $cProfile */
        $cProfile = $paymentFormsService->createPaymentFormCProfile($paymentForm->id, $paymentFormAttempt);

        //send email for generate new password
        EmailFacade::sendPaymentFormGenerateNewPasswordEmail($cProfile->cUser);

        $project = $paymentForm->project;
        /** @var Project $project */

        if($paymentForm->type !== PaymentFormTypes::TYPE_CLIENT_OUTSIDE_FORM) {
            app(WalletService::class)->addNewWallet(app(BitGOAPIService::class), $paymentFormAttempt->to_currency, $cProfile);
        }

        if(!$paymentForm->kyc || !$project->complianceProvider()) {
            EmailFacade::sendSuccessVerificationRegistrationConfirmEmail($cProfile->cUser);
            return response()->json(['kyc' => false]);
        }

        \C\c_user_login($cProfile->cUser);

        $complianceData = $complianceService->getCProfileComplianceData($cProfile);

        $complianceData['cProfileId'] = $cProfile->id;
        $complianceData['phone'] = $cProfile->cUser->phone;
        $complianceData['email'] = $cProfile->cUser->email;
        $complianceData['kyc'] = true;
        return response()->json($complianceData);
    }

    private function setProject(Project $project)
    {
        config()->set('projects.currentProject', $project);
        config()->set('app.name', $project->name);
        config()->set('mail.from.name', $project->name);
        foreach (config('mail.email_providers') as $key => $provider) {
            config()->set('mail.email_providers.' . $key . '.name', $project->name);
        }
        config()->set('cratos.company_details',  (array) $project->companyDetails);
        config()->set('cratos.urls.terms_and_conditions',  $project->companyDetails->terms_and_conditions ?? '');
        config()->set('cratos.urls.aml_policy',  $project->companyDetails->aml_policy ?? '');
        config()->set('cratos.urls.privacy_policy',  $project->companyDetails->privacy_policy ?? '');
        config()->set('cratos.urls.frequently_asked_question',  $project->companyDetails->frequently_asked_question ?? '');
        URL::forceRootUrl($project->domainFullPath());
    }
}
