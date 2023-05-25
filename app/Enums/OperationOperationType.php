<?php


namespace App\Enums;


use App\Models\Account;
use function Symfony\Component\String\s;


//@todo
// add in filters
class OperationOperationType extends Enum
{
    const TYPE_TOP_UP_SEPA = 1;
    const TYPE_TOP_UP_SWIFT = 2;
    const TYPE_CARD = 3;
    const TYPE_WITHDRAW_CRYPTO = 4;
    const TYPE_TOP_UP_CRYPTO = 6;
    const TYPE_EXCHANGE = 5;
    const TYPE_WITHDRAW_WIRE_SEPA = 8;
    const TYPE_WITHDRAW_WIRE_SWIFT = 9;
    const TYPE_PROVIDER_WITHDRAW = 10;
    const TYPE_PROVIDER_TOP_UP = 11;
    const MERCHANT_PAYMENT = 12;

    const TYPE_CARD_PF = 13;
    const TYPE_TOP_UP_CRYPTO_PF = 14;
    const TYPE_WITHDRAW_CRYPTO_PF = 15;
    const TYPE_TOP_UP_CRYPTO_EXTERNAL_PF = 16;
    const TYPE_CRYPTO_TO_CRYPTO_PF = 17;
    const TYPE_SYSTEM_FEE_WITHDRAW = 18;

    const TYPE_CARD_ORDER_PAYMENT_CRYPTO = 19;
    const TYPE_CARD_ORDER_PAYMENT_BANK_CARD = 20;
    const TYPE_CARD_ORDER_PAYMENT_SEPA = 21;
    const TYPE_CARD_ORDER_PAYMENT_SWIFT = 22;

    const TYPE_BUY_CRYPTO_FROM_FIAT = 23;
    const TYPE_WITHDRAW_FROM_FIAT_WALLET = 24;
    const TYPE_BUY_FIAT_FROM_CRYPTO = 25;
    const TYPE_FIAT_TOP_UP_BY_WIRE = 26;

    const BLOCKCHAIN_FEE_COUNT_TOP_UP_WIRE = 2;
    const BLOCKCHAIN_FEE_COUNT_WITHDRAW_CRYPTO = 1;
    const BLOCKCHAIN_FEE_COUNT_WITHDRAW_WIRE = 1;
    const BLOCKCHAIN_FEE_COUNT_WITHDRAW_FIAT = 1;
    const BLOCKCHAIN_FEE_COUNT_TOP_UP_CARD = 2;
    const BLOCKCHAIN_FEE_COUNT_MERCHANT_PAYMENT = 2;
    const BLOCKCHAIN_FEE_COUNT_TYPE_CARD_PF = 2;

    const API_WIRE_TYPE_SWIFT = 0;
    const API_WIRE_TYPE_SEPA = 1;

    const ACCOUNT_OPERATION_TYPES = [
        self::TYPE_TOP_UP_SWIFT => AccountType::TYPE_WIRE_SWIFT,
        self::TYPE_TOP_UP_SEPA => AccountType::TYPE_WIRE_SEPA,
        self::TYPE_TOP_UP_CRYPTO => AccountType::TYPE_CRYPTO,
        self::TYPE_WITHDRAW_CRYPTO => AccountType::TYPE_CRYPTO,
        self::TYPE_WITHDRAW_WIRE_SEPA => AccountType::TYPE_WIRE_SEPA,
        self::TYPE_WITHDRAW_WIRE_SWIFT => AccountType::TYPE_WIRE_SWIFT,
        self::TYPE_CARD => AccountType::TYPE_CARD,
        self::MERCHANT_PAYMENT =>  AccountType::TYPE_CARD,
        self::TYPE_CARD_PF =>  AccountType::TYPE_CARD,
        self::TYPE_TOP_UP_CRYPTO_PF =>  AccountType::TYPE_CRYPTO,
        self::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF =>  AccountType::TYPE_CRYPTO,
        self::TYPE_WITHDRAW_CRYPTO_PF =>  AccountType::TYPE_CRYPTO,
        self::TYPE_CRYPTO_TO_CRYPTO_PF =>  AccountType::TYPE_CRYPTO,
        self::TYPE_CARD_ORDER_PAYMENT_CRYPTO => AccountType::TYPE_CRYPTO,
        self::TYPE_CARD_ORDER_PAYMENT_SEPA => AccountType::TYPE_WIRE_SEPA,
        self::TYPE_CARD_ORDER_PAYMENT_SWIFT => AccountType::TYPE_WIRE_SWIFT,
        self::TYPE_EXCHANGE => null, //@todo need think

        self::TYPE_BUY_CRYPTO_FROM_FIAT => AccountType::TYPE_FIAT,
        self::TYPE_FIAT_TOP_UP_BY_WIRE => AccountType::TYPE_FIAT,
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET => AccountType::TYPE_FIAT,
        self::TYPE_BUY_FIAT_FROM_CRYPTO => AccountType::TYPE_FIAT,



//        self::TYPE_TOP_UP_FOR_FIAT_WALLET => AccountType::TYPE_FIAT,
//        self::TYPE_WITHDRAW_FROM_FIAT_WALLET => AccountType::TYPE_FIAT,


    ];

