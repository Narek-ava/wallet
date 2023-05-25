<?php

namespace App\Models;

use App\Enums\OperationStatuses;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CollectedCryptoFee
 * @package App\Models
 * @property string $id
 * @property string $currency
 * @property float $amount
 * @property string $wallet_id
 * @property string $transaction_id
 * @property string $operation_id
 * @property string $client_account_id
 * @property string $system_account_id
 * @property bool $is_collected
 * @property $created_at
 * @property $updated_at
 * @property Transaction $transaction
 * @property Account $clientAccount
 * @property Account $systemAccount
 * @property Operation $operation
 */
class CollectedCryptoFee extends BaseModel
{
    protected $fillable = [
        'currency', 'amount', 'wallet_id', 'client_account_id', 'system_account_id', 'is_collected', 'transaction_id', 'operation_id'
    ];

    public function clientAccount()
    {
        return $this->belongsTo(Account::class, 'client_account_id', 'id');
    }

    public function systemAccount()
    {
        return $this->belongsTo(Account::class, 'system_account_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class, 'operation_id', 'id');
    }

    public function scopeFilterCollectedTransactions(Builder $query, ?string $from = null, ?string $to = null, ?bool $isCollected = null, ?string $projectId = null)
    {
        if (isset($from)) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }
        if (isset($to)) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }
        if (isset($isCollected)) {
            $query->where('is_collected', $isCollected);
        }

        $query->where(function ($q) use ($projectId) {
            return $q->whereNull('transaction_id')
                ->orWhereHas('transaction.operation', function ($query) use ($projectId) {
                    if ($projectId) {
                        $query->whereHas('cProfile.cUser', function ($q) use ($projectId) {
                            $q->where('project_id', $projectId);
                        });
                    }
                    return $query->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::RETURNED]);
                });
        });
    }
}
