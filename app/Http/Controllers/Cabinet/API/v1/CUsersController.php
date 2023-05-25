<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\CompanyOwners;
use App\Enums\ComplianceLevel;
use App\Enums\CProfileStatuses;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\NotificationRecipients;
use App\Enums\SmsTypes;
use App\Enums\TwoFAType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Requests\Cabinet\API\v1\CUserRegisterSmsRequest;
use App\Http\Controllers\Controller;
use App\Mail\Register;
use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Models\Project;
use App\Models\SmsCode;
use Carbon\Carbon;
use App\Http\Requests\Common\{CProfileUpdateCorporateRequest,
    CProfileUpdateRequest,
    CUserUpdateEmailRequest,
    CUserUpdatePasswordRequest};
use App\Models\Cabinet\CUser;
use App\Services\{BitGOAPIService,
    CaptchaService,
    CProfileService,
    CUserService,
    CUserTemporaryRegisterDataService,
    EmailService,
    EmailVerificationService,
    NotificationService,
    NotificationUserService,
    ProjectService,
    SmsCodeService,
    WalletService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cookie, Hash, Log, Mail};
use Illuminate\Support\Str;

class CUsersController extends Controller
{
    /** @var CProfileService */
    protected $cProfileService;

    /** @var SmsCodeService */
    protected $smsCodeService;

    /** @var CUserService */
    protected $cUserService;


    public function __construct(
        CProfileService $cProfileService,
        SmsCodeService $smsCodeService,
        CUserService $cUserService
    )
    {
        $this->cProfileService = $cProfileService;
        $this->smsCodeService = $smsCodeService;
        $this->cUserService = $cUserService;
    }


    public function registerResendSms(Request $request, CaptchaService $captchaService)
    {
        if (!$captchaService->checkCaptcha($request)) {
            return response()->json([
                'errors' => [
                    'captcha' => t('captcha_error')
                ]
            ], 422);
        }

        $registerData = \C\get_register_temp_data();

        if (isset($registerData['phone']) && $this->smsCodeService->generateConfirm($registerData['phone'])) {
            return response()->json(['success' => true]);
        }
        \C\abort_register('error_sms_sending_header');
    }

    public function registerConfirmsSms(CUserRegisterSmsRequest $request, EmailVerificationService $emailVerificationService, CaptchaService $captchaService)
    {
        $registerData = \C\get_register_temp_data();
        if (!$registerData) {
            // @note 'No registering in progress' не должно показываться
            \C\abort_register('No registering in progress', true);
        }

        if (!$this->smsCodeService->verifyConfirm(
            $registerData['phone'],
            $request->input('code'),
            $allowResend
        )) {
            $errors = [t('error_sms_wrong_code')];
            if (!$allowResend) {
                $errors[] = t('error_sms_resend_block');
            }

            return response()->json([
                'success' => false,
                'allow_resend' => $allowResend,
                'errors' => $errors,
            ], 422);
        }

        //? @todo Service, transaction
        try {
            $cUser = CUser::where('email', $registerData['email'])->first();
            if (!$cUser) {
                $project = Project::getCurrentProject();
                $registerData['project_id'] = $project->id ?? null;

                $cUser = $this->cUserService->createCUser($registerData, true);
                $cUser->email_verified_at = now();
                // Default enable email two-factor authentication
                $cUser->two_fa_type = TwoFAType::EMAIL;
                $cUser->save();
            }
            $cUser->refresh();
            $cProfile = $this->cProfileService->createFromCUser(
                $cUser,
                ['account_type' => $registerData['account_type']]
            );
        } catch (\Throwable $e) {
            /** @todo лог таких событий более тщательно? */
            Log::alert($e->getMessage());
            abort(response()->json(['errors' => ['CAN NOT']], 500)); //? @todo WHAT
        }

        \C\forget_register_temp_data();
        \C\c_user_login($cUser);
        \C\c_user_update_login();
        EmailFacade::sendSuccessfulLogin($cUser);

        return response()->json([
            'success' => true,
            'redirect' => route('cabinet.wallets.index'),
        ]);
    }

