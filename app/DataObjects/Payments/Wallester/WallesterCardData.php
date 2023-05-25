<?php


namespace App\DataObjects\Payments\Wallester;


use App\DataObjects\BaseDataObject;

class WallesterCardData extends BaseDataObject
{
    public string $account_id; //account id in wallester
    public string $person_id;  //person id in wallester
    public ?string $external_id;
    public ?string $type;
    public ?string $name;
    public ?WallesterSecurity $security;
    public ?WallesterSecure $secure_3d_settings;
    public ?WallesterDeliveryAddress $delivery_address;
    public ?WallesterLimits $limits;

}
