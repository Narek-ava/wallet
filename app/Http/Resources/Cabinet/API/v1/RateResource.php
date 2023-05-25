<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\Currency;
use App\Models\RateTemplate;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property RateTemplate $resource
 */
class RateResource extends JsonResource
{
    protected $withAccountMinAmount = false;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataArray = [];
        foreach (Currency::FIAT_CURRENCY_NAMES as $currency) {
            $dataArray['commissions']['Fiat'][] = [
                'Currency' => $currency,
                'incomingFeeSepa' => $this->resource->sepaIncoming($currency) ?? 0,
                'outgoingFeeSepa' => $this->resource->sepaOutgoing($currency) ?? 0,
                'incomingFeeSwift' => $this->resource->swiftIncoming($currency) ?? 0,
                'outgoingFeeSwift' => $this->resource->swiftOutgoing($currency) ?? 0,
                'bankCard' => $this->resource->bankCard($currency) ?? 0,
            ];

            $dataArray['accountMinAmounts']['Fiat'][] = [
                'Currency' => $currency,
                'withdrawSepa' => $this->resource->withdrawSepa($currency) ?? 0,
                'topUpSepa' => $this->resource->topUpSepa($currency) ?? 0,
                'withdrawSwift' => $this->resource->withdrawSwift($currency) ?? 0,
                'topUpSwift' => $this->resource->topUpSwift($currency) ?? 0,
                'topUpBankCard' => $this->resource->topUpBankCard($currency) ?? 0,
            ];
        }
        foreach (Currency::getList() as $cryptoCurrency) {
            $dataArray['commissions']['Crypto'][] = [
                'Currency' =>  $cryptoCurrency,
                'incomingFeeCrypto' => $this->resource->cryptoIncoming($cryptoCurrency) ? $this->resource->cryptoIncoming($cryptoCurrency) : 0,
                'outgoingFeeCrypto' => $this->resource->cryptoOutgoing($cryptoCurrency) ? $this->resource->cryptoOutgoing($cryptoCurrency) : 0,
                'incomingFeeBlockchain' => formatMoney($this->resource->blockchainIncoming($cryptoCurrency), $cryptoCurrency) ?? 0,
                'outgoingFeeBlockchain' => formatMoney($this->resource->blockchainOutgoing($cryptoCurrency), $cryptoCurrency) ?? 0,
            ];
            $dataArray['accountMinAmounts']['Crypto'][] = [
                'Currency' =>  $cryptoCurrency,
                'withdrawCrypto' => $this->resource->withdrawCrypto($cryptoCurrency) ? $this->resource->withdrawCrypto($cryptoCurrency) : 0,
            ];
        }

        if($this->withAccountMinAmount) {
            return $dataArray['accountMinAmounts'];
        }

        return $dataArray['commissions'];
    }

    /**
     * @param bool $withAccountMinAmount
     */
    public function setAccountMinAmount(bool $withAccountMinAmount): RateResource
    {
        $this->withAccountMinAmount = $withAccountMinAmount;

        return $this;
    }
}
