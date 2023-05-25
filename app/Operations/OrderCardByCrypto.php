<?php


namespace App\Operations;


use App\Enums\OperationStatuses;
use App\Enums\OperationSubStatuses;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Enums\WallesterCardTypes;
use App\Exceptions\OperationException;
use App\Models\WallesterAccountDetail;
use App\Services\Wallester\WallesterPaymentService;
use Illuminate\Support\Facades\DB;

class OrderCardByCrypto extends WithdrawWire
{
    public function execute(): void
    {
        try {
            $request = $this->request;
            switch ($this->_operation->step) {
                case 0:
                    $isValid = ($request->transaction_type == TransactionType::CRYPTO_TRX) &&
                        ($request->from_type == Providers::CLIENT) &&
                        ($request->to_type == Providers::PROVIDER_LIQUIDITY) &&
                        ($request->from_account == $this->_operation->from_account) &&
                        ($request->from_currency == $this->_operation->from_currency) && $request->to_account;
                    if (!$isValid) {
                        throw new OperationException(t('withdraw_wire_first_transaction_valid'));
                    }

                    $this->_operation->step++;
                    if ($request->currency_amount != $this->_operation->amount) {
                        throw new OperationException(t('withdraw_wire_withdraw_amount_valid') . $this->_operation->amount . '!');
                    }

                    $availableBalance = $this->_operation->fromAccount->cryptoAccountDetail->getWalletBalance();
                    if (!$availableBalance || floatval($availableBalance) < $this->_operation->amount) {
                        throw new OperationException(t('send_crypto_balance_fail') . ', ' . $availableBalance . ' ' . t('ui_available_balance_in_wallet'));
                    }
                    DB::transaction(function () {
                        $this->feeFromClientToWallet(); // 1.1, 1.2
                        $this->sendFromClientToLiquidity(); //1.3
                        $this->_operation->save();
                    });

                    break;

                case 1:
                    $isValid = ($request->transaction_type == TransactionType::EXCHANGE_TRX) &&
                        ($request->from_type == Providers::PROVIDER_LIQUIDITY) &&
                        ($request->to_type == Providers::PROVIDER_LIQUIDITY) &&
                        ($this->fromAccount->currency == $this->_operation->from_currency) &&
                        ($this->toAccount->currency == $this->_operation->to_currency);
                    if (!$isValid) {
                        throw new OperationException(t('withdraw_wire_second_transaction_valid') . $this->_operation->from_currency . t('withdraw_wire_to_liquidity') . $this->_operation->to_currency . '!');
                    }
                    $this->_operation->step++;
                    $this->exchangeFromCryptoToFiat($this->operationAmount); // 2.1, 2.2
                    $this->markOperationAsSuccessful();
                    break;
            }
        } catch (\Exception $exception) {
            logger()->error('OrderByCryptoException', [
                'message' => $exception->getMessage(),
            ]);
            if (!($exception instanceof OperationException)) {
                if (strpos($exception->getMessage(), OperationSubStatuses::getName(OperationSubStatuses::INSUFFICIENT_FUNDS)) !== false) {
                    $this->_operation->substatus = OperationSubStatuses::INSUFFICIENT_FUNDS;
                } else {
                    $this->_operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                }
                $this->_operation->error_message = $exception->getMessage();
                $this->_operation->save();
            }
            throw new OperationException($exception->getMessage(), 0, $exception);
        }
    }

    protected function markOperationAsSuccessful()
    {
        $this->_operation->status = OperationStatuses::SUCCESSFUL;
        $exchangeTrx = $this->_operation->getExchangeTransaction();
        if (isset($exchangeTrx)) {
            $this->_operation->exchange_rate = $exchangeTrx->exchange_rate;
        }
        $this->_operation->save();

        $additionalData = json_decode($this->_operation->additional_data, true);
        $walAccountDetailId = $additionalData['wallester_account_detail_id'];
        $wallesterAccountDetail = WallesterAccountDetail::findOrFail($walAccountDetailId);

        /* @var WallesterPaymentService $wallesterPaymentService */
        $wallesterPaymentService = resolve(WallesterPaymentService::class);
        $wallesterAccountDetail->operation_id = $this->_operation->id;
        $wallesterAccountDetail->save();

        $currentOrderData = $additionalData['wallester_card_info'];

        $wallesterPaymentService->orderCard($this->_operation->cProfile, $wallesterAccountDetail->account->id, $wallesterAccountDetail->id, $currentOrderData, $this->_operation->cProfile->getFullName());

        $wallesterAccountDetail->is_paid = true;
        $wallesterAccountDetail->save();
    }
}
