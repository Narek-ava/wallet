<?php

namespace App\Services;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\ComplianceLevel;
use App\Enums\CProfileStatuses;
use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Enums\Providers;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\CryptoAccountDetail;
use App\Models\PaymentForm;
use App\Models\PaymentFormAttempt;
use App\Models\Project;
use App\Models\RateTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PaymentFormsService
{
    protected SmsCodeService $smsCodeService;
    protected CUserService $cUserService;
    protected EmailVerificationService $emailVerificationService;
    protected CProfileService $cProfileService;
    protected SumSubService $sumSubService;
    protected ActivityLogService $activityLogService;
    protected BitGOAPIService $bitGOAPIService;
    protected AccountService $accountService;

    const PAYMENT_INFO_CACHE_KEY = 'payment_form_';

    const FIRST_CODE_SENDING_DELAY = 30;
    const SECOND_CODE_SENDING_DELAY = 60;
    const THIRD_CODE_SENDING_DELAY = 120;
    const FOURTH_CODE_SENDING_DELAY = 240;
    const FIFTH_CODE_SENDING_DELAY = 300;

    const EMAIL_CODE_VERIFY_ATTEMPTS = 5;
    const EMAIL_CODE_VERIFY_ATTEMPTS_STOP_SECOND = 3600;

    public function __construct()
    {
        $this->smsCodeService = resolve(SmsCodeService::class);
        $this->cUserService = resolve(CUserService::class);
        $this->emailVerificationService = resolve(EmailVerificationService::class);
        $this->cProfileService = resolve(CProfileService::class);
        $this->sumSubService = resolve(SumSubService::class);
        $this->activityLogService = resolve(ActivityLogService::class);
        $this->bitGOAPIService = resolve(BitGOAPIService::class);
        $this->accountService = resolve(AccountService::class);
    }

    public function getPaymentForms(int $status = null, int $paymentFormType = null, ?string $projectId = null)
    {
        $query = PaymentForm::query();

        if ($status) {
            $query->where('status', $status);
        }

        if ($paymentFormType) {
            $query->where('type', $paymentFormType);
        } else {
            $query->where('type', '<>', PaymentFormTypes::TYPE_CRYPTO_TO_CRYPTO_FORM);
        }

        $bUser = auth()->guard('bUser')->user();
        if ($projectId) {
            $query->where('project_id', $projectId);
        } else if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $query->whereIn('project_id', $projectIds);
        }

        return $query->get();
    }


    public function getKYCTypes(): array
    {
        $kycVariants = PaymentForm::KYC_VARIANTS ?? [];
        foreach ($kycVariants as &$kycVariant) {
            $kycVariant = t($kycVariant);
        }
        return $kycVariants;
    }

    public function createPaymentForm(array $dataArray)
    {
        if (in_array($dataArray['paymentFormType'], PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rateTemplate = RateTemplate::findOrFail($dataArray['paymentFormRate']);
            if ($dataArray['paymentFormType'] == PaymentFormTypes::TYPE_CLIENT_INSIDE_FORM) {
                $kyc = PaymentForm::KYC;
            }
        }else {
            $cProfile = CProfile::findOrFail($dataArray['paymentFormMerchant']);
        }

        $paymentForm = new PaymentForm();
        $paymentForm->fill([
            'name' => $dataArray['paymentFormName'],
            'type' => $dataArray['paymentFormType'],
            'status' => $dataArray['paymentFormStatus'],
            'c_profile_id' => $cProfile->id ?? null,
            'card_provider_id' => $dataArray['paymentFormCardProvider'],
            'liquidity_provider_id' => $dataArray['paymentFormLiquidityProvider'],
            'wallet_provider_id' => $dataArray['paymentFormWalletProvider'],
            'rate_template_id' => $rateTemplate->id ?? null,
            'kyc' => $kyc ?? $dataArray['paymentFormKYC'],
            'project_id' => $dataArray['paymentFormProject'] ?? null,
        ]);

        $paymentForm->save();

        foreach (Currency::getList() as $currency) {
            $key = 'address_' . $currency;
            if (empty($dataArray[$key])) {
                continue;
            }
            $account = $this->accountService->addWalletToClient($dataArray[$key], $currency, $cProfile);
            $paymentForm->accounts()->attach($account->id, ['currency' => $currency]);
        }

        return $paymentForm;
    }

    public function updatePaymentForm(PaymentForm $paymentForm, array $dataArray)
    {
        $formType = $dataArray['paymentFormType'] ?? $paymentForm->type;
        if (in_array($formType, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rateTemplate = RateTemplate::findOrFail($dataArray['paymentFormRate']);
        }else {
            $cProfile = CProfile::findOrFail($dataArray['paymentFormMerchant']);
        }

        $paymentForm->update([
            'name' => $dataArray['paymentFormName'],
            'type' => $dataArray['paymentFormType'] ?? $paymentForm->type,
            'status' => $dataArray['paymentFormStatus'],
            'c_profile_id' => $cProfile->id ?? null,
            'card_provider_id' => $dataArray['paymentFormCardProvider'],
            'liquidity_provider_id' => $dataArray['paymentFormLiquidityProvider'],
            'wallet_provider_id' => $dataArray['paymentFormWalletProvider'],
            'rate_template_id' => $rateTemplate->id ?? null,
            'kyc' => $dataArray['paymentFormKYC'],
        ]);

        if (!$paymentForm->hasOperations()) {
            $paymentForm->project_id = $dataArray['paymentFormProject'];
            $paymentForm->save();
        }

        $paymentForm->accounts()->update([
            'status' => AccountStatuses::STATUS_DISABLED
        ]);

        if ($paymentForm->type == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
            foreach (Currency::getList() as $currency) {
                $key = 'address_' . $currency;
                if (empty($dataArray[$key])) {
                    continue;
                }
                $account = $this->accountService->addWalletToClient($dataArray[$key], $currency, $paymentForm->cProfile);
                if ($account->status != AccountStatuses::STATUS_ACTIVE) {
                    return [
                        'error' => [$key => t('incorrect_address')],
                    ];
                }
                if (!$paymentForm->activeAccounts()->where('accounts.id', $account->id)->exists()) {
                    $paymentForm->accounts()->attach($account->id, ['currency' => $currency]);
                }
            }
        }

        return $paymentForm;
    }

    public function saveInitialDataInAttempt(PaymentForm $paymentForm, array $dataArray)
    {
        $paymentFormAttempt = new PaymentFormAttempt();
        $paymentFormAttempt->fill([
            'payment_form_id' => $paymentForm->id,
            'from_currency' => $dataArray['currency'],
            'to_currency' => $dataArray['cryptoCurrency'],
            'amount' => $dataArray['paymentFormAmount'],
            'wallet_address' => $dataArray['wallet_address'] ?? null,
            'first_name' => $dataArray['first_name'],
            'last_name' => $dataArray['last_name'],
        ]);

        $validated = $this->validateOperationAmount($paymentFormAttempt);
        if (empty($validated['error'])) {
            $paymentFormAttempt->save();
        }

        return $validated;
    }

    public function getLimitsForOperation(PaymentForm $paymentForm)
    {
        $commissionsService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionsService */

        $cProfile = $paymentForm->cProfile;
        if ($cProfile) {
            $rateTemplateId = $cProfile->rate_template_id;
            $level = $cProfile->compliance_level;
        } else {
            $rateTemplateId = $paymentForm->rate_template_id;
            $level = $paymentForm->kyc ? ComplianceLevel::VERIFICATION_LEVEL_1 : ComplianceLevel::VERIFICATION_LEVEL_3;
        }
        return $commissionsService->limits($rateTemplateId, $level);
    }

    public function validateOperationAmount(PaymentFormAttempt $paymentFormAttempt)
    {
        $topUpCardService = resolve(TopUpCardService::class);
        /* @var TopUpCardService $topUpCardService */

        $paymentForm = $paymentFormAttempt->paymentForm;

        //payment form cprofile
        $recipientCProfile = $this->getRecipientCProfile($paymentFormAttempt);

        if (!$recipientCProfile) {
            $recipientCProfile = new CProfile();
        }

        $cProfile = new CProfile();

        if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $recipientCProfile->rate_template_id = $paymentForm->rate_template_id;
        } else {
            $cProfile->rate_template_id = $paymentForm->cProfile->rate_template_id;
        }

        //account limit validation
        $complianceLevel = $this->getComplianceLevel($cProfile, $paymentForm);

        if (!$topUpCardService->validateCardOperationLimits($recipientCProfile, $paymentFormAttempt->from_currency, $paymentFormAttempt->amount, $complianceLevel)) {
            return [
                'error' => t('operation_limit_validation_failed'),
            ];
        }

        $profileCommission = $recipientCProfile->operationCommission(CommissionType::TYPE_CARD, Commissions::TYPE_INCOMING, $paymentFormAttempt->from_currency);
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

    public function getMinAmount(?PaymentForm $paymentForm, string $fromCurrency)
    {
        if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $recipientCProfile = new CProfile();
            $recipientCProfile->rate_template_id = $paymentForm->rate_template_id;
        } else {
            $recipientCProfile = $paymentForm->cProfile;
        }
        $profileCommission = $recipientCProfile->operationCommission(CommissionType::TYPE_CARD, Commissions::TYPE_INCOMING, $fromCurrency);

        return $profileCommission->min_amount ?? 0;
    }


    public function verifyByPhoneNumber($phone, PaymentFormAttempt $paymentFormAttempt)
    {

        $paymentFormAttempt->phone = $phone;
        $paymentFormAttempt->save();

        if ($this->cUserService->getUserByPhone($phone)) {
            return [
                'paymentFormAttemptId' => encrypt($paymentFormAttempt->id),
                'isUserRegistered' => true,
            ];
        }

        if (!$this->smsCodeService->generateConfirmForMerchantPayment($phone, false)) {
            return [
                'error' => [
                    'phone_no_part' => t('ui_incorrect_phone_number'),
                ],
            ];
        }

        return [
            'success' => true,
            'isUserRegistered' => false,
        ];
    }

    public function confirmVerifyCode($phone, $code, PaymentFormAttempt $paymentFormAttempt)
    {
        if(!$phone) {
            return [
                'error' => [
                    'sms_code_verification' => t('sms_code_verification'),
                ],
                'back_to_start' => true
            ];
        }

        $allowResend = $this->getPaymentInfoFromCacheByAttempt($paymentFormAttempt, 'allow_email_resend');

        if (!$code || !is_numeric($code) || !(strlen((string)$code) == 6)) {
            $this->putPaymentInfoIntoCacheByAttempt(['allow_email_resend' => $allowResend], $paymentFormAttempt);

            return [
                'error' => [
                    'sms_code_verification' => [t('error_sms_wrong_code')],
                ],
                'allow_resend' => !$allowResend
            ];
        }

        if (!$this->smsCodeService->verifyConfirm($phone, $code, $allowResend)) {

            $this->putPaymentInfoIntoCacheByAttempt(['allow_email_resend' => $allowResend], $paymentFormAttempt);

            $errors = [t('error_sms_wrong_code')];
            if (!$allowResend) {
                $errors[] = t('error_sms_resend_block');
            }
            return [
                'error' => [
                    'sms_code_verification' => $errors,
                ],
                'back_to_start' => !$allowResend,
                'allow_resend' => $allowResend
            ];
        }

        return ([
            'success' => true,
//            'redirect' => route('cabinet.wallets.index'),
        ]);
    }

    public function putPaymentInfoIntoCacheByAttempt(array $data, PaymentFormAttempt $paymentFormAttempt)
    {
        $paymentInfo = $this->getPaymentInfoFromCacheByAttempt($paymentFormAttempt) ?? [];

        $paymentInfo = array_merge($paymentInfo, $data);

        Cache::put(self::PAYMENT_INFO_CACHE_KEY . $paymentFormAttempt->id, $paymentInfo);
    }

    public function getPaymentInfoFromCacheByAttempt(PaymentFormAttempt $paymentFormAttempt, $key = null)
    {
        $paymentInfo = Cache::get(self::PAYMENT_INFO_CACHE_KEY . $paymentFormAttempt->id);
        if ($key) {
            return $paymentInfo[$key] ?? null;
        }
        return $paymentInfo;
    }

    /**
     * @param string $email
     * @param string $paymentFormAttemptId
     * @return array|string[]
     */
    public function verifyByEmail(string $email, PaymentFormAttempt $paymentFormAttempt)
    {
        if(!$paymentFormAttempt) {
            return [
                'error' => t('something_went_wrong'),
            ];
        }
        $phone = $paymentFormAttempt->phone;
        $paymentFormAttempt->email = $email;
        $paymentFormAttempt->save();

        $cUser = $this->cUserService->getUserByEmailQuery($email)->first();

        $existingCUser = CUser::query()->orWhere([
            'email' => $email,
            'phone' => $phone
        ])->first();

        if ($existingCUser) {
            if ($existingCUser->phone != $phone || $existingCUser->email != $email) {
                return [
                    'error' => t('payment_form_another_one'),
                ];
            }
        }

        if ($cUser && isset($cUser->cProfile)) { //we have user with given email

            $cProfile = $cUser->cProfile ?? null;
            $project = $cUser->project;

            if (!$cProfile || $cProfile->isCorporate()) {
                return [
                    'error' => t('form_is_not_available'),
                ];
            }
            $paymentFormAttempt->profile_id = $cProfile->id;
            $paymentFormAttempt->save();

            $paymentForm = $paymentFormAttempt->paymentForm;
            if ($cProfile->status === CProfileStatuses::STATUS_ACTIVE || !$paymentForm->kyc || !$project->complianceProvider()) { //verified user (Form 7)
                return [
                    'step' => 1,
                    'userVerified' => true,
                    'kyc' => empty($project->complianceProvider()) ? false : $paymentForm->kyc
                ];
            } else { //user not verified, redirect to KYC ((Form 6)

                \C\c_user_login($cUser);

                $complianceData =  app(ComplianceService::class)->getCProfileComplianceData($cProfile);

                $complianceData['step'] = 5;
                $complianceData['email'] = $email;
                $complianceData['phone'] = $phone;
                $complianceData['kyc'] = $paymentForm->kyc;
                return $complianceData;
            }
        }
        //redirect to KYC ((Form 5) and send email for verification (miniEmail)

        $response = $this->emailVerificationService->generatePaymentFormEmailVerifyCode($email);

        if (!$response) {
            return [
              'error' => t('resend_not_allowed'),
              'step' => 3
            ];
        };

        return [
            'step' => 3,
            'timerSeconds' => $response['seconds'],
        ];

    }


    /**
     * @param string $email
     * @param string $code
     * @return array|bool[]
     */
    public function confirmVerifyEmailCode(string $email, string $code, PaymentFormAttempt $paymentFormAttempt)
    {
        $response = $this->emailVerificationService->paymentFormEmailVerifyCode($code ,$email);

        if (!empty($response['error'])) {
            return $response;
        }

        $paymentFormAttempt->email = $email;
        $paymentFormAttempt->save();
        //client verified email address

        return [
            'success' => true,
            'attempts' => false
        ];

    }

    /**
     * @param PaymentFormAttempt $paymentFormAttempt
     * @return array
     */
    public function generateKycAccessData(PaymentFormAttempt $paymentFormAttempt): array
    {
        $phone = $paymentFormAttempt->phone;
        $email = $paymentFormAttempt->email;
        $password_encrypted = bcrypt(Str::uuid());

        $project = Project::getCurrentProject();
        $project_id = $project->id ?? null;

        /** @var CUser $cUser */
        $cUser = $this->cUserService->createCUser(compact('email', 'phone', 'password_encrypted', 'project_id'));
        /** @var CProfile $cProfile */
        $cProfile = $this->cProfileService->createFromCUser($cUser, ['account_type' => CProfile::TYPE_INDIVIDUAL , 'compliance_level' => ComplianceLevel::VERIFICATION_LEVEL_0, 'status' => CProfileStatuses::STATUS_READY_FOR_COMPLIANCE]);

        $cProfileId = $cProfile->id;
        \C\c_user_login($cUser);

        $complianceService = new ComplianceService();
        $complianceData = $complianceService->getCProfileComplianceData($cProfile);

        $complianceData = array_merge($complianceData, compact('email', 'phone', 'cProfileId'));

        return $complianceData;
    }

    /**
     * @param string $paymentFormId
     * @param PaymentFormAttempt $paymentFormAttempt
     * @return CProfile
     */
    public function createPaymentFormCProfile(string $paymentFormId, PaymentFormAttempt $paymentFormAttempt)
    {
        $phone = $paymentFormAttempt->phone;
        $email = $paymentFormAttempt->email;

        $project = $paymentFormAttempt->paymentForm->project;
        $project_id = $project->id ?? null;

        $password_encrypted = bcrypt(Str::uuid());

        /** @var CUser $cUser */
        $cUser = $this->cUserService->createCUser(compact('email', 'phone', 'password_encrypted', 'project_id'));
        $cUser->payment_form_id = $paymentFormId;
        $cUser->email_verified_at = now();
        $cUser->project_id = $project_id;
        $cUser->save();

        $cProfileSaveData = [
            'account_type' => CProfile::TYPE_INDIVIDUAL ,
            'compliance_level' => !$project->complianceProvider() ? ComplianceLevel::VERIFICATION_LEVEL_3 : ComplianceLevel::VERIFICATION_LEVEL_0,
            'status' => CProfileStatuses::STATUS_ACTIVE,
            'first_name' => $paymentFormAttempt->first_name,
            'last_name' => $paymentFormAttempt->last_name,
        ];
        $cProfile = $this->cProfileService->createFromCUser($cUser, $cProfileSaveData);
        /** @var CProfile $cProfile */

//        list($token, $sumSubApiUrl, $sumSubNextLevelName, $contextId) = $this->getSumSubSDKData($cProfile);
//        return  compact('token','sumSubApiUrl','sumSubNextLevelName','contextId','email', 'phone');

        //set cProfileId
        $paymentFormAttempt->profile_id = $cProfile->id;
        $paymentFormAttempt->save();
        return $cProfile;
    }


    public function getPaymentFormAttemptById($id): ?PaymentFormAttempt
    {
        return PaymentFormAttempt::find($id);
    }

    public function loginUser(PaymentFormAttempt $paymentFormAttempt, $password)
    {
        $cUser = $this->cUserService->getUserByPhone($paymentFormAttempt->phone);

        if (!\C\c_user_guard()->attempt(['email' => $cUser->email, 'password' => $password])) {
            return [
                'error' => t('error_password_mismatch')
            ];
        }


        $status = $cUser->cProfile->status;

        if ($status == CProfileStatuses::STATUS_BANNED) {
            \C\c_user_guard()->logout();
            return [
                'error' => t('error_status_banned')
            ];
        }

       return $this->verifyByEmail($cUser->email, $paymentFormAttempt);
    }


    public function resolveToAccountForAttempt(PaymentFormAttempt $paymentFormAttempt, CProfile $cProfile): ?Account
    {
        if ($paymentFormAttempt->paymentForm->kyc) {
            $account = $cProfile->accounts()->where([
                'status' => AccountStatuses::STATUS_ACTIVE,
                'is_external' => false,
                'currency' => $paymentFormAttempt->to_currency,
                'account_type' => AccountType::TYPE_CRYPTO
            ])->first();

            if (!$account) {
                $walletService = resolve(WalletService::class);
                /* @var WalletService $walletService */
                $account = $walletService->addNewWallet($this->bitGOAPIService, $paymentFormAttempt->to_currency, $cProfile);
            }
        } else {
            $projectId = $cProfile->cUser->project_id ?? null;
            $walletProviderAccount = Account::getProviderAccount($paymentFormAttempt->to_currency, Providers::PROVIDER_WALLET, null, null, $projectId);

            if (!$walletProviderAccount) {
                return null;
            }

            $label = $cProfile->getFullName() . ' ' . $paymentFormAttempt->to_currency . ' PF';
            $walletId = $walletProviderAccount->cryptoAccountDetail->wallet_id;
            $generatedAddressJSON = $this->bitGOAPIService->generateWalletAddress($paymentFormAttempt->to_currency, $walletId, $label);
            $generatedAddress = json_decode($generatedAddressJSON);

            $account = new Account([
                'name' => $generatedAddress->label,
                'status' => AccountStatuses::STATUS_ACTIVE,
                'c_profile_id' => $cProfile->id,
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
                'account_type' => AccountType::TYPE_CRYPTO_FAKE,
                'currency' => $paymentFormAttempt->to_currency,
            ]);
            $account->save();

            $cryptoAccountDetails = new CryptoAccountDetail([
                'coin' => $paymentFormAttempt->to_currency,
                'label' => $generatedAddress->label,
                'passphrase' => $walletProviderAccount->cryptoAccountDetail->passphrase,
                'address' => $generatedAddress->address,
                'wallet_id' => $walletId,
                'account_id' => $account->id,
                'wallet_data' => $generatedAddressJSON,
            ]);
            $cryptoAccountDetails->save();
        }

        if ($account) {
            $paymentFormAttempt->to_account_id = $account->id;
            $paymentFormAttempt->save();
        }

        return $account;
    }


    public function getRecipientCProfile(PaymentFormAttempt $paymentFormAttempt)
    {
        $paymentForm = $paymentFormAttempt->paymentForm;
        if (in_array($paymentForm->type,PaymentFormTypes::MERCHANT_PAYMENT_FORMS)) {
            return $paymentForm->cProfile;
        }

        return $paymentFormAttempt->cProfile;
    }

    public function getComplianceLevel(CProfile $cProfile, PaymentForm $paymentForm = null): ?int
    {
        $paymentForm = $paymentForm ?? $cProfile->cUser->paymentForm;
        if ($paymentForm) {
            if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
                 if ($cProfile->compliance_level) {
                     return $cProfile->compliance_level;
                 }
                return $paymentForm->kyc ? ComplianceLevel::VERIFICATION_LEVEL_1 : ComplianceLevel::VERIFICATION_LEVEL_3;
            }
            return $paymentForm->cProfile->compliance_level;
        }
        return $cProfile->compliance_level;
    }

    /**
     * @param array $dataArray
     * @return PaymentForm
     */
    public function createPaymentFormCrypto(array $dataArray)
    {

        $cProfile = CProfile::find($dataArray['paymentFormMerchant']);

        $imageName = time() . '_' . $dataArray['paymentFormMerchantLogo']->getClientOriginalName();
        $dataArray['paymentFormMerchantLogo']->move(public_path('cratos.theme/images'), $imageName);

        $paymentForm = new PaymentForm();
        $paymentForm->fill([
            'name' => $dataArray['paymentFormName'],
            'type' => PaymentFormTypes::TYPE_CRYPTO_TO_CRYPTO_FORM,
            'status' => $dataArray['paymentFormStatus'],
            'c_profile_id' => $cProfile->id ?? null,
            'wallet_provider_id' => $dataArray['paymentFormWalletProvider'],
            'card_provider_id' => $dataArray['paymentFormCardProvider'] ?? null,
            'liquidity_provider_id' => $dataArray['paymentFormLiquidityProvider'],
            'rate_template_id' => $cProfile->rate_template_id ?? null,
            'website_url' => $dataArray['paymentFormWebSiteUrl'],
            'description' => $dataArray['paymentFormDescription'],
            'merchant_logo' => $imageName,
            'incoming_fee' => $dataArray['paymentFormIncomingFee'],
            'project_id' => $dataArray['paymentFormProject'],
        ]);

        return $paymentForm->save();
    }

    public function updateCryptoPaymentForm(PaymentForm $paymentForm, array $dataArray)
    {

        $cProfile = CProfile::find($dataArray['paymentFormMerchant']);

        if(!empty($dataArray['paymentFormMerchantLogo'])) {
            $imageName = time() . '_' . $dataArray['paymentFormMerchantLogo']->getClientOriginalName();
            $dataArray['paymentFormMerchantLogo']->move(public_path('cratos.theme/images'), $imageName);
            $paymentForm->fill(['merchant_logo' => $imageName]);
        }

        $paymentForm->fill([
            'name' => $dataArray['paymentFormName'],
            'type' => $dataArray['paymentFormType'] ?? $paymentForm->type,
            'status' => $dataArray['paymentFormStatus'],
            'c_profile_id' => $cProfile->id,
            'wallet_provider_id' => $dataArray['paymentFormWalletProvider'],
            'website_url' => $dataArray['paymentFormWebSiteUrl'],
            'description' => $dataArray['paymentFormDescription'],
            'incoming_fee' => $dataArray['paymentFormIncomingFee'],
            'project_id' => $dataArray['paymentFormProject'],
        ]);
        if (!$paymentForm->hasOperations()) {
            $paymentForm->project_id = $dataArray['paymentFormProject'] ?? null;
        }
        return $paymentForm->update();
    }

}
