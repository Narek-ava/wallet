<?php

namespace App\Http\Requests;

use App\Services\WalletService;
use Illuminate\Validation\Rule;

class FiatWalletRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $cProfile = getCProfile();
        /* @var WalletService $walletService */
        $walletService = resolve(WalletService::class);

        $allowedCoins = $walletService->getAllowedFiatForNewWallets($cProfile);
        return [
            'currency' => ['bail', 'required', Rule::in($allowedCoins)],
        ];
    }
}
