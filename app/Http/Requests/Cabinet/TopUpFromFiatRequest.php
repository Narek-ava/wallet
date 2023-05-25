<?php

namespace App\Http\Requests\Cabinet;

use App\Enums\{AccountType, OperationOperationType, TransactionType};
use App\Models\{Account, CryptoAccountDetail, Operation};
use Illuminate\Foundation\Http\FormRequest;

class TopUpFromFiatRequest extends FormRequest
{

    public Account $fiatWallet;
    public CryptoAccountDetail $cryptoAccountDetail;

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

        $cProfile = getCProfile();
        $id = $this->route()->parameter('id');

        $this->fiatWallet = $cProfile->getAccountById($this->currency, AccountType::TYPE_FIAT);
        $this->cryptoAccountDetail = $cProfile->getCryptoAccountDetailById($id);

        $availableBalance = $this->fiatWallet->getAvailableBalance();
        $tempOperation = new Operation(['operation_type' => OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT]);
        $commission = $this->fiatWallet->getAccountCommission(true, TransactionType::BANK_TRX, $tempOperation);
        return [
            'amount' => ['bail', 'required', 'numeric', 'gt:0'],
            'amountFiat' => ['bail', 'required', 'numeric', 'gt:0', 'gte:'.($commission->min_amount ?? 0), "max:{$availableBalance}"], // @todo fiat get min amount for amountFiat by rates
            'operation_id' => ['bail', 'required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'amountFiat.max' => t('error_insufficient_funds'),
        ];
    }
}
