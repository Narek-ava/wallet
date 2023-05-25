<?php

namespace App\Http\Controllers\Cabinet\Auth;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\API\v1\CUserRegistrationRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\Project;
use App\Rules\Password;
use App\Services\CaptchaService;
use App\Services\CProfileService;
use App\Services\CUserService;
use App\Services\CUserTemporaryRegisterDataService;
use App\Services\SmsCodeService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use App\Services\EmailVerificationService;


class CUserRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register CUser Controller
    |--------------------------------------------------------------------------
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = 'cabinet.wallets.index'; //?!

    /** @var CProfileService */
    protected $cProfileService;

    /** @var SmsCodeService */
    protected $smsCodeService;

    protected $guard = 'cUser'; //?!

    /** @var CaptchaService */
    protected $captchaService;

    /** @var EmailVerificationService */
    protected $emailVerificationService;


    public function __construct(
        CProfileService $cProfileService,
        SmsCodeService $smsCodeService,
        CaptchaService $captchaService,
        EmailVerificationService $emailVerificationService
    )
    {
        $this->cProfileService = $cProfileService;
        $this->smsCodeService = $smsCodeService;
        $this->captchaService = $captchaService;
        $this->emailVerificationService = $emailVerificationService;
    }


    protected function index(Request $request)
    {
        $accountType = null;

        if ($request->path() === 'cabinet/cregister'){
            $accountType = CProfile::TYPE_CORPORATE;
        }elseif($request->path() === 'cabinet/iregister'){
            $accountType = CProfile::TYPE_INDIVIDUAL;
        }

        if ($ref = $request->get('ref')) {
            Cookie::queue('ref', $ref);
            return redirect(route('cabinet.register.get'));
        }

        if (url()->previous() !== url()->current() && !$request->get('complete')) {
            session()->pull(\C\REGISTER_SESSION_DATA_KEY);
        }
        $registerData = \C\get_register_temp_data();

        return view('cabinet.auth.register', [
            // @see var smsRegisterToShow = {{ $smsRegisterToShow }};
            'emailRegisterToShow' => $registerData['emailRegisterToShow'] ?? false,
            'smsRegisterToShow' => $registerData['smsRegisterToShow'] ?? false,
            'accountType'=>$accountType,
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param CUserRegistrationRequest $request
     * @return Response | RedirectResponse
     */
    protected function create(CUserRegistrationRequest $request)
    {
        if (!$this->captchaService->checkCaptcha($request)) {
            \C\forget_register_temp_data();
            return \C\bad_captcha();
        };

        $phone = $request->input('phone_cc_part') . $request->input('phone_no_part');
        $email = $request->input('email');
        $exists = CUser::where('phone', $phone)
            ->orWhere('email', $email)
            ->first();
        if ($exists) {
            \C\forget_register_temp_data();
            return back()
                ->withInput()
                ->withErrors(['credentials' => t('error_duplicate_credentials')]);
        }

        $project = Project::getCurrentProject();
        if($project->status != ProjectStatuses::STATUS_ACTIVE){
            return back()
                ->withInput()
                ->withErrors(['credentials' => t('project_not_found')]);
        }
        $registerData = [
            'expires_at' => now()->add(\C\REGISTER_SESSION_DATA_TTL),
            'account_type' => $request->input('account_type'),
            'email' => $email,
            'phone' => $phone,
            'password_encrypted' => bcrypt($request->input('password')),
            'emailRegisterToShow' => true,
            'project_id' => $project->id ?? null,
            'smsRegisterToShow' => null,
        ];
        session()->put(\C\REGISTER_SESSION_DATA_KEY, $registerData);

        if (!$this->emailVerificationService->generateEmailConfirmCode($email)) {
            \C\forget_register_temp_data();
            return back()->withInput()->withErrors(['email' => t('incorrect_email_error')]);
        }

        return back()->withInput();
    }

    public function completeRegistration(
        Request $request,
        CUserTemporaryRegisterDataService $cUserTemporaryRegisterDataService,
        CUserService $cUserService
    )
    {
        $temporaryData = $cUserTemporaryRegisterDataService->find($request->route()->parameter('temporaryDataId'));

        if (!$temporaryData) {
            return redirect()->route('cabinet.login.get');
        }

        if ($cUserService->firstByEmail($temporaryData->email)) {
            $temporaryData->delete();
            return redirect()->route('cabinet.login.get');
        }

        $registerData = [
            'expires_at' => now()->add(\C\REGISTER_SESSION_DATA_TTL),
            'account_type' => $temporaryData->account_type,
            'email' => $temporaryData->email,
            'phone' => $temporaryData->phone,
            'password_encrypted' => $temporaryData->password_encrypted,
            'emailRegisterToShow' => false,
            'smsRegisterToShow' => true,
        ];
        session()->put(\C\REGISTER_SESSION_DATA_KEY, $registerData);

        session()->save();
        //If email is confirm, send sms verification code and open SMS code confirmation popup.
        if (!$this->smsCodeService->generateConfirm($registerData['phone'], false)) {
            \C\forget_register_temp_data();
            return redirect()->route('cabinet.login.get');
        }

        return redirect()->route('cabinet.register.get', ['complete' => true]);
    }
}
