<?php

namespace App\Http\Requests;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @property string|array $phoneCode
 * @property string $name
 * @property string $code
 * @property int $isBanned
 * @property int $isAlphanumericSender
 */
class CountryRequest extends FormRequest
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
        $rules = [
            'name' => ['required', 'string', 'max:191', 'regex:/^[a-zA-Z .\-]+$/u'],
            'code' => ['required', 'string', 'max:2', 'alpha','unique:countries,code,' .  $this->route('country') ],
            'code_ISO3' => ['required', 'string', 'max:3', 'alpha','unique:countries,code_ISO3,' .  $this->route('country') ],
            'phoneCode' => [
                'required',
                'string',
                function ($attribute, $codeString, $fail) {
                    foreach ($this->phoneCode as $code) {
                        if ($code !== (string)(int)$code) {
                            $fail(t('ui_country_code_format_fail'));
                        }
                        if (Str::length($code) > 6) {
                            $fail(t('ui_country_code_length_fail'));
                        }
                    }
                },
            ],
            'isBanned' => ['required', Rule::in(array_keys(Country::BANNED_NAMES))],
            'isAlphanumericSender' => ['required', Rule::in(array_keys(Country::ALPHANUMERIC_SENDER_NAMES))],
        ];


        if (!file_exists(public_path('/cratos.theme/images/flag/') . $this->code . '.png') || $this->hasFile('countryFlag')) {
            $rules['countryFlag'] = ['required', 'image', 'mimes:png', 'max:1024'];
        }

        return $rules;

    }

    protected function prepareForValidation()
    {
        $exploded = explode(',', $this->phoneCode);

        $this->phoneCode = array_map(function ($item) {
            return trim($item);
        }, $exploded);
    }

    public function attributes()
    {
        return [
            'code_ISO3' => 'code ISO-3'
        ];
    }
}
