<?php


namespace App\Enums;


class WireType extends Enum
{
    const TYPE_SEPA = AccountType::TYPE_WIRE_SEPA;
    const TYPE_SWIFT = AccountType::TYPE_WIRE_SWIFT;

    const NAMES = [
        self::TYPE_SEPA => 'SEPA',
        self::TYPE_SWIFT => 'SWIFT',
    ];

    const OPERATION_WIRE_TYPES  = [
        self::TYPE_SEPA => OperationOperationType::TYPE_TOP_UP_SEPA,
        self::TYPE_SWIFT => OperationOperationType::TYPE_TOP_UP_SWIFT,
    ];
    
    const OPERATION_WITHDRAW_WIRE_TYPES = [
        self::TYPE_SEPA => OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA,
        self::TYPE_SWIFT => OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT,
    ];

    const IMAGES = [
        'SEPA' => 'sepa.png',
        'SWIFT' => 'swift.jpg',
    ];
}
