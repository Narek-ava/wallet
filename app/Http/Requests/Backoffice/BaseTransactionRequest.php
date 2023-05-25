<?php


namespace App\Http\Requests\Backoffice;


use App\Enums\TransactionType;
use App\Models\Account;
use App\Rules\CheckExchangeProviderAccounts;
use FontLib\Table\Type\name;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class AbstractTransactionRequest
 * @package App\Http\Requests\Backoffice
 *
 * @property string $date
 * @property int $transaction_type
 * @property int $from_type
 * @property int $to_type
 * @property string $from_currency
 * @property string $from_account
 * @property string $to_account
 * @property float $currency_amount
 */
class BaseTransactionRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'date' => 'required|before:tomorrow',
            'transaction_type' => 'required',
            'from_currency' => 'required',
            'from_type' => 'required',
            'from_account' => 'required',
            'to_type' => 'required',
            'currency_amount' => 'required|numeric|min:0',
        ];

        if ($this->transaction_type == TransactionType::EXCHANGE_TRX && $this->from_account && $this->to_account) {
            $fromAccount = Account::find($this->from_account);
            $provider = $fromAccount->provider ?? null;
            $rules['to_account'] = ['required', new CheckExchangeProviderAccounts($provider)];
        } else {
            $rules['to_account'] = ['required'];
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
