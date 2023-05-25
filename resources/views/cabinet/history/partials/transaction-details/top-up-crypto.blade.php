<div class="display-none details{{ $operation->id }} col-md-12">
    <hr class="hr-style">
    <div class="col-md-12 mt-3 fs14">
        <div class="row">
            @php($isPaymentFormOperation = ($operation->parent && in_array($operation->operation_type, [\App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]))
                || $operation->operation_type == \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
            <div class="col-md-{{ $isPaymentFormOperation ? 2 : 4 }}"></div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('top_up_deposit_type') }}</span><br>{{ \App\Enums\OperationOperationType::getName($operation->operation_type) }}<br><br>
                <span class="activeLink">{{ t('top_up_deposit_cryptocurrency') }}</span><br>{{ $operation->toAccount->currency }}<br><br>
                <span class="activeLink">{{ t('top_up_amount_euro') }}</span><br> {{ eur_format($operation->amount_in_euro ?? null) }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('top_up_fee') }}</span><br>
                @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
                    {{ $operation->getCryptoFeeFormatted() }}
                @else
                    {{ $isPaymentFormOperation ? $operation->getCryptoFeeFormatted() . ' + ' . $operation->formattedBlockchainFeeFromRate : $operation->getCryptoFeeFormatted() }}
                @endif
                <br><br>
                <span class="activeLink">{{ t('top_up_wallet_verified') }}</span><br>{{ $operation->isVerified ? t('yes') : t('no') }}<br><br>
                @if($isPaymentFormOperation && isset($operation->parent->parent))
                    <span class="activeLink">{{ t('initial_amount') }}</span><br>{{ $operation->parent->parent->amount . ' ' . $operation->parent->parent->from_currency }}<br><br>
                @endif
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('top_up_credited') }}</span><br>{{ $operation->getLastTransactionByType(\App\Enums\TransactionType::CRYPTO_TRX)->trans_amount ?? 0 }}<br><br>
                <span class="activeLink">{{ t('top_up_wallet_service_fee') }}</span><br>0<br><br>
            </div>
            @if(!$isPaymentFormOperation)
                <div class="col-md-2"></div>
                <div class="col-md-4"></div>
            @endif
            <div class="col-md-{{ $isPaymentFormOperation ? 2 : 6 }} mt-4 breakWord" style="overflow: unset">
                <span class="activeLink">{{ t('top_up_from_wallet') }}</span><br>{{ $operation->fromAccount->cryptoAccountDetail->address ?? '' }}<br><br>
                @if ($operation->getCryptoExplorerUrl())
                    <a href="{{ $operation->getCryptoExplorerUrl() }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                @endif
            </div>
            @if($isPaymentFormOperation)
                @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
                    <div class="col-md-2 mt-4">
                        <span class="activeLink">{{ t('payer_name') }}</span><br> {{ $operation->paymentFormAttempt->getPayerFullName() }} <br><br>
                        <span class="activeLink">{{ t('payer_phone_number') }}</span><br>{{ $operation->paymentFormAttempt->phone ?? '-' }}<br><br>
                        <span class="activeLink">{{ t('payer_email') }}</span><br>{{ $operation->paymentFormAttempt->email ?? '-' }}<br><br>
                    </div>
                @else
                    @php($payerCProfile = $operation->parent->cProfile)
                    <div class="col-md-2 mt-4">
                        <span class="activeLink">{{ t('payer_name') }}</span><br> {{ $payerCProfile->getFullName() }} <br><br>
                        <span class="activeLink">{{ t('payer_phone_number') }}</span><br>{{ $payerCProfile->cUser->phone ?? '-' }}<br><br>
                        <span class="activeLink">{{ t('payer_email') }}</span><br>{{ $payerCProfile->cUser->email ?? '-' }}<br><br>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
