<?php

namespace App\Models;

use App\Enums\{AccountStatuses,
    AccountType,
    Commissions,
    OperationOperationType,
    OperationStatuses,
    Providers,
    TransactionStatuses,
    TransactionType};
use App\Models\Cabinet\CProfile;
use Illuminate\Database\{Eloquent\Builder, Eloquent\Model, Eloquent\Relations\HasOne};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Class Account
 * @package App\Models
 * @property $id
 * @property $status
 * @property $c_profile_id
 * @property $owner_type
 * @property $payment_provider_id
 * @property $from_commission_id
 * @property $to_commission_id
 * @property $internal_commission_id
 * @property $refund_commission_id
 * @property $name
 * @property $account_type
 * @property $currency
 * @property $balance
 * @property $parent_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property $country
 * @property $account_id
 * @property $minimum_balance_alert
 * @property $is_external
 * @property $fiat_type
 * @property CryptoAccountDetail $cryptoAccountDetail
 * @property CProfile $cProfile
 * @property WireAccountDetail $wire
 * @property CardAccountDetails $card
 * @property Commissions[] $commissions
 * @property Commission $refundCommission
 * @property Commission $internalCommission
 * @property Commission $toCommission
 * @property Commission $fromCommission
 * @property Limit $limit
 * @property AccountCountry[] $countries
 * @property Account $childAccount
 * @property Account $parentAccount
 * @property Account $providerFeeAccount
 * @property AccountClientPolicy $accountClientPolicy
 * @property PaymentProvider $provider
 * @property PaymentProvider $walletProvider
 * @property WallesterAccountDetail $wallesterAccountDetail
 * @property Transaction[] $incomingTransactions
 * @property Transaction[] $outgoingTransactions
 * @property Transaction[] $fromTransaction
 * @property Transaction[] $toTransaction
 * @property Operation[] $withdrawOperations
 * @property PaymentForm[] $paymentForms
 * @property PaymentFormAttempt[] $paymentFormAttempts

 */
class Account extends BaseModel
{
    public $incrementing = false;

    protected $fillable = [
        'status', 'c_profile_id', 'owner_type', 'payment_provider_id', 'name', 'account_type', 'currency', 'balance',
        'is_external', 'from_commission_id', 'to_commission_id', 'refund_commission_id', 'internal_commission_id', 'parent_id', 'country', 'minimum_balance_alert', 'fiat_type'
    ];

    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
    protected $guarded = [];

    /**
     * returns system account filtering by currency and account_type
     * @param string $currency
     * @param int $accountType
     * @return null|self
     */
    public static function getSystemAccount(string $currency, int $accountType): ?self
    {
        $account = self::query()->where([
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
            'currency' => $currency,
            'account_type' => $accountType
        ])
            ->whereNull('c_profile_id')
            ->whereNull('payment_provider_id')
            ->first();
        if (!$account) {
            logger()->error('NoSystemAccount', [
                'currency' => $currency,
                'account_type' => $accountType
            ]);
        }
        return $account;
    }

    public static function getProviderAccount(string $currency, int $providerType, ?int $paymentType = null, ?int $cardSecure = null, ?string $projectId = null, bool $default = null): ?self
    {
        $query = self::getProviderAccountsQuery($currency, $providerType);

        if (!is_null($paymentType)) {
            $query->whereHas('cardAccountDetail', function ($q) use($paymentType) {
                return $q->where('payment_system', $paymentType);
            });
        }

        if (!is_null($cardSecure)) {
            $query->whereHas('cardAccountDetail', function ($q) use($cardSecure) {
                return $q->where('secure', $cardSecure);
            });
        }

        if (!is_null($projectId)) {
            $query->whereHas('provider', function ($q) use($projectId, $default) {
                if (isset($default)) {
                    $q->where('is_default', $default);
                }
                return $q->queryByProject($projectId);
            });
        }

        $account = $query->first();

        if (!$account) {
            logger()->error('NoProviderAccount', compact('currency', 'providerType'));
        }

        return $account;
    }


    public static function getProviderAccountsQuery(string $currency, int $providerType, $status = \App\Enums\PaymentProvider::STATUS_ACTIVE)
    {
        return self::getProviderAccountsWithoutCurrencyQuery($providerType, $status)->where('currency', $currency);
    }

