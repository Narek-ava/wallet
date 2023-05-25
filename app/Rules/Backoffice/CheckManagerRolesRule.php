<?php

namespace App\Rules\Backoffice;

use Illuminate\Contracts\Validation\Rule;
use Spatie\Permission\Models\Role;

class CheckManagerRolesRule implements Rule
{

    protected array $available_roles;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->available_roles = Role::query()->pluck('name')->toArray();
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
        foreach ($value as $roleName) {
            if (!in_array($roleName, $this->available_roles)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t('invalid_role');
    }
}
