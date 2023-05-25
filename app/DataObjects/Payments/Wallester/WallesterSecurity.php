<?php


namespace App\DataObjects\Payments\Wallester;

use App\DataObjects\BaseDataObject;

class WallesterSecurity extends BaseDataObject
{
    public ?bool $contactless_enabled;
    public ?bool $withdrawal_enabled;
    public bool $internet_purchase_enabled;
    public bool $overall_limits_enabled;
}
