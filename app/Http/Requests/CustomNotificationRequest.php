<?php

namespace App\Http\Requests;

use App\Enums\BUserPermissions;
use App\Enums\NotificationRecipients;
use App\Models\Cabinet\CProfile;
use App\Rules\Backoffice\CheckManagerToHavePermissionRule;
use Illuminate\Validation\Rule;

class CustomNotificationRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'message' => ['bail', 'required', 'string','max:800'],
            'recipients' => ['bail', 'required', 'int', Rule::in(array_keys(NotificationRecipients::RECIPIENTS))],
            'project_id' => ['bail', 'nullable', 'string', 'exists:projects,id'],
            'title' => ['bail', 'nullable', 'string'],
        ];

        if ($this->recipients === NotificationRecipients::CURRENT_CLIENT) {
            $rules['profile_id'] = ['bail', 'required', 'int','exists:c_profiles,profile_id'];

            if ($this->profile_id) {
                $cProfile = CProfile::query()->where('profile_id', $this->profile_id)->first();
                if ($cProfile) {
                    $rules['profile_id'][] = new CheckManagerToHavePermissionRule([BUserPermissions::ADD_NEW_NOTIFICATIONS], $cProfile->cUser->project_id);
                }
            }
        }

        return $rules;
    }
}
