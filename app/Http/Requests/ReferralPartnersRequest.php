<?php

namespace App\Http\Requests;

use App\Models\Cabinet\CProfile;
use App\Models\ReferralPartner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferralPartnersRequest extends FormRequest
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
        $rules = ['name' => ['required', 'string', 'max:25', 'regex:/^[A-Za-z0-9 ]+$/']];
        if ($this->isMethod('put')) {
            $rules['partner_id'] = ['exists:referral_partners,id'];
        }

        return $rules;
    }


}
