<?php


namespace App\Enums;


class CommissionFieldNames extends Enum
{
    const INCOMING_FLAT = 0;
    const INCOMING_PERCENTAGE = 1;
    const INCOMING_MIN = 2;
    const INCOMING_MAX = 3;
    const OUTGOING_FLAT = 4;
    const OUTGOING_PERCENTAGE = 5;
    const OUTGOING_MIN = 6;
    const OUTGOING_MAX = 7;
    const INTERNAL_FLAT = 8;
    const INTERNAL_PERCENTAGE = 9;
    const INTERNAL_MIN = 10;
    const INTERNAL_MAX = 11;
    const REFUND_FLAT = 12;
    const REFUND_PERCENTAGE = 13;
    const REFUND_MIN = 14;
    const REFUND_MAX = 15;

    const RATE_FLAT = 16;
    const RATE_PERCENTAGE = 17;
    const RATE_MIN = 18;
    const RATE_MAX = 19;
    const RATE_AMOUNT = 20;

    const REFUND_TRANSFER_PERCENT = 21;
    const REFUND_TRANSFER_EUR = 22;
    const REFUND_TRANSFER_USD = 23;
    const REFUND_MINIMUM_FEE = 24;

    const BLOCKCHAIN_FEE = 25;

    const INCOMING_FUNDS = 26;
    const AUTHORIZATION_FEE = 27;
    const CARD_REFUND_PERCENT = 28;
    const CARD_REFUND = 29;
    const CARD_CHARGEBACK_FEE = 30;
    const CARD_CHARGEBACK = 31;

    const NAMES = [
        self::INCOMING_FLAT => 'commission_incoming_flat',
        self::INCOMING_PERCENTAGE => 'commission_incoming_percentage',
        self::INCOMING_MIN => 'commission_incoming_min',
        self::INCOMING_MAX => 'commission_incoming_max',
        self::OUTGOING_FLAT => 'commission_outgoing_flat',
        self::OUTGOING_PERCENTAGE => 'commission_outgoing_percentage',
        self::OUTGOING_MIN => 'commission_outgoing_min',
        self::OUTGOING_MAX => 'commission_outgoing_max',
        self::INTERNAL_FLAT => 'commission_internal_flat',
        self::INTERNAL_PERCENTAGE => 'commission_internal_percentage',
        self::INTERNAL_MIN => 'commission_internal_min',
        self::INTERNAL_MAX => 'commission_internal_max',
        self::REFUND_FLAT => 'commission_refund_flat',
        self::REFUND_PERCENTAGE => 'commission_refund_percentage',
        self::REFUND_MIN => 'commission_refund_min',
        self::REFUND_MAX => 'commission_refund_max',
        self::RATE_FLAT => 'commission_rate_flat',
        self::RATE_PERCENTAGE => 'commission_rate_percentage',
        self::RATE_MIN => 'commission_rate_min',
        self::RATE_MAX => 'commission_rate_max',
        self::RATE_AMOUNT => 'commission_rate_amount',
        self::REFUND_TRANSFER_PERCENT => 'refund_transfer_percent',
        self::REFUND_TRANSFER_EUR => 'refund_transfer_eur',
        self::REFUND_TRANSFER_USD => 'refund_transfer_usd',
        self::REFUND_MINIMUM_FEE => 'refund_minimum_fee',
        self::BLOCKCHAIN_FEE => 'blockchain_fee',
        self::INCOMING_FUNDS => 'ui_incoming_funds',
        self::AUTHORIZATION_FEE => 'ui_authorization_fee',
        self::CARD_REFUND_PERCENT => 'ui_card_refund_percent',
        self::CARD_REFUND => 'ui_card_refund',
        self::CARD_CHARGEBACK_FEE => 'ui_card_chargeback_percent',
        self::CARD_CHARGEBACK => 'ui_card_chargeback',
    ];
}
