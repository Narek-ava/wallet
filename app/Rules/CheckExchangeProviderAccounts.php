<?php

namespace App\Rules;

use App\Models\PaymentProvider;
use Illuminate\Contracts\Validation\Rule;

class CheckExchangeProviderAccounts implements Rule
{

    protected $provider;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?PaymentProvider $provider)
    {
        $this->provider = $provider;
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
        return $this->provider->accounts()->where('id', $value)->count() > 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t('provider_account_error');
    }
}
