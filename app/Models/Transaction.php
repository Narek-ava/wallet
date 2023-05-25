<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\TransactionStatuses;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Transaction
 * @package App\Models
 * @property $id
 * @property $type
 * @property $trans_amount
 * @property $recipient_amount
 * @property $from_account
 * @property $to_account
 * @property $creation_date
 * @property $transaction_due_date
 * @property $commit_date
 * @property $confirm_date
 * @property $status
 * @property $decline_reason
 * @property $exchange_rate
 * @property $from_commission_id
 * @property $to_commission_id
 * @property $exchange_request_id
 * @property $operation_id
 * @property $parent_id
 * @property $transaction_id
 * @property $tx_id
 * @property Account $fromAccount
 * @property Account $toAccount
 * @property CollectedCryptoFee $collectedCryptoFee
 * @property Operation $operation
 * @property Transaction $feeChildTransactions
 * @property Transaction $parentTransaction
 */
class Transaction extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
    protected $guarded = [];
    public $timestamps = true;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * relation from accounts
     */
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * relation to accounts
     */
    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    /**
     * @return bool
     */
    public function markAsSuccessful(): bool
    {
        $this->status = TransactionStatuses::SUCCESSFUL;

        if (!$this->save()) {
            return false;
        }
        //dd($this->toAccount);
        $this->toAccount->updateBalance();
        $this->fromAccount->updateBalance();

        return true;
    }

    public function collectedCryptoFee()
    {
        return $this->hasOne(CollectedCryptoFee::class, 'transaction_id', 'id');
    }

    /**
     * @param string $txId
     * @return bool
     */
    public function setTxId(string $txId): bool
    {
        $this->tx_id = $txId;
        return $this->save();
    }
  /**
     * @param string $refId
     * @return bool
     */
    public function setRefId(string $refId): bool
    {
        $this->ref_id = $refId;
        return $this->save();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fromCommission()
    {
        return $this->belongsTo(Commission::class, 'from_commission_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function toCommission()
    {
        return $this->belongsTo(Commission::class, 'to_commission_id', 'id');
    }

    public function parentTransaction()
    {
        return $this->belongsTo(Transaction::class,'parent_id','id');
    }

    public function feeChildTransactions()
    {
        return $this->hasMany(Transaction::class,'parent_id', 'id')->orderBy('transaction_id');
    }

    public function getTransAmountAttribute($value)
    {
        return formatMoney($value, $this->fromAccount->currency ?? null);
    }

    public function getRecipientAmountAttribute($value)
    {
        return formatMoney($value, $this->toAccount->currency ?? null);
    }

    public function getExchangeRateAttribute($value)
    {
        return floatval($value);
    }

    public function getTxExplorerUrl()
    {
        if ($this->tx_id) {
            return str_replace('{tx_id}', $this->tx_id, Currency::TX_EXPLORER_MAP[$this->toAccount->currency]);
        }
        return null;
    }

    public static function getPendingTransactionQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Transaction::query()
            ->with('operation')
            ->where('status', TransactionStatuses::PENDING)
            ->whereNotNull('tx_id');
    }

    public function calculateTransactionFee($account)
    {
        $incomingFeeTransactionsAmount = $this->feeChildTransactions()->where('to_account', $account->childAccount->id)->sum('trans_amount');
        $outgoingFeeTransactionsAmount = $this->feeChildTransactions()->where('from_account', $account->id)->sum('trans_amount');

        return $incomingFeeTransactionsAmount - $outgoingFeeTransactionsAmount;
    }
}
