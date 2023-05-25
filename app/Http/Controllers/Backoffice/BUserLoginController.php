<?php


namespace App\Http\Controllers\Backoffice;

use App\Enums\LogLevel;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TwoFAType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Models\Backoffice\BUser;
use App\Services\ActivityLogService;
use App\Services\CaptchaService;
use App\Services\CUserService;
use App\Services\TwoFAService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;


class BUserLoginController extends Controller

{
    /*
    |--------------------------------------------------------------------------
    | Login BUserLoginController
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating busers for the backoffice application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    use AuthenticatesUsers;

    protected $guard = 'bUser';

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    protected $redirectTo = '/backoffice/dashboard';

    /** @var TwoFAService */
    protected $twoFAService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        TwoFAService $twoFAService
    )
    {
        $this->middleware('guest')->except('logout');
        $this->twoFAService = $twoFAService;
    }


    public function showLoginForm()
    {
        return view('backoffice.auth.login');
    }


    public function login(Request $request, CUserService $cUserService, CaptchaService $captchaService)
    {
        if (!$captchaService->checkCaptcha($request)) {
            return \C\bad_captcha();
        };

        if (auth()->guard('bUser')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $bUser = \C\b_user();
            if (!empty($bUser->two_fa_type)) {
                \C\b_user_guard()->logout();
                session()->put('2fa_logining_b_user_id', encrypt($bUser->id));
                return $this->showLoginForm2FA($bUser->two_fa_type);
            }
            ActivityLogFacade::saveLog(LogMessage::B_USER_LOGIN_SUCCESS , ['email' => $request->email], LogResult::RESULT_SUCCESS, LogType::TYPE_B_USER_LOGIN);
            return redirect()->intended('backoffice/two-factor');
        }
        if (!$cUserService->findByEmail($request->email)) {
            ActivityLogFacade::saveLog(LogMessage::B_USER_LOGIN_FAILED,['email' => $request->email], LogResult::RESULT_FAILURE,LogType::TYPE_B_USER_LOGIN);
        }
        return back()->withErrors(['email' => t('error_password_mismatch')]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('backoffice/login');
    }

    public function showLoginForm2FA(int $twoFAType, bool $wrongCode = false)
    {
        if ($twoFAType == TwoFAType::EMAIL) {
            $twoFAStrings = [
                'header' => t('ui_2fa_confirm_email_header'),
                'label' => t('ui_2fa_confirm_email_label'),
            ];
        }

        /** @var \Illuminate\View\View|\Illuminate\Contracts\View\Factory $view */
        $view = view('backoffice.auth.login', [
            'twoFAToShow' => true,
            'two_fa_type' => $twoFAType,
            'twoFAStrings' => $twoFAStrings ?? null,
        ]);
        if ($wrongCode) {
            return $view->withErrors(['code' => t('error_2fa_wrong_code')]);
        };
        return $view;
    }

    public function twoFactorLogin(Request $request)
    {
        $code = $request->get('2fa-confirm-code');

        $bUserId = session()->get('2fa_logining_b_user_id');

        if(!$bUserId) {
            return response()->json([
                'isValid' => false,
            ]);
        }

        $bUserId = decrypt($bUserId);

        /** @var BUser $cUser */
        $bUser = BUser::find($bUserId);

        $verified = $this->twoFAService->verify($code, $bUser);
        if ($verified) {
            $this->twoFALoginSuccess($bUser);
        }

        return response()->json([
            'isValid' => intval($verified),
        ]);
    }

    protected function twoFALoginSuccess(BUser $bUser)
    {
        session()->forget('2fa_logining_b_user_id');
        \C\b_user_login($bUser);
        ActivityLogFacade::saveLog(LogMessage::B_USER_LOGIN_SUCCESS , ['email' => $bUser->email], LogResult::RESULT_SUCCESS, LogType::TYPE_B_USER_LOGIN);
        return redirect()->route('backoffice.profiles', ['type' => 1]);
    }

}
