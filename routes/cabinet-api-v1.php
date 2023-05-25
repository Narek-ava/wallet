<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cabinet API v1 Routes
|--------------------------------------------------------------------------
*/

Route::group(['excluded_middleware'=> VerifyCsrfToken::class, 'middleware' => ['auth:cUser', 'check.status:' .
    implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)]], function () {
    Route::post('2fa-email-enable', 'TwoFAController@emailEnable'); // @todo artak check middlewars, auth, status
    Route::post('2fa-email-enable-confirm', 'TwoFAController@emailEnableConfirm')
        ->name('cabinet.api.v1.2fa.email-enable-confirm');
    Route::post('2fa-email-disable-confirm', 'TwoFAController@emailDisableConfirm')
        ->name('cabinet.api.v1.2fa.email-disable-confirm');
    Route::post('2fa-google-register', 'TwoFAController@googleRegister');
    Route::post('2fa-google-confirm', 'TwoFAController@googleConfirm');
    Route::post('2fa-google-disable', 'TwoFAController@googleDisable');
    Route::post('2fa-email-disable', 'TwoFAController@emailDisable');
});

//? @todo excluded_middleware  or ['except' => []
Route::post('register-confirms-sms', 'CUsersController@registerConfirmsSms')
    ->name('cabinet.api.v1.cuser.register-confirms-sms');
Route::post('register-resend-sms', 'CUsersController@registerResendSms')
    ->name('cabinet.api.v1.cuser.register-resend-sms');

Route::post('register-confirms-email', 'CUsersController@registerConfirmsEmail')
    ->name('cabinet.api.v1.cuser.register-confirms-email');
Route::post('register-resend-email', 'CUsersController@registerResendEmail')
    ->name('cabinet.api.v1.cuser.register-resend-email');


// @todo Can we use single request
Route::middleware(['auth:cUser'
    , 'check.status:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)
])->group(function () {
    Route::post('2fa-operation-confirm', 'TwoFAController@twoFaOperationConfirm')->withoutMiddleware(VerifyCsrfToken::class)->name('cabinet.api.2fa-operation-confirm');
    Route::post('2fa-operation-init/{generateAnyway?}', 'TwoFAController@twoFaOperationInit')->withoutMiddleware(VerifyCsrfToken::class)->name('cabinet.api.2fa-operation-init');
    Route::patch('settings-update', 'CUsersController@update')->name('cabinet.settings.update');
    Route::patch('settings-corporate-update', 'CUsersController@updateCorporate')->name('cabinet.settings.updateCorporate');
    Route::patch('settings-update-password', 'CUsersController@updatePassword')->name('cabinet.settings.updatePassword');
    Route::patch('settings-update-email', 'CUsersController@updateEmail')->name('cabinet.settings.updateEmail');
    Route::post('compliance-request', 'ComplianceController@saveComplianceRequest')->name('cabinet.compliance.saveComplianceRequest');
    Route::get('deposit-template/{templateId?}', 'DepositController@getTemplate');
    Route::post('providers-by-country','TransferController@providersByCountry');
    Route::post('get-commissions','TransferController@getCommissions');
    Route::post('get-limits','TransferController@getLimits');
    Route::get('get-blockchain-fee/{currency}','TransferController@getBlockChainFee');
    Route::post('get-provider','TransferController@getProvider');
    Route::post('get-provider-account','TransferController@getProviderAccount');
    Route::post('get-withdraw-fee','TransferController@getWithdrawFee')->name('cabinet.withdraw.fee');
    Route::post('get-bank-templates','TransferController@getBankTemplates')->name('cabinet.get.bank.templates');
    Route::post('get-bank-template','TransferController@getBankTemplate')->name('cabinet.get.bank.template');
    Route::post('get-withdraw-wire-limits','TransferController@getWithdrawWireLimits')->name('cabinet.withdraw.wire.limits');
    Route::post('get-available-countries','TransferController@getAvailableCountries')->name('cabinet.withdraw.available.countries');
    Route::get('decline-operation-data','TransferController@declineOperationData')->name('cabinet.decline.operation.data');

});

Route::post('get-rate-crypto-fiat','TransferController@getRateCryptoFiat')->name('cabinet.get.rate.crypto');
Route::post('get-rate-crypto-max-payment-amount','TransferController@getRateMaxPaymentAmount')->name('cabinet.get.rate.maxPaymentAmount');
Route::post('payment-form-compliance-request', 'ComplianceController@saveComplianceRequest')->name('cabinet.compliance.saveComplianceRequest');
