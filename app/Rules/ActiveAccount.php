<?php

namespace App\Rules;

use App\Enums\AccountStatuses;
use App\Models\CryptoAccountDetail;
use Illuminate\Contracts\Validation\Rule;

class ActiveAccount implements Rule
{
    private $currency;
    private $address;
    private $profile;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($cryptoCurrency , $walletAddress, $cProfile)
    {
        $this->currency = $cryptoCurrency;
        $this->address = $walletAddress;
        $this->profile = $cProfile;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $accountIds = $this->profile->accounts()
            ->where('status',AccountStatuses::STATUS_ACTIVE)
            ->where('currency',  $this->currency)
            ->where('is_external', true)
            ->whereHas('cryptoAccountDetail', function ($q) {
                $q->where([
                    'coin' => $this->currency,
                ]);
            }
            )->pluck('id')->toArray();
        $userAccountWalletAddresses = CryptoAccountDetail::all()->whereIn('account_id', $accountIds)->pluck('address')->toArray();
        return  !in_array($this->address, $userAccountWalletAddresses);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t('wallet_address_field_unique');
    }
}
