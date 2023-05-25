<?php

use App\Enums\CProfileStatuses;
use Illuminate\Support\Facades\Route;
use Twilio\Rest\Client;

/*
|--------------------------------------------------------------------------
| Cabinet Web Routes
|--------------------------------------------------------------------------
*/


Route::middleware('guest:cUser')->group(function () {
    Route::get('iregister', 'Cabinet\Auth\CUserRegisterController@index')
        ->name('cabinet.iregister.get');
    Route::get('cregister', 'Cabinet\Auth\CUserRegisterController@index')
        ->name('cabinet.cregister.get');
    Route::get('register', 'Cabinet\Auth\CUserRegisterController@index')
        ->name('cabinet.register.get');
    Route::post('register', 'Cabinet\Auth\CUserRegisterController@create')
        ->name('cabinet.register.post');
    Route::get('complete-registration/{temporaryDataId}', 'Cabinet\Auth\CUserRegisterController@completeRegistration')
        ->middleware(['signed'])
        ->name('cabinet.register.complete');
    Route::get('login', 'Cabinet\Auth\CUserLoginController@showLoginForm')
        ->name('cabinet.login.get');
    Route::post('login', 'Cabinet\Auth\CUserLoginController@login')
        ->name('cabinet.login.post');
    Route::post('2fa-operation-confirm', 'Cabinet\Auth\CUserLoginController@twoFALogin')
        ->name('cabinet.2fa-login.post');
});


Route::get('password-reset-request', 'Cabinet\Auth\CUserEtcController@getPasswordResetRequest')
    ->name('cabinet.password-reset-request.get');
Route::get('password-reset-done', 'Cabinet\Auth\CUserEtcController@getPasswordResetDone')
    ->name('cabinet.password-reset-done');
Route::post('password-reset-request', 'Cabinet\Auth\CUserEtcController@postPasswordResetRequest')
    ->name('cabinet.password-reset-request');
Route::get('password-reset-finish', 'Cabinet\Auth\CUserEtcController@getPasswordResetFinish')
    ->name('cabinet.password-reset-finish');

//! @todo (maybe it's ok) ->name()
Route::get('/password-reset', 'Cabinet\Auth\CUserEtcController@getPasswordReset')
    ->name('password.reset');
Route::post('/password-reset', 'Cabinet\Auth\CUserEtcController@postPasswordReset')
    ->name('password.update');

Route::get('/verify/email/{token}/{id}', 'Cabinet\CUserController@verifyEmail')
    ->name('verify.email');

Route::get('resend-email-verification/{cUser}', 'Cabinet\CUserController@resendEmailVerification')
    ->name('cabinet.resend.email.verification');

Route::post('logout', 'Cabinet\Auth\CUserLoginController@logout')->name('cabinet.logout');
Route::get('logout', 'Cabinet\Auth\CUserLoginController@logout')->name('cabinet.logout');

Route::get('download-pdf-operation/{operationId}', 'Cabinet\CabinetController@download')
    ->name('client.download.pdf.operation');

Route::middleware(['auth:cUser', 'restrict.compliance.level.0'
    , 'check.status:' . implode('-', CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)])->group(function () {
    Route::post('withdrawal', 'Cabinet\WithdrawCryptoController@withdrawalPost')
        ->name('cabinet.withdrawal.post')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));
    Route::get('withdraw-crypto', 'Cabinet\WalletController@sendCrypto')->name('cabinet.wallets.send.crypto')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));
    Route::get('withdraw-crypto/{id}', 'Cabinet\WithdrawCryptoController@sendCrypto')
        ->name('cabinet.wallets.send.crypto')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('send-wire/{id}', 'Cabinet\WithdrawCryptoController@sendWire')
        ->name('cabinet.wallets.send.wire')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('wire-transfer/{id}', 'Cabinet\TransferController@showWireTransfer')
        ->name('cabinet.wallets.wireTransfer')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_PAYMENT
        ]);
    Route::post('wire-transfer/{id}', 'Cabinet\TransferController@createWireTransfer')
        ->name('cabinet.wallets.wire.transfer')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_PAYMENT
        ]);


    Route::get('card-transfer/{id}', 'Cabinet\CardTransferController@showCardTransfer')
        ->name('cabinet.wallets.cardTransfer')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_CARD
        ]);
    Route::post('card-transfer/{id}', 'Cabinet\CardTransferController@createCardTransfer')
        ->name('cabinet.wallets.card.transfer')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_CARD
        ]);


    Route::get('card-transfers/pay', 'Cabinet\CardTransferController@pay')
        ->name('cabinet.wallets.pay');

    Route::post('withdraw-crypto/{id}', 'Cabinet\WithdrawCryptoController@createSendCrypto')
        ->name('cabinet.wallets.send.crypto.create')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('withdraw-wire/{cryptoAccountDetail}', 'Cabinet\WithdrawWireController@showWithdrawWire')
        ->name('cabinet.wallets.withdraw.wire')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_PAYMENT
        ]);

    Route::post('withdraw-wire/{cryptoAccountDetail}', 'Cabinet\WithdrawWireController@withdrawWireOperation')
        ->name('cabinet.wallets.withdraw.wire.operation')->middleware([
            'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
            'check.availability:' . \App\Enums\Providers::PROVIDER_PAYMENT
        ]);

    Route::post('withdraw-to-fiat/get-limits', 'Cabinet\WithdrawToFiatController@getLimits')
        ->name('cabinet.wallets.withdraw.to.fiat.get.limits')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('withdraw-to-fiat/{cryptoAccountDetail}', 'Cabinet\WithdrawToFiatController@showWithdrawToFiat')
        ->name('cabinet.wallets.withdraw.to.fiat')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::post('withdraw-to-fiat/{cryptoAccountDetail}', 'Cabinet\WithdrawToFiatController@withdrawToFiatOperation')
        ->name('cabinet.wallets.withdraw.to.fiat.operation')->middleware(['enable.fiat.wallets', 'check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE])]);

});

