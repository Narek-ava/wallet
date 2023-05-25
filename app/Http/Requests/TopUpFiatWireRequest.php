<?php


namespace App\Http\Requests;


use App\Http\Requests\Backoffice\BaseTransactionRequest;

class TopUpFiatWireRequest extends BaseTransactionRequest
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
        $generalFields = parent::rules();
        $additionalFields = [];

        return $generalFields + $additionalFields;
    }
}
