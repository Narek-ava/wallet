<?php

namespace App\Rules\Backoffice;

use Illuminate\Contracts\Validation\Rule;

class CheckAllManagersToHaveRolesRule implements Rule
{

    protected array $bUsers;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $bUsers)
    {
        $this->bUsers = $bUsers;
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
        return count(array_diff($this->bUsers, array_keys($value))) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'empty_roles';
    }


}
