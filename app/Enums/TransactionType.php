<?php


namespace App\Enums;


class TransactionType extends Enum
{
    const BANK_TRX = 1;
    const CRYPTO_TRX = 2;
    const EXCHANGE_TRX = 3;
    const REFUND = 4;
    const SYSTEM_FEE = 6;
    const BLOCKCHAIN_FEE = 7;
    const CARD_TRX = 8;
    const PROVIDER_WITHDRAW_TRX = 9;
    const PROVIDER_TOP_UP_TRX = 10;
    const CHARGEBACK = 11;
    const MONTHLY_FEE = 12;
    const CRATOS_FEE = 13;


    const GROUP_FEE_TRX = 'fee_transaction';
    const GROUP_TRX = 'transaction';

    const GROUPS = [
        self::GROUP_FEE_TRX => [
            self::SYSTEM_FEE,
            self::BLOCKCHAIN_FEE,
            self::MONTHLY_FEE,
            self::CRATOS_FEE
        ],
        self::GROUP_TRX => [
            self::BANK_TRX,
            self::CRYPTO_TRX,
            self::EXCHANGE_TRX,
            self::REFUND,
            self::CARD_TRX,
            self::PROVIDER_WITHDRAW_TRX,
            self::PROVIDER_TOP_UP_TRX,
            self::CHARGEBACK
        ]
    ];

    const NAMES = [
        self::BANK_TRX => 'bank_transaction',
        self::CRYPTO_TRX => 'crypto_transaction',
        self::EXCHANGE_TRX => 'exchange_transaction',
        self::REFUND => 'refund',
        //self::INTERNAL_TRANSFER => 'internal_transfer',
        self::SYSTEM_FEE => 'system_fee',
        self::BLOCKCHAIN_FEE => 'blockchain_fee',
        self::CARD_TRX => 'card_transaction',
        self::PROVIDER_WITHDRAW_TRX => 'provider_withdraw_trx',
        self::PROVIDER_TOP_UP_TRX => 'provider_top_up_trx',
        self::CHARGEBACK => 'chargeback',
        self::MONTHLY_FEE => 'monthly_fee',
        self::CRATOS_FEE => 'cratos_fee',
    ];

    const TRX_TYPES = [
        self::BANK_TRX => self::NAMES[self::BANK_TRX],
        self::CARD_TRX => self::NAMES[self::CARD_TRX],
        self::CRYPTO_TRX => self::NAMES[self::CRYPTO_TRX],
        self::EXCHANGE_TRX => self::NAMES[self::EXCHANGE_TRX],
        self::REFUND => self::NAMES[self::REFUND],
    ];

    const TRX_TYPES_FOR_TOP_UP_CARD = [
        self::CARD_TRX => self::NAMES[self::CARD_TRX],
        self::CRYPTO_TRX => self::NAMES[self::CRYPTO_TRX],
        self::EXCHANGE_TRX => self::NAMES[self::EXCHANGE_TRX],
        self::REFUND => self::NAMES[self::REFUND],
        self::CHARGEBACK => self::NAMES[self::CHARGEBACK],
    ];

    const PROVIDER_TRX_TYPES = [
        self::PROVIDER_WITHDRAW_TRX => self::NAMES[self::PROVIDER_WITHDRAW_TRX],
        self::PROVIDER_TOP_UP_TRX => self::NAMES[self::PROVIDER_TOP_UP_TRX],
    ];

    const CARD_PROVIDER_FILTER_TYPES = [
        self::CARD_TRX => self::NAMES[self::CARD_TRX],
        self::REFUND => self::NAMES[self::REFUND],
        self::PROVIDER_WITHDRAW_TRX => self::NAMES[self::PROVIDER_WITHDRAW_TRX],
        self::PROVIDER_TOP_UP_TRX => self::NAMES[self::PROVIDER_TOP_UP_TRX],
        self::CHARGEBACK => self::NAMES[self::CHARGEBACK],
        self::MONTHLY_FEE => self::NAMES[self::MONTHLY_FEE],
        self::CRATOS_FEE => self::NAMES[self::CRATOS_FEE],
    ];
}
