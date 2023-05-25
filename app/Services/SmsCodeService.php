<?php

namespace App\Services;

use App\Facades\EmailFacade;
use App\Models\Project;
use App\Models\SmsCode;
use App\Services\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function C\getUserIp;

class SmsCodeService
{
    CONST TYPE_CONFIRM = 1;
    CONST TYPE_2FA = 2;

    protected function _code(): string
    {
        // @todo CodeDup _code()
        $size = \C\SMS_SIZE;
        return (string)rand(10 ** ($size - 1), 10 ** ($size) - 1);
    }

    protected function _send(string $phone, string $value): bool
    {
        // @todo save только после true тут
        $subject = 'SMS code';
        return EmailFacade::send(
            'cabinet.emails.sms_emulate',
            $subject,
            [
                'phone' => $phone,
                'code' => $value,
            ],
            config('mail.from.address')
        );
    }

    protected function _getSent(string $phone, $type): ?SmsCode
    {
        return SmsCode::where('phone', $phone)->where('type', $type)->first();
    }

    public function verifyConfirm(
        string $phone,
        string $verifyingValue,
        ?bool &$allowResend
    ): bool
    {
        $sentCode = $this->_getSent($phone, self::TYPE_CONFIRM);
        if (!$sentCode) {
            // @maybe abort
            $allowResend = true;
            return false;
        }

        $allowResend = (empty($sentCode->blocked_till)) || (now()->gt($sentCode->blocked_till));

        // @todo CRATOS-355 Story - Expiration of temp data
        if ($verifyingValue === $sentCode->value) {
            $sentCode->delete();
            return true;
        }

        return false;
    }

    public function verifyConfirmApi(string $phone, string $verifyingValue): array
    {
        $sentCode = $this->_getSent($phone, self::TYPE_CONFIRM);
        if (!$sentCode) {
            return [
                'success' => false,
                'error' => t('sms_code_verification'),
            ];
        }

        if ($sentCode->try_count >= \C\CODE_VERIFY_TRY_ATTEMPTS) {
            return [
                'success' => false,
                'error' => t('error_sms_wrong_code_require_new'),
            ];
        }

        if ($verifyingValue === $sentCode->value) {
            $sentCode->delete();
            return [
                'success' => true,
            ];
        }

        $sentCode->increment('try_count');
        $sentCode->save();
        return [
            'success' => false,
            'error' => t('error_sms_wrong_code'),
        ];
    }


    /**
     * @param string $phone
     * @param string $verifyingValue
     * @param bool|null $allowResend
     * @param bool|null $tryAgain
     * @return bool
     * @throws \Exception
     */
    public function verifyConfirmForCryptoPayment(string $phone, string $verifyingValue, ?bool &$allowResend, ?bool &$tryAgain): bool
    {
        $sentCode = $this->_getSent($phone, self::TYPE_CONFIRM);
        if (!$sentCode) {
            $allowResend = false;
            return false;
        }

        $allowResend = (empty($sentCode->blocked_till)) || (now()->gt($sentCode->blocked_till));

        if ($verifyingValue === $sentCode->value) {
            $sentCode->delete();
            return true;
        }

        $sentCode->increment('try_count');
        $sentCode->save();

        $tryAgain = !($sentCode->try_count >= \C\CRYPTO_CODE_VERIFY_TRY_ATTEMPTS);

        return  false;
    }

    public function generateConfirm(string $phone, bool $abortIfBlocked = true): bool
    {
        $type = self::TYPE_CONFIRM;
        $_code = $this->_code();
        $sentSmsCode = $this->_getSent($phone, $type);
        if (!$sentSmsCode) {
            // @todo? expires_at = Carbon::now()->add('1m'); // @todo config(...)
            $smsCode = SmsCode::create([
                'type' => $type,
                'phone' => $phone,
                'value' => $_code,
                'sent_count' => 1,
            ]);
            $sent = $this->sendToPhone($phone, $_code, $smsCode->sent_count);
//            $this->_send($phone, $_code);
            if ($sent) {
                return true;
            }
            \C\forget_register_temp_data();
            $smsCode->delete();
            return false;
        }

        // @todo CRATOS-355 Story - Expiration of temp data
        // @todo CRATOS-387 Story - unify API response
        if ($sentSmsCode->blocked_till) {
            if (now()->lt($sentSmsCode->blocked_till)) {
                if ($abortIfBlocked) {
                    \C\abort_register('error_sms_resend_block');
                } else {
                    return false;
                }
            }

            $sentSmsCode->blocked_till = null;
            $sentSmsCode->sent_count = 0;
        }

        $sentSmsCode->value = $_code;
        $sentSmsCode->increment('sent_count');
        if ($sentSmsCode->sent_count >= \C\SMS_ATTEMPTS ) {
            $sentSmsCode->blocked_till = now()->add(\C\SMS_BLOCK_TTL);
        }
        $sentSmsCode->save();
        $sent = $this->sendToPhone($phone, $_code, $sentSmsCode->sent_count);
//        $this->_send($phone, $_code);
        if ($sent) {
            return true;
        }
        \C\forget_register_temp_data();
        return false;
    }

