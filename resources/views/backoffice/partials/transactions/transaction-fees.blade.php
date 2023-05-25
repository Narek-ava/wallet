@if( in_array($operation->operation_type, [
                \App\Enums\OperationOperationType::TYPE_CARD,
                \App\Enums\OperationOperationType::TYPE_TOP_UP_SEPA ,
                \App\Enums\OperationOperationType::TYPE_TOP_UP_SWIFT,
                \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO,
                \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA,
                \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT,
                \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF,
                \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF,
                \App\Enums\OperationOperationType::TYPE_CARD_PF,
                \App\Enums\OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET,
                \App\Enums\OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT,
                \App\Enums\OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE,
                \App\Enums\OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO,
                ]) &&
 $operation->status != \App\Enums\OperationStatuses::PENDING)
    <div class="row pt-5 pl-2">
        <div class="col-md-3 text-left p-4 ml-2 common-shadow-theme">
            <h5> {{ t('client_fee') }} </h5> <br>
            <h6 class="d-block">{{ t('ui_fiat') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getClientFeeFiatAmount(), $operation->getOperationFiatCurrency(), true) }} {{' (' . ($operationCalculator->getClientFeeFiatPercentCommission() ?? '-') . '%) '}}</p>
            <h6 class="d-block">{{ t('ui_crypto') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getClientFeeCryptoAmount(), $operation->getOperationCryptoCurrency(), true) }} </p>
        </div>

        <div class="col-md-3 text-left p-4 ml-4 common-shadow-theme">
            <h5> {{ t('provider_fee') }} </h5> <br>
            <h6 class="d-block">{{ t('ui_card_provider_fee') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getCardProviderFeeAmount(), $operation->getOperationFiatCurrency(), true) }} {{' (' . ($operationCalculator->getProviderCardProviderFeePercentCommission() ?? '-') . '%) '}}</p>
            <h6 class="d-block">{{ t('ui_liquidity_provider_fee') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getLiquidityProviderFeeAmountFiat(), $operation->getOperationFiatCurrency(), true) }} </p>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getLiquidityProviderFeeAmountCrypto(), $operation->getOperationCryptoCurrency(), true) }} {{ ' (' . ($operationCalculator->getProviderLiquidityFeeCryptoPercentCommission() ?? '-') . '%) '}}</p>
            <h6 class="d-block">{{ t('ui_wallet_provider_fee') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getWalletProviderFeeAmount(), $operation->getOperationCryptoCurrency(), true) }} {{ ' (' . ($operationCalculator->getProviderWalletFeePercentCommission() ?? '-') . '%) ' }}</p>
            <h6 class="d-block">{{ t('ui_payment_provider_fee') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getPaymentProviderFeeAmount(), $operation->getOperationFiatCurrency(), true) }} {{' (' . ($operationCalculator->getProviderPaymentProviderFeePercentCommission() ?? '-') . '%) '}}</p>
        </div>

        <div class="col-md-3 text-left p-4 ml-4 common-shadow-theme">
            <h5> {{ t('cratos_fee') }} </h5> <br>
            <h6 class="d-block">{{ t('ui_fiat') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getCratosFeeAmountFiat(), $operation->getOperationFiatCurrency(), true) }} </p>
            <h6 class="d-block">{{ t('ui_crypto') }}</h6>
            <p class="d-block">{{ generalMoneyFormat($operationCalculator->getCratosFeeAmountCrypto(), $operation->getOperationCryptoCurrency(), true) }} </p>
            <br>
        </div>

    </div>
@endif
