<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\DataObjects\Payments\TrustPayment\FormData;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property FormData $resource
 */
class TrustPaymentsForCardOperationResource extends JsonResource
{

    protected string $siteReference;
    protected string $operationId;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'url' => "https://payments.securetrading.net/process/payments/choice?" . $this->generateUrlQuery(),
        ];
    }

    /**
     * @param string $operationId
     */
    public function setOperationId(string $operationId): TrustPaymentsForCardOperationResource
    {
        $this->operationId = $operationId;

        return $this;
    }

    /**
     * @param string $siteReference
     */
    public function setSiteReference(string $siteReference): TrustPaymentsForCardOperationResource
    {
        $this->siteReference = $siteReference;

        return $this;
    }

    /**
     * @return string
     */
    protected function generateUrlQuery()
    {
        $queryParams = [
            'sitereference' => $this->siteReference,
            'stprofile' => "default",
            'stdefaultprofile' => "st_paymentcardonly",
            'strequiredfields' => "nameoncard",
            'currencyiso3a' => $this->resource->currencyiso3a,
            'mainamount' => $this->resource->mainamount,
            'orderreference' => $this->operationId,
            'version' => 2,
            'ruleidentifier' => "STR-8",
            'successfulurlnotification' => route('webhook.trust.payments.transfer'),
        ];

      return  http_build_query($queryParams, '', '&');
    }
}
