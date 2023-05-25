<?php
namespace App\Enums;

class OperationType extends Enum
{
    const ALL = 1;
    const TOP_UP_WIRE = 2;
    const TOP_UP_CRYPTO = 3;
    const TOP_UP_CARD = 4;
    const EXCHANGE = 5;
    const WITHDRAW_CRYPTO = 7;
    const WITHDRAW_WIRE = 8;
    const MERCHANT_PAYMENT = 9;

    const TOP_UP_CARD_PF = 10;
    const TOP_UP_CRYPTO_PF = 11;
    const WITHDRAW_CRYPTO_PF = 12;
    const TOP_UP_CRYPTO_EXTERNAL_PF = 13;
    const TOP_UP_CRYPTO_TO_CRYPTO_PF = 14;

    const CARD_ORDER_BY_WIRE = 15;
    const SYSTEM_FEE_WITHDRAW = 16;

    const TOP_UP_FROM_FIAT = 17;
    const WITHDRAW_WIRE_FIAT = 18;
    const TYPE_WITHDRAW_FROM_FIAT_WALLET = 19;
    const FIAT_TOP_UP_BY_WIRE = 20;
    const TYPE_WITHDRAW_TO_FIAT_WALLET = 21;
    const TYPE_BUY_CRYPTO_FROM_FIAT = 22;
    const CARD_ORDER_BY_CRYPTO = 23;
    const CARD_ORDER_BY_BANK_CARD = 24;





    const NAMES = [
        self::ALL => 'operation_type_all',
        self::TOP_UP_WIRE => 'operation_type_top_up_wire',
        self::TOP_UP_CRYPTO => 'operation_type_top_upcrypto',
        self::TOP_UP_CARD => 'operation_type_card',
        self::MERCHANT_PAYMENT => 'operation_type_merchant_payment',
        self::WITHDRAW_CRYPTO => 'operation_type_crypto_wallet',
        self::WITHDRAW_WIRE => 'operation_type_send_wire',

        self::TOP_UP_CARD_PF => 'operation_type_card_pf',
        self::TOP_UP_CRYPTO_PF => 'operation_type_top_up_crypto_pf',
        self::WITHDRAW_CRYPTO_PF => 'operation_type_withdraw_crypto_pf',
        self::TOP_UP_CRYPTO_TO_CRYPTO_PF => 'operation_type_crypto_to_crypto_pf',
        self::TOP_UP_CRYPTO_EXTERNAL_PF => 'operation_operation_type_top_up_crypto_external_pf',
        self::CARD_ORDER_BY_WIRE => 'operation_operation_type_card_order_by_wire',
        self::CARD_ORDER_BY_BANK_CARD => 'operation_operation_type_card_order_by_bank_card',
        self::CARD_ORDER_BY_CRYPTO => 'operation_operation_type_card_order_by_crypto',
        self::SYSTEM_FEE_WITHDRAW => 'operation_operation_type_system_fee_withdraw',
        self::WITHDRAW_WIRE_FIAT => 'operation_operation_type_withdraw_from_fiat_wallet',
        self::FIAT_TOP_UP_BY_WIRE => 'operation_operation_type_top_up_for_fiat_wallet',
        self::TYPE_WITHDRAW_TO_FIAT_WALLET => 'operation_operation_type_withdraw_to_fiat_wallet',
        self::TYPE_BUY_CRYPTO_FROM_FIAT => 'operation_operation_type_top_up_from_fiat',
    ];

    const WITH_PROVIDER_TYPES = [
        self::ALL => 'operation_type_all',
        self::TOP_UP_WIRE => 'operation_type_top_up_wire',
        self::TOP_UP_CRYPTO => 'operation_type_top_upcrypto',
        self::TOP_UP_CARD => 'operation_type_card',
        self::WITHDRAW_CRYPTO => 'operation_type_crypto_wallet',
        self::WITHDRAW_WIRE => 'operation_type_send_wire',
        OperationOperationType::TYPE_PROVIDER_WITHDRAW => 'operation_type_provider_withdraw',
        OperationOperationType::TYPE_PROVIDER_TOP_UP => 'operation_type_provider_top_up',
    ];

