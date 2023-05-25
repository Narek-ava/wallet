<div class="display-none details{{ $operation->id }} col-md-12">
    <hr class="hr-style">
    <div class="col-md-12 mt-3 fs14">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('transaction_history_detail_withdrawal_type') }}</span><br>{{ \App\Enums\OperationOperationType::getName($operation->operation_type) }}<br><br>
                <span class="activeLink">{{ t('transaction_history_detail_cryptocurrency') }}</span><br>{{ $operation->toAccount->currency }}<br><br>
                <span class="activeLink">{{ t('transaction_history_detail_amount_in_eur') }}</span><br>{{ eur_format($operation->amount_in_euro ?? null) }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('transaction_history_detail_withdrawal_fee') }}</span><br>{{ $operation->getWithdrawCryptoCommissionsFromClientAccount()['withdrawalFee'] ?? 0 }}<br><br>
                <span class="activeLink">{{ t('transaction_history_detail_blockchain_fee') }}</span><br>{{ $operation->getWithdrawCryptoCommissionsFromClientAccount()['blockchainFee'] ?? 0 }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('transaction_history_detail_sending_amount') }}</span><br>{{ generalMoneyFormat($operation->amount, $operation->fromAccount->currency ?? '') }}<br><br>
                <span class="activeLink">{{ t('transaction_history_detail_wallet_service_fee') }}</span><br> 0 <br><br>
            </div>
            <div class="col-md-4 mt-4 breakWord">
                <span class="activeLink">{{ t('transaction_history_detail_wallet_verified') }}</span><br>{{ $operation->getIsVerifiedAttribute() ? t('yes') : t('no') }}<br><br>
                <span class="activeLink">{{ t('transaction_history_detail_to_wallet') }}</span><br>{{ $operation->toAccount->cryptoAccountDetail->address ?? '' }}<br><br>
                @if ($operation->getCryptoExplorerUrl())
                    <a href="{{ $operation->getCryptoExplorerUrl() }}" target="_blank">{{ t('ui_view_transaction_details') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
