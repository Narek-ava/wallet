<?php

namespace App\DataObjects\Payments\TrustPayment;

use App\DataObjects\BaseDataObject;

class FormData extends BaseDataObject
{
    public ?string $currencyiso3a;
    public ?string $mainamount;

}
