<?php


namespace App\Enums;


class SmsProviders extends Enum
{
    const TWILIO = 'twilio';
    const SALES_LV = 'saleslv';


    const NAMES = [
        self::TWILIO => 'sms_provider_twilio',
        self::SALES_LV => 'sms_provider_sales_lv',
    ];
}
