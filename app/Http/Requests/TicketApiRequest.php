<?php

namespace App\Http\Requests;

use App\Rules\NoEmojiRule;

class TicketApiRequest extends BaseRequest
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
            'subject' => ['required', 'min: 5', 'max: 50', 'string', new NoEmojiRule],
            'question' => ['required','min: 5', 'max:1000', 'string', new NoEmojiRule],
            'file' => 'nullable|mimes:jpg,pdf,png|max:'.config('view.upload.ticket.file.size'),
        ];
    }

}
