<?php


namespace App\Enums;


class TemplateType extends Enum
{
    const TEMPLATE_TYPE_SWIFT = 0;
    const TEMPLATE_TYPE_SEPA = 1;
    const TEMPLATE_TYPE_CRYPTO = 2;
    const TEMPLATE_TYPE_CARD_CREDIT = 3;
    const TEMPLATE_TYPE_CARD_DEBIT = 4;

    const NAMES = [
        self::TEMPLATE_TYPE_SWIFT => 'enum_account_type_swift',
        self::TEMPLATE_TYPE_SEPA => 'enum_account_type_sepa',
        self::TEMPLATE_TYPE_CRYPTO => 'enum_account_type_crypto',
        self::TEMPLATE_TYPE_CARD_CREDIT => 'enum_account_type_credit',
        self::TEMPLATE_TYPE_CARD_DEBIT => 'enum_account_type_debit',
    ];

    const NAMES_PAYMENT_ACCOUNT_TYPES = [
        self::TEMPLATE_TYPE_SWIFT => 'enum_account_type_swift',
        self::TEMPLATE_TYPE_SEPA => 'enum_account_type_sepa',
    ];

    const LIQUIDY_NAMES = [
        self::TEMPLATE_TYPE_SEPA => 'enum_account_type_sepa',
        self::TEMPLATE_TYPE_SWIFT => 'enum_account_type_swift',
        self::TEMPLATE_TYPE_CRYPTO => 'enum_account_type_crypto',
    ];

    const CARD_TYPES = [
        self::TEMPLATE_TYPE_CARD_CREDIT,
        self::TEMPLATE_TYPE_CARD_DEBIT,
    ];


}