Route::middleware(['auth:cUser'
    , 'check.status:' . implode('-', CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES)
])
    ->group(function () {


        Route::middleware(['enable.fiat.wallets' , 'check.status:'.CProfileStatuses::STATUS_ACTIVE])->group(function () {
            Route::get('fiat-top-up/{id}', 'Cabinet\FiatTopUpController@topUp')->name('cabinet.fiat.top_up');
            Route::get('fiat-top-up/wire/{id}', 'Cabinet\FiatTopUpController@topUpByWire')->name('cabinet.fiat.top_up.wire');
            Route::post('fiat-top-up/wire/{id}', 'Cabinet\FiatTopUpController@createOperation')->name('cabinet.fiat.top_up.wire.create');
            Route::get('fiat-withdraw/{fiatAccount}', 'Cabinet\FiatWithdrawController@showWithdrawWire')->name('cabinet.fiat.withdraw.wire');
            Route::post('fiat-withdraw/{fiatAccount}', 'Cabinet\FiatWithdrawController@withdrawWireOperation')->name('cabinet.fiat.withdraw.wire.operation');
        });

    //! dev mode
    Route::get('settings', 'Cabinet\CUserController@index')
        ->name('cabinet.settings.get')
        ->middleware('check.status:' . implode('-', CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES));

    Route::patch('update-timezone', 'Cabinet\CUserController@updateTimezone')
        ->name('cabinet.timezone.update')
        ->middleware('check.status:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES));

    Route::get('dashboard', 'Cabinet\CabinetController@dashboard')->name('cabinet.dashboard');
    Route::get('wallet-exchange', 'Cabinet\WalletController@walletExchange')->name('cabinet.wallets.exchange');

    Route::get('top-up', 'Cabinet\WalletController@topUp')->name('cabinet.wallets.top_up');
    Route::post('add-wallet', 'Cabinet\WalletController@addWallet')->name('cabinet.wallets.add.wallet')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::post('add-fiat/{cProfile}', 'Cabinet\WalletController@addFiat')->name('cabinet.wallets.add.fiat')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

        Route::get('request', 'Cabinet\Transaction\DepositController@index')
        ->name('cabinet.request')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('download/{filename}', 'Cabinet\Transaction\DepositController@downloadPdf')
        ->name('cabinet.download.pdf')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('get-rate-value/{by}', 'Cabinet\Transaction\DepositController@getRateValue')
        ->name('cabinet.get.rate.value')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('get-transactions-month-limit', 'Cabinet\Transaction\DepositController@getTransactionsMonthLimit')
        ->name('cabinet.get.transactions.month.limit')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('get-rate-min/{currency}/{by}', 'Cabinet\Transaction\DepositController@getRateMin')
        ->name('cabinet.get.rate.min')
        ->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::post('request', 'Cabinet\Transaction\DepositController@store')
        ->name('cabinet.request.store');

    Route::get('exchange', 'Cabinet\CabinetController@exchange');

    Route::get('deposit', 'Cabinet\CabinetController@deposit');

    Route::get('deposit/set-status/{id}/{status}', 'Cabinet\Transaction\DepositController@setStatus')->name('deposit.set.status');
    Route::get('deposit/{exchangeRequestId}/{bankAccountTemplateId}', 'Cabinet\Transaction\DepositController@showExchangeRequest')->name('deposit.show.exchange.request');

    Route::post('deposit/upload-proof/{id}', 'Cabinet\Transaction\DepositController@uploadProof')->name('deposit.upload.proof');

    Route::get('compliance', 'Cabinet\ComplianceController@index')
        ->name('cabinet.compliance')
        ->middleware('check.status:' . implode('-', CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES));

    Route::get('limits', 'Cabinet\ComplianceController@index')
        ->name('cabinet.limits')
        ->middleware('check.status:' . implode('-', \App\Enums\CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES));

   Route::get('verify-notification', 'Cabinet\CabinetController@verifyNotification')->name('verify.notification');

    Route::get('get-notification/{admin?}', 'Cabinet\CabinetController@getNotification')->name('cabinet.get.notification');

    Route::get('history/{number?}/{type?}/{from?}/{to?}/{wallet?}', 'Cabinet\History\HistoryController@index')->name('cabinet.history')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    //Wallets
    Route::get('wallets', 'Cabinet\WalletController@wallets')
        ->name('cabinet.wallets.index');

    Route::get('reports', 'Cabinet\History\HistoryController@showMerchantOperationsCsvFilterPage')
            ->name('cabinet.reports.index');

    Route::post('reports', 'Cabinet\History\HistoryController@generateCsvForMerchantOperations')
        ->name('cabinet.merchant.operations.reports.post');

    Route::get('wallet-exchange/{id}', 'Cabinet\WalletController@walletExchange')
        ->name('cabinet.wallets.exchange')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('top-up-crypto/{id}', 'Cabinet\WalletController@topUpCrypto')
        ->name('cabinet.wallets.top_up_crypto')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('buy-crypto-from-fiat/{id}', 'Cabinet\BuyCryptoFromFiatController@buyCryptoFromFiat')
        ->name('cabinet.wallets.buy.crypto.from.fiat')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::post('buy-crypto-from-fiat/{id}', 'Cabinet\BuyCryptoFromFiatController@createTopUpFromFiat')
        ->name('cabinet.wallets.create.buy.crypto.from.fiat')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('wallets/{id}', 'Cabinet\WalletController@show')->name('cabinet.wallets.show')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));
    Route::get('wallets/fiat/{id}', 'Cabinet\WalletController@showFiatWallet')->name('cabinet.wallets.fiat.show')->middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]));

    Route::get('download-transaction-report-pdf/{operation}', 'Cabinet\History\HistoryController@downloadTransactionReportPdf')
        ->name('cabinet.download.transaction.report.pdf');

    Route::get('download-history-report-pdf', 'Cabinet\History\HistoryController@downloadHistoryReportPdf')
            ->name('cabinet.download.history.report.pdf');


    Route::middleware('check.status:' . implode('-', [CProfileStatuses::STATUS_ACTIVE]))->group(function () {
        Route::get('bank-details', 'Cabinet\BankDetailsController@index')->name('cabinet.bank.details');
        Route::post('bank-details', 'Cabinet\BankDetailsController@store')->name('cabinet.bank.details.store');
        Route::put('bank-details', 'Cabinet\BankDetailsController@update')->name('cabinet.bank.details.update');
        Route::post('bank-details-delete', 'Cabinet\BankDetailsController@delete')->name('cabinet.bank.details.delete');
    });
    Route::get('account/{id}', 'Cabinet\BankDetailsController@getAccountWithWire')->name('cabinet.bank.account.wire');
    Route::post('check-wallet', 'Cabinet\BankDetailsController@checkWalletAddress')->name('cabinet.crypto.check.address');

    Route::get('notifications', 'Cabinet\NotificationsController@index')->name('cabinet.notifications.index');

    Route::get('help-desk', 'Cabinet\HelpDeskController@index')->name('cabinet.help.desk');
    Route::post('store-ticket', 'Cabinet\HelpDeskController@storeTicket')->name('cabinet.store.ticket');
    Route::get('ticket/{id}', 'Cabinet\HelpDeskController@getTicket')->name('cabinet.get.ticket');
    Route::post('ticket-message', 'Cabinet\HelpDeskController@sendTicketMessage')->name('cabinet.send.ticket.message');
    Route::get('close-ticket/{id}', 'Cabinet\HelpDeskController@closeTicket')->name('cabinet.close.ticket');
    Route::get('view-message/{ticketId}/{type}', 'Cabinet\HelpDeskController@viewMessage')->name('cabinet.view.message');

    Route::get('cabinet-download-ticket-message-pdf-file/{file}', 'Cabinet\HelpDeskController@downloadTicketMessagePdfFile')->name('cabinet.download.ticket.message.pdf.file');


    Route::post('operation/{operation}/compliance-level-change', 'Cabinet\ComplianceController@requestComplianceLevelChange')->name('cabinet.transaction.compliance.level.change');
    Route::post('operation/{operation}/decline', 'Cabinet\TransferController@declineOperation')->name('cabinet.operation.decline');

    Route::post('update/webhook-url/{profile}', 'Cabinet\CUserController@updateWebhookUrl')->name('update.webhook.url');
