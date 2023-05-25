<?php

namespace App\Operations;

use App\Enums\PaymentFormTypes;
use App\Exceptions\OperationException;
use App\Models\Commission;

class WithdrawCryptoPF extends WithdrawCrypto
{

    public function getClientCommission(): Commission
    {
        $topUpCardPFOperation = $this->_operation->parent;
        if (!$topUpCardPFOperation) {
            throw new OperationException(t('withdraw_crypto_error_message'));
        }
        $paymentForm = $topUpCardPFOperation->paymentForm;
        if (!$paymentForm) {
            throw new OperationException(t('withdraw_crypto_error_message'));
        }
        if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            return $this->_operation->fromAccount->getAccountCommission(true);
        }

        return $topUpCardPFOperation->paymentFormAttempt->toAccount->getAccountCommission(true);
    }


}
