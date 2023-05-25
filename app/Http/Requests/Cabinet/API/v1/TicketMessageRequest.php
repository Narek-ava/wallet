<?php

namespace App\Http\Requests\Cabinet\API\v1;

use App\Rules\NoEmojiRule;

class TicketMessageRequest extends BaseRequest
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
            'ticket_id' => ['required', 'string'],
            'message' => ['required', new NoEmojiRule],
            'm_file' => 'nullable|mimes:jpg,pdf,png|max:'.config('view.upload.ticket.file.size'),
        ];
    }

    public function messages()
    {
        return [
            'ticket_id.required' => t('provider_field_required'),
            'message.required' => t('provider_field_required'),
            'message.regex' => t('provider_field_regex'),
            'm_file.mimes' => t('mimes_ticket'),
            'm_file.max' => t('max_file_size', ['size' => config('view.upload.ticket.file.size') . ' kb']),
        ];
    }

}
