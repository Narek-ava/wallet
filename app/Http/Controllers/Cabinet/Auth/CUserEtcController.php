<?php


namespace App\Http\Controllers\Cabinet\Auth;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\ProjectStatuses;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Models\Cabinet\CUser;
use App\Models\Project;
use App\Services\CaptchaService;
use App\Services\CUserService;
use App\Services\EmailService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Rules\Password as PasswordRule;


class CUserEtcController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CUser etc Controller
    |--------------------------------------------------------------------------
    | password restore etc
    |
    */

    use ResetsPasswords;

    /** @var CaptchaService */
    private $captchaService;

    protected $redirectTo = 'cabinet/wallets'; // @todo cabinet.dashboard to be

    public function __construct(CaptchaService $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    public function getPasswordResetRequest()
    {
        return view('cabinet.auth.password-reset-request');
    }

    public function getPasswordResetDone()
    {
        return view('cabinet.auth.password-reset-done');
    }

    public function getPasswordResetFinish(Request $request)
    {
        return view('cabinet.auth.password-reset-finish', ['payment_form' => $request->payment_form]);
    }

    public function postPasswordResetRequest(Request $request)
    {
        if (!$this->captchaService->checkCaptcha($request)) {
            return \C\bad_captcha();
        };

        $project = Project::getCurrentProject();
        if($project->status != ProjectStatuses::STATUS_ACTIVE){
            return redirect()->back()->withErrors([
                'email' => t('project_not_found')
            ]);
        }

        $rules = ['email' => 'required|email'] + \C\PHONE_RULES;
        $messages = [
            'email.*' => t(\C\EMAIL_ERROR_KEY),
            'phone_cc_part.*' => t(\C\PHONE_ERROR_KEY),
            'phone_no_part.*' => t(\C\PHONE_ERROR_KEY),
        ];
        $input = $this->validate($request, $rules, $messages);

        $phone = $input['phone_cc_part'] . $input['phone_no_part'];
        $cUser = CUser::where('email',  $input['email'])
            ->where([
                'phone' => $phone,
                'project_id' => $project->id
        ])
            ->first();

        if (!$cUser) {
            return redirect()->back()->withErrors([
                'email' => t('error_not_registered')
            ]);
        }

        $response = EmailFacade::sendPasswordRecovery($cUser);

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return redirect(route('cabinet.password-reset-done'));
            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
        }
    }

    public function getPasswordReset(Request $request, $token = null)
    {
        return view('cabinet.auth.password-reset')->with(
            ['token' => $request->token, 'email' => $request->email, 'payment_form' => $request->payment_form]
        );
    }

    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);
        $user->save();

        event(new PasswordReset($user));

        \C\c_user_login($user);
    }

    public function postPasswordReset(Request $request)
    {
        $rules = [
            'password' => [new PasswordRule(), 'confirmed'],
            'token' => 'required',
            'email' => 'required',
        ];
        $messages = [
            'password.confirmed' => t('error_password_reset_new_password_not_confirmed'),
        ];
        $this->validate($request, $rules, $messages);

        $user = CUser::whereEmail($request->email)->first();

        $token = Cache::get(CUserService::USER_PASSWORD_RESET_CACHE . $user->id);

        if($request->token != $token) {
            return back()->withErrors(['token' =>  t('error_password_reset_broken_token')]);
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' =>  t('error_password_reset_new_password_sane')]);
        };
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
        $credentials['token'] = app(PasswordBroker::class)->createToken($user);

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                \C\c_user_update_login();
                ActivityLogFacade::saveLog(LogMessage::RESET_PASSWORD,['name' => $user->getFullNameAttribute()],LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_PASSWORD_RESTORE);
                EmailFacade::sendSuccessfulPasswordResetMessage($user);
                Cache::forget(CUserService::USER_PASSWORD_RESET_CACHE . $user->id);
                if($request->payment_form) {
                    return redirect()->route('cabinet.password-reset-finish', ['payment_form' => $request->payment_form]);
                }
                return redirect()->route('cabinet.password-reset-finish');
                break;
            default:
                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => trans($response)]);
        }
    }
}
