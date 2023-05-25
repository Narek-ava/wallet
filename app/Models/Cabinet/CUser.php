<?php

namespace App\Models\Cabinet;

use App\Enums\{Country, TicketStatuses, TwoFAType};
use App\Models\{Ip, NotificationUser, PaymentForm, Project, Ticket, Log, TicketMessage};
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use function C\twoFACode;

/**
 * Class CUser
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $email
 * @property $password
 * @property $phone
 * @property $c_profile_id
 * @property $project_id
 * @property $c_user_no
 * @property $two_fa_type
 * @property $google2fa_secret
 * @property $email_verified_at
 * @property CProfile $cProfile
 * @property Log[] $logs
 * @property NotificationUser[] $comments
 * @property NotificationUser[] $notifications
 * @property Ticket[] $tickets
 * @property Ticket[] $openTickets
 * @property Ticket[] $closedTickets
 * @property TicketMessage[] $messages
 * @property PaymentForm $paymentForm
 * @property Project $project
 */
class CUser extends Authenticatable
{
    use CanResetPassword, Notifiable, HasApiTokens;

    const REGISTER_DATA_CACHE_KEY = 'register_temp_data_';

    public $timestamps = false;
    protected $guarded = []; //! temporary
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google2fa_secret',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'c_profile_id', 'id');
    }


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function logs()
    {
        return $this->hasMany(Log::class, 'c_user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(NotificationUser::class, 'userable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(NotificationUser::class, 'userable')->with('notification');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function openTickets()
    {
        return $this->hasMany(Ticket::class, 'to_client', 'id')->whereIn('status', [TicketStatuses::STATUS_OPEN,TicketStatuses::STATUS_NEW]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function closedTickets()
    {
        return $this->hasMany(Ticket::class, 'to_client', 'id')->where('status', TicketStatuses::STATUS_CLOSE);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'to_client', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function messages()
    {
        return $this->morphMany(TicketMessage::class, 'massageable');
    }

    /**
     * @return mixed
     */
    public function getLimitAttribute()
    {
        return $this->cProfile->rateTemplate->limits()->where('level', $this->cProfile->compliance_level)->first();
    }

    /**
     * @return Collection
     */
    public function getOperationsAttribute()
    {
        if (is_object($this->cProfile)) {
            return $this->cProfile->operations;
        }
        return new Collection();
    }

    /**
     * @return Collection
     */
    public function getAccountsAttribute()
    {
        if (is_object($this->cProfile)) {
            return $this->cProfile->accounts;
        }
        return new Collection();
    }

    /**
     * @return Collection
     */
    public function getTransactionsAttribute()
    {
        if ($this->accounts->isNotEmpty()) {
            $transactions = new Collection();
            foreach ($this->accounts as $account) {
                if ($account->fromTransaction->isNotEmpty()) {
                    $transactions = $transactions->merge($account->fromTransaction);
                }
                if ($account->toTransaction->isNotEmpty()) {
                    $transactions = $transactions->merge($account->toTransaction);
                }
            }
            return $transactions;
        }
        return new Collection();
    }

    /**
     * @return |null
     */
    public function getProfileIdAttribute()
    {
        if ($this->cProfile) {
            return $this->cProfile->profile_id;
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getFullNameAttribute()
    {
        if ($this->cProfile) {
            return $this->cProfile->first_name . ' ' . $this->cProfile->last_name;
        }
        return null;
    }

    /**
     * @return |null
     */
    public function getDateOfBirthAttribute()
    {
        if ($this->cProfile) {
            return $this->cProfile->date_of_birth;
        }
        return null;
    }

    /**
     * @return array|string|null
     */
    public function getCountryAttribute()
    {
        if ($this->cProfile) {
            return \App\Models\Country::getCountryNameByCode($this->cProfile->country);
        }
        return null;
    }

    public function ips()
    {
        return $this->hasMany(Ip::class, 'c_user_id', 'id');
    }

    public function getTwoFaConnectedStatusAttribute()
    {
        if ($this->two_fa_type) {
            return t(TwoFAType::TWO_FA_CONNECTED);
        }
        return t(TwoFAType::TWO_FA_NOT_CONNECTED);
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id', 'id');
    }
}