    public static function getProviderAccountsWithoutCurrencyQuery(int $providerType, $status = \App\Enums\PaymentProvider::STATUS_ACTIVE)
    {
        return self::query()->where([
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
            'status' => AccountStatuses::STATUS_ACTIVE,
        ])->whereHas('provider', function ($q) use ($providerType, $status) {
            return $q->where([
                'provider_type' => $providerType,
                'status' => $status
            ]);
        });
    }

    public function paymentForms()
    {
        return $this->belongsToMany(PaymentForm::class, 'payment_form_account', 'account_id', 'form_id');
    }

    public function paymentFormAttempts()
    {
        return $this->hasMany(PaymentFormAttempt::class, 'to_account_id', 'id');
    }

    public static function isPaymentProviderForWithdrawOperation($cProfile)
    {
        return self::getProviderAccountsWithoutCurrencyQuery(Providers::PROVIDER_PAYMENT)
            ->whereHas('accountClientPolicy', function (Builder $q) use ($cProfile) {
                if ($cProfile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL) {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2C);
                } else {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                }
            })->exists();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getActiveClientCryptoAccounts()
    {
        return self::query()
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->where('account_type', AccountType::TYPE_CRYPTO)
            ->whereNotNull('c_profile_id')
            ->whereHas('cryptoAccountDetail')
            ->whereHas('walletProvider')
            ->with('cryptoAccountDetail');
    }

    /**
     * @return HasOne
     */
    public function cryptoAccountDetail()
    {
        return $this->hasOne(CryptoAccountDetail::class, 'account_id', 'id');
    }

    public function wallesterAccountDetail()
    {
        return $this->hasOne(WallesterAccountDetail::class, 'account_id', 'id');
    }

