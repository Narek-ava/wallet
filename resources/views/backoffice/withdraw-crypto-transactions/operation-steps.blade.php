<div class="row col-md-3 pt-4 pl-3 wallet-instruction-items pr-3 pr-md-0 mt-3">
    <div class="col-md pl-0 pr-0">
        <div class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_0'] }}">
            <div class="col-12">
                <div class="textBold activeLevel"></div>
                <div class="textBold inactiveLevel"></div>
                <h2 class="mb-3">{{ t('ui_step_first_withdraw_crypto') }}</h2>
            </div>
            <div class="col-12">
                @if($operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO)
                    <p>{{ t('ui_withdraw_crypto_trx_step_one') }}</p>
                @elseif($operation->operation_type == \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO)
                    <p>{{ t('ui_top_up_crypto_trx_step_one') }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
        <div class="dashedBlock"></div>
    </div>
</div>
