<?php


namespace App\Models;

use App\Enums\{AccountType,
    Commissions,
    CommissionType,
    ComplianceLevel,
    Currency,
    OperationOperationType,
    OperationStatuses,
    OperationType,
    Providers,
    TransactionStatuses,
    TransactionSteps,
    TransactionType};
use App\Facades\{ExchangeRatesBitstampFacade, KrakenFacade};
use App\Models\Cabinet\CProfile;
use App\Operations\AmountCalculators\TopUpCardCalculator;
use App\Services\{CommissionsService, OperationService, PaymentFormCryptoService, PaymentFormsService};
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Operation
 * @package App\Models
 * @property $id
 * @property $operation_type
 * @property $amount
 * @property $amount_in_euro
 * @property $received_amount
 * @property $received_amount_currency
 * @property $from_currency
 * @property $to_currency
 * @property $from_account
 * @property $to_account
 * @property $confirm_date
 * @property $confirm_doc
 * @property $exchange_rate
 * @property $client_rate
 * @property $created_by
 * @property $c_profile_id
 * @property $parent_id
 * @property $b_user_id
 * @property $status
 * @property $substatus
 * @property $error_message
 * @property $operation_id
 * @property $project_id
 * @property $compliance_request_id
 * @property $payment_provider_id
 * @property $provider_account_id
 * @property $additional_data
 * @property $comment
 * @property $address
 * @property $payment_form_id
 * @property $step
 * @property $created_at
 * @property $updated_at
 * @property CProfile $cProfile
 * @property Operation $parent
 * @property Operation $child
 * @property OperationFee $operationFee
 * @property ComplianceRequest[] $complianceRequests
 * @property Account $fromAccount
 * @property Account $toAccount
 * @property Account $providerAccount
 * @property Transaction[] $transactions
 * @property MerchantOperationsInformation $merchantOperationsInformation
 * @property PaymentForm $paymentForm
 * @property PaymentFormAttempt $paymentFormAttempt
 * @property MerchantWebhookAttempt[] $merchantWebhookAttempts
 * @property CollectedCryptoFee[] $collectedFees
 * @property Project $project
 * @property string $credited
 *
 *
 * @method static Operation findOrFail(string $id)
 */
class Operation extends BaseModel
{

    protected $casts = [
        'addition_data' => 'array',
    ];

