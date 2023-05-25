<?php
namespace App\Enums;

class CommissionType extends Enum
{
    const TYPE_SEPA = 1;
    const TYPE_SWIFT = 2;
    const TYPE_CARD = 3;
    const TYPE_CRYPTO = 4;
    const TYPE_EXCHANGE = 5;
    const TYPE_FIAT = 6;
    const TYPE_TOP_UP_FIAT_BY_WIRE = 7;
    const TYPE_BUY_CRYPTO_FROM_FIAT_WALLET = 8;
    const TYPE_WITHDRAW_FIAT_BY_WIRE = 9;
    const TYPE_BUY_FIAT_FROM_CRYPTO_WALLET = 10;


    const ACCOUNT_TYPES_MAP = [
        AccountType::TYPE_WIRE_SEPA => self::TYPE_SEPA,
        AccountType::TYPE_WIRE_SWIFT => self::TYPE_SWIFT,
        AccountType::TYPE_CRYPTO => self::TYPE_CRYPTO,
        AccountType::TYPE_CARD => self::TYPE_CARD,
        AccountType::TYPE_FIAT => self::TYPE_FIAT,
        OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE => self::TYPE_TOP_UP_FIAT_BY_WIRE,
        OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT => self::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET,
        OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET => self::TYPE_WITHDRAW_FIAT_BY_WIRE,
        OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO => self::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET,
    ];

    const COMMISSION_NAMES = [
        self::TYPE_SEPA => 'SEPA',
        self::TYPE_SWIFT => 'SWIFT',
        self::TYPE_CARD => 'CARD',
        self::TYPE_CRYPTO => 'CRYPTO',
        self::TYPE_EXCHANGE => 'EXCHANGE',
        self::TYPE_FIAT => 'FIAT',
        self::TYPE_TOP_UP_FIAT_BY_WIRE => 'TUP UP FIAT BY WIRE',
        self::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET => 'BUY CRYPTO FROM FIAT WALLET',
        self::TYPE_WITHDRAW_FIAT_BY_WIRE => 'WITHDRAW FROM FIAT BY WIRE',
        self::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET => 'BUY FIAT FROM CRYPTO WALLET',
    ];


    const COMMISSION_TYPES_FOR_FIAT_RATES_SEPA_SWIFT = [
        self::TYPE_SEPA,
        self::TYPE_SWIFT,
        self::TYPE_FIAT,
        self::TYPE_TOP_UP_FIAT_BY_WIRE,
        self::TYPE_WITHDRAW_FIAT_BY_WIRE,
    ];


}
