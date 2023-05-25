<?php


namespace App\Services;


use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\Providers;
use App\Models\Cabinet\CProfile;
use App\Models\PaymentProvider;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProviderService
{
    public function providerStore($data)
    {
        $providerType = $this->getProviderType($data['providerType']);
        $data = $data + ['id' => Str::uuid()->toString(), 'provider_type' => $providerType, 'b_user_id' => Auth::user()->id];
        $paymentProvider = PaymentProvider::create($data);
        $paymentProvider->refresh();
        return $paymentProvider;
    }

    public function projectHasDefaultLiqProvider(string $projectId)
    {
        return PaymentProvider::query()->where([
            'type' => Providers::PROVIDER_LIQUIDITY,
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId)->exists();

    }

    public function getProviderType($type)
    {
        $providerType = null;
        switch ($type) {
            case 'payment-providers':
                $providerType = Providers::PROVIDER_PAYMENT;
                break;
            case 'liquidity-providers':
                $providerType = Providers::PROVIDER_LIQUIDITY;
                break;
            case 'wallet-providers':
                $providerType = Providers::PROVIDER_WALLET;
                break;
            case 'credit-card-providers':
                $providerType = Providers::PROVIDER_CARD;
                break;
            case 'card-issuing-providers':
                $providerType = Providers::PROVIDER_CARD_ISSUING;
                break;
            case 'compliance-providers':
                $providerType = Providers::PROVIDER_COMPLIANCE;
                break;
        }
        return $providerType;
    }

    public function getProvidersActive($providerType = Providers::PROVIDER_PAYMENT, ?string $projectId = null, ?bool $isDefault = null)
    {
        return PaymentProvider::where([
            'provider_type' => $providerType,
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId, $isDefault)->orderBy('id', 'desc')->get();
    }

    public function getDefaultProviderByType($providerType, ?string $projectId = null)
    {
        return PaymentProvider::where([
            'provider_type' => $providerType,
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId, true)->first();
    }

    public function getDefaultLiquidityProvider(string $projectId)
    {
        return $this->getProjectLiquidityProvidersByDefaultTypeQuery($projectId, true)->first();
    }

    public function getNotDefaultLiquidityProviders(string $projectId)
    {
        return $this->getProjectLiquidityProvidersByDefaultTypeQuery($projectId)->get();
    }

    public function getProjectLiquidityProvidersByDefaultTypeQuery(string $projectId, bool $isDefault = false)
    {
        return PaymentProvider::where([
            'provider_type' => Providers::PROVIDER_LIQUIDITY,
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->whereHas('projects', function ($q) use ($projectId, $isDefault) {
            return $q->where('projects.id', $projectId)->where('is_default', $isDefault);
        });
    }

    public function getProviders($page)
    {
        return PaymentProvider::where(['provider_type' => $this->getProviderType($page)])->get();
    }

    public function getProviderWithProjects($id)
    {
        return PaymentProvider::query()->where('id', $id)->with('projects')->first();
    }

    public function updateProvider($data)
    {
        $provider = PaymentProvider::findOrFail($data['provider_id']);
        $provider->update($data);
        (new AccountService())->updateStatus($provider->status, $provider->accounts);
        return $provider;
    }

    public function getFilteredPaymentProviders(
        int $accountType,
        string $countryCode,
        string $currency,
        int $operationType,
        int $profileAccountType,
        int $fiatType = AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT
    ): array
    {
        $project = Project::getCurrentProject();
        $providers = [];
        $collection = PaymentProvider::query()
            ->where([
                'provider_type' => Providers::PROVIDER_PAYMENT,
                'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE,
            ])
            ->queryByProject($project->id ?? null)
            ->whereHas('accounts', function ($q) use ($accountType, $countryCode, $currency, $operationType, $profileAccountType, $fiatType) {
                return $q->filteredForPayment($accountType, $countryCode, $currency, $operationType, $profileAccountType, $fiatType);
            })
            ->with(['accounts' => function ($q) use ($accountType, $countryCode, $currency, $operationType, $profileAccountType, $fiatType) {
                return $q->filteredForPayment($accountType, $countryCode, $currency, $operationType, $profileAccountType, $fiatType)
                ->with('wire')
                ->with('countries');
            }])
            ->get();
        foreach ($collection as $provider) {
            if ($provider->accounts->isNotEmpty()) {
                $providers[] = $provider;
            }
        }
        return $providers;
    }

    public function getProvidersWithoutCurrencyQuery(int $providerType = null, int $status = null)
    {
        $query = PaymentProvider::query();
        if ($providerType) {
            $query->where('provider_type', $providerType);
        }
        if ($status) {
            $query->where('status', $status);
        }
        $query->whereHas('accounts', function ($q) {
            return $q->where( 'owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM);
        });

        return $query;
    }


    public function getProviderForProject(int $type, ?string $projectId = null)
    {
        return $this->getProvidersWithoutCurrencyQuery($type, \App\Enums\PaymentProvider::STATUS_ACTIVE)->queryByProject($projectId)->first();

    }

    public function getWalletProviderForProject(?string $projectId = null)
    {
        return $this->getProviderForProject(Providers::PROVIDER_WALLET, $projectId);
    }

    public function checkProjectProviderExistsByType(string $projectId, int $providerType): bool
    {
        return PaymentProvider::where([
            'provider_type' => $providerType,
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE
        ])->queryByProject($projectId)->exists();
    }

}
