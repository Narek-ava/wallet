<?php

namespace App\Http\Requests\API\v1;

use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Http\Requests\BaseRequest;
use App\Rules\ValidateAccountBalance;
use Illuminate\Validation\Rule;

class FiatWithdrawWireRequest extends WithdrawWireRequest
{

}
