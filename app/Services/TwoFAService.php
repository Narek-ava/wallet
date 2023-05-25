<?php

namespace App\Services;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TwoFAType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CUser;
use App\Models\TwoFACode;
use Google2FA;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TwoFAService
{

    const SESSION_KEY = 'twoFAVerified';
    const TWO_FA_STATUS_PENDING = 0;
    const TWO_FA_STATUS_CHECKED = 1;

    protected function _code(): string
    {
        // @todo CodeDup _code()
        $size = \C\TWO_FA_CODE_SIZE;
        return (string)rand(10 ** ($size - 1), 10 ** ($size) - 1);
    }

    protected function _send(TwoFACode $code, CUser $cUser)
    {
        switch ($code->type) {
            case TwoFAType::EMAIL:
                EmailFacade::send2FACode($cUser, $code->value);
                break;
        }
    }

    public function saveSession(): void
    {
        Session::put(self::SESSION_KEY, true);
    }

    public function checkSession(): bool
    {
        return Session::get(self::SESSION_KEY, false);
    }

    public function removeSession()
    {
        return Session::remove(self::SESSION_KEY);
    }

    public function verify(string $code, CUser $user, $isApi = false, bool $generateAnyway = false): bool
    {
        $type = $user->two_fa_type;
        $type = $user->two_fa_type;
        if (!$type && $generateAnyway) {
            $type = TwoFAType::EMAIL;
        }

        switch ($type) {
            case TwoFAType::GOOGLE:
                if (!$user->google2fa_secret) {
                    return false;
                }
                return Google2FA::verify($code, decrypt($user->google2fa_secret), \C\TWO_FA_GOOGLE_WINDOW);
                break;

            case TwoFAType::EMAIL:
                $sent2FACode = TwoFACode::where('c_user_id', $user->id)
                    ->where('type', $type)
                    ->first();
                if (!$sent2FACode) {
                    return false;
                }
                if (\C\is_expired($sent2FACode->expires_at)) {
                    return false;
                }
                if ($code == $sent2FACode->value) {
                    if (!$isApi) {
                        $sent2FACode->delete();
                    }
                    return true;
                }
                break;
            default:
                break;
        }
        return false;
    }

    public function generateIfNeeded(CUser $cUser, ?bool $generateAnyway = false)
    {
        $newCode = $this->generateTwoFaCode($cUser, false, $generateAnyway);

        if (($cUser->two_fa_type == TwoFAType::EMAIL || $generateAnyway) && $newCode) {
            $this->_send($newCode, $cUser);
        }
    }

    public function enableGoogleTwoFactorAuth(CUser $cUser, $isApi = false)
    {

        $check = $this->checkSwitchCondition($cUser, TwoFAType::GOOGLE);
        if (!empty($check)) {
            return $check;
        }

        $secret = Google2FA::generateSecretKey();

        Cache::put('2fa_google_secret_register' . $cUser->id, encrypt($secret), 180);

        try {
            $qrImage = Google2FA::getQRCodeInline(
                config('app.name'),
                $cUser->email,
                $secret
            );
        } catch (\Exception $e) {
            return [
                "success" => false,
                "errors" => ['2fa_error' => t('cannot_connect_google_auth'),]
            ];
        }


        return [
            'success' => (bool)$cUser->email_verified_at,
            'qrImage' => $qrImage,
            'secret' => $secret,
        ];
    }

    public function enableEmailTwoFactorAuth(CUser $cUser, $isApi = false)
    {
        $check = $this->checkSwitchCondition($cUser, TwoFAType::EMAIL);
        if (!empty($check)) {
            return $check;
        }

        $cUser->two_fa_type = TwoFAType::EMAIL;
        $this->generateIfNeeded($cUser);

        $response = [
            'success' => (bool)$cUser->email_verified_at,
            'message' => t('sent_verify_code')
        ];

        if (!$isApi) {
            $response = array_merge($response, ['header' => t('ui_2fa_confirm_email_header'), 'label' => t('ui_2fa_confirm_email_label'),]);
        }
        return $response;
    }

    public function confirmGoogleTwoFactorAuth(CUser $cUser, $isApi = false)
    {

        $code = \C\twoFACode();
        $encryptedSecret = Cache::get('2fa_google_secret_register' . $cUser->id);
        $cUser->two_fa_type = TwoFAType::GOOGLE;
        $cUser->google2fa_secret = $encryptedSecret;

        if (!$this->verify($code, $cUser)) {
            return [
                "success" => false,
                "errors" => ['error_2fa_wrong_code' => t('error_2fa_wrong_code'),]
            ];
        }

        $cUser->save();
        Cache::forget('2fa_google_secret_register' . $cUser->id);
        EmailFacade::sendBinding2FA($cUser);
        ActivityLogFacade::saveLog(
            LogMessage::USER_2FA_GOOGLE_WAS_ENABLED, [],
            LogResult::RESULT_SUCCESS, LogType::TYPE_2FA_GOOGLE_ENABLED,
            null, $cUser->id
        );

        if (!$isApi) {
            $response = ['two_fa_type' => $cUser->two_fa_type];
        }

        $response['success'] = true;

        return $response;
    }

    public function confirmEmailTwoFactorAuth(CUser $cUser, $isApi = false)
    {

        $code = \C\twoFACode();
        $cUser->two_fa_type = TwoFAType::EMAIL;
        if (!$this->verify($code, $cUser)) {
            return [
                "success" => false,
                "errors" => ['error_2fa_wrong_code' => t('error_2fa_wrong_code'),]
            ];
        }

        $cUser->save();
        EmailFacade::sendBinding2FA($cUser);
        ActivityLogFacade::saveLog(
            LogMessage::USER_2FA_EMAIL_WAS_ENABLED,
            [], LogResult::RESULT_SUCCESS,
            LogType::TYPE_2FA_EMAIL_ENABLED, null, $cUser->id
        );

        if (!$isApi) {
            $response = ['two_fa_type' => $cUser->two_fa_type,];
        }
        $response['success'] = true;

        return $response;
    }

    public function disableGoogleTwoFactorAuth(CUser $cUser, $isApi = false)
    {
        $code = \C\twoFACode();
        if (!$this->verify($code, $cUser)) {
            return [
                "success" => false,
                "errors" => ['error_2fa_wrong_code' => t('error_2fa_wrong_code'),]
            ];
        }

        $cUser->two_fa_type = TwoFAType::NONE;
        $cUser->save();
        EmailFacade::sendUnlink2FA($cUser);
        ActivityLogFacade::saveLog(
            LogMessage::USER_2FA_GOOGLE_WAS_DISABLED,
            [],
            LogResult::RESULT_SUCCESS,
            LogType::TYPE_2FA_GOOGLE_DISABLED,
            null,
            $cUser->id
        );

        $response = ['success' => true];

        if (!$isApi) {
            $response['two_fa_type'] = $cUser->two_fa_type;
        }

        return $response;
    }

    public function disableEmailTwoFactorAuth(CUser $cUser, $isApi = false)
    {

        $this->generateIfNeeded($cUser);

        if (!$isApi) {
            $response = [
                'header' => t('ui_2fa_confirm_email_header'),
                'label' => t('ui_2fa_confirm_email_label'),
            ];
        }
        $response['success'] = true;

        return $response;
    }

    public function confirmDisableEmailTwoFactorAuth(CUser $cUser, $isApi = false)
    {

        $code = \C\twoFACode();
        if (!$this->verify($code, $cUser)) {
            return [
                "success" => false,
                "errors" => ['error_2fa_wrong_code' => t('error_2fa_wrong_code'),]
            ];
        }

        $cUser->two_fa_type = TwoFAType::NONE;
        $cUser->save();
        EmailFacade::sendUnlink2FA($cUser);
        ActivityLogFacade::saveLog(
            LogMessage::USER_2FA_EMAIL_WAS_DISABLED,
            [],
            LogResult::RESULT_SUCCESS,
            LogType::TYPE_2FA_EMAIL_DISABLED,
            null,
            $cUser->id
        );

        if (!$isApi) {
            $response = ['two_fa_type' => $cUser->two_fa_type,];
        }

        $response['success'] = true;

        return $response;
    }

    public function createTwoFACode(CUser $cUser)
    {
        $newCode = $this->generateTwoFaCode($cUser, true);

        if ($cUser->two_fa_type == TwoFAType::EMAIL && $newCode) {
            $this->_send($newCode, $cUser);
        }

        return $newCode;
    }

    public function generateTwoFAToken($twoFaId, $code)
    {
        $twoFACode = TwoFACode::find($twoFaId);

        if (empty($twoFACode)) {
            return [
                "response" => ['success' => false, "errors" => ['2fa_code_error' => t('not_found_2fa_code')]],
                "status_code" => 401
            ];
        }

        if (!$this->verify($code, $twoFACode->user, true)) {
            return [
                "response" => ['success' => false, "errors" => ['error_2fa_wrong_code' => t('error_2fa_wrong_code')]],
                "status_code" => 403
            ];
        }

        $twoFACode->update(['token' => Str::random(60)]);

        return [
            "response" => ['success' => true, 'token' => $twoFACode->token],
            "status_code" => 200
        ];
    }

    public function verifyToken($token, CUser $cUser)
    {
        if (!$token) {
            return false;
        }

        $twoFACode = TwoFACode::where(['token' => $token, 'c_user_id' => $cUser->id])->first();

        if (!empty($twoFACode)) {
            $twoFACode->delete();
            return true;
        }

        return false;
    }

    /**
     *  Возвращает объект response, если проверка не прошла!!!
     */
    public function checkSwitchCondition(CUser $cUser, int $type)
    {
        if (!$cUser->two_fa_type) {
            return [];
        }

        $typeName = TwoFAType::getName($type);
        switch ($type) {
            case  TwoFAType::EMAIL:
                $errorMessage = $cUser->two_fa_type == TwoFAType::EMAIL ? t('already_enable_2fa', ['2fa' => $typeName]) : t('message_2fa_switch_condition', ['type2' => $typeName, 'type1' => TwoFAType::getName($cUser->two_fa_type)]);
                break;
            case TwoFAType::GOOGLE:
                $errorMessage = $cUser->two_fa_type == TwoFAType::GOOGLE ? t('already_enable_2fa', ['2fa' => $typeName]) : t('message_2fa_switch_condition', ['type2' => $typeName, 'type1' => TwoFAType::getName($cUser->two_fa_type)]);
                break;
            default:
                $errorMessage = t('invalid_status');
                break;
        }

        return [
            'success' => false,
            "errors" => ['enable_2fa_error' => $errorMessage,]
        ];
    }

    protected function generateTwoFaCode(CUser $cUser, $isApi = false, $generateAnyway = false)
    {
        $type = $cUser->two_fa_type;
        if (!$type && $generateAnyway) {
            $type = TwoFAType::EMAIL;
        }
        if (empty($type) || ($type == TwoFAType::GOOGLE && !$isApi)) {
            return null;
        }

        $value = $this->_code();
        $hasCode = TwoFACode::where('c_user_id', $cUser->id)->first();
        if ($hasCode) {
            $hasCode->delete();
        }

        $newCode = new TwoFACode();

        $newCode->id = Str::uuid();
        $newCode->type = $type;
        $newCode->value = $value;
        $newCode->status = self::TWO_FA_STATUS_PENDING;
        $newCode->expires_at = now()->add(\C\TWO_FA_CODE_TTL);
        $newCode->c_user_id = $cUser->id;

        $newCode->save();

        return $newCode;
    }

    public function getTwoFactorAuth(BUser $bUser)
    {
        $google2faSecret = $bUser->google2fa_secret;
        $secret = $google2faSecret ? decrypt($google2faSecret) : Google2FA::generateSecretKey();

        $qrImage = Google2FA::getQRCodeInline(
            config('app.name'),
            $bUser->email,
            $secret
        );
        return [
            'qrImage' => $qrImage,
            'secret' => $secret,
        ];

    }

    public function generateTwoFactorAuth(BUser $bUser)
    {
        $secret = Google2FA::generateSecretKey();

        Cache::put('2fa_google_secret_register' . $bUser->id, encrypt($secret), 180);

        $qrImage = Google2FA::getQRCodeInline(
            config('app.name'),
            $bUser->email,
            $secret
        );
        return [
            'qrImage' => $qrImage,
            'secret' => $secret,
        ];

    }

    public function confirmGoogleTwoFactorAuthAdmin()
    {
        $bUser = \C\b_user();
        $code = \C\twoFACode();
        $encryptedSecret = Cache::get('2fa_google_secret_register' . $bUser->id);

        if(!$encryptedSecret) {
            return [
                "success" => false,
                "error" => t('error_unknown')
            ];
        }

        if (!Google2FA::verify($code, decrypt($encryptedSecret), \C\TWO_FA_GOOGLE_WINDOW)) {
            return [
                "success" => false,
                "error" => t('error_2fa_wrong_code')
            ];
        }

        $bUser->two_fa_type = TwoFAType::GOOGLE;
        $bUser->google2fa_secret = $encryptedSecret;
        $bUser->save();

        Cache::forget('2fa_google_secret_register' . $bUser->id);

        ActivityLogFacade::saveLog(
            LogMessage::ADMIN_2FA_GOOGLE_WAS_ENABLED, [],
            LogResult::RESULT_SUCCESS, LogType::TYPE_ADMIN_2FA_GOOGLE_ENABLED,
            $bUser->id
        );

        return ['success' => true];
    }


}
