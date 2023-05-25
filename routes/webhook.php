<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/
Route::post('/compliance-applicant-reviewed', 'ComplianceController@handleApplicantReviewed');
Route::post('/card-applicant-action-reviewed', 'ComplianceController@handleCardApplicantActionReviewed');
Route::any('/bitgo/transfer/{walletId}', 'BitgoController@transfer')->name('webhook.bitgo.transfer');
Route::post('/payments/trust-payment', 'TrustPaymentController@cardTransfer')->name('webhook.trust.payments.transfer');
