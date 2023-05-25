<?php

namespace C;

use App\Facades\EmailFacade;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

/**
 заготовка
 * @param null $time
 * @return string
 */
function time($time = null)
{
    $TZ = 'CET'; // @todo нужно правильно определять
    if (!$time) {
        $time = now();
    }
    return $time->timezone($TZ)->format('d/m/Y H:i');
}

function notify(CUser $cUser, string $msgKey)
{
    $subject = t('mail_notification_subject');
    $message_text =  t($msgKey);
    EmailFacade::send(
        'cabinet.emails._notify',
        $subject,
        compact('subject', 'message_text'),
        $cUser->email
    );
}

function twoFACode(string $paramName = null): string
{
    if (!$paramName) {
        $paramName = 'code';
    }
    return preg_replace('/\D/', '', request()->$paramName);
}

function _rates(string $key): string
{
    return _('rates_title_' . $key);
}

function rates_format($number, int $precision = \C\RATES_DEFAULT_PRECISION): ?string
{
    if (is_null($number)) {
        return null;
    }
    return number_format($number, $precision, '.', '');
}


function rates_input(string $key0, $value0, int $level, int $precision = \C\RATES_DEFAULT_PRECISION): string
{
    $key = $key0 . ($level ? ('['. $level .']') : '');
    $value = $level ? $value0[$level] : $value0;
    return  '<input data-decimal="' . $precision . '" class="rates-input" style="width: 95%" name="'.$key.'" value="'. $value . '">';
}

/**
 * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
 */
function c_user_guard()
{
    return auth()->guard('cUser');
}

/**
 * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
 */
function b_user_guard()
{
    return auth()->guard('bUser');
}

function c_user_api_guard()
{
    return auth()->guard('api');
}

function c_user()
{
    return c_user_guard()->user();
}

function api_c_user()
{
    return c_user_api_guard()->user();
}

function b_user()
{
    return b_user_guard()->user();
}

function c_user_update_login(): void
{
    if (! ($cUser = c_user())) {
        return;
    }
    if (! ($cProfile = $cUser->cProfile())) {
        return;
    }
    $cUser->cProfile()->update([
        'last_login' => now(),
    ]);
}

function c_user_login(cUser $cUser): void
{
    c_user_guard()->login($cUser);
}

function b_user_login(BUser $bUser): void
{
    b_user_guard()->login($bUser);
}

function bad_captcha(): RedirectResponse
{
    return back()
        ->withInput()
        ->withErrors(['captcha' => t('error_bad_captcha')]);
}

function is_expired(?Carbon $value): bool
{
    if (!$value) {
        return true;
    }
    return now()->gt($value);
}

// @todo better VO
function get_register_temp_data(): ?array
{
    $registerData = session()->get(\C\REGISTER_SESSION_DATA_KEY);
    // @note без expires_at было до CRATOS-368 Complex bug fix 2020-10-16, т.е. такого не должно быть
    if (!$registerData || !$registerData['expires_at']) {
        return null;
    }
    if (is_expired($registerData['expires_at'])) {
        forget_register_temp_data();
        return null;
    }
    return $registerData;
}

function forget_register_temp_data(): void
{
    session()->forget(\C\REGISTER_SESSION_DATA_KEY);
}

function forget_payment_data(): void
{
    session()->forget(\C\MERCHANT_PAYMENT_DATA_KEY);
}

/**
 * for API only?
 *
 * @param string|null $error_key
 * @param mixed $redirect {true ==> default}
 * @param int $code
 */
function abort_register(?string $error_key, $redirect = false, $code = 520): void
{
    $redirectResponsePart = [];
    if (is_string($redirect)) {
        $redirectResponsePart = ['redirect' => $redirect];
    } elseif ($redirect == true) {
        $redirectResponsePart = ['redirect' => route('cabinet.register.get')];
    }

    $responseData = ['errors' => [
        t($error_key ?: \C\COMMON_ERROR_KEY), // @note вообще-то такого не должно быть)
    ]];
    $responseData += $redirectResponsePart;
    abort(response()->json($responseData, $code));
}

function fee($amount, $percent)
{
    $value = $amount / 100 * $percent;
    return $value < 36 ? 35 : $value;
}

function getUserIp()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipData = explode(':', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return $ipData[0];
    }
    return \Request::ip();
}

function getProjectColors()
{
    $project = Project::getCurrentProject();
    return $project->colors ?? null;
}
