<?php

namespace App\DataObjects;

/**
 * Class CryptoTransferData
 * @package App\DataObjects
 * @property ?string $from_address
 * @property ?string $to_address
 * @property bool $is_approved
 * @property bool $is_received
 * @property ?string $tx_id
 * @property ?string $value
 */
class CryptoTransferData extends BaseDataObject
{
    public ?string $from_address;
    public ?string $to_address;
    public bool $is_approved;
    public bool $is_received;
    public bool $is_init = false;
    public ?string $tx_id;
    public ?string $value;

}
