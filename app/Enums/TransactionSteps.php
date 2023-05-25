<?php


namespace App\Enums;


class TransactionSteps extends Enum
{
    const TRX_STEP_ONE = 0;
    const TRX_STEP_TWO = 1;
    const TRX_STEP_THREE = 2;
    const TRX_STEP_FOUR = 3;
    const TRX_STEP_FIVE = 4;
    const TRX_STEP_REFUND = -1;

    const NAMES = [
        self::TRX_STEP_ONE => 'trx_step_one',
        self::TRX_STEP_TWO => 'trx_step_two',
        self::TRX_STEP_THREE => 'trx_step_three',
        self::TRX_STEP_FOUR => 'trx_step_four',
        self::TRX_STEP_FIVE => 'trx_step_five',
        self::TRX_STEP_REFUND => 'trx_step_refund',
    ];
}
