<?php

use App\Http\Controllers\Cabinet\API\v1\ModuleController;
use App\Enums\CProfileStatuses;
use App\Http\Controllers\Cabinet\API\v1\CardAccountDetailsController;
use App\Http\Controllers\Cabinet\API\v1\CUsersController;
use App\Http\Controllers\Cabinet\API\v1\WallesterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('registration', 'Cabinet\API\v1\CUserRegisterController@create')
    ->name('api.registration.create');

Route::post('verify/phone/get/code', 'Cabinet\API\v1\CUserRegisterController@sendPhoneVerificationCode');
Route::post('verify/phone/resend/code', 'Cabinet\API\v1\CUserRegisterController@resendPhoneVerificationCode');
Route::post('verify/phone', 'Cabinet\API\v1\CUserRegisterController@verifyPhoneCode');

Route::post('verify/email/get/code', 'Cabinet\API\v1\CUserRegisterController@sendEmailVerificationCode');
Route::post('verify/email/resend/code', 'Cabinet\API\v1\CUserRegisterController@resendEmailVerificationCode');
Route::post('verify/email', 'Cabinet\API\v1\CUserRegisterController@verifyEmailCode');


Route::post('login', 'Cabinet\API\v1\CUserLoginController@login')
    ->name('api.login.post');
Route::post('tokens/update', 'Cabinet\API\v1\CUserLoginController@updateTokens')
    ->name('api.updateTokens.post');
Route::post('2fa/verify', 'Cabinet\API\v1\CUserLoginController@verifyTwoFA')
    ->name('api.2fa.verify.post');
Route::get('countries', 'Cabinet\API\v1\CountryController@getCountries')->name('api.countries.get');
Route::get('project/settings', 'Cabinet\API\v1\SettingController@settings')->name('api.project.settings.get');

