<?php

namespace App\Http\Resources\Backoffice;

use App\Enums\Currency;
use App\Enums\PaymentFormTypes;
use App\Enums\Providers;
use App\Models\Cabinet\CProfile;
use App\Models\PaymentForm;
use App\Services\CProfileService;
use App\Services\ProviderService;
use App\Services\RateTemplatesService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PaymentForm $resource
 */
class PaymentFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $currencyAddresses = [];
        foreach (Currency::getList() as $currency) {
            $currencyAddresses[] = [
                'name' => 'address_' . $currency,
                'value' => $this->resource->getCryptoAddressByCurrency($currency)
            ];
        }


        $admin = auth()->guard('bUser')->user();

        if ($admin) {
            $returnData = [
                'alreadyHasProject' => !is_null($this->resource->project_id),
                'projectName' => $this->resource->project->name ?? '',
                'projectId' => $this->resource->project->id ?? '',
            ];
        }

        $returnData['isSuperAdmin'] = $admin->is_super_admin;

        $providerService = resolve(ProviderService::class);
        $cProfileService = resolve(CProfileService::class);
        $rateTemplatesService = resolve(RateTemplatesService::class);

        return array_merge([
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'status' => $this->resource->status,
            'currencyAddresses' => $currencyAddresses,
            'paymentFormMerchant' => $this->resource->cProfile->id ?? null,
            'paymentFormCardProvider' => $this->resource->cardProvider->id ?? null,
            'paymentFormWalletProvider' => $this->resource->walletProvider->id ?? null,
            'paymentFormLiquidityProvider' => $this->resource->liquidityProvider->id ?? null,
            'paymentFormRate' => $this->resource->rate_template_id ?? null,
            'paymentFormType' => $this->resource->type,
            'paymentTypeName' => PaymentFormTypes::getName($this->resource->type),
            'paymentFormKYC' => $this->resource->kyc,
            'hasOperations' => $this->resource->hasOperations(),
            'paymentFormWebSiteUrl' => $this->resource->website_url ?? null,
            'paymentFormDescription' => $this->resource->description ?? null,
            'paymentFormMerchantLogo' => $this->resource->merchant_logo ?? null,
            'paymentFormIncomingFee' => $this->resource->incoming_fee ?? null,
            'cardProviders' => $providerService->getProvidersActive(Providers::PROVIDER_CARD, $this->resource->project_id)->pluck('name', 'id'),
            'walletProviders' => $providerService->getProvidersActive(Providers::PROVIDER_WALLET, $this->resource->project_id)->pluck('name', 'id'),
            'liquidityProviders' => $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY, $this->resource->project_id)->pluck('name', 'id'),
            'merchants' => $cProfileService->getActiveMerchants($this->resource->project_id)->pluck('company_name', 'id'),
            'rates' => $rateTemplatesService->getActiveRatesByAccountType(CProfile::TYPE_INDIVIDUAL),

        ], $returnData);


    }
}
