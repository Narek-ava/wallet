<?php


namespace App\Operations;


use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\OperationSubStatuses;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Models\Account;
use App\Models\Commission;
use App\Services\OperationService;
use Illuminate\Support\Facades\DB;

// @todo
// check abstract functions
// make transactions
// create abstract class for Top Up By Wire FW Operation and call execute method in createFiatTopUpByWireFWOperation() function
class WithdrawToFiat extends AbstractOperation
{

    public function getClientCommission(): Commission
    {
        // TODO: Implement getClientCommission() method.
    }

    protected function getSystemAccountType(): int
    {
        return AccountType::TYPE_WIRE_SEPA;
    }

    protected function getSystemAccount(): ?Account
    {
        return $this->_operation->getOperationSystemAccount();
    }

    public function createFiatTopUpByWireFWOperation()
    {
        $operationService = resolve(OperationService::class);
        $operationService->createOperation($this->_operation->c_profile_id,
            OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE,
            $this->leftAmount, $this->_operation->to_currency, $this->_operation->to_currency,
            $this->toAccount, $this->_operation->toAccount,
        );
    }

    public function execute(): void
    {
        config()->set('projects.project', $this->_operation->cProfile->cUser->project);

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
                        throw new OperationException( t('withdraw_wire_first_transaction_valid'));
                    }
                    $this->_operation->step++;
                    if ($request->currency_amount != $this->_operation->amount) {
                        throw new OperationException(t('withdraw_wire_withdraw_amount_valid') . $this->_operation->amount . '!');
                    }
                    $availableBalance = $this->_operation->fromAccount->cryptoAccountDetail->getWalletBalance();
                    if (!$availableBalance || floatval($availableBalance) < $this->_operation->amount) {
                        throw new OperationException(t('send_crypto_balance_fail'). ', '.$availableBalance. ' '. t('ui_available_balance_in_wallet'));
                    }
                    DB::transaction(function () {
//                        $this->feeFromClientToWallet(); // 1.1, 1.2
//                        $this->sendFromClientToLiquidity(); //1.3
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
//                    $this->exchangeFromCryptoToFiat($this->operationAmount); // 2.1, 2.2
                    break;

                case 2:

                    $isValid = ($request->transaction_type == TransactionType::BANK_TRX) &&
                        ($request->from_type == Providers::PROVIDER_LIQUIDITY) &&
                        ($request->to_type == Providers::PROVIDER_PAYMENT) &&
                        ($request->from_currency == $this->_operation->to_currency) &&
                        ($this->toAccount->currency = $this->_operation->toAccount->currency);
                    if (!$isValid) {
                        throw new OperationException(t('withdraw_wire_bank_trx_liquidity') . $this->fromAccount->currency . t('withdraw_wire_to_payment') . $this->_operation->to_currency . '!');
                    }

                    DB::transaction(function () {
                        $this->_operation->step++;
//                        $this->sendFromLiquidityToPayment(); //3.1
//                        $this->feeFromPaymentToSystem(); //3.2
//                        $this->feeFromSystemToLiquidity(); //3.3
//                        $this->feeIncomingFromSystemToPayment(); //3.4
                        $this->createFiatTopUpByWireFWOperation();
                    });

                    break;
                case 4:
                    $isValid = (
                        $request->transaction_type == TransactionType::REFUND &&
                        $request->from_type == Providers::CLIENT &&
                        $request->to_type == Providers::PROVIDER_PAYMENT &&
                        $request->from_currency == $this->_operation->toAccount->currency
                    );

                    if (!$isValid) {
                        throw new OperationException(t('withdraw_wire_refund_providers'));
                    }

                    DB::transaction(function () {
                        $this->_operation->step--;
//                        $this->refundFromClientToPayment();
                    });

                    break;

            }
        }catch (\Exception $exception) {
            logger()->error('WithdrawWireException', [
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
        }    }


}