Route::middleware([
    'auth:api', 'check.status.api:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)
])->group(function () {
    Route::get('verify/email', 'Cabinet\API\v1\UserController@sendEmailVerification');

    Route::get('cuser-settings', 'Cabinet\API\v1\CUsersController@index')->name('api.settings.get');

    //Notification
    Route::get('notifications', 'Cabinet\API\v1\NotificationsController@index')->name('api.v1.cabinet.notifications');
    Route::get('notification/{id}', 'Cabinet\API\v1\NotificationsController@getNotificationUserById')->name('api.v1.cabinet.get-notification-data');
    Route::get('notifications/{id}/seen', 'Cabinet\API\v1\NotificationsController@verifyNotificationApi');

    //Help desk
    Route::get('help/desk', 'Cabinet\API\v1\HelpDeskController@index')->name('api.v1.cabinet.help.desk');
    Route::post('store/ticket', 'Cabinet\API\v1\HelpDeskController@storeTicket')->name('api.v1.cabinet.store.ticket');
    Route::get('ticket/{id}', 'Cabinet\API\v1\HelpDeskController@getTicket')->name('api.v1.cabinet.get.ticket');
    Route::post('ticket/message', 'Cabinet\API\v1\HelpDeskController@sendTicketMessage')->name('api.v1.cabinet.send.ticket.message');
    Route::get('close/ticket/{id}', 'Cabinet\API\v1\HelpDeskController@closeTicket')->name('api.v1.cabinet.close.ticket');
    Route::get('view/message/{id}', 'Cabinet\API\v1\HelpDeskController@viewMessage')->name('api.v1.cabinet.view.message');
    Route::get('ticket/message/file/{fileName}', 'Cabinet\API\v1\HelpDeskController@downloadTicketMessageFile')->name('api.v1.cabinet.view.message');


    Route::get('rates', 'Cabinet\API\v1\CProfileDetailsController@getRates')->name('api.v1.cabinet.rates');

    Route::patch('profile/update', 'Cabinet\API\v1\CProfileSettingsController@update')->name('api.settings.update');
    Route::patch('corporate/profile/update', 'Cabinet\API\v1\CProfileSettingsController@updateCorporate')->name('api.settings.update.corporate');
    Route::patch('password/update', 'Cabinet\API\v1\CProfileSettingsController@updatePassword')->name('api.settings.update.password');
    Route::patch('email/update', 'Cabinet\API\v1\CProfileSettingsController@updateEmail')->name('api.settings.update.email');

    Route::get('wallets', 'Cabinet\API\v1\WalletController@wallets')->name('cabinet.api.wallets.index');
    Route::get('wallet/{id}', 'Cabinet\API\v1\WalletController@show')->name('cabinet.api.wallets.show');

    Route::get('accounts/wire', 'Cabinet\API\v1\BankDetailsController@getWireAccounts')->name('cabinet.api.wire.bank.details');
    Route::get('accounts/crypto', 'Cabinet\API\v1\BankDetailsController@getCryptoAccounts')->name('cabinet.api.crypto.bank.details');

    Route::get('providers', 'Cabinet\API\v1\TopUpWireController@getProvidersByCountry')->name('cabinet.api.providers');

    Route::get('operations', 'Cabinet\API\v1\OperationHistoryController@index')->name('api.v1.operation.history.get');
    Route::get('operation/card/{id}', 'Cabinet\API\v1\OperationHistoryController@getBankCardOperationData');
    Route::get('operation/topup/crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getTopUpCryptoOperationData');
    Route::get('operation/topup/wire/{id}', 'Cabinet\API\v1\OperationHistoryController@getTopUpWireOperationData');
    Route::get('operation/withdraw/crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getWithdrawCryptoOperationData');
    Route::get('operation/withdraw/wire/{id}', 'Cabinet\API\v1\OperationHistoryController@getWithdrawWireOperationData');

    Route::get('operation/fiat-wallet/topup/wire/{id}', 'Cabinet\API\v1\OperationHistoryController@getFiatTopUpWireOperationData');
    Route::get('operation/fiat-wallet/withdraw/wire/{id}', 'Cabinet\API\v1\OperationHistoryController@getFiatWithdrawWireOperationData');
    Route::get('operation/fiat-wallet/buy-crypto-from-fiat/{id}', 'Cabinet\API\v1\OperationHistoryController@getBuyCryptoFromFiatOperationData');
    Route::get('operation/fiat-wallet/buy-fiat-from-crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getBuyFiatFromCryptoOperationData');



    Route::get('operation/pf/card/{id}', 'Cabinet\API\v1\OperationHistoryController@getTopUpCardPFOperationData');
    Route::get('operation/pf/topup/crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getTopUpCryptoPFOperationData');
    Route::get('operation/pf/withdraw/crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getWithdrawCryptoPFOperationData');
    Route::get('operation/pf/crypto/crypto/{id}', 'Cabinet\API\v1\OperationHistoryController@getCryptoToCryptoPFOperationData');

    Route::get('logout', 'Cabinet\API\v1\CUserLoginController@logOutApi')->name('api.logout.get');

    Route::get('profile/info', 'Cabinet\API\v1\CProfileSettingsController@getProfileInfo')->name('api.profile.info.get');
    Route::group([ 'prefix' => '2fa',], function () {
        Route::get('google/enable', 'Cabinet\API\v1\CProfileSettingsController@enableGoogleTwoFactorAuthentication')->name('api.2fa.google.enable');
        Route::get('email/enable', 'Cabinet\API\v1\CProfileSettingsController@enableEmailTwoFactorAuthentication')->name('api.2fa.email.enable');
        Route::post('google/enable/confirm', 'Cabinet\API\v1\CProfileSettingsController@confirmGoogleTwoFactorAuthentication')->name('api.2fa.google.enable.confirm');
        Route::post('email/enable/confirm', 'Cabinet\API\v1\CProfileSettingsController@confirmEmailTwoFactorAuthentication')->name('api.2fa.email.enable.confirm');
        Route::post('google/disable', 'Cabinet\API\v1\CProfileSettingsController@disableGoogleTwoFactorAuthentication')->name('api.2fa.google.disable');
        Route::get('email/disable', 'Cabinet\API\v1\CProfileSettingsController@disableEmailTwoFactorAuthentication')->name('api.2fa.email.disable');
        Route::post('email/disable/confirm', 'Cabinet\API\v1\CProfileSettingsController@confirmDisableEmailTwoFactorAuthentication')->name('api.2fa.email.disable.confirm');
        Route::get('create', 'Cabinet\API\v1\CProfileSettingsController@createTwoFaCode')->name('api.2fa.create');
    });
});

Route::middleware(['auth:cUser', 'restrict.compliance.level.0.api'
    , 'check.status:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)])->group(function () {

    Route::post('operation/withdraw/crypto', 'Cabinet\API\v1\WithdrawCryptoController@withdrawalPost')
        ->name('cabinet.api.withdrawal.post');

    Route::post('operation/withdraw/wire', 'Cabinet\API\v1\WithdrawWireController@withdrawWireOperation')
        ->name('cabinet.api.wallets.withdraw.wire.operation');

    Route::post('operation/topup/wire', 'Cabinet\API\v1\TopUpWireController@createTopUpWire')
        ->name('cabinet.api.wallets.wire.transfer');

    Route::post('operation/card', 'Cabinet\API\v1\CardTransferController@createCardTransfer')
        ->name('cabinet.api.wallets.card.transfer');
});

