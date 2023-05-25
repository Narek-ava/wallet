<?php

namespace App\Services;

use App\Facades\EmailFacade;
use App\Models\Cabinet\CUserTemporaryRegisterData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CUserTemporaryRegisterDataService
{
    public const FIRST_NOTIFICATION_INTERVAL_MINUTES = 10;
    public const NOTIFICATION_INTERVAL_MINUTES = 24*60;
    public const NOTIFICATIONS_COUNT = 2;

    /**
     * @param string $email
     * @param int $accountType
     * @param string $phone
     * @param string $passwordEncrypted
     * @return Builder|Model
     */
    public function updateOrCreate(string $email, int $accountType, string $phone, string $passwordEncrypted)
    {
        return CUserTemporaryRegisterData::query()->updateOrCreate(
            ['email' => $email],
            [
                'account_type' => $accountType,
                'phone' => $phone,
                'password_encrypted' => $passwordEncrypted,
            ]);
    }

    /**
     * @return Builder[]|Collection
     */
    public function getToBeNotified()
    {
        return CUserTemporaryRegisterData::query()
            ->where('notifications_count', '<', self::NOTIFICATIONS_COUNT)
            ->where(function (Builder $query) {
                $query->whereNotNull('last_notified_at')
                    ->where('last_notified_at', '<', Carbon::now()->addMinutes(-self::NOTIFICATION_INTERVAL_MINUTES));
                $query->orWhere(function (Builder $query) {
                    $query->whereNull('last_notified_at')
                        ->where('created_at', '<', Carbon::now()->addMinutes(-self::FIRST_NOTIFICATION_INTERVAL_MINUTES));
                });
            })
            ->get();
    }

    public function removeCompletedRegistration()
    {
        return CUserTemporaryRegisterData::query()
            ->join('c_users','c_user_temporary_register_data.email', '=','c_users.email')
            ->delete();
    }

    public function notify(CUserTemporaryRegisterData $cUserTemporaryRegisterData)
    {
        EmailFacade::sendVerifyPhoneReminder($cUserTemporaryRegisterData);
        $cUserTemporaryRegisterData->update([
            'last_notified_at' => Carbon::now(),
            'notifications_count' => ($cUserTemporaryRegisterData->notifications_count + 1),
        ]);
    }

    public function find(int $id)
    {
        return CUserTemporaryRegisterData::query()->where('id', $id)->first();
    }
}
