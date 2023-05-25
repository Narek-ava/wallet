<?php
namespace App\Services;

use App\Models\WireAccountDetail;
use Illuminate\Support\Str;

class WireAccountService
{
    public function createWireAccount($data)
    {
        $data['id'] = Str::uuid()->toString();
        WireAccountDetail::create($data);
    }
}
