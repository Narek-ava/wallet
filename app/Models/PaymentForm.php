<?php

namespace App\Models;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;

/**
 * Class PaymentForm
 * @property string $id
 * @property int $type
 * @property int $status
 * @property string $project_id
 * @property string $c_profile_id
 * @property string $card_provider_id
 * @property string $liquidity_provider_id
 * @property string $rate_template_id
 * @property string $wallet_provider_id
 * @property string $website_url
 * @property string $description
 * @property string $merchant_logo
 * @property string $incoming_fee
 * @property PaymentProvider $cardProvider
 * @property PaymentProvider $liquidityProvider
 * @property PaymentProvider $walletProvider
 * @property Account[] $accounts
 * @property CProfile $cProfile
 * @property array $allowed_crypto_currencies
 * @property RateTemplate $rate
 * @property int $kyc
 * @property string $name
 * @property Project $project
 * @property Operation[] $operations
 * @property PaymentFormAttempt[] $paymentFormAttempts
 *
 */
class PaymentForm extends BaseModel
{
    protected $fillable = ['id', 'name', 'kyc', 'rate_template_id','type','status', 'c_profile_id','card_provider_id', 'wallet_provider_id',
        'liquidity_provider_id', 'website_url', 'description', 'merchant_logo', 'incoming_fee', 'project_id'];

    const NO_KYC = 0;
    const KYC = 1;

    const KYC_VARIANTS = [
        self::NO_KYC => 'no',
        self::KYC => 'yes',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function cardProvider()
    {
        return $this->hasOne(PaymentProvider::class, 'id', 'card_provider_id');
    }

    public function liquidityProvider()
    {
        return $this->hasOne(PaymentProvider::class, 'id', 'liquidity_provider_id');
    }

    public function paymentFormAttempts()
    {
        return $this->hasMany(PaymentFormAttempt::class, 'payment_form_id', 'id');
    }

    public function walletProvider()
    {
        return $this->hasOne(PaymentProvider::class, 'id', 'wallet_provider_id');
    }

    public function cProfile()
    {
        return $this->hasOne(CProfile::class, 'id', 'c_profile_id');
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'payment_form_account', 'form_id', 'account_id');
    }

    public function activeAccounts()
    {
        return $this->accounts()->where('status', AccountStatuses::STATUS_ACTIVE);
    }

    public function getAllowedCryptoCurrenciesAttribute()
    {
        if ($this->type == PaymentFormTypes::TYPE_MERCHANT_OUTSIDE_FORM) {
            $accountCurrencies = $this->activeAccounts()
                ->pluck('accounts.currency')
                ->toArray();
        } else {
            if ($this->cProfile) {
                $accountCurrencies = $this->cProfile->accounts()
                    ->where([
                        'status' => AccountStatuses::STATUS_ACTIVE,
                        'is_external' => false,
                        'account_type' => AccountType::TYPE_CRYPTO,
                    ])
                    ->pluck('accounts.currency')
                    ->toArray();
            } else {
                return Currency::getList();
            }

        }

        // todo remove array intersect
        return array_intersect($accountCurrencies, Currency::getList());
    }

    public function getCryptoAddressByCurrency(string $currency)
    {
        $account = $this->activeAccounts()->where('accounts.currency', $currency)->latest()->first();

        return $account ? ($account->cryptoAccountDetail->address ?? '') : '';
    }

    public function rate()
    {
        return $this->belongsTo(RateTemplate::class, 'rate_template_id', 'id');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'payment_form_id', 'id');
    }


    public function hasOperations(): bool
    {
        return $this->operations()->exists();
    }

    public function cUser()
    {
        return $this->hasMany(CUser::class, 'payment_form_id', 'id');
    }
}
