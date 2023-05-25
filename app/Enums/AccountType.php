<?php


namespace App\Enums;


class AccountType extends Enum
{
    const TYPE_WIRE_SWIFT = 0;
    const TYPE_WIRE_SEPA = 1;
    const TYPE_CRYPTO = 2;
    const TYPE_CARD = 3;
    const TYPE_CRYPTO_FAKE = 4;
    const TYPE_FIAT = 5;
//    const TYPE_BUY_CRYPTO_FROM_FIAT_WALLET = 7;
//    const TYPE_WITHDRAW_FIAT_BY_WIRE = 8;

    const ACCOUNT_EXTERNAL = 1;

    const ACCOUNT_OWNER_TYPE_PROVIDER = 1;
    const ACCOUNT_OWNER_TYPE_CLIENT = 2;
    const ACCOUNT_OWNER_TYPE_SYSTEM = 3;
    const ACCOUNT_OWNER_TYPE_SYSTEM_CRYPTO_FEE = 4;

    const PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT = 1;
    const PAYMENT_PROVIDER_FIAT_TYPE_FIAT = 2;


    const WIRE_PROVIDER_C2B = 1;
    const WIRE_PROVIDER_B2B = 2;
    const WIRE_PROVIDER_B2C = 3;

    const WIRE_PROVIDER_TYPES = [
        self::WIRE_PROVIDER_C2B ,
        self::WIRE_PROVIDER_B2B ,
        self::WIRE_PROVIDER_B2C ,
    ];

    const GET_WIRE_PROVIDER_TYPE_NAMES = [
        self::WIRE_PROVIDER_C2B => 'C2B',
        self::WIRE_PROVIDER_B2B => 'B2B',
        self::WIRE_PROVIDER_B2C => 'B2C',
    ];

    const ACCOUNT_COMMISSION_TYPES = [
        self::TYPE_WIRE_SWIFT => CommissionType::TYPE_SWIFT,
        self::TYPE_WIRE_SEPA => CommissionType::TYPE_SEPA,
        self::TYPE_CRYPTO => CommissionType::TYPE_CRYPTO,
        self::TYPE_CARD => CommissionType::TYPE_CARD,
        self::TYPE_CRYPTO_FAKE => CommissionType::TYPE_CRYPTO,
        self::TYPE_FIAT => CommissionType::TYPE_FIAT,
        OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE => CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE,
        OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET => CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE,
        OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT => CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET,
        OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO => CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET,

    ];


    const NAMES = [
        self::TYPE_WIRE_SWIFT => 'enum_account_type_swift',
        self::TYPE_WIRE_SEPA => 'enum_account_type_sepa',
        self::TYPE_CRYPTO => 'enum_account_type_crypto',
        self::TYPE_CRYPTO_FAKE => 'enum_account_type_crypto_fake',
        self::TYPE_FIAT => 'enum_account_type_fiat',
//        self::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET => 'rate_by_crypto_fiat_wallet',
//        self::TYPE_WITHDRAW_FIAT_BY_WIRE => 'rate_withdraw_fiat_wallet',
    ];

    const ACCOUNT_WIRE_TYPES = [
        self::TYPE_WIRE_SEPA => 'SEPA',
        self::TYPE_WIRE_SWIFT => 'SWIFT',
    ];

    const ACCOUNT_PAYMENT_PROVIDER_FIAT_TYPES = [
        self::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT => 'Default',
        self::PAYMENT_PROVIDER_FIAT_TYPE_FIAT => 'For fiat wallets'
    ];
}
