<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BCHAddressFormat implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($walletAddress, $wantsJson)
    {
        $this->wallet_address = $walletAddress;
        $this->wantsJson = $wantsJson;
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
        return strpos($this->wallet_address, 'bitcoincash:') === false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->wantsJson ? t('bch_form_convert_api') : t('bch_form_convert');
    }
}
