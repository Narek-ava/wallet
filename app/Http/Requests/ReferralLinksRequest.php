<?php

namespace App\Http\Requests;

use App\Models\Cabinet\CProfile;
use App\Models\ReferralPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferralLinksRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'max:25', 'regex:/^[A-Za-z0-9 ]+$/'],
            'partner_id' => ['required', 'exists:referral_partners,id'],
            'individual_rate_templates_id' => ['required', 'exists:rate_templates,id'],
            'corporate_rate_templates_id' => ['required', 'exists:rate_templates,id'],
            'activation_date' => ['required', 'date:Y-m-d',],
            'deactivation_date' => ['required', 'date:Y-m-d',],
        ];
    }


}