    const WITHDRAW_OPERATIONS = [
        self::TYPE_WITHDRAW_WIRE_SWIFT,
        self::TYPE_WITHDRAW_WIRE_SEPA,
        self::TYPE_WITHDRAW_CRYPTO,
        self::TYPE_WITHDRAW_CRYPTO_PF,
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET,
    ];

    const TOP_UP_OPERATIONS = [
        self::TYPE_TOP_UP_SWIFT,
        self::TYPE_TOP_UP_SEPA,
        self::TYPE_TOP_UP_CRYPTO,
        self::TYPE_CARD,
        self::MERCHANT_PAYMENT,
        self::TYPE_CARD_PF,
        self::TYPE_TOP_UP_CRYPTO_PF,
        self::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF,
        self::TYPE_CRYPTO_TO_CRYPTO_PF,
        self::TYPE_BUY_CRYPTO_FROM_FIAT
    ];

    const FIAT_WALLET_OPERATIONS = [
        self::TYPE_FIAT_TOP_UP_BY_WIRE,
        self::TYPE_BUY_CRYPTO_FROM_FIAT,
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET,
        self::TYPE_BUY_FIAT_FROM_CRYPTO,
    ];

    const ACCOUNT_OPERATION_TYPES_WIRE = [
        self::TYPE_TOP_UP_SWIFT => AccountType::TYPE_WIRE_SWIFT,
        self::TYPE_TOP_UP_SEPA => AccountType::TYPE_WIRE_SEPA,
    ];

    const NAMES = [
        self::TYPE_TOP_UP_SEPA => 'operation_type_top_up_sepa',
        self::TYPE_TOP_UP_SWIFT => 'operation_type_top_up_swift',
        self::TYPE_TOP_UP_CRYPTO => 'operation_type_top_up_crypto',
        self::TYPE_CARD => 'operation_operation_type_card',
        self::MERCHANT_PAYMENT => 'operation_operation_type_merchant_payment',
        self::TYPE_WITHDRAW_CRYPTO => 'operation_type_withdraw_crypto',
        self::TYPE_EXCHANGE => 'exchange',
        self::TYPE_WITHDRAW_WIRE_SEPA => 'operation_type_send_wire_sepa',
        self::TYPE_WITHDRAW_WIRE_SWIFT => 'operation_type_send_wire_swift',
        self::TYPE_PROVIDER_WITHDRAW => 'operation_type_provider_withdraw',
        self::TYPE_PROVIDER_TOP_UP => 'operation_type_provider_top_up',
        self::TYPE_CARD_PF => 'operation_operation_type_card_pf',
        self::TYPE_TOP_UP_CRYPTO_PF => 'operation_operation_type_top_up_crypto_pf',
        self::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF => 'operation_operation_type_top_up_crypto_external_pf',
        self::TYPE_WITHDRAW_CRYPTO_PF => 'operation_operation_type_withdraw_crypto_pf',
        self::TYPE_CRYPTO_TO_CRYPTO_PF => 'operation_operation_type_crypto_to_crypto_pf',
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET => 'operation_operation_type_withdraw_from_fiat_wallet',
        self::TYPE_BUY_CRYPTO_FROM_FIAT => 'operation_operation_type_top_up_from_fiat',
        self::TYPE_BUY_FIAT_FROM_CRYPTO => 'operation_operation_type_withdraw_to_fiat_wallet',
        self::TYPE_FIAT_TOP_UP_BY_WIRE => 'operation_operation_type_top_up_for_fiat_wallet',

        self::TYPE_CARD_ORDER_PAYMENT_SEPA => 'operation_operation_type_card_order_sepa',
        self::TYPE_CARD_ORDER_PAYMENT_SWIFT => 'operation_operation_type_card_order_swift',
        self::TYPE_CARD_ORDER_PAYMENT_CRYPTO => 'operation_operation_type_card_order_crypto',
    ];

