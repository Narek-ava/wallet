<?php

namespace App\Http\Resources\Cabinet\API\v1;

use App\Enums\CProfileStatuses;
use App\Enums\Industry;
use App\Enums\Language;
use App\Enums\LegalForm;
use App\Enums\TwoFAType;
use App\Models\Cabinet\CProfile;
use App\Models\Country;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @property CProfile $resource
 */
class CProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        if (!$this->resource) {
            return [];
        }

        if( $this->resource->account_type == CProfile::TYPE_INDIVIDUAL) {
            return [
                'firstName' => $this->resource->first_name,
                'lastName' => $this->resource->last_name,
                'dateOfBirth' => $this->resource->date_of_birth,
                'email' => $this->resource->cUser->email,
                'phone' => $this->resource->cUser->phone,
                'country' => Country::getCountryNameByCode($this->resource->country),
                'city' =>  $this->resource->city,
                'citizenship' => $this->resource->citizenship,
                'zipCode' => $this->resource->zip_code,
                'address' => $this->resource->address,
                'twoFactorAuthentication' => TwoFAType::getName($this->resource->cUser->two_fa_type),
                'status' => CProfileStatuses::getName($this->resource->status),
                'createdAt' => $this->resource->created_at->toDateTimeString(),
            ];
        }

        return [
            'companyName' => $this->resource->company_name,
            'companyEmail' => $this->resource->company_email,
            'companyPhone' => $this->resource->company_phone,
            'registrationDate' => $this->resource->registration_date,
            'registrationNumber' => $this->resource->registration_number,
            'country' => Country::getCountryNameByCode($this->resource->country),
            'legalAddress' => $this->resource->legal_address,
            'tradingAddress' => $this->resource->trading_address,
            'ceosNames' => $this->resource->getCeosForProfile(),
            'beneficialOwners' => $this->resource->getBeneficialOwnersForProfile(),
            'shareholders' => $this->resource->getShareholdersForProfile(),
            'contactEmail' => $this->resource->contact_email,
            'interfaceLanguage' => $this->resource->interface_language,
            'email' => $this->resource->cUser->email,
            'phone' => $this->resource->cUser->phone,
            'twoFactorAuthentication' => TwoFAType::getName($this->resource->cUser->two_fa_type),
            'status' => CProfileStatuses::getName($this->resource->status),
            'createdAt' => $this->resource->created_at->toDateTimeString(),
        ];
    }
}
