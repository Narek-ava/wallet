<?php

namespace App\Rules\Backoffice;

use Illuminate\Contracts\Validation\Rule;

class CheckManagerToHavePermissionRule implements Rule
{
    protected array $permissions;
    protected string $projectId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $permissions, string $projectId)
    {
        $this->permissions = $permissions;
        $this->project_id = $projectId;
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
        $bUser = auth()->guard('bUser')->user();
        return $bUser->isAllowed($this->permissions, $this->projectId);

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return t('permission_error');
    }
}
