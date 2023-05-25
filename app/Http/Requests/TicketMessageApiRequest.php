<?php

namespace App\Http\Requests;

use App\Models\Backoffice\BUser;
use App\Models\Ticket;
use App\Rules\NoEmojiRule;
use Illuminate\Foundation\Http\FormRequest;

class TicketMessageApiRequest extends BaseRequest
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
            'ticket_id' => ['required', 'string', 'exists:tickets,id'],
            'message' => ['required','min: 5', 'max:1000', 'string', new NoEmojiRule],
            'm_file' => 'nullable|mimes:jpg,pdf,png|max:'.config('view.upload.ticket.file.size'),
        ];
    }
}
