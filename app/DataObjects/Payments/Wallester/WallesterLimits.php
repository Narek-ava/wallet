<?php


namespace App\DataObjects\Payments\Wallester;


use App\DataObjects\BaseDataObject;

class WallesterLimits extends BaseDataObject
{
    public float $daily_purchase;
    public float $daily_withdrawal;
    public float $daily_internet_purchase;
    public float $daily_contactless_purchase;
    public float $weekly_purchase;
    public float $weekly_withdrawal;
    public float $weekly_internet_purchase;
    public float $weekly_contactless_purchase;
    public float $monthly_purchase;
    public float $monthly_withdrawal;
    public float $monthly_internet_purchase;
    public float $monthly_contactless_purchase;
    public float $transaction_purchase;
    public float $transaction_withdrawal;
    public float $transaction_internet_purchase;
    public float $transaction_contactless_purchase;
    public ?float $daily_overall_purchase;
    public ?float $weekly_overall_purchase;
    public ?float $monthly_overall_purchase;


}
