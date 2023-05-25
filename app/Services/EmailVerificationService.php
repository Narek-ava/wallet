<?php

namespace App\Services;

use App\Facades\EmailFacade;
use App\Models\Cabinet\CUser;
use App\Models\EmailVerification;
use App\Models\PaymentForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailVerificationService
{
    // @todo private после выпиливания \App\Services\CUserService::createEmailVerification
    public CONST TYPE_CONFIRM = 1;
    public CONST TYPE_CHANGE = 2;

    public CONST EMAIL_CACHE_KEY = 'email_send_';
    public CONST EMAIL_VERIFICATION_PAYMENT_FORM_CACHE_KEY = 'email_verification_payment_form_send_';

    public CONST EMAIL_CODE_VERIFY_ATTEMPTS = 3;
    public CONST EMAIL_VERIFICATION_API_CACHE_KEY = 'email_verification_api_send_';

    // @todo protected when
    public function _completeVerified(EmailVerification $emailVerification)
    {
        try {
            DB::transaction(function () use ($emailVerification) {
                $cUser = $emailVerification->cUser;
                $cUser->email_verified_at = now();
                $cUser->save();
                $emailVerification->delete();
            });
        } catch (\Exception $e) {
            /** @note вообще, тут эксепшн может появиться только при illegal usage
             * и, видимо, @todo logging_trace(..)
             */
            abort(422);
        }
    }

    protected function createEmailVerification(CUser $cUser, int $type, string $newEmail = null): ?EmailVerification
    {
        $emailVerification = EmailVerification::where('c_user_id', $cUser->id)
            ->where('type', $type)
            ->first();

        if (!$emailVerification) {
            $emailVerification = new EmailVerification;
            $emailVerification->fill([
                'id' => Str::uuid(),
                'type' => $type,
                'c_user_id' => $cUser->id,
            ]);
        }

        $emailVerification->fill([
            'new_email' => $newEmail,
            'token' => Str::random(16),
        ])->save();
        return $emailVerification;
    }

    protected function createPaymentFormEmailVerification(string $email, int $type): ?EmailVerification
    {
        $emailVerification = EmailVerification::where('new_email', $email)
            ->where('type', $type)
            ->first();

        if (!$emailVerification) {
            $emailVerification = new EmailVerification;
            $emailVerification->fill([
                'id' => Str::uuid(),
                'type' => $type,
                'new_email' => $email,
            ]);
        }

        $emailVerification->token = rand(100000,999999);
        $emailVerification->save();

        return $emailVerification;
    }

    public function generateToChange(CUser $cUser, string $newEmail = null)
    {
        $emailVerification = $this->createEmailVerification($cUser, self::TYPE_CHANGE, $newEmail);
        EmailFacade::sendEmailUpdate($cUser, $newEmail, $emailVerification);
    }

    public function generatePaymentFormEmailVerifyCode($email)
    {
        $emailVerificationData = $this->getEmailVerificationDataFromCache($email);
        if (!empty($emailVerificationData)) {
            if (Carbon::now() >= Carbon::parse($emailVerificationData['nextCodeSendingData'])) {
                $attempts = ++$emailVerificationData['attempts'];
            } else {
                return false;
            }
        } else {
            $attempts = 1;
        }

        $seconds = $this->getSecondsForNextCodeRequest($attempts);
        $this->setEmailVerificationDataToCache($email, $seconds, $attempts);
        $emailVerification = $this->createPaymentFormEmailVerification($email, self::TYPE_CONFIRM);
        EmailFacade::sendPaymentFormEmailConfirm($emailVerification);

        return [
            'emailVerification' => $emailVerification,
            'seconds' => $seconds
        ];

    }

    public function getSecondsForNextCodeRequest($attempts): int
    {
        switch ($attempts) {
            case 1:
                return PaymentFormsService::FIRST_CODE_SENDING_DELAY;
            case 2:
                return PaymentFormsService::SECOND_CODE_SENDING_DELAY;
            case 3:
                return PaymentFormsService::THIRD_CODE_SENDING_DELAY;
            case 4:
                return PaymentFormsService::FOURTH_CODE_SENDING_DELAY;
            case 5:
                return PaymentFormsService::FIFTH_CODE_SENDING_DELAY;
            default:
                return 0;
        }
    }

    /**
     * @param string $token
     * @param string $email
     * @return array|bool[]
     */
    public function paymentFormEmailVerifyCode(string $token, string $email)
    {
        $emailVerificationData = $this->getEmailVerificationDataFromCache($email);

        $attempts = 1;

        if (!empty($emailVerificationData)) {
            $attempts = $emailVerificationData['attempts'];
            if ($attempts < PaymentFormsService::EMAIL_CODE_VERIFY_ATTEMPTS || Carbon::now() > Carbon::parse($emailVerificationData['nextCodeSendingData'])) {
                $attempts++;
                if ($attempts > PaymentFormsService::EMAIL_CODE_VERIFY_ATTEMPTS) {
                    $attempts = 1;
                }
            } else {
                return [
                    'error' => t("error_cratos_email_code_block"),
                    'attempts' => true
                ];
            }
        }

        $seconds = null;
        if ($attempts >= PaymentFormsService::EMAIL_CODE_VERIFY_ATTEMPTS) {
            $seconds = PaymentFormsService::EMAIL_CODE_VERIFY_ATTEMPTS_STOP_SECOND;
        }

        $this->setEmailVerificationDataToCache($email, $seconds, $attempts);

        $emailVerification = EmailVerification::where([
            'token' => $token,
            'new_email' => $email,
        ])->first();

        if (!empty($emailVerification)) {
            return [ 'success' => true];
        }

        return [
            'error' => t("error_cratos_email_code"),
            'attempts' => false
        ];
    }

    public function generateToConfirm(CUser $cUser)
    {
        EmailFacade::sendCreatedNewAccount($cUser);
        $this->emailVerify($cUser);
    }

    public function emailVerify($cUser)
    {
        $emailVerification = $this->createEmailVerification($cUser, self::TYPE_CONFIRM);
        EmailFacade::sendEmailRegistrationConfirm($cUser, $emailVerification);
    }

    public function verify(string $token): bool
    {
        $emailVerification = EmailVerification::where('token', $token)->firstOrFail();
        switch ($emailVerification->type) {
            case self::TYPE_CONFIRM:
                if ($emailVerification->token === $token) {
                    $this->_completeVerified($emailVerification);
                    return true;
                }
        }
        return false;
    }

    public function setEmailVerificationSentStatusToCache($cProfileId)
    {
        $cacheKey = self::EMAIL_CACHE_KEY.$cProfileId;
        Cache::add($cacheKey, true);

    }

    public function isEmailVerificationSent($cProfileId)
    {
        $cacheKey = self::EMAIL_CACHE_KEY.$cProfileId;
        return Cache::has($cacheKey);
    }

    public function setEmailVerificationDataToCache($email, $seconds, $attempts)
    {
        $data = [
            'email' => $email,
            'nextCodeSendingData' => Carbon::now()->addSeconds($seconds)->toDateTimeString(),
            'attempts' => $attempts
        ];

        Cache::put(self::EMAIL_VERIFICATION_PAYMENT_FORM_CACHE_KEY . $email, $data, now()->addHour());
    }

    public function getEmailVerificationDataFromCache($email)
    {
        return Cache::get(self::EMAIL_VERIFICATION_PAYMENT_FORM_CACHE_KEY . $email) ?? [];
    }

    public function setEmailVerificationDataToCacheForApi(string $email, array $data)
    {
        Cache::put(self::EMAIL_VERIFICATION_API_CACHE_KEY . $email, $data);
    }

    public function getEmailVerificationDataFromCacheForApi(string $email)
    {
        return Cache::get(self::EMAIL_VERIFICATION_API_CACHE_KEY . $email) ?? [];
    }

    public function deleteEmailVerificationDataFromCacheForApi(string $email)
    {
        return Cache::forget(self::EMAIL_VERIFICATION_API_CACHE_KEY . $email);
    }

    /**
     * @param string $email
     * @return EmailVerification
     */
    public function generateEmailConfirmCode(string $email): EmailVerification
    {
        $emailVerification = EmailVerification::query()->where('new_email', $email)->first();

        if (!$emailVerification) {
            $emailVerification = new EmailVerification;
            $emailVerification->fill([
                'type' => self::TYPE_CONFIRM,
                'new_email' => $email,
            ]);
        }

        $emailVerification->token = rand(100000,999999);
        $emailVerification->save();

        EmailFacade::sendPaymentFormEmailConfirm($emailVerification);

        return $emailVerification;
    }

    public function verifyConfirmEmailCode(
        string $email,
        string $verifyingCode,
        ?bool &$allowResend
    )
    {
        $emailVerification = EmailVerification::where('new_email', $email)->first();
        if (!$emailVerification) {
            $allowResend = true;
            return false;
        }

        $emailVerificationData = $this->getEmailVerificationDataFromCache($email);
        $allowResend = true;

        if (!empty($emailVerificationData)) {
            $attempts = $emailVerificationData['attempts'];
            if ($attempts < self::EMAIL_CODE_VERIFY_ATTEMPTS) {
                $attempts++;
            } else {
                $allowResend = false;
                return false;
            }
        } else {
            $attempts = 1;
        }
        $this->setEmailVerificationDataToCache($email, 0, $attempts);

        if ($verifyingCode === $emailVerification->token) {
            $emailVerification->delete();
            return true;
        }

        return false;
    }

    public function generateEmailConfirmCodeForApi(string $email): array
    {
        $emailVerificationData = $this->getEmailVerificationDataFromCacheForApi($email);
        if (empty($emailVerificationData)) {
            $emailVerificationData = [
                'attempts' => 1,
                'try_count' => 0,
                'blocked_till' => null
            ];
        } else {
            if ($emailVerificationData['blocked_till']) {
                if (now()->toDateTimeString() < $emailVerificationData['blocked_till']) {
                    return [
                        'success' => false,
                        'error' => ['error_is_blocked' => t('email_address_is_blocked')]
                    ];
                }
                $emailVerificationData['attempts'] = 0;
                $emailVerificationData['blocked_till'] = null;
            }

            if ($emailVerificationData['attempts'] > self::EMAIL_CODE_VERIFY_ATTEMPTS) {
                $emailVerificationData['blocked_till'] = Carbon::now()->addDay()->toDateTimeString();
                return [
                    'success' => false,
                    'error' => ['error_is_blocked' => t('email_address_is_blocked')]
                ];
            }
            ++$emailVerificationData['attempts'];
        }

        $this->generateEmailConfirmCode($email);
        $this->setEmailVerificationDataToCacheForApi($email, $emailVerificationData);
        return [
            'success' => true,
        ];
    }

    public function verifyConfirmApi(string $email, $code)
    {
        $emailVerificationData = $this->getEmailVerificationDataFromCacheForApi($email);
        $emailVerification = EmailVerification::query()->where('new_email', $email)->first();

        if (empty($emailVerificationData) || !$emailVerification) {
            return [
                'success' => false,
                'error' => t('invalid_verification_email'),
            ];
        }

        $try_count = $emailVerificationData['try_count'] ?? 1;

        if ($try_count > self::EMAIL_CODE_VERIFY_ATTEMPTS) {
            return [
                'success' => false,
                'error' => t('error_email_wrong_code_require_new'),
            ];
        }

        if ($code === $emailVerification->token) {
            $emailVerification->delete();
            $this->deleteEmailVerificationDataFromCacheForApi($email);
            return [
                'success' => true,
            ];
        }

        $emailVerificationData['try_count']++;

        $this->setEmailVerificationDataToCacheForApi($email, $emailVerificationData);

        return [
            'success' => false,
            'error' => t('error_sms_wrong_code'),
        ];
    }
}
