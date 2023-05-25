<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF compliance_level.
     *
     * @var array
     */
    protected $except = [
        //
        'cabinet/logout',
        'backoffice/logout',
        'payment/form/*',
        'ajax/get-rate-crypto-fiat',
        'ajax/get-rate-crypto-max-payment-amount',
        'ajax/payment-form-reset-url',
        'payment-verify-compliance-status',
        'payment-verify-email-code',
        'payment-verify-email',
        'payment-verify-phone',
        'payment-verify-sms-code',
        'payment-form',
        'get-compliance-data-url',
        'ajax/payment-form-compliance-request',
        'ajax/compliance-request',
        'submit/payment/form/*',
        'payment-verify-wallet-address',
        'get-min-payment-amount/*',
        'get-changed-payment-amount/*',
        'crypto/payment/form/*',
        'crypto/payment/form/save-initial-data/*',
        'crypto/payment-verify-phone',
        'crypto/payment-verify-email',
        'crypto/check',
        'verify-payment-form'
    ];
}