    const VALUES = [
        self::ALL => [
            OperationOperationType::TYPE_TOP_UP_SEPA,
            OperationOperationType::TYPE_TOP_UP_SWIFT,
            OperationOperationType::TYPE_PROVIDER_WITHDRAW,
            OperationOperationType::TYPE_PROVIDER_TOP_UP,
            // TODO add from OperationOperationType values
            self::TOP_UP_CRYPTO,
            self::TOP_UP_CARD,
            self::MERCHANT_PAYMENT,
            self::EXCHANGE,
            self::WITHDRAW_CRYPTO,
            self::WITHDRAW_WIRE,
            self::TOP_UP_CARD_PF,
            self::TOP_UP_CRYPTO_PF,
            self::WITHDRAW_CRYPTO_PF,
            self::TOP_UP_CRYPTO_TO_CRYPTO_PF,
            self::CARD_ORDER_BY_WIRE,
            self::CARD_ORDER_BY_CRYPTO,
            self::SYSTEM_FEE_WITHDRAW,
            self::TYPE_WITHDRAW_TO_FIAT_WALLET,
        ],
        self::FIAT_TOP_UP_BY_WIRE => OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE,
        self::WITHDRAW_WIRE_FIAT => OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET,
        self::TYPE_WITHDRAW_TO_FIAT_WALLET => OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO,
        self::TOP_UP_WIRE => [
            OperationOperationType::TYPE_TOP_UP_SEPA,
            OperationOperationType::TYPE_TOP_UP_SWIFT,
        ],
        self::CARD_ORDER_BY_WIRE =>[
            OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA,
            OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT
        ],
        self::CARD_ORDER_BY_BANK_CARD => OperationOperationType::TYPE_CARD_ORDER_PAYMENT_BANK_CARD,
        self::CARD_ORDER_BY_CRYPTO => OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO,
        // TODO add from OperationOperationType values
        self::TOP_UP_CRYPTO => OperationOperationType::TYPE_TOP_UP_CRYPTO,
        self::TOP_UP_CARD => OperationOperationType::TYPE_CARD,
        self::MERCHANT_PAYMENT => OperationOperationType::MERCHANT_PAYMENT,
        self::TOP_UP_CARD_PF => OperationOperationType::TYPE_CARD_PF,
        self::TOP_UP_CRYPTO_PF => OperationOperationType::TYPE_TOP_UP_CRYPTO_PF,
        self::TOP_UP_CRYPTO_EXTERNAL_PF => OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF,
        self::TOP_UP_CRYPTO_TO_CRYPTO_PF => OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF,
        self::WITHDRAW_CRYPTO_PF => OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF,
        self::EXCHANGE => OperationOperationType::TYPE_EXCHANGE,
        self::WITHDRAW_CRYPTO => OperationOperationType::TYPE_WITHDRAW_CRYPTO,
        self::WITHDRAW_WIRE => [
            OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA,
            OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT,
        ],
        self::CARD_ORDER_BY_WIRE => [
            OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA,
            OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT,
        ],
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET => OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET,
        self::SYSTEM_FEE_WITHDRAW => OperationOperationType::TYPE_SYSTEM_FEE_WITHDRAW,
        self::TYPE_BUY_CRYPTO_FROM_FIAT => OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT
//        OperationOperationType::TYPE_PROVIDER_WITHDRAW => OperationOperationType::TYPE_PROVIDER_WITHDRAW,
//        OperationOperationType::TYPE_PROVIDER_TOP_UP =>  OperationOperationType::TYPE_PROVIDER_TOP_UP,
    ];

    const OPERATION_DETAIL_VIEWS = [
        OperationOperationType::TYPE_TOP_UP_SEPA => 'cabinet.history.partials.transaction-details.top-up-wire',
        OperationOperationType::TYPE_TOP_UP_SWIFT => 'cabinet.history.partials.transaction-details.top-up-wire',
        OperationOperationType::TYPE_CARD => 'cabinet.history.partials.transaction-details.top-up-card',
        OperationOperationType::MERCHANT_PAYMENT => 'cabinet.history.partials.transaction-details.top-up-card',
        OperationOperationType::TYPE_WITHDRAW_CRYPTO => 'cabinet.history.partials.transaction-details.withdrawal-crypto',
        OperationOperationType::TYPE_TOP_UP_CRYPTO => 'cabinet.history.partials.transaction-details.top-up-crypto',
        OperationOperationType::TYPE_EXCHANGE => '',
        OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA => 'cabinet.history.partials.transaction-details.withdraw-wire',
        OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT => 'cabinet.history.partials.transaction-details.withdraw-wire',

        OperationOperationType::TYPE_CARD_PF => 'cabinet.history.partials.transaction-details.top-up-card',
        OperationOperationType::TYPE_TOP_UP_CRYPTO_PF => 'cabinet.history.partials.transaction-details.top-up-crypto',
        OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF => 'cabinet.history.partials.transaction-details.withdrawal-crypto',
        OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF => 'cabinet.history.partials.transaction-details.top-up-crypto',

        OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF => 'cabinet.history.partials.transaction-details.top-up-crypto',
        OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA => 'cabinet.history.partials.transaction-details.card-order',
        OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT => 'cabinet.history.partials.transaction-details.card-order',
        OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO => 'cabinet.history.partials.transaction-details.card-order',
        OperationOperationType::TYPE_CARD_ORDER_PAYMENT_BANK_CARD => 'cabinet.history.partials.transaction-details.card-order',

        OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE => 'cabinet.history.partials.transaction-details.top-up-fiat-wallet-by-wire',
        OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET => 'cabinet.history.partials.transaction-details.withdraw-from-fiat-wire',
        OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT => 'cabinet.history.partials.transaction-details.buy-crypto-from-fiat',
        OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO => 'cabinet.history.partials.transaction-details.buy-fiat-from-crypto',
    ];

    const MERCHANT_PAYMENT_TYPES = [
        self::MERCHANT_PAYMENT,
        self::TOP_UP_CARD_PF,
        self::TOP_UP_CRYPTO_PF,
        self::WITHDRAW_CRYPTO_PF,
        self::TOP_UP_CRYPTO_EXTERNAL_PF,
        self::TOP_UP_CRYPTO_TO_CRYPTO_PF,
    ];

    const FIAT_PAYMENT_TYPES = [
        self::TOP_UP_FROM_FIAT,
        self::WITHDRAW_WIRE_FIAT,
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET,
        self::FIAT_TOP_UP_BY_WIRE,
    ];

}