    public function cardAccountDetail()
    {
        return $this->hasOne(CardAccountDetail::class, 'account_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'c_profile_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function wire()
    {
        return $this->hasOne(WireAccountDetail::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * @return HasOne
     */
    public function limit()
    {
        return $this->hasOne(Limit::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countries()
    {
        return $this->hasMany(AccountCountry::class);
    }

    /**
     * @return HasOne
     */
    public function fromCommission()
    {
        return $this->belongsTo(Commission::class, 'from_commission_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function toCommission()
    {
        return $this->belongsTo(Commission::class, 'to_commission_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function chargebackCommission()
    {
        return $this->belongsTo(Commission::class, 'chargeback_commission_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function internalCommission()
    {
        return $this->belongsTo(Commission::class, 'internal_commission_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function refundCommission()
    {
        return $this->belongsTo(Commission::class, 'refund_commission_id', 'id');
    }

    /**
     * @return Account|Model|HasOne|object|null
     */
    public function providerFeeAccount()
    {
        return $this->childAccount()->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_PROVIDER);
    }

    public function accountClientPolicy()
    {
        return $this->hasMany(AccountClientPolicy::class,'account_id' , 'id');
    }

    public function getWalletProviderFeeAccount(): ?Account
    {
        $account = $this->provider->accounts()->where([
            'status' => AccountStatuses::STATUS_ACTIVE,
            'currency' => $this->currency,
        ])->first();
        /* @var Account $account*/
        if ($account) {
            return $account->childAccount;
        }
    }

    /**
     * @param bool $from
     * @param int|null $trxType
     * @param Operation|null $operation
     * @return Commission|null
     */
    public function getAccountCommission(bool $from, int $trxType = null, ?Operation $operation = null): ?Commission
    {
        if ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_CLIENT) {

            if ($trxType == TransactionType::REFUND) {
                if (!empty($operation->operation_type)) {
                    if (in_array($operation->operation_type, OperationOperationType::TOP_UP_OPERATIONS)) {
                        $type = Commissions::TYPE_INCOMING;
                    } else {
                        $type = Commissions::TYPE_OUTGOING;
                    }
                } else {
                    $type = Commissions::TYPE_INCOMING;
                }
            } else {
                $type = $from ? Commissions::TYPE_OUTGOING : Commissions::TYPE_INCOMING;
            }


            if ($operation && in_array($operation->operation_type, [OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_CARD_PF]) && !$from) {
                $commissionType = AccountType::TYPE_CRYPTO;
            } elseif ($operation && in_array($operation->operation_type, OperationOperationType::FIAT_WALLET_OPERATIONS)) {
                $commissionType = AccountType::ACCOUNT_COMMISSION_TYPES[$operation->operation_type];
            } else {
                $commissionType = AccountType::ACCOUNT_COMMISSION_TYPES[$this->account_type];
            }
            return $this->cProfile->operationCommission($commissionType, $type, $this->currency);
        }
        if ($trxType == TransactionType::REFUND) {
            return $this->refundCommission;
        } elseif ($trxType == TransactionType::CHARGEBACK) {
            return $this->chargebackCommission;
        }
        if ($from) {
            return $this->fromCommission;
        }

        return $this->toCommission;
    }

    /**
     * returns account commission (if its user account, then returns cprofile commission by rate templates)
     * @param $currency
     * @return mixed
     */
    public function checkIfClientAccount()
    {
        return $this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_CLIENT;
    }

    /**
     * @return HasOne
     */
    public function childAccount()
    {
        return $this->hasOne(Account::class, 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function provider()
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function walletProvider()
    {
        return $this->provider()
            ->where('status', \App\Enums\PaymentProvider::STATUS_ACTIVE)
            ->where('provider_type', Providers::PROVIDER_WALLET);
    }

    /**
     * @return bool
     * checking if account is payment provider
     */
    public function checkIfPaymentProviderAccount()
    {
        return $this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_SYSTEM && $this->payment_provider_id;
    }

    /**
     * @param int $count
     * @return float|int
     */
    public function cryptoBlockChainFee($count = 1)
    {
        //outgoing blockchain fee
        $commission = $this->fromCommission;
        if ($commission) {
            $blockChainFee = $commission->blockchain_fee;
            return $blockChainFee * $count;
        }

    }

    /**
     * @param $currency
     * @return mixed
     */
    public function getLiquidityCryptoAccount(string $currency): ?self
    {
        $provider = $this->provider;
        if ($provider) {
            /* @var PaymentProvider $provider*/
            $account = $provider->accountByCurrency($currency, AccountType::TYPE_CRYPTO);
            return $account;
        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function updateBalance()
    {
        $operationStatuses = [OperationStatuses::SUCCESSFUL, OperationStatuses::RETURNED];

        $incomes = $this->incomingTransactions()->where('status', TransactionStatuses::SUCCESSFUL)
            ->whereHas('operation', function ($q) use($operationStatuses) {
                $q->whereIn('status', $operationStatuses);
            })
            ->sum('recipient_amount');

        if ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_SYSTEM) {
            $operationStatuses[] = OperationStatuses::PENDING;
        }
        $outcomes = $this->outgoingTransactions()->whereIn('status', [TransactionStatuses::SUCCESSFUL, TransactionStatuses::PENDING])
            ->whereHas('operation', function ($q) use($operationStatuses) {
                $q->whereIn('status', $operationStatuses);
            })
            ->sum('trans_amount');

        $withdrawOperations = $this->withdrawOperations()->where('status', OperationStatuses::PENDING)->sum('amount');

        /*$refunds = $this->outgoingTransactions()->where([
            'status' => TransactionStatuses::PENDING,
            'type' => TransactionType::REFUND
        ])->sum('trans_amount');*/


        $balance = $incomes - abs($outcomes) - abs($withdrawOperations);
        $this->balance = formatMoney($balance, $this->currency);
        $this->save();
        return $this->balance;
    }


    public function calculateBalance(Transaction $transaction)
    {
        $incomes = $this->incomingTransactions()
            ->where('transaction_id', '<=', $transaction->transaction_id)
            ->where('status', TransactionStatuses::SUCCESSFUL)
            ->sum('recipient_amount');

        $operationStatuses = [OperationStatuses::SUCCESSFUL, OperationStatuses::RETURNED];
        if ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_SYSTEM) {
            $operationStatuses[] = OperationStatuses::PENDING;
        }

        $outcomes = $this->outgoingTransactions()
            ->where('transaction_id', '<=', $transaction->transaction_id)
            ->where('status', TransactionStatuses::SUCCESSFUL)
            ->whereHas('operation', function ($q) use($operationStatuses) {
                $q->whereIn('status', $operationStatuses);
            })
            ->sum('trans_amount');

        $refunds = $this->outgoingTransactions()
            ->where('transaction_id', '<=', $transaction->transaction_id)
            ->where('type', TransactionType::REFUND)
            ->where('status', TransactionStatuses::PENDING)
            ->sum('trans_amount');

        return $incomes - $outcomes - $refunds;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function incomingTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outgoingTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fromTransaction()
    {
        return $this->hasMany(Transaction::class, 'from_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function toTransaction()
    {
        return $this->hasMany(Transaction::class, 'to_account', 'id');
    }

    /**
     * @param string $id
     * @return static
     */
    public static function getActiveAccountById(string $id): self
    {
        return self::query()->where([
            'id' => $id,
            'status' => AccountStatuses::STATUS_ACTIVE
        ])->firstOrFail();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawOperations()
    {
        return $this->hasMany(Operation::class, 'from_account', 'id');
    }

    public function operations()
    {
        return Operation::where('from_account', $this->id)->orWhere('to_account', $this->id)->get();
    }

    /**
     * @return mixed
     */
    public function getAvailableBalance(): float
    {
        $this->updateBalance();
        return $this->balance;
    }

    public function amountValidator($amount)
    {
        $available = $this->getAvailableBalance();
        return Validator::make(['amount' => $amount], ['amount' => "numeric|gt:0|max:{$available}"]);
    }

    public function displayAvailableBalance()
    {
        return generalMoneyFormat($this->getAvailableBalance(), $this->currency);
    }

    public function getBalanceAttribute($value)
    {
        return floatval($value);
    }

    public function getAccountTypeName()
    {
        if ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_SYSTEM) {
            if (!$this->payment_provider_id) {
                //system account
                return t('system_account');
            } else {
                //provider account
                if ($provider = $this->provider) {
                    return Providers::NAMES[$provider->provider_type];
                }
            }
        } elseif ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_CLIENT) {
            //client account
            return t('client');
        } elseif ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_PROVIDER) {
            //providers commission account
            return Providers::NAMES[$this->provider->provider_type];
        }elseif ($this->owner_type == AccountType::ACCOUNT_OWNER_TYPE_SYSTEM_CRYPTO_FEE) {
            return t('system_crypto_fee_account');
        }
    }

    public function scopeFilteredForPayment(
        Builder $query, int $accountType,
        string $countryCode,
        string $currency,
        int $operationType,
        int $profileAccountType,
        int $fiatType = AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT
    )
    {

        // @todo fiat withdraw operations check method
        $query->whereHas('wire');
        $query->where([
            'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE,
            'account_type' => $accountType,
            'currency' => $currency,
            'fiat_type' => $fiatType
        ])->whereHas('countries', function ($q) use ($countryCode) {
            $q->where('country', $countryCode);
        })
            ->whereHas('accountClientPolicy', function ($q) use ($operationType, $profileAccountType) {
            if (in_array($operationType, OperationOperationType::TOP_UP_OPERATIONS)) {
                if ($profileAccountType === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL) {
                    $q->where('type', AccountType::WIRE_PROVIDER_C2B);
                } else {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                }
            } elseif (in_array($operationType, OperationOperationType::WITHDRAW_OPERATIONS)) {
                if ($profileAccountType === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL) {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2C);
                } else {
                    $q->where('type', AccountType::WIRE_PROVIDER_B2B);
                }
            }
        });
    }

    public function scopeFilterPaymentProviderAccounts(
        Builder $query,
        int $accountType,
        string $countryCode,
        string $currency,
        int $operationType,
        int $profileAccountType,
        ?string $projectId = null,
        int $fiatType = AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT
    )
    {
        $query->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
            ->filteredForPayment($accountType, $countryCode, $currency, $operationType, $profileAccountType, $fiatType)
            ->with('wire')
            ->with('countries')
            ->whereHas('provider', function ($q) use ($projectId) {
                return $q->where([
                    'provider_type' => Providers::PROVIDER_PAYMENT,
                    'status' => \App\Enums\PaymentProvider::STATUS_ACTIVE,
                ])->queryByProject($projectId);
            });
    }
}
