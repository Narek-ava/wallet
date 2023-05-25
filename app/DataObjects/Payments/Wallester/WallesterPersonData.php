<?php


namespace App\DataObjects\Payments\Wallester;


use App\DataObjects\BaseDataObject;

class WallesterPersonData extends BaseDataObject
{
    public string $first_name;
    public string $last_name;
    public ?string $birth_date;
    public ?string $email;
    public ?string $phone;
    public ?string $external_id;
    public ?string $personal_number_issuer;
    public ?array $limits;

}