    public function registerConfirmsEmail(
        CUserRegisterSmsRequest $request,
        EmailVerificationService $emailVerificationService,
        CaptchaService $captchaService,
        CUserTemporaryRegisterDataService $CUserTemporaryRegisterDataService
    ) {
        $registerData = \C\get_register_temp_data();

        if (!$registerData) {
            // @note 'No registering in progress' не должно показываться
            \C\abort_register('No registering in progress', true);
        }

        if (!$emailVerificationService->verifyConfirmEmailCode(
            $registerData['email'],
            $request->input('code'),
            $allowResend
        )) {
            $errors = [t('error_sms_wrong_code')];
            if (!$allowResend) {
                $errors[] = t('error_sms_resend_block');
                \C\forget_register_temp_data();
            }

            return response()->json([
                'success' => false,
                'allow_resend' => $allowResend,
                'errors' => $errors,
            ], 422);
        }
        $registerData['emailRegisterToShow'] = null;
        $registerData['smsRegisterToShow'] = true;

        session()->put(\C\REGISTER_SESSION_DATA_KEY, $registerData);

        $CUserTemporaryRegisterDataService->updateOrCreate(
            $registerData['email'],
            $registerData['account_type'],
            $registerData['phone'],
            $registerData['password_encrypted'],
        );

        //If email is confirm, send sms verification code and open SMS code confirmation popup.
        if (!$this->smsCodeService->generateConfirm($registerData['phone'], false)) {
            \C\forget_register_temp_data();
            return response()->json([
                'success' => false,
                'errors' => [t('something_went_wrong_twillio')],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'allow_resend' => $allowResend,
        ], 200);

    }

    public function registerResendEmail(Request $request, EmailVerificationService $emailVerificationService)
    {
        $registerData = \C\get_register_temp_data();

        if (isset($registerData['email']) && $emailVerificationService->generateEmailConfirmCode($registerData['email'])) {
            return response()->json(['success' => true]);
        }
        \C\abort_register('error_sms_sending_header');
    }

    /**
     * @param CProfileUpdateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CProfileUpdateRequest $request, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {
       // @todo unified style Facades | helpers
        $cUser = Auth::user();
        $profile = $cUser->cProfile;

        if ($profile->compliance_level >=  \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider()) {
            $citizenship = Country::getCountryNameByCode($request->citizenship);

            $profileFields = $request->only([
                    'account_type', 'first_name', 'last_name', 'country', 'company_name', 'company_email', 'company_phone',
                    'city', 'zip_code', 'address', 'gender', 'passport'
                ]) + [
                    'date_of_birth' => date('Y-m-d', strtotime($request->year . '-' . $request->month . '-' . $request->day)),
                    'citizenship' => $citizenship
                ];
        } else {
            $profileFields = $request->only([
                    'first_name', 'last_name', 'country'
            ]);
        }

        //@TODO add Middleware
        if (!in_array($profile->status, CProfileStatuses::ALLOWED_TO_CHANGE_SETTINGS_STATUSES)) {
            $fullName = $profile->getFullName();
            if ($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
                $cUser->fill($request->only(['phone'/*, 'email'*/]));
            }
            $profile->fill($profileFields);
            $changedAttributes = array_merge($profile->getDirty(), $cUser->getDirty());
            if ($changedAttributes) {
                EmailFacade::sendSettingUpdateToManager($profile, $changedAttributes, $fullName);
            }
            $successMessage = t('ui_settings_update_message_to_client');
        } else {

            if(!$profile->cUser->project->complianceProvider()){
                $profileFields['compliance_level'] = 3;
            }
            $profile->fill($profileFields + ['status' => CProfileStatuses::STATUS_ACTIVE]); //Update user status to allow user add compliance
            $changedAttributes = $profile->getDirty();
            if ($changedAttributes) {
                ActivityLogFacade::saveLog(LogMessage::USER_PERSONAL_INFORMATION_UPDATED_CABINET, ['name' => $profile->getFullName()], LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_INFORMATION_CHANGE_CABINET, null, $cUser->id);
                EmailFacade::sendSuccessUpdatePersonalInformationMessage($profile, $changedAttributes);
            }
            $profile->update();
            $profile->save();
            $profile->refresh();
            $walletService->addNewWallet($bitGOAPIService, Currency::getDefaultWalletCoin($profile->cUser->project_id), $profile);

            $successMessage = t('ui_settings_update_thank_you_message');
            $redirect = route('cabinet.settings.get');
        }

        return response()->json([
            'success' => $successMessage,
            'redirect' => $redirect ?? null,
        ]);
    }

    /**
     * @param CProfileUpdateCorporateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCorporate(CProfileUpdateCorporateRequest $request, CProfileService $CProfileService, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {

        $cUser = Auth::user();
        $profile = $cUser->cProfile;
        if ($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider()) {
            $profileFields = $request->only([
                    'company_email', 'company_name', 'company_phone', 'registration_number',
                    'country','legal_address', 'trading_address',
                    'contact_email', 'interface_language', 'currency_rate', 'webhook_url'
                ]) + ['registration_date' => date('Y-m-d', strtotime($request->year.'-'.$request->month.'-'.$request->day))];
        } else {
            $profileFields = $request->only(['company_name', 'country']);
        }

        if (!in_array($profile->status, CProfileStatuses::ALLOWED_TO_CHANGE_SETTINGS_STATUSES)) {
            $fullName = $profile->getFullName();
            $profile->fill($profileFields);
            $changedAttributes = $profile->getDirty();

            if ($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
                if($request->contact_phone !== $cUser->phone){
                    $cUser->phone = $request->contact_phone;
                    $changedAttributes['phone'] = $cUser->phone;
                }
                if ($request->beneficial_owners != $profile->getBeneficialOwnersForProfile()) {
                    $changedAttributes['beneficial_owners'] = $request->beneficial_owners;
                }
                if ($request->ceos != $profile->getCeosForProfile()) {
                    $changedAttributes['ceos'] = $request->ceos;
                }
                if ($request->shareholders != $profile->getShareholdersForProfile()) {
                    $changedAttributes['shareholders'] = $request->shareholders;
                }
            }
            if ($changedAttributes) {
                EmailFacade::sendSettingUpdateToManager($profile, $changedAttributes, $fullName);
            }
            $successMessage = t('ui_settings_update_message_to_client');
        } else {

            if(!$profile->cUser->project->complianceProvider()){
                $profileFields['compliance_level'] = 3;
            }

            $profile->fill($profileFields + ['status' => CProfileStatuses::STATUS_ACTIVE]); //Update user status to allow user add compliance
            $changedAttributes = $profile->getDirty();
            $successMessage = t('ui_settings_update_thank_you_message');
            if ($changedAttributes) {
                $fullName = $profile->getFullName();
                EmailFacade::sendSettingUpdateToManager($profile, $changedAttributes, $fullName);
            }
            $profile->update();
            $profile->refresh();
            $walletService->addNewWallet($bitGOAPIService, Currency::getDefaultWalletCoin($profile->cUser->project_id), $profile);
            $redirect = route('cabinet.settings.get');
        }


        return response()->json([
            'success' => $successMessage,
            'redirect' => $redirect ?? null
        ]);
    }

    /**
     * @param CUserUpdatePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(CUserUpdatePasswordRequest $request)
    {
        //TODO add log
        $user = Auth::user();
        $user->fill([
            'password' => Hash::make($request->password)
        ])->save();
        if (!$request->isMethod('patch')) {
            EmailFacade::sendPasswordUpdateEmail($user);
        }else{
            EmailFacade::sendSuccessfulPasswordResetMessage($user);
            ActivityLogFacade::saveLog(LogMessage::UPDATE_PASSWORD,['name' => $user->getFullNameAttribute()],LogResult::RESULT_SUCCESS,LogType::TYPE_C_PROFILE_PASSWORD_CHANGE);
        }
        \auth()->logout();
        return  redirect()->back();
    }

    /**
     * @param CUserUpdateEmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmail(CUserUpdateEmailRequest $request, EmailVerificationService $emailVerificationService)
    {
        // @ todo сделать проверку на одинаковость нового и старого?
        // @todo уточнить, может ли пользователь это делать сам, без подтверждения "менеджера"
        // @todo (когда-нибудь) сделать более отзывчивый UI
//        \auth()->user()->update(['email' => $request->email, 'email_verified_at' => null]);
        $emailVerificationService->generateToChange(Auth::user(), $request->email);
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_EMAIL_CHANGE_CABINET, ['email' => Auth::user()->email, 'newEmail' => $request->email],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_EMAIL_CHANGE_CABINET, null, Auth::user()->id);
        \auth()->logout();
        return  redirect()->back();
    }

}
