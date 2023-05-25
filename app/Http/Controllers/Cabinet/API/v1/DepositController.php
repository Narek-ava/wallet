<?php
namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Models\BankAccountTemplate;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{

    public function getTemplate($templateId = null)
    {
        $profile = Auth::user()->cProfile;
        if ($templateId && $profile->id) {
            $template = BankAccountTemplate::query()->where([
                ['id', '=', $templateId],
                ['c_profile_id','=', $profile->id],
            ])->first();
            /* @var $template BankAccountTemplate*/
            $templateData = [
                'name' => $template->name ?? '',
                'account_holder' => $template->holder ?? '',
                'account_number' => $template->number ?? '',
                'bank_name' => $template->bank_name ?? '',
                'bank_address' => $template->bank_address ?? '',
                'iban' => $template->IBAN ?? '',
                'swift' => $template->SWIFT ?? '',
            ];

        }else {
            $templateKeys = ['name', 'account_holder', 'account_number', 'bank_name', 'bank_address', 'iban', 'swift'];
            $templateData = array_fill_keys($templateKeys, '');
        }

        return $templateData;

    }


}
