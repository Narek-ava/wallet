<?php


namespace App\DataObjects\Payments\Wallester;


use App\DataObjects\BaseDataObject;

class WallesterDeliveryAddress extends BaseDataObject
{
    public ?string $first_name;
    public ?string $last_name;
    public ?string $company_name;
    public string $address1;
    public ?string $address2;
    public string $postal_code;
    public string $city;
    public string $country_code;

}
