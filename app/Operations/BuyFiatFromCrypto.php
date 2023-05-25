<?php


namespace App\Operations;


use App\Enums\{AccountType,
    Currency,
    LogMessage,
    LogResult,
    LogType,
    OperationSubStatuses,
    Providers,
    TransactionStatuses,
    TransactionType};
use App\Exceptions\OperationException;
use App\Facades\ActivityLogFacade;
use App\Facades\KrakenFacade;
use App\Facades\EmailFacade;
use App\Services\BitGOAPIService;
use App\Services\CommissionsService;
use App\Services\ExchangeInterface;
use App\Services\ExchangeRatesBitstampService;
use App\Services\KrakenService;
use App\Services\TransactionService;
use App\Models\{Account, Commission, Operation, Transaction};
use Illuminate\Support\Facades\DB;

class BuyFiatFromCrypto extends WithdrawWire
{

    protected function refundFromClientToPayment()
    {
        throw new OperationException('Refund not supported!');
    }


    protected function sendFromPaymentToClient()
    {

        $fromCommission = $this->fromAccount->getAccountCommission(true);
        $transactionService = new TransactionService();
        $this->_transaction = $transactionService->createTransactions(
            TransactionType::BANK_TRX, $this->operationAmount, $this->fromAccount, $this->toAccount, $this->date,
            TransactionStatuses::SUCCESSFUL, null, $this->_operation, $fromCommission->id, null,
            $this->_operation->step
        );

        EmailFacade::sendCompletedRequestForWithdrawalViaSepaOrSwift($this->_operation, $this->operationAmount);

    }

    public function getClientCommission(): Commission
    {
        return $this->_operation->toAccount->getAccountCommission(false, TransactionType::BANK_TRX, $this->_operation);
    }




}