    public function generateConfirmForApi(string $phone): array
    {
        $type = self::TYPE_CONFIRM;
        $_code = $this->_code();
        $sentSmsCode = $this->_getSent($phone, $type);
        if (!$sentSmsCode) {
            $smsCode = SmsCode::create([
                'type' => $type,
                'phone' => $phone,
                'value' => $_code,
                'sent_count' => 1,
            ]);
            $sent = $this->sendToPhone($phone, $_code, $smsCode->sent_count);
            if ($sent) {
                return [
                    'success' => true,
                ];
            }

            $smsCode->delete();
            return [
                'success' => false,
                'error' => t('ui_unable_to_send_code')
            ];
        }

        if ($sentSmsCode->blocked_till) {
            if (now()->lt($sentSmsCode->blocked_till)) {
                return [
                    'success' => false,
                    'error' => t('phone_number_is_blocked'),
                ];
            }

            $sentSmsCode->blocked_till = null;
            $sentSmsCode->sent_count = 0;
        }

        $sentSmsCode->value = $_code;
        $sentSmsCode->increment('sent_count');
        if ($sentSmsCode->sent_count >= \C\SMS_ATTEMPTS ) {
            $sentSmsCode->blocked_till = now()->add(\C\SMS_BLOCK_TTL);
        }
        $sentSmsCode->try_count = 0;
        $sentSmsCode->save();
        $sent = $this->sendToPhone($phone, $_code, $sentSmsCode->sent_count);
        if ($sent) {
            return [
                'success' => true
            ];
        }
        return [
            'success' => false,
            'error' => t('resend_error'),
        ];
    }

    private function sendToPhone($phone, $code, $sendCount = 1, ?string $projectId = null)
    {

        logger()->error('NewSmsSend', [
            'ip' => getUserIp(),
            'phone' => $phone,
            'userAgent'=> request()->server('HTTP_USER_AGENT')
        ]);
        if (strpos($phone, '845') === 0) {
            return false;
        }

        return $this->send(t('mail_email_phone_verification_during_registration_body', ['code' => $code, 'appName' => config('app.name')]),
            $phone, $projectId);
    }


    public function generateConfirmForMerchantPayment(string $phone, bool $abortIfBlocked = true): bool
    {
        $type = self::TYPE_CONFIRM;
        $_code = $this->_code();
        $sentSmsCode = $this->_getSent($phone, $type);
        if (!$sentSmsCode) {
            $smsCode = SmsCode::create([
                'type' => $type,
                'phone' => $phone,
                'value' => $_code,
                'sent_count' => 1,
            ]);
            $sent = $this->sendToPhone($phone, $_code, $smsCode->sent_count);
            if ($sent) {
                return true;
            }
            $smsCode->delete();
            return false;
        }

        if ($sentSmsCode->blocked_till) {
            if (now()->lt($sentSmsCode->blocked_till)) {
                \C\forget_payment_data();
                return false;
            }
            $sentSmsCode->blocked_till = null;
            $sentSmsCode->sent_count = 0;
        }

        $sentSmsCode->value = $_code;
        $sentSmsCode->increment('sent_count');
        if ($sentSmsCode->sent_count >= \C\MERCHANT_PAYMENT_SMS_ATTEMPTS ) {
            $sentSmsCode->blocked_till = now()->add(\C\MERCHANT_PAYMENT_SMS_BLOCK_TTL);
            $sentSmsCode->try_count = 0;
            $sentSmsCode->save();
            \C\forget_payment_data();
            return false;
        }

        $sentSmsCode->try_count = 0;
        $sentSmsCode->save();
        $sent = $this->sendToPhone($phone, $_code, $sentSmsCode->sent_count);
        if ($sent) {
            return true;
        }
        return false;
    }


    public function getSmsProviderKeysForProject(Project $project)
    {
        return $project->smsProviders()->pluck('key', 'key')->toArray();
    }

    public function send($message, $phone, $projectId = null)
    {
        $project = $projectId ? Project::findOrFail($projectId) : Project::getCurrentProject();
        $availableSmsProvidersArray = array_intersect_key(config('services.enabled_sms_providers'), $this->getSmsProviderKeysForProject($project));
        foreach ($availableSmsProvidersArray as $name => $availableSmsProviderService) {
            try {
                $key = 'services.'. $name . '.enabled';
                if (config($key)) {
                    (new $availableSmsProviderService())->send($message, '+' . $phone);
                    return true;
                }

            } catch (\Throwable $exception) {
                logger()->error($name . 'Error', [$phone, $exception->getMessage()]);
                if ($name === array_key_last($availableSmsProvidersArray)) {
                    return false;
                }
            }

        }
    }
}
