<?php


namespace App\Services;


use App\DataObjects\WireTransferSelectionData;
use App\Enums\AccountType;
use App\Enums\WireType;
use App\Exceptions\InvalidArgumentException;
use App\Models\Account;
use App\Models\Country;
use Illuminate\Support\Str;

class WireTransferSelectionService
{
    public function getWireFiatSelectionDTO(Account $fiatAccount, bool $isTopUp): WireTransferSelectionData
    {
        if ($fiatAccount->account_type !== AccountType::TYPE_FIAT) {
            throw new InvalidArgumentException();
        }
        return new WireTransferSelectionData([
            'currency' => $fiatAccount->currency,
            'countries' => $this->getCountries(),
            'providerWireType' => $this->getAvailableWireProviderType($fiatAccount->cProfile->isIndividual(), $isTopUp),
            'currentOperationId' => $this->generateUid(),
        ]);
    }

    public function getCountries(): array
    {
        return Country::getCountries(false);
    }

    public function getAvailableWireProviderType(bool $isIndividual, bool $isTopUp): int
    {
        if (!$isIndividual) {
            return AccountType::WIRE_PROVIDER_B2B;
        }

        return $isTopUp ? AccountType::WIRE_PROVIDER_C2B : AccountType::WIRE_PROVIDER_B2C;
    }

    public function generateUid(): string
    {
        return Str::uuid()->toString();
    }
}