//    Route::get('operation/card-transfer/createOperation', 'Cabinet\CardTransferController@testTrustPayment')->name('test.trust.payment');


    Route::middleware('check.individual')->group(function () {
            Route::resource('wallester-cards', 'Cabinet\WallesterController')
                ->middleware([
                    'check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]),
                    'check.availability:' . \App\Enums\Providers::PROVIDER_CARD_ISSUING
                    ]);

            Route::get('order-card/{type}', 'Cabinet\WallesterController@orderStepTwo')->name('show.order')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('save-limits', 'Cabinet\WallesterController@saveLimits')->name('wallester.card.order.save.limits')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('save-delivery-data', 'Cabinet\WallesterController@saveDeliveryData')->name('wallester.card.save.delivery.data')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::post('order-card', 'Cabinet\WallesterController@orderStepThree')->name('order.card')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::get('order-plastic-card-delivery', 'Cabinet\WallesterController@orderStepThreePlastic')->name('show.order.step.3.plastic')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::post('order-plastic-card-delivery', 'Cabinet\WallesterController@confirmPlasticOrderDelivery')->name('confirm.order.plastic.card.delivery')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('order-plastic-card-confirmed', 'Cabinet\WallesterController@confirmPlasticOrder')->name('confirm.order.plastic.card')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::get('order-virtual-summary', 'Cabinet\WallesterController@showVirtualOrderSummary')->name('show.virtual.order.summary')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::post('confirm-delivery', 'Cabinet\WallesterController@confirmDelivery')->name('confirm.delivery.plastic.card')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::get('details/{id?}', 'Cabinet\WallesterController@viewCardDetails')->name('wallester.card.details')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::get('check-2fa', 'Cabinet\WallesterController@checkTwoFa')->name('wallester.check-2fa')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('encrypted-details', 'Cabinet\WallesterController@showCardEncryptedDetails')->name('show.card.encrypted.details')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

            Route::patch('details-limits/{id}', 'Cabinet\WallesterController@updateCardLimits')->name('wallester.update.card.limits')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('block-card/{id}', 'Cabinet\WallesterController@blockCard')->name('wallester.block.card')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('unblock-card/{id}', 'Cabinet\WallesterController@unblockCard')->name('wallester.unblock.card')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('get-pin/{id}', 'Cabinet\WallesterController@getPinCode')->name('wallester.get.pin.code')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('get-cvv/{id}', 'Cabinet\WallesterController@getCVVCode')->name('wallester.get.cvv.code')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('update-security/{id}', 'Cabinet\WallesterController@updateCardSecurity')->name('wallester.update.card.security')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('remind-3ds/{id}', 'Cabinet\WallesterController@remind3dsPassword')->name('wallester.remind.3ds.password')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::get('card/pay/{id}', 'Cabinet\WallesterController@payForWallesterCard')->name('wallester.card.pay.crypto.payment')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('card/pay/crypto', 'Cabinet\WallesterController@confirmCardPaymentByCrypto')->name('wallester.confirm.crypto.payment')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('card/pay/wire', 'Cabinet\WallesterController@confirmCardPaymentByWire')->name('wallester.confirm.wire.payment')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('card/confirm', 'Cabinet\WallesterController@confirmCardOrderSummary')->name('wallester.confirm.card.order.summary')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));
            Route::post('crypto/summary', 'Cabinet\WallesterController@showCryptoPaymentSummary')->name('wallester.show.crypto.payment.summary')->middleware('check.status:' . implode('-', [\App\Enums\CProfileStatuses::STATUS_ACTIVE]));

        });

    });

Route::fallback(function (){
    abort(404);
});