    protected $fillable = ['id', 'operation_type', 'amount', 'amount_in_euro', 'from_currency', 'to_currency', 'address',
        'from_account', 'to_account', 'confirm_date', 'confirm_doc', 'exchange_rate',
        'client_rate', 'created_by', 'c_profile_id', 'b_user_id', 'status', 'substatus', 'error_message', 'received_amount',
        'received_amount_currency', 'compliance_request_id', 'payment_provider_id', 'provider_account_id', 'step', 'comment',
        'additional_data', 'project_id'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cProfile()
    {
        return $this->belongsTo(CProfile::class, 'c_profile_id', 'id');
    }

    public function merchantOperationsInformation()
    {
        return $this->hasOne(MerchantOperationsInformation::class, 'operation_id','id');
    }

    public function collectedFees()
    {
        return $this->hasMany(CollectedCryptoFee::class, 'operation_id', 'id');
    }

    public function getCollectedTransactions()
    {
        $transactionIds = $this->collectedFees()->pluck('transaction_id')->toArray();
        return Transaction::query()->whereIn('id', $transactionIds);
    }

    public function merchantWebhookAttempts()
    {
        return $this->hasMany(MerchantWebhookAttempt::class, 'operation_id', 'id');
    }

    public function complianceRequests()
    {
        return $this->hasMany(ComplianceRequest::class, 'id', 'compliance_request_id');
    }

    public function operationFee(): HasOne
    {
        return $this->hasOne(OperationFee::class);
    }

    public function project()
    {
        return$this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account', 'id');
    }

    public function paymentForm()
    {
        return $this->belongsTo(PaymentForm::class, 'payment_form_id', 'id');
    }

    public function paymentFormAttempt()
    {
        return $this->hasOne(PaymentFormAttempt::class, 'operation_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Operation::class, 'parent_id', 'id');
    }

    public function child()
    {
        return $this->hasOne(Operation::class, 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * get all transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'operation_id');
    }

    public function getSelectedExchangeProvider()
    {
        $transaction = $this->transactions()->whereHas('toAccount', function ($q) {
            return $q->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)->whereHas('provider', function ($query) {
                return $query->where('provider_type', Providers::PROVIDER_LIQUIDITY);
            });
        })->first();

        return $transaction->toAccount->provider ?? null;
    }

    public function getCardTransactionReference()
    {
        $transaction = $this->transactions()->where([
           'type' => TransactionType::CARD_TRX,
        ])->whereNotNull('tx_id')->first();

        return $transaction->tx_id ?? null;
    }


    public function getExchangeTransaction(): ?Transaction
    {
        return $this->transactions()->where('type', TransactionType::EXCHANGE_TRX)->first();
    }

    public function getCardTransaction()
    {
        return $this->transactions()->where('type', TransactionType::CARD_TRX)->first();
    }

    /**
     * @return mixed
     * get liquidity provider
     */
    public function getLiquidityProvider()
    {
        $toAccounts = $this->transactions->pluck('to_account');
        return Account::whereIn('id', $toAccounts)->where('owner_type', Providers::PROVIDER_LIQUIDITY)->first(); // todo ????
    }

    public function calculateOperationMaxAmount()
    {
        if ($this->received_amount) {
            $systemTransactionFromValterToCratos = $this->transactions()
                ->whereHas('toAccount', function ($q) {
                    $q->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                        ->where('currency', $this->received_amount_currency)
                        ->whereNull('c_profile_id')
                        ->whereNull('payment_provider_id');
                })
                ->where('type', TransactionType::SYSTEM_FEE)
                ->where('status', TransactionStatuses::SUCCESSFUL)
                ->first();
            if (!$systemTransactionFromValterToCratos) {
                return back()->with(['warning' => t('no_system_transaction')]);
            }
            if ($this->received_amount < $systemTransactionFromValterToCratos->trans_amount) {
                return back()->with(['warning' => t('operation_amount_less_system_amount')]);
            }
            return round($this->received_amount - $systemTransactionFromValterToCratos->trans_amount, 2);
        }
    }

    /**
     * @return mixed
     * get wallet provider
     */
    public function getWalletProvider()
    {
        $toAccounts = $this->transactions->pluck('to_account');
        return Account::whereIn('id', $toAccounts)->where('owner_type', Providers::PROVIDER_WALLET)->first(); // todo ????
    }

    /**
     * @return mixed
     */
    public function getPaymentProviderCountry(): ?string
    {
        return Account::where('payment_provider_id', $this->payment_provider_id)->first()->country ?? null;
    }

    public function getOperationCryptoCurrency(): ?string
    {
        if (!in_array($this->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
            $currency = $this->toAccount->currency ?? '';
        } else {
            $currency = $this->fromAccount->currency;
        }

        if(in_array($currency, array_keys(  Currency::ADDITIONAL_CURRENCY_NAMES))) {
            return $currency;
        }
        return '';
    }

    public function getOperationFiatCurrency(): ?string
    {
        if (in_array($this->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
            return $this->toAccount->currency;
        } elseif ($this->operation_type == OperationOperationType::TYPE_CARD) {
            return $this->from_currency;
        }
        return $this->fromAccount ? $this->fromAccount->currency : '';

    }

    public function getPaymentProvider(): ?PaymentProvider
    {
        return PaymentProvider::query()->where('id', $this->payment_provider_id)->first();
    }

    public function getOperationSystemAccount(): ?Account
    {
        $currency = in_array($this->operation_type, OperationOperationType::TYPES_WIRE_LAST) ? $this->to_currency : ($this->received_amount_currency ?? $this->from_currency);

        $accountType = in_array($this->operation_type, [
            OperationOperationType::TYPE_CARD,
            OperationOperationType::MERCHANT_PAYMENT,
            OperationOperationType::TYPE_CARD_PF
        ])
            ? AccountType::TYPE_WIRE_SEPA
            : OperationOperationType::ACCOUNT_OPERATION_TYPES[$this->operation_type];
        return Account::getSystemAccount($currency, $accountType);
    }

    /**
     * @return string
     */
    public function getIsVerifiedAttribute()
    {
        if ($this->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO) {
            $cryptoAccountDetail = $this->fromAccount ? $this->fromAccount->cryptoAccountDetail : null;
        }else {
            $cryptoAccountDetail = $this->toAccount ? $this->toAccount->cryptoAccountDetail : null;
        }
        return $cryptoAccountDetail && $cryptoAccountDetail->verified_at && $cryptoAccountDetail->isAllowedRisk();
    }

    public function getWithdrawalFeeAttribute(): ?string
    {
        $commission = $this->calculateFeeCommissions();

        if ($commission && $commission->percent_commission) {
            $min = t('min.');
            return "{$commission->percent_commission} % ({$min} {$commission->min_commission} {$commission->currency})";
        }

        return '-';
    }

    /**
     * @return string
     */
    public function getWithdrawalFeeForReportAttribute(): string
    {
        $commission = $this->calculateFeeCommissions();

        if ($commission && $commission->percent_commission) {
            $fee = number_format( ($this->amount * $commission->percent_commission / 100), 4);
            return "{$commission->percent_commission}% / {$fee}";
        }

        return '0% / 0' ;
    }

   public function getTopUpByCardFeeForReportAttribute(): string
    {
        $topUpCardCalculator = new TopUpCardCalculator($this);
        $commissionPercent = $topUpCardCalculator->getClientFeeFiatPercentCommission();
        $amountPercent = $topUpCardCalculator->getClientFeeFiatAmount();

         return ($commissionPercent && $amountPercent) ? $commissionPercent . '% / ' . $amountPercent . $this->from_currency : '0% / 0';
    }

    /**
     * @return string
     */
    public function getCryptoToCryptoFeeForReportAttribute(): string
    {
        /* @var PaymentFormCryptoService $paymentFormCryptoService */
        $paymentFormCryptoService = resolve(PaymentFormCryptoService::class);

        $calculateFee = $paymentFormCryptoService->calculateFee($this->paymentForm, $this->amount);

        if ($calculateFee && $calculateFee['feeAmount'] != 0) {
            return "{$calculateFee['feePercent']}% / " . generalMoneyFormat($calculateFee['feeAmount'], $this->to_currency);
        }

        return "0% / 0";
    }

    /**
     * @return |null
     */
    public function getBlockchainFeeAttribute()
    {
        $blockchainFeeTrx = $this->transactions()->where('type', TransactionType::BLOCKCHAIN_FEE)->first();

        if ($blockchainFeeTrx) {
            $blockchainFee = $blockchainFeeTrx->trans_amount;
        }

        return $blockchainFee ?? null;
    }

    /**
     * @return array|int|string|null
     */
    public function getOpTypeAttribute()
    {
        return $this->getOperationDetail();
    }

    /**
     * @return |null
     */
    public function getOperationDetailViewAttribute()
    {
        if (array_key_exists($this->operation_type, OperationType::OPERATION_DETAIL_VIEWS)) {
            return OperationType::OPERATION_DETAIL_VIEWS[$this->operation_type];
        }
        return null;
    }

    public function getBlockchainFeeFromRateAttribute()
    {
        if ($this->parent && in_array($this->operation_type, [OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF])) {
            $crypto = $cryptoAccountDetail->coin ?? null;
            $commissionsService = resolve(CommissionsService::class);
            /* @var CommissionsService $commissionsService */
            if ($crypto) {
                $commissionForBlockChainFee = $commissionsService->commissions(auth()->user()->cProfile->rate_template_id, \App\Enums\CommissionType::TYPE_CRYPTO, $crypto);
                $blockChainFee = ($commissionForBlockChainFee->blockchain_fee * OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[OperationOperationType::TYPE_CARD]);
            }

            return $blockChainFee ?? 0;
        }

        return null;
    }

    public function getFormattedBlockchainFeeFromRateAttribute()
    {
        return isset($this->blockchainFeeFromRate) ?  $this->blockchainFeeFromRate . ' ' . $this->to_currency : '';
    }

    /**
     * @param bool $forView
     * @return array|int|string|null
     */
    private function getOperationDetail($forView = false)
    {
        foreach (OperationType::VALUES as $key => $opType) {
            if (is_array($opType)) {
                foreach ($opType as $type) {
                    if ((int)$type === (int)$this->operation_type) {
                        return $forView ? $key : t(OperationType::OPERATION_DETAIL_VIEWS[$key]);
                    }
                }
            } elseif ((int)$opType === (int)$this->operation_type) {
                return $forView ? $key : t($opType);
            }
        }
        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function providerAccount()
    {
        return $this->belongsTo(Account::class, 'provider_account_id', 'id');
    }

    /**
     * @todo remove
     * @return mixed
     */
    public function getProviderAccount()
    {
        return Account::where('id', $this->provider_account_id)->first();
    }

    public function getLiquidityAccount()
    {
        $exchangeTrx = $this->transactions()->where('type', TransactionType::EXCHANGE_TRX)->first();

        if ($exchangeTrx) {
            $liquidityAccount = $exchangeTrx->toAccount;

            if ($liquidityAccount) {
                return $liquidityAccount;
            }
        }

        return null;
    }

    public function getExchangeFeeAmount()
    {
        $exchangeTrx = $this->transactions()->where('type', TransactionType::EXCHANGE_TRX)->first();
        /* @var Transaction $exchangeTrx*/
        if ($exchangeTrx) {
            $liquidityAccount = $exchangeTrx->fromAccount;
            $liquidityFeeAccount = $liquidityAccount->providerFeeAccount;

            $systemTrx = $this->transactions()
                ->where('from_account', $liquidityAccount->id)
                ->where('to_account', $liquidityFeeAccount->id)
                ->where('type', TransactionType::SYSTEM_FEE)
                ->first();

            if ($systemTrx) {
                return $systemTrx->trans_amount;
            }
        }

        return null;
    }

    public function calculateAmountInEuro()
    {
        if ($this->from_currency == Currency::CURRENCY_EUR) {
            $this->amount_in_euro = $this->amount;
        } else {
            $this->amount_in_euro = in_array($this->from_currency, Currency::FIAT_CURRENCY_NAMES) ?
                ExchangeRatesBitstampFacade::rate($this->amount)
                : KrakenFacade::getRateCryptoFiat($this->from_currency, Currency::CURRENCY_EUR, $this->amount);
        }
    }

    public function getCreditedAttribute()
    {
        if ($this->status == OperationStatuses::SUCCESSFUL) {
            if (in_array($this->operation_type, OperationOperationType::TYPES_CRYPTO_LAST)) {
                $type = TransactionType::CRYPTO_TRX;
            } elseif (in_array($this->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
                $type = TransactionType::BANK_TRX;
            }

            if (!empty($type)) {
                $transaction = $this->transactions()->where('type', $type)->latest()->first();
                if ($transaction) {
                    /* @var Transaction $transaction*/
                    $currency = $transaction->fromAccount->currency;
                    return formatMoney($transaction->trans_amount, $currency). ' ' . $currency;
                }
            }
        }
        return '-';
    }


    public function getTopUpFeeCommission()
    {
        $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$this->operation_type] ?? $this->operation_type;
        if (in_array($this->operation_type, OperationOperationType::FIAT_WALLET_OPERATIONS)) {
            $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$this->operation_type] ?? null;
        } else {
            $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$accountType];
        }
        $cProfile = $this->cProfile;
        $fromCurrency = $this->from_currency;
        if ($this->operation_type == OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO) {
            $fromCurrency = $this->to_currency;
        }

        return (new CommissionsService)->commissions($cProfile->rate_template_id, $commissionType, $fromCurrency, Commissions::TYPE_INCOMING);
    }

    public function getTopUpFeeAttribute()
    {
        $commission = $this->getTopUpFeeCommission();
        return $commission->percent_commission ?? null;
    }

    public function getTopUpFeeWithMinCommission()
    {
        $commission = $this->getTopUpFeeCommission();

        if (!$commission || is_null($commission->percent_commission)) {
            return '-';
        }

        return $commission->percent_commission . ' % ( Min. ' . $commission->min_commission . ' ' . $commission->currency . ')';
    }


    public function getCardTransferBlockchainFee()
    {
        $commissionsService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionsService */
        $commissions = $commissionsService->commissions($this->cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $this->toAccount->currency);

        return $commissions->blockchain_fee * OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[OperationOperationType::TYPE_TOP_UP_SEPA];

    }

    public function getExchangeFeeAttribute()
    {
        $commission = (new CommissionsService)->commissions(
            auth()->user()->cProfile->rate_template_id,
            CommissionType::TYPE_EXCHANGE,
            $this->from_currency,
            Commissions::TYPE_OUTGOING);
        if ($commission && $commission->percent_commission) {
            return $commission->percent_commission . '%';
        }
        return '-';
    }

    public function getWithdrawExchangeFeeAttribute()
    {
        $commission = (new CommissionsService)->commissions(
            auth()->user()->cProfile->rate_template_id,
            CommissionType::TYPE_EXCHANGE,
            $this->to_currency,
            Commissions::TYPE_INCOMING);
        if ($commission && $commission->percent_commission) {
            return $commission->percent_commission . '%';
        }
        return '-';
    }

    public function getCryptoFeeAttribute()
    {
        if ($this->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) {
            return $this->paymentForm->incoming_fee ?? 0;
        }

        $commission = (new CommissionsService)->commissions(
            auth()->user()->cProfile->rate_template_id,
            CommissionType::TYPE_CRYPTO,
            $this->to_currency,
            Commissions::TYPE_INCOMING);
        if ($commission && $commission->percent_commission) {
            return $commission->percent_commission;
        }
        return 0;
    }

    public function getCryptoFeeFormatted()
    {
        return $this->getCryptoFeeAttribute() . ' %';
    }

    public function isLimitsVerified($complianceLevel = null): bool
    {
        if ($this->status != OperationStatuses::PENDING) {
            return true;
        }
        $cProfile = $this->cProfile;
        $paymentFormService = resolve(PaymentFormsService::class);
        /* @var PaymentFormsService $paymentFormService*/
        $complianceLevel = $complianceLevel ?? $paymentFormService->getComplianceLevel($cProfile);
        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $complianceLevel)
            ->first();

        $receivedAmountForCurrentMonth = (new OperationService())->getCurrentMonthOperationsAmountSum($cProfile);
        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        if ($availableMonthlyAmount < 0) {
            $availableMonthlyAmount = 0;
        }


        if ($this->amount_in_euro > $limits->transaction_amount_max ||
            $this->amount_in_euro > $limits->monthly_amount_max ||
            $this->amount_in_euro > $availableMonthlyAmount ||
            $availableMonthlyAmount <= 0) {
            return false;
        }

        return true;
    }

    public function nextComplianceLevel(): int
    {
        $cProfile = $this->cProfile;
        if ( $cProfile->compliance_level == ComplianceLevel::VERIFICATION_LEVEL_2) {
            return ComplianceLevel::VERIFICATION_LEVEL_3;
        }

        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', ComplianceLevel::VERIFICATION_LEVEL_2)
            ->first();

        $receivedAmountForCurrentMonth = (new OperationService())->getCurrentMonthOperationsAmountSum($cProfile);
        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;


        if ($this->amount_in_euro > $limits->transaction_amount_max ||
            $this->amount_in_euro > $limits->monthly_amount_max ||
            $this->amount_in_euro > $availableMonthlyAmount ||
            $availableMonthlyAmount <= 0) {
            return ComplianceLevel::VERIFICATION_LEVEL_3;
        }

        return ComplianceLevel::VERIFICATION_LEVEL_2;
    }

    public function getWithdrawCryptoCommissionsFromClientAccount()
    {
        $commission = $this->getWithdrawCryptoCommissions();

        if ($commission) {
            $operationFee['blockchainFee'] = ($commission->blockchain_fee * OperationOperationType::BLOCKCHAIN_FEE_COUNT_WITHDRAW_CRYPTO) .' '. $this->from_currency;
            $operationFee['withdrawalFee'] = ($commission->percent_commission ?? 0) . ' % ';
            $operationFee['withdrawalFee'] .= $commission->min_commission ? '('. t('min.') . ' '. $commission->min_commission .'  '. $commission->currency . ')' : '';
            $operationFee['walletServiceFee'] = 0;
        }

        return $operationFee ?? null;
    }

    public function getWithdrawCryptoCommissions()
    {
        $toServiceTransaction = $this->transactions()
            ->where('type', TransactionType::SYSTEM_FEE)
            ->where('status', TransactionStatuses::SUCCESSFUL)
            ->where('from_account', $this->from_account)
            ->whereNotNull('from_commission_id')->first();

        return $toServiceTransaction->fromCommission ?? null;
    }

    public function getAmountAttribute($value)
    {
        return floatval($value);
    }

    public function getAmountInEuroAttribute($value)
    {
        return floatval($value);
    }

    public function getReceivedAmountAttribute($value)
    {
        return floatval($value);
    }

    public function getExchangeRateAttribute($value)
    {
        return floatval($value);
    }

    public function getClientRateAttribute($value)
    {
        return floatval($value);
    }

    public function getOperationType() :string
    {
        if ($this->operation_type == OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT || $this->operation_type == OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA) {
            $type = OperationType::getName(OperationType::WITHDRAW_WIRE) ?? '-';
        } else if ($this->operation_type == OperationOperationType::TYPE_TOP_UP_SWIFT || $this->operation_type == OperationOperationType::TYPE_TOP_UP_SEPA) {
            $type = OperationType::getName(OperationType::TOP_UP_WIRE) ?? '-';
        } else if ($this->operation_type == OperationOperationType::TYPE_SYSTEM_FEE_WITHDRAW) {
            $type = OperationType::getName(OperationType::SYSTEM_FEE_WITHDRAW)  ?? '-';
        } elseif($this->operation_type == OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE) {
            $type = OperationType::getName(OperationType::FIAT_TOP_UP_BY_WIRE)  ?? '-';
        } else {
            $type = OperationOperationType::getName($this->operation_type) ?? '-';
        }
        return $type ?? '-';
    }

    public function getOperationMethodName() :string
    {
        if ($this->operation_type == OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE
            || $this->operation_type == OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET
        ) {
            $operationAdditionalData = json_decode($this->additional_data, true);
            $operationType = $operationAdditionalData['payment_method'] ?? null;
        } else {
            $operationType = $this->operation_type;
        }
        if (in_array($operationType, [OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT, OperationOperationType::TYPE_TOP_UP_SWIFT])) {
            $type = t('enum_account_type_swift');
        } else if (in_array($operationType, [OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, OperationOperationType::TYPE_TOP_UP_SEPA])) {
            $type = t('enum_account_type_sepa');
        } else {
            $type = OperationOperationType::getName($this->operation_type) ?? '-';
        }
        return $type ?? '-';
    }

    public function getCryptoFixedCommission(?Commission $commission): ?float
    {
        if (!$commission) {
            return null;
        }
        return $this->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO ? $commission->refund_transfer : $commission->fixed_commission;
    }

    public function getCryptoPercentCommission(?Commission $commission): ?float
    {
        if (!$commission) {
            return null;
        }
        return $this->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO ? $commission->refund_transfer_percent : $commission->percent_commission;
    }

    public function pendingCrypto(): ?Transaction
    {
        return $this->transactions()->where('type', TransactionType::CRYPTO_TRX)
            ->where('status', TransactionStatuses::PENDING)
            ->latest()
            ->first();
    }

    public function getLastTransactionByType(int $type): ?Transaction
    {
        return $this->transactions()->where('type', $type)->latest()->first();
    }

    public function getCryptoExplorerUrl(): ?string
    {
        $condition = ['type' => TransactionType::CRYPTO_TRX];
        if ($this->operation_type == OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA ||
            $this->operation_type == OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT) {
            $condition['from_account'] = $this->from_account;
        } else {
            $condition['to_account'] = $this->to_account;
        }
        $txTransaction = $this->transactions()->where($condition)->first();
        return $txTransaction ? $txTransaction->getTxExplorerUrl() : null;
    }


    public function getCardProviderAccount(): ?Account
    {
        $projectId = $this->cProfile->cUser->project_id;
        return Account::getProviderAccount($this->from_currency, Providers::PROVIDER_CARD, null, null, $projectId);
    }

    public function getCardProviderIdAccountFromCardTransaction(): ?string
    {
        return $this->transactions()->where('type', TransactionType::CARD_TRX)->first()->to_account ?? null;
    }


    public function getTransactionByAccount(int $type, int $status = TransactionStatuses::SUCCESSFUL, string $fromAccountId = null, string $toAccountId = null)
    {
        $transaction = $this->transactions()->where([
            'type' => $type,
            'status' => $status
        ]);

        if ($fromAccountId) {
            $transaction->where('from_account', $fromAccountId);
        }
        if ($toAccountId) {
            $transaction->where('to_account', $toAccountId);
        }

        return $transaction->first();
    }


    public function getAllTransactionsByProviderTypesQuery($crypto = false, $fromAccountType = null, $toAccountType = null)
    {
        $currencies = $crypto ? Currency::getList() : Currency::FIAT_CURRENCY_NAMES;

        $query = $this->transactions()
            ->where('type', TransactionType::SYSTEM_FEE)
            ->whereHas('fromAccount', function ($q) use ($currencies, $fromAccountType) {
                $q->whereIn('currency', $currencies);
                if (isset($fromAccountType)) {
                    $q->whereHas('provider', function ($q) use($fromAccountType) {
                        $q->where('provider_type', $fromAccountType);
                    });
                }
            });
        if ($toAccountType) {
            $query->whereHas('toAccount', function ($q) use ($toAccountType) {
                $q->whereHas('provider', function ($q) use ($toAccountType) {
                    $q->where('provider_type', $toAccountType);
                });
            });
        }

        return $query;
    }

    public function calculateFeeCommissions()
    {
        $commissionsService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionsService */
        $cProfile = $this->cProfile;

        if ($this->operation_type == OperationOperationType::TYPE_WITHDRAW_CRYPTO) {
            $commissionType = CommissionType::TYPE_CRYPTO;
            $currency = $this->from_currency;
        } else {
            if (!$this->toAccount) {
                return null;
            }
            $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$this->toAccount->account_type] ?? null;
            if (!$commissionType) {
                return null;
            }
            $currency = $this->to_currency;
        }
        // @todo get commission from transactions
        return $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $currency, Commissions::TYPE_OUTGOING);

    }

    public function stepInfo()
    {
        if ($this->step == TransactionSteps::TRX_STEP_REFUND) {
            return [
                'stepState_0' => 'step-completed',
                'stepState_1' => 'step-next',
                'stepState_2' => 'step-next',
                'stepState_3' => 'step-next',
                'stepState_4' => 'step-next',
            ];
        }

        if ($this->status == OperationStatuses::SUCCESSFUL) {
            return [
                'stepState_0' => 'step-completed',
                'stepState_1' => 'step-completed',
                'stepState_2' => 'step-completed',
                'stepState_3' => 'step-completed',
                'stepState_4' => 'step-completed',
            ];
        }

        if ($this->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO) {
            return $this->status == OperationStatuses::SUCCESSFUL ? ['stepState_0' => 'step-completed'] : ['stepState_0' => 'step-next'];
        } elseif (in_array($this->operation_type , OperationOperationType::TYPES_TOP_UP)
            || in_array($this->operation_type , [OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA, OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT])) {
            if ($this->step == TransactionSteps::TRX_STEP_ONE) {
                $bankTrxCount = $this->transactions()->where('type', TransactionType::BANK_TRX)->count();
                $steps = [
                    'stepState_2' => 'step-next',
                    'stepState_3' => 'step-next',
                    'stepState_4' => 'step-next',
                ];
                if ($bankTrxCount == 1) {
                    $steps['stepState_0'] = 'step-completed';
                    $steps['stepState_1'] = 'step-current';
                } else {
                    $steps['stepState_0'] = 'step-current';
                    $steps['stepState_1'] = 'step-next';
                }
                return $steps;
            } elseif ($this->step == TransactionSteps::TRX_STEP_TWO) {
                return [
                    'stepState_0' => 'step-completed',
                    'stepState_1' => 'step-completed',
                    'stepState_2' => 'step-current',
                    'stepState_3' => 'step-next',
                    'stepState_4' => 'step-next',
                ];
            } elseif ($this->step == TransactionSteps::TRX_STEP_THREE) {
                return [
                    'stepState_0' => 'step-completed',
                    'stepState_1' => 'step-completed',
                    'stepState_2' => 'step-completed',
                    'stepState_3' => 'step-current',
                    'stepState_4' => 'step-next',
                ];
            } elseif ($this->step == TransactionSteps::TRX_STEP_FOUR) {
                $cryptoTrxCount = $this->transactions()->where('type', TransactionType::CRYPTO_TRX)->where('status', TransactionStatuses::SUCCESSFUL)->count();
                $steps = [
                    'stepState_0' => 'step-completed',
                    'stepState_1' => 'step-completed',
                    'stepState_2' => 'step-completed',
                    'stepState_3' => 'step-completed',
                ];
                if ($cryptoTrxCount == 1) {
                    $steps['stepState_4'] = 'step-current';
                }elseif ($cryptoTrxCount == 2) {
                    $steps['stepState_4'] = 'step-completed';
                }
                return $steps;
            }
        } elseif (in_array($this->operation_type, OperationOperationType::TYPES_WIRE_LAST)) {
             for ($i = TransactionSteps::TRX_STEP_ONE; $i < ($this->step) ; $i++) {
                 $name = 'stepState_' . $i;
                 $steps[$name] = 'step-completed';
             }
             $name = 'stepState_' . ($this->step);
             $steps[$name] = 'step-current';
             for ($i = $this->step + 1; $i <= TransactionSteps::TRX_STEP_FIVE; $i++) {
                 $name = 'stepState_' . $i;
                 $steps[$name] = 'step-next';
             }
             return $steps;
        }

        for ($i = TransactionSteps::TRX_STEP_ONE; $i < $this->step ; $i++) {
            $name = 'stepState_' . $i;
            $steps[$name] = 'step-completed';
        }
        $name = 'stepState_' . $this->step;
        $steps[$name] = 'step-current';
        for ($i = $this->step + 1 ; $i <= TransactionSteps::TRX_STEP_FIVE; $i++) {
            $name = 'stepState_' . $i;
            $steps[$name] = 'step-next';
        }

        return $steps;
    }

    public function getPaymentTransaction()
    {
        return $this->transactions()->where('type', TransactionType::BANK_TRX)->first();
    }

    /**
     * @return string
     */
    public function getTopUpFiatFeeForReportAttribute(): string
    {
        $commissionsService = resolve(CommissionsService::class);
        /* @var CommissionsService $commissionsService */
        $cProfile = $this->cProfile;

        $commissionType = CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE;
        $currency = $this->from_currency;
        $commission = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $currency, Commissions::TYPE_INCOMING);

        if ($commission && $commission->percent_commission) {
            $fee = number_format( ($this->amount * $commission->percent_commission / 100), 4);
            return "{$commission->percent_commission}% / {$fee} {$currency}";
        }

        return '0% / 0' ;
    }

}