    const OPERATION_BLOCKCHAIN_FEE_COUNT = [
        self::TYPE_TOP_UP_SEPA => self::BLOCKCHAIN_FEE_COUNT_TOP_UP_WIRE,
        self::TYPE_TOP_UP_SWIFT =>  self::BLOCKCHAIN_FEE_COUNT_TOP_UP_WIRE,
        self::TYPE_CARD => self::BLOCKCHAIN_FEE_COUNT_TOP_UP_CARD,
        self::MERCHANT_PAYMENT => self::BLOCKCHAIN_FEE_COUNT_MERCHANT_PAYMENT,
        self::TYPE_CARD_PF => self::BLOCKCHAIN_FEE_COUNT_TYPE_CARD_PF,
        self::TYPE_WITHDRAW_CRYPTO => self::BLOCKCHAIN_FEE_COUNT_WITHDRAW_CRYPTO,
        self::TYPE_WITHDRAW_CRYPTO_PF => self::BLOCKCHAIN_FEE_COUNT_WITHDRAW_CRYPTO,
        self::TYPE_WITHDRAW_WIRE_SEPA => self::BLOCKCHAIN_FEE_COUNT_WITHDRAW_WIRE,
        self::TYPE_WITHDRAW_WIRE_SWIFT => self::BLOCKCHAIN_FEE_COUNT_WITHDRAW_WIRE,
        self::TYPE_BUY_CRYPTO_FROM_FIAT => self::BLOCKCHAIN_FEE_COUNT_TOP_UP_WIRE,
    ];


    const TYPES_CRYPTO_LAST = [
        self::TYPE_TOP_UP_SWIFT,
        self::TYPE_TOP_UP_SEPA,
        self::TYPE_TOP_UP_CRYPTO,
        self::TYPE_WITHDRAW_CRYPTO,
        self::TYPE_CARD,
        self::MERCHANT_PAYMENT,
        self::TYPE_CARD_PF,
        self::TYPE_TOP_UP_CRYPTO_PF,
        self::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF,
        self::TYPE_WITHDRAW_CRYPTO_PF,
        self::TYPE_CRYPTO_TO_CRYPTO_PF,
        self::TYPE_BUY_CRYPTO_FROM_FIAT
    ];

    const TYPES_WIRE_LAST = [
        self::TYPE_WITHDRAW_WIRE_SWIFT,
        self::TYPE_WITHDRAW_WIRE_SEPA,
        self::TYPE_FIAT_TOP_UP_BY_WIRE,
        self::TYPE_WITHDRAW_FROM_FIAT_WALLET,
        self::TYPE_BUY_FIAT_FROM_CRYPTO,
    ];

    const TYPES_TOP_UP = [
        self::TYPE_TOP_UP_SEPA,
        self::TYPE_TOP_UP_SWIFT,
        self::TYPE_FIAT_TOP_UP_BY_WIRE,
    ];

    const SWIFT_TYPES = [
        self::TYPE_WITHDRAW_WIRE_SWIFT,
        self::TYPE_TOP_UP_SWIFT,
    ];

    const ALL_SWIFT_SEPA_TYPES = [
        self::TYPE_WITHDRAW_WIRE_SWIFT,
        self::TYPE_WITHDRAW_WIRE_SEPA,
        self::TYPE_TOP_UP_SEPA,
        self::TYPE_TOP_UP_SWIFT,
    ];

    const CARD_ORDER_OPERATIONS = [
        self::TYPE_CARD_ORDER_PAYMENT_SWIFT,
        self::TYPE_CARD_ORDER_PAYMENT_SEPA,
        self::TYPE_CARD_ORDER_PAYMENT_CRYPTO,
        self::TYPE_CARD_ORDER_PAYMENT_BANK_CARD
    ];

    const API_WIRE_TYPES = [
        self::API_WIRE_TYPE_SEPA,
        self::API_WIRE_TYPE_SWIFT
    ];

    const API_TOP_UP_WIRE = [
        self::API_WIRE_TYPE_SEPA => self::TYPE_TOP_UP_SEPA,
        self::API_WIRE_TYPE_SWIFT => self::TYPE_TOP_UP_SWIFT,
    ];

    const API_WITHDRAW_WIRE = [
        self::API_WIRE_TYPE_SEPA => self::TYPE_WITHDRAW_WIRE_SEPA,
        self::API_WIRE_TYPE_SWIFT => self::TYPE_WITHDRAW_WIRE_SWIFT,
    ];
}
