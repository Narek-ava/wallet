<?php

namespace App\Models;

use App\{Enums\AccountStatuses,
    Enums\AccountType,
    Enums\OperationOperationType,
    Enums\Providers,
    Models\Backoffice\BUser};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PaymentProvider
 * @package App\Models
 * @property $id
 * @property $provider_type
 * @property $name
 * @property $status
 * @property $b_user_id
 * @property $api
 * @property $created_at
 * @property $updated_at
 * @property Account[] $accounts
 * @property Project[] $projects
 */
class PaymentProvider extends BaseModel
{
    protected $fillable = ['id', 'name', 'status', 'type', 'provider_type', 'currency', 'b_user_id', 'api', 'api_account', 'plastic_card_amount', 'virtual_card_amount'];

    protected $casts = [
        'id' => 'string'
    ];

    public function scopeQueryByProject($query, ?string $projectId = null, ?bool $isDefault = null)
    {
         if ($projectId) {
            $query->whereHas('projects', function ($q) use ($projectId, $isDefault) {
                $queryArr['projects.id'] = $projectId;
                if (isset($isDefault)) {
                    $queryArr['is_default'] = $isDefault;
                }
                return $q->where('projects.id', $projectId);
            });
        }
        return $query;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'payment_provider_id')
            ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM);
    }


    public function accountByCurrency(string $currency, int $type): ?Account
    {
        return $this->accounts()
            ->where('currency', $currency)
            ->where('account_type', $type)
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->first();
    }


    public function accountByCurrencyTypeCountry(string $currency, int $type, string $country): ?Account
    {
        return $this->accounts()
            ->where('currency', $currency)
            ->where('account_type', $type)
            ->where('country', $country)
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->first();
    }

    public function getProviderGroup()
    {
        if ($this->provider_type == Providers::PROVIDER_WALLET) {
            return 'wallet-providers';
        }
        if ($this->provider_type == Providers::PROVIDER_CARD) {
            return 'credit-card-providers';
        }
    }


    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_providers', 'provider_id', 'project_id')->withTimestamps()->withPivot(['is_default']);
    }

    public function getCardOrderAmountByCardType(int $type): ?float
    {
        $propertyName = WallesterAccountDetail::CARD_SETTING_KEYS[$type];
        return $this->$propertyName;
    }
}