Route::middleware([
    'auth:api', 'check.status.api:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES)
])->group(function () {
    Route::get('compliance/get/token', 'Cabinet\API\v1\CProfileDetailsController@getTokenForCompliance');
    Route::get('compliance', 'Cabinet\API\v1\CProfileDetailsController@getComplianceData');
    Route::get('available/currencies', 'Cabinet\API\v1\CProfileSettingsController@getAvailableCurrency');
});

Route::middleware([
    'auth:api', 'check.status.api:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE])])->group(function () {


    Route::get('fiat-wallets', 'Cabinet\API\v1\FiatWalletController@wallets')->name('cabinet.api.fiat.wallets.index');
    Route::get('fiat-wallet/{id}', 'Cabinet\API\v1\FiatWalletController@show')->name('cabinet.api.fiat.wallets.show');

    Route::post('fiat-wallet', 'Cabinet\API\v1\FiatWalletController@addWallet')->name('cabinet.api.fiat.wallets.add.wallet');
    Route::post('fiat-wallet/topup-wire', 'Cabinet\API\v1\FiatWalletController@createTopUpByWire')
        ->name('cabinet.api.fiat.wallets.create.topup.wire');

    Route::post('fiat-wallet/withdraw/wire', 'Cabinet\API\v1\FiatWalletController@withdrawWireOperation')
        ->name('cabinet.api.fiat.wallets.withdraw.wire.operation');

    Route::post('fiat-wallet/buy-fiat-from-crypto', 'Cabinet\API\v1\FiatWalletController@createBuyFiatFromCryptoOperation')
        ->name('cabinet.api.fiat.wallets.buy.fiat.from.crypto');

    Route::post('fiat-wallet/buy-crypto-from-fiat', 'Cabinet\API\v1\FiatWalletController@createBuyCryptoFromFiatOperation')
        ->name('cabinet.api.fiat.wallets.buy.crypto.from.fiat');

    Route::post('wallet', 'Cabinet\API\v1\WalletController@addWallet')->name('cabinet.api.wallets.add.wallet');


    Route::post('account/crypto', 'Cabinet\API\v1\BankDetailsController@addNewCryptoAccounts')->name('cabinet.api.crypto.check.address');
    Route::post('account/wire', 'Cabinet\API\v1\BankDetailsController@storeWireAccount')->name('cabinet.api.bank.details.store');
    Route::put('account/wire', 'Cabinet\API\v1\BankDetailsController@updateWireAccount')->name('cabinet.api.bank.details.update');
    Route::delete('account/wire', 'Cabinet\API\v1\BankDetailsController@deleteWireAccount')->name('cabinet.api.bank.details.delete');

    Route::post('operation/withdraw/crypto', 'Cabinet\API\v1\WithdrawCryptoController@withdrawalPost')
        ->name('cabinet.api.withdrawal.post');

    Route::post('operation/withdraw/wire', 'Cabinet\API\v1\WithdrawWireController@withdrawWireOperation')
        ->name('cabinet.api.wallets.withdraw.wire.operation');

    Route::post('operation/topup/wire', 'Cabinet\API\v1\TopUpWireController@createTopUpWire')
        ->name('cabinet.api.wallets.wire.transfer');

    Route::post('operation/card', 'Cabinet\API\v1\CardTransferController@createCardTransfer')
        ->name('cabinet.api.wallets.card.transfer');

    Route::post('get/operation/report/pdf/{id}', 'Cabinet\API\v1\OperationHistoryController@downloadReportPDF');
    Route::post('get/transaction/report/pdf/{id}', 'Cabinet\API\v1\OperationHistoryController@downloadReportForTransactionPDF');
    Route::get('modules',[ModuleController::class,'getModules']);
    Route::post('card/payment/crypto',[WallesterController::class,'confirmCardPaymentByCrypto'])
    ->name('cabinet.api.v1.cardAccountDetails.confirmCardPaymentByCrypto');

    Route::post('card/order',[WallesterController::class,'cardOrder'])
        ->name('cabinet.api.v1.wallester.cardOrder');

    Route::post('card/payment/wire',[WallesterController::class,'confirmCardPaymentByWire'])
        ->name('cabinet.api.v1.wallester.cardPaymentByWire');
});

Route::group([
    'prefix' => 'users',
    'middleware' => ['auth:api', 'check.status.api:' . implode('-', [CProfileStatuses::STATUS_ACTIVE])]
], function () {
    Route::get('/cards', [CardAccountDetailsController::class, 'getUserCards'])
        ->name('cabinet.api.v1.cardAccountDetails.getUserCards');
    Route::get('wallester/card/prices',[WallesterController::class,'wallesterCardPrices']);
    Route::get('wallester/payment/methods',[WallesterController::class,'getAvailablePaymentMethods'])
        ->name('cabinet.api.v1.wallesterOrderCardPaymentMethods');
});
