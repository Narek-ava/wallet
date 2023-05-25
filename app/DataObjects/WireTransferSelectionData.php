<?php

namespace App\DataObjects;

/**
 * Class WireTransferSelectionData
 * @package App\DataObjects
 */
class WireTransferSelectionData extends BaseDataObject
{
    public string $currency;
    public array $countries;
    public int $providerWireType;
    public string $currentOperationId;

}
