<?php


namespace App\Http\Controllers\Cabinet\Auth;

use App\Enums\CProfileStatuses;
use App\Enums\ProjectStatuses;
use App\Enums\TwoFAType;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Models\Cabinet\CUser;
use App\Models\Project;
use App\Services\CaptchaService;
use App\Services\CUserService;
use App\Services\EmailService;
use App\Services\SmsCodeService;
use App\Services\TwoFAService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Google2FA;
use Illuminate\Support\Facades\Cookie;


class CUserLoginController extends Controller

{
    /*
    |--------------------------------------------------------------------------
    | Login CUser Controller
    |--------------------------------------------------------------------------
    | This controller handles authenticating CUsers for the cabinet
    */

    use AuthenticatesUsers;

    protected $guard = 'cUser'; //?

    /** @var string Where to redirect users after login */

    protected $redirectTo = 'cabinet.login.get';

    /** @var CaptchaService */
    private $captchaService;

    /** @var SmsCodeService */
    protected $smsCodeService;

    /** @var TwoFAService */
    protected $twoFAService;

    public function __construct(
        TwoFAService $twoFAService,
        SmsCodeService $smsCodeService,
        CaptchaService $captchaService
    )
    {
        $this->middleware('guest')->except('logout');
        $this->captchaService = $captchaService;
        $this->smsCodeService = $smsCodeService;
        $this->twoFAService = $twoFAService;
    }

    protected function twoFALoginSuccess(CUser $cUser)
    {
        session()->forget('2fa_logining_c_user_id');
        \C\c_user_login($cUser);
        \C\c_user_update_login();
        return redirect()->route('cabinet.wallets.index');
    }

    public function twoFALogin(Request $request)
    {
        $code = \C\twoFACode('2fa-confirm-code');
        $cUserId = decrypt(session()->get('2fa_logining_c_user_id'));
        /** @var CUser $cUser */
        $cUser = CUser::find($cUserId);
        $verified = $this->twoFAService->verify($code, $cUser);
        if ($verified) {
            $this->twoFALoginSuccess($cUser);
        }

        return response()->json([
            'isValid' => intval($verified),
        ]);
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
        $view = view('cabinet.auth.login', [
            'twoFAToShow' => true,
            'twoFAStrings' => $twoFAStrings ?? null,
            'two_fa_type' => $twoFAType
        ]);
        if ($wrongCode) {
            return $view->withErrors(['code' => t('error_2fa_wrong_code')]);
        };
        return $view;
    }

    public function showLoginForm(Request $request)
    {
        if ($ref = $request->get('ref')) {
            if (strlen($ref) <= 20) {
                Cookie::queue('ref', $ref);
            }
            return redirect(route('cabinet.login.get'));
        }
        $cookie = $_COOKIE[\C\REMEMBER_MY_USERNAME_COOKIE] ?? null;
        ${\C\REMEMBER_MY_USERNAME_COOKIE} = $cookie ? decrypt($cookie) : null;

        return view('cabinet.auth.login', compact(\C\REMEMBER_MY_USERNAME_COOKIE));
    }

    public function login(Request $request, CUserService $cUserService)
    {
        if (!$this->captchaService->checkCaptcha($request)) {
            return \C\bad_captcha();
        };

        if (! \C\c_user_guard()->attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withInput()->withErrors(['email' => t('error_password_mismatch')]);
        }

        $cUser = \C\c_user();
        $status = $cUser->cProfile->status;

        $project = $cUser->project;
        if($project->status != ProjectStatuses::STATUS_ACTIVE || $project->id !== Project::getCurrentProject()->id) {
            \C\c_user_guard()->logout();
            return back()
                ->withInput()
                ->withErrors(['email' => t('project_not_found')]);
        }

        // @todo status is status value in status-list of statuses
        if ($status == CProfileStatuses::STATUS_BANNED) {
            \C\c_user_guard()->logout();
            return back()->withInput()->withErrors(['email' => t('error_status_banned')]);
        }

        if ($request->remember_my_username) {
            setcookie(\C\REMEMBER_MY_USERNAME_COOKIE, encrypt($request->email), time()+60*\C\REMEMBER_MY_USERNAME_TTL);
        } else {
            setcookie(\C\REMEMBER_MY_USERNAME_COOKIE, '', 0);
        }

        if (!empty($cUser->two_fa_type)) {
            \C\c_user_guard()->logout();
            session()->put('2fa_logining_c_user_id', encrypt($cUser->id));
            $this->twoFAService->generateIfNeeded($cUser);
            return $this->showLoginForm2FA($cUser->two_fa_type);
        }

        \C\c_user_update_login();

        EmailFacade::sendSuccessfulLogin($cUser);
        $hasNotCurrentIp = $cUserService->hasNotCurrentIp(\C\getUserIp(), $cUser->id);
        if ($hasNotCurrentIp) {
            EmailFacade::sendLoginFromNewDevice($cUser);
        }

        return redirect()->route('cabinet.wallets.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('cabinet/login');
    }
}
