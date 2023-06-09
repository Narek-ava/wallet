<?php

namespace App\Http\Requests;

use App\Rules\NoEmojiRule;

class TicketRequest extends BaseRequest
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
            'subject' => ['required', new NoEmojiRule],
            'question' => ['required', new NoEmojiRule],
            'file' => 'nullable|mimes:jpg,pdf,png|max:'.config('view.upload.ticket.file.size'),
        ];
    }

    public function messages()
    {
        return [
            'subject.required' => t('provider_field_required'),
            'subject.regex' => t('provider_field_regex'),
            'question.required' => t('provider_field_required'),
            'question.regex' => t('provider_field_regex'),
            'file.mimes' => t('mimes_ticket'),
            'file.max' => t('max_file_size', ['size' => config('view.upload.ticket.file.size') . ' kb']),
        ];
    }
}
