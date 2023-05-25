<?php


namespace App\DataObjects\Payments\Wallester;

use App\DataObjects\BaseDataObject;

class WallesterSecure extends BaseDataObject
{
    public ?string $type;
    public ?string $mobile;
    public string $password;

}
