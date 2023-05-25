<?php

namespace App\Models;

use App\Enums\{Commissions, CommissionType};
use App\Models\Cabinet\CProfile;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RateTemplate
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $name
 * @property $is_default
 * @property $status
 * @property $type_client
 * @property $opening
 * @property $maintenance
 * @property $account_closure
 * @property $referral_remuneration
 * @property $referral_partner_id
 * @property Limit[] $limits
 * @property Commission[] $commissions
 * @property RateTemplateCountry[] $countries
 * @property CProfile[] $cProfiles
 * @property Project $project
 */
class RateTemplate extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function limits()
    {
        return $this->hasMany(Limit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countries()
    {
        return $this->hasMany(RateTemplateCountry::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cProfiles()
    {
        return $this->hasMany(CProfile::class);
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function sepaIncoming($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_SEPA, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function sepaOutgoing($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_SEPA, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function swiftIncoming($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_SWIFT, 'currency' => $currency])->latest()->first();

        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function swiftOutgoing($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_SWIFT, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function bankCard($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_CARD, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function cryptoIncoming($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function cryptoOutgoing($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function cardIncoming($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_CARD, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function exchangeOutgoing($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_EXCHANGE, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function blockchainIncoming($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->blockchain_fee;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function blockchainOutgoing($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->blockchain_fee;
        } else {
            return false;
        }
    }

    public function paymentForms()
    {
        return $this->hasMany(PaymentForm::class, 'rate_template_id', 'id');
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function minAmount($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function withdrawSepa($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_SEPA, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function topUpSepa($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_SEPA, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }


    /**
     * @param $currency
     * @return bool|mixed
     */
    public function withdrawSwift($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_SWIFT, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function topUpSwift($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_SWIFT, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }


    /**
     * @param $currency
     * @return bool|mixed
     */
    public function topUpBankCard($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_CARD, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function withdrawCrypto($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_CRYPTO, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->min_amount;
        } else {
            return false;
        }

    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function fiatBuyCryptoFromFiatWallet($currency)
    {
        $commissions = $this->commissions()->where(['commission_type' => CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function fiatTopUpFiatByWire($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return bool|mixed
     */
    public function fiatWithdrawFiatByWire($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_OUTGOING, 'commission_type' => CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

    /**
     * @param $currency
     * @return false|\Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed
     */
    public function fiatByFiatFromCryptoWallet($currency)
    {
        $commissions = $this->commissions()->where(['type' => Commissions::TYPE_INCOMING, 'commission_type' => CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET, 'currency' => $currency])->latest()->first();
        if ($commissions) {
            return $commissions->percent_commission;
        } else {
            return false;
        }
    }

}
