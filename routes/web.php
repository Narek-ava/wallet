<?php

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\Providers;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Models\ClientSystemWallet;
use App\Models\Project;
use App\Models\Transaction;
use App\Services\BitGOAPIService;
use App\Services\OperationService;
use App\Services\ProviderService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;
use Spatie\Permission\Models\Role;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function (){
    return redirect()->route('cabinet.login.get');
});

Route::get('vko-sumsub', function (\App\Services\SumSubService $sumSubService, \App\Services\CProfileService $cProfileService, \App\Services\ComplianceService $complianceService) {
    $applicantData = [
        'id' => '6203b57169fc980001e0045e',
        'createdAt' => '2022-02-09 12:37:05',
        'key' => 'JRFMUFVTZBEKQK',
        'clientId' => 'cratos',
        'inspectionId' => '6203b57169fc980001e0045f',
        'externalUserId' => 'cee1b9c4-3dd5-4c4c-b3f8-29b7ea93c44c',
        'info' => [

            'companyInfo' => [
                'companyName' => 'Akurateco Lab, Informática Unipessoal Lda',
                'registrationNumber' => '516406205',
                'country' => 'PRT',
                'legalAddress' => 'Rua de Breijinjho, № 4, 2 dt. 2135-099 Samora correia, Portugal',
                'address' => [
                    'town' => 'Benavente (Santarém)',
                ],

                'incorporatedOn' => '2020-03-30 00:00:00',
                'email' => 'kv@akurateco.com',
                'phone' => '+31 6 14997879',
                'taxId' => '516406205',
                'registrationLocation' => 'Rua de Breijinjho, № 4, 2 dt. 2135-099 Samora correia, Portugal',
                'website' => 'https://akurateco.com/',
                'postalAddress' => '2135-099',
                'beneficiaries' => [
                    [
                        'applicantId' => '6203bfa155e80e0001c90083',
                        'positions' => [
                            'shareholder', 'director'
                        ],
                        'type' => 'ubo',
                        'inRegistry' => '',
                        'imageIds' => '',
                        'applicant' => '',
                        'shareSize' => '',
                    ],

                    [
                        'applicantId' => '6203c73669fc980001e1b11c',
                        'positions' => [
                            'shareholder', 'director'
                        ],
                        'type' => 'shareholder',
                        'inRegistry' => '',
                        'imageIds' => '',
                        'applicant' => '',
                        'shareSize' => '',
                    ],

                ],

            ],

        ],
        'email' => 'natalia . tymoshenko@akurateco . com',
        'phone' => '380661845526',
        'review' => [
            'reviewId' => 'huPAv',
            'attemptId' => 'fUcWX',
            'attemptCnt' => '2',
            'elapsedSincePendingMs' => '142692',
            'elapsedSinceQueuedMs' => '142692',
            'reprocessing' => '1',
            'levelName' => 'Corporate - Level 2',
            'createDate' => '2022 - 02 - 16 11:28:12 + 0000',
            'reviewDate' => '2022 - 02 - 16 11:30:35 + 0000',
            'reviewResult' => [
                'reviewAnswer' => 'GREEN',
                'rejectLabels' => [
                    'NOT_ALL_CHECKS_COMPLETED',
                ],
            ],

            'reviewStatus' => 'completed',
            'priority' => '0',
            'autoChecked' => '',
        ],

        'lang' => 'en',
        'type' => 'company',
        'questionnaires' => [
            [
                'id' => '1',
                'sections' => [
                    'director' => [
                        'items' => [
                            'surname_ne19q' => [
                                'value' => 'Padytel',
                            ],

                            'name_i9atz' => [
                                'value' => 'Yuriy',
                            ],

                            'street_n1xzm' => [
                                'value' => 'Rua do Breijinho',
                            ],

                            'email_6c9h8' => [
                                'value' => 'padytel81@sapo . pt',
                            ],

                            'houseNo_rxg5z' => [
                                'value' => '4',
                            ],

                            'country_8jbht' => [
                                'value' => 'PRT',
                            ],

                            'flatNo_xsrbl' => [
                                'value' => '2º Dtº',
                            ],

                            'identityNoOrDa_3n3fh' => ['value' => '08.06.1981',
                            ],

                            'phoneNo_jvb2l' => [
                                'value' => '351934885099',
                            ],

                            'postalCode_jx6sh' => [
                                'value' => '2135 - 099',
                            ],

                            'town_uj568' => [
                                'value' => 'Benavente(Santarém)',
                            ],

                        ],

                    ],

                    'legalAddress_rlsuv' => [
                        'items' => [
                            'country_q54qs' => [
                                'value' => 'PRT',
                            ],

                            'street_l9yhg' => [
                                'value' => 'Rua do Breijinho',
                            ],
                            'citytownvillag_vrz2c' => ['value' => 'Benavente(Santarém)',
                            ],
                            'houseNo_kn5um' => [
                                'value' => '4',
                            ],

                            'postalCodezip_fa30j' => [
                                'value' => '2135 - 099'
                            ],

                            'flatNo_n7feo' => [
                                'value' => '2º Dtº'
                            ],
                        ],
                    ],

                    'beneficialOwner' => [
                        'items' =>
                            ['dateOfExpiry' => [
                                'value' => '13.07.2030'
                            ],

                                'country' => [
                                    'value' => 'PRT',
                                ],

                                'clarify' => [],

                                'passportIdNumberOfUb' => [
                                    'value' => '325967105ZZ3'
                                ],

                                'countryOfIssue' => [
                                    'value' => 'PRT',
                                ],

                                'town' => [
                                    'value' => 'Benavente(Santarém)'
                                ],

                                'name_vztza' => [
                                    'value' => 'Yuriy',
                                ],

                                'issuingInstitution' => [
                                    'value' => 'Portuguese Republic',
                                ],

                                'postalCode' => [
                                    'value' => '2135 - 099',
                                ],

                                'identityNoOrDa_gpveu' => [
                                    'value' => '08.06.1981'
                                ],
                                'surname_xhcjo' => ['value' => 'Padytel'
                                ],

                                'isPoliticallyE_gpiit' => ['value' => '2',
                                ],

                                'dateOfIssue' => [
                                    'value' => '13.07.2020'
                                ],

                                'countryOrOrganisatio' => [],

                                'owned_cn75r' => ['value' => 100
                                ],

                                'flatNo' => [
                                    'value' => '2º Dtº'
                                ],

                                'street' => [
                                    'value' => 'Rua do Breijinho'
                                ],

                                'houseNo' => [
                                    'value' => '4'
                                ],

                                'position' => [],
                            ],
                    ],
                    'contacts_d47nn' => [
                        'items' => [
                            'companyPhoneNo_2a7ls' => [
                                'value' => '31614997879'
                            ],

                            'companyEmail_zq78d' => [
                                'value' => 'kv@akurateco . com'
                            ],

                            'companyWebpage_504fi' => [
                                'value' => 'https://akurateco.com/'
                            ],

                        ],

                    ],

                ],

            ],

        ],

    ];

    $cProfile = \App\Models\Cabinet\CProfile::find('adad6964-a2b5-46fd-8665-532cbf754823');

    $cProfileDataArray = $complianceService->getUpdateDataForCProfile($cProfile, $applicantData['info']);

    $beneficialOwners = null;
    $ceo = null;
    if ($cProfile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE) {
        $beneficialOwners = $complianceService->getCompanyOwners($applicantData['questionnaires'][0]['sections']['beneficialOwner'] ?? null);
        $ceo = $complianceService->getCompanyOwners($applicantData['questionnaires'][0]['sections']['director'] ?? null);
    }
    $cProfileService->updateProfile($cProfile, $cProfileDataArray, $beneficialOwners, $ceo);

    dd($applicantData);
});

Route::get('vko-sumsub', function (\App\Services\SumSubService $sumSubService, \App\Services\CProfileService $cProfileService, \App\Services\ComplianceService $complianceService) {

    $applicantData = [
        'id' => '6203b57169fc980001e0045e',
        'createdAt' => '2022-02-09 12:37:05',
        'key' => 'JRFMUFVTZBEKQK',
        'clientId' => 'cratos',
        'inspectionId' => '6203b57169fc980001e0045f',
        'externalUserId' => 'cee1b9c4-3dd5-4c4c-b3f8-29b7ea93c44c',
        'info' => [

            'companyInfo' => [
                'companyName' => 'Akurateco Lab, Informática Unipessoal Lda',
                'registrationNumber' => '516406205',
                'country' => 'PRT',
                'legalAddress' => 'Rua de Breijinjho, № 4, 2 dt. 2135-099 Samora correia, Portugal',
                'address' => [
                    'town' => 'Benavente (Santarém)',
                ],

                'incorporatedOn' => '2020-03-30 00:00:00',
                'email' => 'kv@akurateco.com',
                'phone' => '+31 6 14997879',
                'taxId' => '516406205',
                'registrationLocation' => 'Rua de Breijinjho, № 4, 2 dt. 2135-099 Samora correia, Portugal',
                'website' => 'https://akurateco.com/',
                'postalAddress' => '2135-099',
                'beneficiaries' => [
                    [
                        'applicantId' => '6203bfa155e80e0001c90083',
                        'positions' => [
                            'shareholder', 'director'
                        ],
                        'type' => 'ubo',
                        'inRegistry' => '',
                        'imageIds' => '',
                        'applicant' => '',
                        'shareSize' => '',
                    ],

                    [
                        'applicantId' => '6203c73669fc980001e1b11c',
                        'positions' => [
                            'shareholder', 'director'
                        ],
                        'type' => 'shareholder',
                        'inRegistry' => '',
                        'imageIds' => '',
                        'applicant' => '',
                        'shareSize' => '',
                    ],

                ],

            ],

        ],
        'email' => 'natalia . tymoshenko@akurateco . com',
        'phone' => '380661845526',
        'review' => [
            'reviewId' => 'huPAv',
            'attemptId' => 'fUcWX',
            'attemptCnt' => '2',
            'elapsedSincePendingMs' => '142692',
            'elapsedSinceQueuedMs' => '142692',
            'reprocessing' => '1',
            'levelName' => 'Corporate - Level 2',
            'createDate' => '2022 - 02 - 16 11:28:12 + 0000',
            'reviewDate' => '2022 - 02 - 16 11:30:35 + 0000',
            'reviewResult' => [
                'reviewAnswer' => 'GREEN',
                'rejectLabels' => [
                    'NOT_ALL_CHECKS_COMPLETED',
                ],
            ],

            'reviewStatus' => 'completed',
            'priority' => '0',
            'autoChecked' => '',
        ],

        'lang' => 'en',
        'type' => 'company',
        'questionnaires' => [
            [
                'id' => '1',
                'sections' => [
                    'director' => [
                        'items' => [
                            'surname_ne19q' => [
                                'value' => 'Padytel',
                            ],

                            'name_i9atz' => [
                                'value' => 'Yuriy',
                            ],

                            'street_n1xzm' => [
                                'value' => 'Rua do Breijinho',
                            ],

                            'email_6c9h8' => [
                                'value' => 'padytel81@sapo . pt',
                            ],

                            'houseNo_rxg5z' => [
                                'value' => '4',
                            ],

                            'country_8jbht' => [
                                'value' => 'PRT',
                            ],

                            'flatNo_xsrbl' => [
                                'value' => '2º Dtº',
                            ],

                            'identityNoOrDa_3n3fh' => ['value' => '08.06.1981',
                            ],

                            'phoneNo_jvb2l' => [
                                'value' => '351934885099',
                            ],

                            'postalCode_jx6sh' => [
                                'value' => '2135 - 099',
                            ],

                            'town_uj568' => [
                                'value' => 'Benavente(Santarém)',
                            ],

                        ],

                    ],

                    'legalAddress_rlsuv' => [
                        'items' => [
                            'country_q54qs' => [
                                'value' => 'PRT',
                            ],

                            'street_l9yhg' => [
                                'value' => 'Rua do Breijinho',
                            ],
                            'citytownvillag_vrz2c' => ['value' => 'Benavente(Santarém)',
                            ],
                            'houseNo_kn5um' => [
                                'value' => '4',
                            ],

                            'postalCodezip_fa30j' => [
                                'value' => '2135 - 099'
                            ],

                            'flatNo_n7feo' => [
                                'value' => '2º Dtº'
                            ],
                        ],
                    ],

                    'beneficialOwner' => [
                        'items' =>
                            ['dateOfExpiry' => [
                                'value' => '13.07.2030'
                            ],

                                'country' => [
                                    'value' => 'PRT',
                                ],

                                'clarify' => [],

                                'passportIdNumberOfUb' => [
                                    'value' => '325967105ZZ3'
                                ],

                                'countryOfIssue' => [
                                    'value' => 'PRT',
                                ],

                                'town' => [
                                    'value' => 'Benavente(Santarém)'
                                ],

                                'name_vztza' => [
                                    'value' => 'Yuriy',
                                ],

                                'issuingInstitution' => [
                                    'value' => 'Portuguese Republic',
                                ],

                                'postalCode' => [
                                    'value' => '2135 - 099',
                                ],

                                'identityNoOrDa_gpveu' => [
                                    'value' => '08.06.1981'
                                ],
                                'surname_xhcjo' => ['value' => 'Padytel'
                                ],

                                'isPoliticallyE_gpiit' => ['value' => '2',
                                ],

                                'dateOfIssue' => [
                                    'value' => '13.07.2020'
                                ],

                                'countryOrOrganisatio' => [],

                                'owned_cn75r' => ['value' => 100
                                ],

                                'flatNo' => [
                                    'value' => '2º Dtº'
                                ],

                                'street' => [
                                    'value' => 'Rua do Breijinho'
                                ],

                                'houseNo' => [
                                    'value' => '4'
                                ],

                                'position' => [],
                            ],
                    ],
                    'contacts_d47nn' => [
                        'items' => [
                            'companyPhoneNo_2a7ls' => [
                                'value' => '31614997879'
                            ],

                            'companyEmail_zq78d' => [
                                'value' => 'kv@akurateco . com'
                            ],

                            'companyWebpage_504fi' => [
                                'value' => 'https://akurateco.com/'
                            ],

                        ],

                    ],

                ],

            ],

        ],


    ];

    $cProfile = \App\Models\Cabinet\CProfile::find('adad6964-a2b5-46fd-8665-532cbf754823');

    $cProfileDataArray = $complianceService->getUpdateDataForCProfile($cProfile, $applicantData['info']);

    $beneficialOwners = null;
    $ceo = null;
    if ($cProfile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE) {
        $beneficialOwners = $complianceService->getCompanyOwners($applicantData['questionnaires'][0]['sections']['beneficialOwner'] ?? null);
        $ceo = $complianceService->getCompanyOwners($applicantData['questionnaires'][0]['sections']['director'] ?? null);
    }
    $cProfileService->updateProfile($cProfile, $cProfileDataArray, $beneficialOwners, $ceo);

    dd($applicantData);
});

Route::get('vko1234', function(\App\Services\KrakenService $krakenService, \App\Services\TransactionService $transactionService) {
    die;
    $transaction = \App\Models\Transaction::find('a34b621c-b9aa-4001-b3dc-2a6fa514f0f1');
    $transactionService->approveTransaction($transaction);
    die;
    dd($krakenService->exchangeResult('ORN5D3-CQSAI-MNQL6R'));

    die;

    $topUpCard->handleTransaction('55-9-1313979');

    $configData = [
        'username' => 'api@vultureou79830.com',
        'password' => 'P!!(3!Km'
    ];
    die;
echo '<pre>';
    print_r($subService->getModerationMessage('60e43f65648bca0001c9994a'));
    die;
    $address = 'LVUHREP6crrNYq6ZskzniYgC8sb4qUD5K5';
    $account = \App\Models\Account::findOrFail('c8564642-8604-43eb-9117-476f87f41cee');

    $cryptoAccountService->checkNewIncomingTrx($account, $address, 'zzz4', 2 * Currency::BASE_CURRENCY[$account->currency]);

    die;
    $result = $krakenService->executeExchange('LTC', 'USD', 0.03, 'de37e465-0093-4adf-8676-8e7258ec3559');

    dd($result);

    $queryOrder = ($krakenService->exchangeResult('OTK5I4-GT5PX-UD6KMH'));
    $transactionAmount = $krakenService->getTransactionAmount($queryOrder, 'OTK5I4-GT5PX-UD6KMH');

    $rateAmount = 1 / $krakenService->getRateAmount($queryOrder, 'OTK5I4-GT5PX-UD6KMH');
    dd($krakenService->withdrawStatus('BTC'));
});
Route::middleware('guest:bUser')->group(function () {
    Route::get('login', 'Backoffice\BUserLoginController@showLoginForm')->prefix('backoffice');
    Route::post('login', ['as' => 'backoffice/login', 'uses' => 'Backoffice\BUserLoginController@login'])->prefix('backoffice');
    Route::get('buser/set-new-password/{token}', 'Backoffice\BUserController@setNewPassword')->name('b-user.new.password');
    Route::post('buser/set-new-password/{token}', 'Backoffice\BUserController@storeNewPassword')->name('b-user.new.password.post');
    Route::post('2fa-operation-confirm', 'Backoffice\BUserLoginController@twoFactorLogin')->prefix('backoffice')->withoutMiddleware(VerifyCsrfToken::class);
});

Route::group(['middleware' => ['middleware'=>'auth:bUser'], 'prefix' => 'backoffice'], function()
{
    Route::get('check-manager-permission', 'Backoffice\ProjectController@checkManagerPermissionsInProject')->name('backoffice.check.manager.permission');


    Route::get('kraken-balance', 'Backoffice\DashboardController@getKrakenBalance')->name('get.kraken.balance')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));
    Route::middleware('super.admin')->group(function () {
        Route::resource('b-users', 'Backoffice\BUserController');
        Route::get('enable/{id}', 'Backoffice\BUserController@enableAdmin')->name('b-users.enable');
        Route::post('two-factor/disable', 'Backoffice\BUserController@twoFactorAuthDisable')->name('b-users.twoFactor.disable');

         });


    Route::get('settings', ['as'=>'backoffice.settings', function () {
        return view('backoffice.settings');
    }]);

    Route::get('reports', 'Backoffice\ReportController@index')->name('backoffice.reports')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));
    Route::post('report-check-status', 'Backoffice\ReportController@checkStatus')->name('backoffice.report.check.status')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));


    Route::get('two-factor', 'Backoffice\BUserController@twoFactorAuth')->name('b-users.twoFactor');
    Route::post('two-factor/generate', 'Backoffice\BUserController@twoFactorAuthGenerate')->name('b-users.twoFactor.post');
    Route::post('two-factor/confirm', 'Backoffice\BUserController@twoFactorAuthConfirm')->name('b-users.twoFactor.confirm');

    Route::resource('projects', 'Backoffice\ProjectController')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROJECTS]));

    Route::get('get-projects/{part}', 'Backoffice\ProjectController@getProjects')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROJECTS]));

    Route::resource('roles', 'Backoffice\RoleController')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ROLES]));

    Route::get('payment-providers', 'Backoffice\PaymentProviderController@index')->name('backoffice.payment.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::get('liquidity-providers', 'Backoffice\LiquidityProviderController@index')->name('backoffice.liquidity.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::get('wallet-providers', 'Backoffice\WalletProviderController@index')->name('backoffice.wallet.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::get('card-issuing-providers', 'Backoffice\CardIssuingProviderController@index')->name('backoffice.card.issuing.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));


    Route::get('credit-card-providers', 'Backoffice\CreditCardProviderController@index')->name('backoffice.credit.card.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::get('compliance-providers', 'Backoffice\ComplianceProviderController@index')->name('backoffice.compliance.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));
    Route::get('kyt-providers', 'Backoffice\KYTProvidrsController@index')->name('backoffice.kyt.providers')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::post('compliance-providers', 'Backoffice\ComplianceProviderController@store')->name('backoffice.compliance.provider.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));
    Route::post('kyt-providers', 'Backoffice\KYTProvidrsController@store')->name('backoffice.kyt.provider.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('compliance-provider-update', 'Backoffice\ComplianceProviderController@providerUpdate')->name('backoffice.compliance.provider.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('kyt-provider-update', 'Backoffice\KYTProvidrsController@providerUpdate')->name('backoffice.kyt.provider.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('add-compliance-provider-account', 'Backoffice\ComplianceProviderController@addProviderAccount')->name('backoffice.add.compliance.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::get('get-compliance-provider/{provider}', 'Backoffice\ComplianceProviderController@getProvider')->name('backoffice.get.provider');
    Route::get('get-kyt-provider/{provider}', 'Backoffice\KYTProvidrsController@getProvider')->name('backoffice.get.kyt.provider');


    Route::get('get-compliance-providers/{part}', 'Backoffice\ComplianceProviderController@getProviders')->name('backoffice.get.providers');
    Route::get('get-compliance-provider-accounts/{provider}', 'Backoffice\ComplianceProviderController@getAccountByProvider')->name('backoffice.get.provider.account');
    Route::get('get-kyt-provider-accounts/{provider}', 'Backoffice\KYTProvidrsController@getAccountByProvider')->name('backoffice.get.kyt.provider.account');
    Route::get('get-kyt-providers/{part}', 'Backoffice\KYTProvidrsController@getProviders')->name('backoffice.get.providers');
    Route::get('/referral-links/token', 'Backoffice\ReferralLinksController@generateToken')
        ->name('referral_links.generate_token');
    Route::post('referrral_partner', 'Backoffice\ReferralLinksController@storePartner')
        ->name('referral_links.add_partner');
    Route::get('get-partner/{id}', 'Backoffice\ReferralLinksController@getPartner')
        ->name('partner.get');
    Route::put('referrral_partner', 'Backoffice\ReferralLinksController@updatePartner')
        ->name('referral_links.partner.put');
    Route::get('get-partner-links/{partnerId}', 'Backoffice\ReferralLinksController@getLinksByPartner')
        ->name('referral_links.partner.get');
    Route::get('create-link/{partner_id?}', 'Backoffice\ReferralLinksController@createLink')
        ->name('referral_links.link.show');
    Route::get('get-partners', 'Backoffice\ReferralLinksController@getPartners')->name('backoffice.get.partners');


    Route::resource('referral-links', 'Backoffice\ReferralLinksController');

    Route::get('payment-providers', 'Backoffice\PaymentProviderController@index')->name('backoffice.payment.providers');
    Route::get('liquidity-providers', 'Backoffice\LiquidityProviderController@index')->name('backoffice.liquidity.providers');
    Route::get('wallet-providers', 'Backoffice\WalletProviderController@index')->name('backoffice.wallet.providers');
    Route::get('credit-card-providers', 'Backoffice\CreditCardProviderController@index')->name('backoffice.credit.card.providers');
    Route::get('get-sumsub-configs/{id}', 'Backoffice\ProjectController@getSumSubConfigs')->name('backoffice.sumsub.configs.get')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROJECTS]));

    Route::post('store-sumsub-configs', 'Backoffice\ProjectController@storeSumSubConfigs')->name('backoffice.sumsub.configs.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));


    // @note AJAX request
    Route::post('rates/deactivate', 'Backoffice\RatesController@deactivate');
    Route::resource('rates', 'Backoffice\RatesController')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENT_RATES]));


    Route::resource('countries', 'Backoffice\CountryController')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_COUNTRIES]));

    Route::resource('client-wallets', 'Backoffice\ClientSystemWalletController')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENT_WALLETS]));
    Route::post('regenerate-webhook/{clientSystemWallet}', 'Backoffice\ClientSystemWalletController@regenerateWalletWebhook')->name('regenerate.webhook');

    Route::get('client-wallets/create/{projectId}', 'Backoffice\ClientSystemWalletController@create')->name('backoffice.client.wallet.create');
    Route::post('client-wallets/create/{projectId}', 'Backoffice\ClientSystemWalletController@store')->name('backoffice.client.wallet.store');

    Route::get('rate', 'Backoffice\RateTemplatesController@index')->name('rate.templates.index')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENT_RATES]));

    Route::post('rate', 'Backoffice\RateTemplatesController@store')->name('rate.templates.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]));

    Route::get('clients/{accountType}', 'Backoffice\RateTemplatesController@getUserProfileIdsArray')->name('get.typed.user.profile.ids');
    Route::get('get-rate-template-countries/{id}', 'Backoffice\RateTemplatesController@getRateTemplateCountries')->name('get.rate.template.countries');
    Route::get('get-rate-template/{id}', 'Backoffice\RateTemplatesController@getRateTemplate')->name('get.rate.template');
    Route::put('rate', 'Backoffice\RateTemplatesController@putRateTemplate')->name('rate.templates.put')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]));


    // Bank card rate template
    Route::post('card-rate', 'Backoffice\RateTemplatesController@storeBankCardRateTemplate')->name('card.templates.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]));

    Route::get('get-card-rate-template/{id}', 'Backoffice\RateTemplatesController@getBankCardRateTemplate')->name('get.rate.template');
    Route::post('card-rate-template/update', 'Backoffice\RateTemplatesController@updateBankCardRateTemplate')->name('card.templates.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]));



    // @note AJAX request

    Route::post('logout', 'Backoffice\BUserLoginController@logout')->name('backoffice.logout');


    Route::get('dashboard', 'Backoffice\DashboardController@index')->name('backoffice.dashboard')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS]));

    Route::get('profiles', 'Backoffice\CProfileController@index')->name('backoffice.profiles')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));

    Route::get('profile/{profileId}','Backoffice\CProfileController@view')->name('backoffice.profile')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));

    Route::patch('profile/{profileId}/update','Backoffice\CProfileController@update')->name('backoffice.profile.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::patch('profile/{profileId}/updateCorporate','Backoffice\CProfileController@updateCorporate')->name('backoffice.profile.updateCorporate')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::patch('profile/{profileId}/updateComplianceOfficer','Backoffice\CProfileController@updateComplianceOfficer')->name('backoffice.profile.updateComplianceOfficer')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::patch('profile/{profileId}/updateManager','Backoffice\CProfileController@updateManager')->name('backoffice.profile.updateManager')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::patch('profile/{profileId}/updateEmail','Backoffice\CProfileController@updateEmail')->name('backoffice.profile.updateEmail')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::patch('profile/{profileId}/updateTimezone','Backoffice\CProfileController@updateTimezone')->name('backoffice.profile.updateTimezone')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::post('profile/{profileId}/changeStatus','Backoffice\CProfileController@changeStatus')->name('backoffice.profile.changeStatus')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::post('profile/store','Backoffice\CProfileController@store')->name('backoffice.profile.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));

    Route::post('profile/storeCorporate','Backoffice\CProfileController@storeCorporate')->name('backoffice.profile.storeCorporate')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]));


    //Compliance section routes
    // @note AJAX request
    Route::get('compliance/applicant-docs/{applicantId}','Backoffice\ComplianceController@applicantDocs')->name('backoffice.compliance.applicantDocs')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));

    Route::post('compliance/request-documents-delete','Backoffice\ComplianceController@requestDocumentsDelete')->name('backoffice.compliance.requestDocumentsDelete')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::REQUEST_COMPLIANCE]));

    Route::post('compliance/renew-date/{profileId}','Backoffice\ComplianceController@renew')->name('backoffice.compliance.renew')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::REQUEST_COMPLIANCE]));

    Route::post('compliance/request-cancel', 'Backoffice\ComplianceController@cancelComplianceRequest')->name('backoffice.compliance.requestCancel')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::REQUEST_COMPLIANCE]));

    //TODO remove this line
    Route::get('profile/sendTestCompletedCompliance/{profileId}/{success}','Backoffice\CProfileController@sendTestCompletedCompliance')->name('backoffice.profile.sendTestCompletedCompliance')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::REQUEST_COMPLIANCE]));

    Route::get('card/{success}','Backoffice\CProfileController@sendTestCompletedCard')->name('backoffice.profile.sendTestCompletedCard');
    Route::get('applicant/{applicantId}', function ($applicantId){
        if (session()->has('applicantId')) {
            session()->forget('applicantId');
            session(['applicantId' => $applicantId]);
        }
    });

    Route::get('notifications', 'Backoffice\NotificationsController@index')->name('backoffice.notifications')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_NEW_NOTIFICATIONS]));

    Route::get('profiles-with-names', 'Backoffice\NotificationsController@profilesWithNames')->name('get.profile.names')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_NEW_NOTIFICATIONS]));


    Route::get('notifications-history', 'Backoffice\NotificationsController@history')->name('backoffice.notifications.history')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_NEW_NOTIFICATIONS]));

    Route::post('add-custom-notification', 'Backoffice\NotificationsController@notify')->name('backoffice.notifications.notify')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_NEW_NOTIFICATIONS]));

    Route::get('reset-client-password/{id}', 'Backoffice\ComplianceController@resetClientPassword')->name('dashboard.reset.client.password');
    Route::get('reset-client-2fa/{id}', 'Backoffice\ComplianceController@resetClient2FA')->name('dashboard.reset.client.2fa');
    Route::get('notifications/{notificationId}', 'Backoffice\NotificationsController@show')->name('backoffice.notifications.show');

    Route::get('verify-notification', 'Backoffice\NotificationsController@verifyNotification')->name('verify.notification.admin');

    Route::post('payment-provider', 'Backoffice\PaymentProviderController@store')->name('backoffice.payment.provider.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('card-issuing-providers', 'Backoffice\CardIssuingProviderController@store')->name('backoffice.card.issuing.provider.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::get('get-providers/{part}/{page}', 'Backoffice\PaymentProviderController@getProviders')->name('backoffice.get.providers');
    Route::post('add-provider-account', 'Backoffice\PaymentProviderController@addProviderAccount')->name('backoffice.add.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));


    Route::put('payment-provider', 'Backoffice\PaymentProviderController@providerUpdate')->name('backoffice.payment.provider.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::get('get-provider-accounts/{providerId}', 'Backoffice\PaymentProviderController@getProviderAccounts')->name('backoffice.get.provider.accounts');
    Route::put('add-provider-account', 'Backoffice\PaymentProviderController@putProviderAccount')->name('backoffice.put.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::get('get-account/{account}', 'Backoffice\AccountController@getAccount')->name('backoffice.get.account');
    Route::get('get-provider/{provider}', 'Backoffice\PaymentProviderController@getProvider')->name('backoffice.get.provider');

    Route::post('add-card-provider-account', 'Backoffice\CreditCardProviderController@addProviderAccount')->name('backoffice.add.card.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::get('get-card-api-accounts', 'Backoffice\CreditCardProviderController@getApiAccounts')->name('backoffice.get.card.provider.api.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));
    Route::get('get-card-issuing-api-accounts', 'Backoffice\CardIssuingProviderController@getApiAccounts')->name('backoffice.get.card.issuing.provider.api.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));
    Route::get('get-liquidity-api-accounts', 'Backoffice\LiquidityProviderController@getApiAccounts')->name('backoffice.get.liquidity.provider.api.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));
    Route::get('get-wallet-api-accounts', 'Backoffice\WalletProviderController@getApiAccounts')->name('backoffice.get.wallet.provider.api.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('add-wallet-provider-account', 'Backoffice\WalletProviderController@addProviderAccount')->name('backoffice.add.wallet.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('add-liquidity-provider-account-sepa', 'Backoffice\LiquidityProviderController@addProviderAccountSepa')->name('backoffice.add.liquidity.sepa.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::post('add-liquidity-provider-account-btc', 'Backoffice\LiquidityProviderController@addProviderAccountBtc')->name('backoffice.add.liquidity.btc.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));


    Route::put('add-card-provider-account', 'Backoffice\CreditCardProviderController@putProviderAccount')->name('backoffice.put.card.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::put('add-wallet-provider-account', 'Backoffice\WalletProviderController@putProviderAccount')->name('backoffice.put.wallet.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::put('add-liquidity-provider-account-sepa', 'Backoffice\LiquidityProviderController@putProviderAccountSepa')->name('backoffice.put.liquidity.sepa.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));

    Route::put('add-liquidity-provider-account-btc', 'Backoffice\LiquidityProviderController@putProviderAccountBtc')->name('backoffice.put.liquidity.btc.provider.account')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PROVIDERS, \App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]));


    Route::get('transactions/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details');
    Route::get('transactions', 'Backoffice\OperationController@index')->name('backoffice.transactions')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));

    Route::get('payment-form', 'Backoffice\PaymentFormController@index')->name('backoffice.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS]));
    Route::post('payment-form/create', 'Backoffice\PaymentFormController@create')->name('backoffice.add.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS, \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]));
    Route::post('payment-form/get-data', 'Backoffice\PaymentFormController@getData')->name('backoffice.get.payment.form.data.project')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS, \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]));
    Route::post('payment-form/update/{paymentForm}', 'Backoffice\PaymentFormController@update')->name('backoffice.update.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS, \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]));
    Route::post('payment-form/crypto/create', 'Backoffice\PaymentFormController@createCryptoForm')->name('backoffice.add.payment.crypto.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS, \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]));
    Route::post('payment-form/update/crypto/{paymentForm}', 'Backoffice\PaymentFormController@updateCryptoForm')->name('backoffice.update.payment.crypto.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS, \App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]));
    Route::get('payment-form/get-data/{paymentForm}', 'Backoffice\PaymentFormController@getForm')->name('backoffice.get.payment.form.data')
    ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS]));
    Route::get('get-payment-form/{paymentForm}', 'Backoffice\PaymentFormController@getPaymentForm')->name('backoffice.get.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS]));
    Route::get('payment-form-operations/{paymentForm}', 'Backoffice\PaymentFormController@getPaymentForm')->name('backoffice.get.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_PAYMENT_FORMS]));


    Route::post('transactions/get-accounts-by-type', 'Backoffice\OperationController@getFromAndToAccountsForOperation')->name('test');
    Route::post('transactions/get-to-address', 'Backoffice\OperationController@getCryptoAccountDetailAddress')->name('backoffice.withdraw.wire.to.account.address');


    Route::post('transactions/transaction/{id}', 'Backoffice\OperationController@addTransaction')->name('backoffice.add.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));
    Route::post('transactions/order-card-transaction/{id}', 'Backoffice\OperationController@addTransactionForCardOrderByWire')->name('backoffice.add.transaction.card.order.wire')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));
    Route::post('transactions/get-from-accounts-by-type', 'Backoffice\OperationController@getAccountsByType')->name('backoffice.from.accounts.by.type');
    Route::post('transactions/get-to-accounts-by-type', 'Backoffice\OperationController@getAccountsByType')->name('backoffice.to.accounts.by.type');
    Route::post('transactions/get-from-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.transaction.get.from.commissions');
    Route::post('transactions/get-to-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.transaction.to.from.commissions');
    Route::post('transactions/get-accounts-by-currency', 'Backoffice\OperationController@getAccountsByCurrency')->name('backoffice.transaction.get.account.currency');
    Route::post('transactions/{id}', 'Backoffice\OperationController@addBankDetail')->name('backoffice.add.bank.detail');
    Route::get('transactions/{id}', 'Backoffice\OperationController@showTransaction')->name('backoffice.show.transaction');
    Route::post('transactions/{id}/confirm', 'Backoffice\OperationController@confirmTransaction')->name('backoffice.confirm.transaction');
    Route::post('transactions/{id}/compliance-level-change', 'Backoffice\ComplianceController@requestComplianceLevelChange')->name('backoffice.transaction.compliance.level.change');


    Route::get('transactions/payment-form/{id}', 'Backoffice\PaymentFormController@showPaymentFormTransactions')->name('backoffice.transaction.payment.form')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));


    Route::post('transactions/bank/card/get-accounts-by-type', 'Backoffice\OperationController@getFromAndToAccountsForOperation');
    Route::post('transactions/bank/card/get-from-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.get.from.commissions');
    Route::post('transactions/bank/card/get-to-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.to.from.commissions');

    Route::get('transactions/bank/card/{id}', 'Backoffice\WallesterController@showTransaction')->name('backoffice.card.order.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));

    Route::get('transactions/card-order/crypto/{id}', 'Backoffice\CardOrderCryptoController@showTransaction')->name('backoffice.card.order.crypto.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));

    Route::post('transactions/card-order/crypto/{id}', 'Backoffice\CardOrderCryptoController@addTransaction')->name('backoffice.add.card.order.crypto.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));


    Route::get('top-up-fiat-wire/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details')->middleware('enable.fiat.wallets');
    Route::post('top-up-fiat-wire/{id}/add-bank-detail', 'Backoffice\TopUpFiatWireController@addBankDetail')->name('backoffice.add.top.up.fiat.wire.bank.detail')->middleware('enable.fiat.wallets');
    Route::get('top-up-fiat-wire/{id}', 'Backoffice\TopUpFiatWireController@showTransaction')->name('backoffice.top.up.fiat.wire.show.transaction')->middleware('enable.fiat.wallets');
    Route::post('top-up-fiat-wire/{operation}', 'Backoffice\TopUpFiatWireController@makeTransaction')->name('backoffice.add.top.up.fiat.wire.transaction')->middleware('enable.fiat.wallets');

    // kraken calls
    Route::post('get-ticker', 'Backoffice\KrakenController@getTicker')->name('get.ticker');
    Route::post('get-volume', 'Backoffice\KrakenController@getVolume')->name('get.volume');

    Route::post('change-profile-rate-template', 'Backoffice\RateTemplatesController@changeProfileRateTemplate')->name('change.cprofile.rate.template.id');
    Route::post('update-is-merchant', 'Backoffice\CProfileController@updateIsMerchant')->name('update.is.merchant');


    Route::get('cratos-settings', 'Backoffice\CratosSettingsController@index')->name('cratos.settings')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ADDRESS_SETTINGS]));

    Route::get('cratos-settings-add', 'Backoffice\CratosSettingsController@add')->name('cratos.add.settings')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ADDRESS_SETTINGS, \App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS]));

    Route::post('cratos-settings-create', 'Backoffice\CratosSettingsController@create')->name('cratos.setting.create')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ADDRESS_SETTINGS, \App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS]));

    Route::get('cratos-settings-edit/{id}', 'Backoffice\CratosSettingsController@edit')->name('cratos.setting.edit')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ADDRESS_SETTINGS, \App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS]));

    Route::put('cratos-settings-create', 'Backoffice\CratosSettingsController@put')->name('cratos.setting.put')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_ADDRESS_SETTINGS, \App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS]));


    // kraken calls
    Route::post('get-ticker', 'Backoffice\KrakenController@getTicker')->name('get.ticker');


    //withdraw crypto transactions
    Route::get('crypto-transactions/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details');
    Route::post('crypto-transactions/get-accounts', 'Backoffice\WithdrawCryptoController@getAccounts')->name('backoffice.withdraw.get.accounts');
    Route::get('crypto-transactions/{id}', 'Backoffice\WithdrawCryptoController@showTransaction')->name('backoffice.withdraw.crypto.transaction');
    Route::get('topup-crypto-pf/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details');
    Route::get('topup-crypto-pf/{id}', 'Backoffice\PaymentFormController@showCryptoToCryptoOperation')->name('backoffice.topup.crypto.to.crypto.pf.transaction');

    Route::post('crypto-transactions/transaction/{id}', 'Backoffice\WithdrawCryptoController@addTransaction')->name('backoffice.add.withdraw.crypto.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));

    Route::post('crypto-transactions/transaction/{id}/change-status', 'Backoffice\OperationController@changeStatus')->name('backoffice.withdraw.change.status')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS]));

    Route::post('transactions/transaction/{id}/change-status', 'Backoffice\OperationController@changeStatus')->name('backoffice.transaction.change.status')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS]));

    //top up crypto
    Route::post('topup-crypto-transactions/transaction/{id}', 'Backoffice\OperationController@addTopUpCryptoTransaction')->name('backoffice.add.topup.crypto.transaction')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));


    Route::post('bank-details', 'Backoffice\BankDetailsController@store')->name('backoffice.bank.details.store')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT]));

    Route::put('bank-details', 'Backoffice\BankDetailsController@update')->name('backoffice.bank.details.update')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT]));

    Route::post('bank-details-delete', 'Backoffice\BankDetailsController@delete')->name('backoffice.bank.details.delete')
    ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT]));

    Route::post('drop-account', 'Backoffice\BankDetailsController@dropAccount')->name('backoffice.account.drop');
    Route::get('account/{id}', 'Backoffice\BankDetailsController@getAccountWithWire')->name('backoffice.bank.account.wire');
    Route::post('check-wallet', 'Backoffice\BankDetailsController@checkWalletAddress')->name('backoffice.crypto.check.address')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_EDIT_BANK_DETAILS_FOR_CLIENT]));

    Route::post('profile/wallets/withdraw-crypto/get-withdraw-fee', 'Backoffice\WithdrawCryptoController@getWithdrawalFee')->name('withdraw-crypto-fee');
    Route::get('notification-body/{title}', 'Backoffice\NotificationsController@getNotificationBodyByTitle')->name('get.notification.body');
    Route::post('approve-transaction/{id}', 'Backoffice\OperationController@approveTransaction')->name('approve-transaction');
    Route::post('profile/withdraw-crypto', 'Backoffice\WithdrawCryptoController@withdrawCrypto')->name('backoffice.wallets.withdraw.crypto');
    Route::get('profile/fiat-wallets/{id}', 'Backoffice\CProfileController@viewFiatWallet')->name('backoffice.profile.fiat.wallet')->middleware('enable.fiat.wallets');

    Route::get('profile/wallets/{id}','Backoffice\CProfileController@viewWallet')->name('backoffice.profile.wallet')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));



    Route::get('tickets', 'Backoffice\TicketsController@index')->name('backoffice.tickets')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]));

    Route::get('tickets', 'Backoffice\TicketsController@index')->name('backoffice.tickets');
    Route::post('store-ticket', 'Backoffice\TicketsController@storeTicket')->name('backoffice.store.ticket');
    Route::get('ticket/{id}/{toClient}', 'Backoffice\TicketsController@getTicket')->name('backoffice.get.ticket');
    Route::post('ticket-message', 'Backoffice\TicketsController@sendTicketMessage')->name('backoffice.send.ticket.message');
    Route::get('close-ticket/{id}', 'Backoffice\TicketsController@closeTicket')->name('backoffice.close.ticket');
    Route::get('view-message/{ticketId}/{type}', 'Backoffice\TicketsController@viewMessage')->name('backoffice.view.message');

    Route::get('ticket/{id}/{toClient}', 'Backoffice\TicketsController@getTicket')->name('backoffice.get.ticket')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]));

    Route::post('ticket-message', 'Backoffice\TicketsController@sendTicketMessage')->name('backoffice.send.ticket.message')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]));

    Route::get('close-ticket/{id}', 'Backoffice\TicketsController@closeTicket')->name('backoffice.close.ticket')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]));

    Route::get('view-message/{ticketId}/{type}', 'Backoffice\TicketsController@viewMessage')->name('backoffice.view.message')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS, \App\Enums\BUserPermissions::ADD_ANSWER_TICKETS]));

    Route::post('export-operations', 'Backoffice\ReportController@getOperationsCsv')->name('export.operations')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));
    Route::post('export-clients', 'Backoffice\ReportController@getClientsCsv')->name('export.clients')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_CLIENTS]));

    Route::get('withdraw-wire-transactions/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details');
    Route::post('withdraw-wire-transactions/show-bank-detail', 'Backoffice\WithdrawWireController@showBankDetail')->name('backoffice.withdraw.wire.bank.detail');
    Route::get('withdraw-wire-transactions/{operation}', 'Backoffice\WithdrawWireController@showTransaction')->name('backoffice.withdraw.wire.transaction');

    Route::post('transactions/card-transaction/{operation}', 'Backoffice\CardTransactionController@makeTransaction')->name('backoffice.add.card.transaction')
    ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::ADD_TRANSACTION]));
    Route::get('withdraw-to-fiat-transactions/{operation}', 'Backoffice\WithdrawToFiatController@showTransaction')->name('backoffice.withdraw.to.fiat.transaction')->middleware('enable.fiat.wallets');

    Route::get('buy-crypto-from-fiat-transactions/{operation}', 'Backoffice\BuyCryptoFromFiatController@showTransaction')->name('backoffice.buy.crypto.from.fiat.transaction')->middleware('enable.fiat.wallets');
    Route::post('buy-crypto-from-fiat-transactions/{operation}', 'Backoffice\BuyCryptoFromFiatController@makeTransaction')->name('backoffice.add.buy.crypto.from.fiat.transaction')->middleware('enable.fiat.wallets');

    Route::get('buy-fiat-from-crypto-transactions/{operation}', 'Backoffice\BuyFiatFromCryptoController@showTransaction')->name('backoffice.buy.fiat.from.crypto.transaction');
    Route::post('buy-fiat-from-crypto-transactions/{operation}', 'Backoffice\BuyFiatFromCryptoController@makeTransaction')->name('backoffice.add.buy.fiat.from.crypto.transaction');

    Route::post('withdraw-from-fiat-transactions/{id}/add-bank-detail', 'Backoffice\WithdrawFromFiatController@addBankDetail')->name('backoffice.wire.fiat.add.bank.detail');
    Route::get('withdraw-from-fiat-transactions/get-transaction-details', 'Backoffice\OperationController@getTransactionDetails')->name('backoffice.transactions.details');
    Route::get('withdraw-from-fiat-transactions/{operation}', 'Backoffice\WithdrawFromFiatController@showTransaction')->name('backoffice.withdraw.from.fiat.transaction');
    Route::post('withdraw-from-fiat-transactions/get-from-accounts-by-type', 'Backoffice\OperationController@getAccountsByType')->name('backoffice.withdraw.wire.from.accounts.by.type');
    Route::post('withdraw-from-fiat-transactions/get-to-accounts-by-type', 'Backoffice\OperationController@getAccountsByType')->name('backoffice.withdraw.wire.to.accounts.by.type');
    Route::post('withdraw-from-fiat-transactions/get-from-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.get.from.commissions');
    Route::post('withdraw-from-fiat-transactions/get-to-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.to.from.commissions');
    Route::post('withdraw-from-fiat-transactions/get-accounts-by-currency', 'Backoffice\OperationController@getAccountsByCurrency')->name('backoffice.withdraw.wire.get.account.currency');
    Route::post('withdraw-from-fiat-transactions/{operation}', 'Backoffice\WithdrawFromFiatController@makeTransaction')->name('backoffice.add.withdraw.fiat.wire.transaction');


    Route::post('transactions/card-transaction/{operation}', 'Backoffice\CardTransactionController@makeTransaction')->name('backoffice.add.card.transaction');
    Route::get('card-transactions/{operation}', 'Backoffice\CardTransactionController@show')->name('backoffice.card.transaction');

    Route::get('collected-fee-withdraw-transactions/{operation}', 'Backoffice\CollectedCryptoFeeController@show')->name('backoffice.system.fee.withdraw.transaction');
    Route::post('collected-fee-withdraw-transactions/get-transaction-details', 'Backoffice\CollectedCryptoFeeController@getTransactionDetails')->name('backoffice.system.fee.withdraw.transaction.details');
    Route::post('collected-fee-withdraw-transactions/get-fees', 'Backoffice\CollectedCryptoFeeController@getFeesForNotCollectedTransactions')->name('get.not.collected.transactions.fee');

    Route::post('withdraw-wire-transactions/get-from-accounts-by-type', 'Backoffice\WithdrawWireController@getAccountsByType')->name('backoffice.withdraw.wire.from.accounts.by.type');
    Route::post('withdraw-wire-transactions/get-to-accounts-by-type', 'Backoffice\WithdrawWireController@getAccountsByType')->name('backoffice.withdraw.wire.to.accounts.by.type');

    Route::post('withdraw-wire-transactions/get-accounts-by-type', 'Backoffice\OperationController@getFromAndToAccountsForOperation')->name('backoffice.withdraw.wire.to.accounts.by.type');
    Route::post('withdraw-wire-transactions/get-to-address', 'Backoffice\OperationController@getCryptoAccountDetailAddress')->name('backoffice.withdraw.wire.to.account.address');



    Route::post('withdraw-wire-transactions/get-from-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.get.from.commissions');
    Route::post('withdraw-wire-transactions/get-to-commissions', 'Backoffice\OperationController@getCommissions')->name('backoffice.withdraw.wire.to.from.commissions');
    Route::post('withdraw-wire-transactions/get-accounts-by-currency', 'Backoffice\OperationController@getAccountsByCurrency')->name('backoffice.withdraw.wire.get.account.currency');
    Route::post('withdraw-wire-transactions/{operation}', 'Backoffice\WithdrawWireController@makeTransaction')->name('backoffice.add.withdraw.wire.transaction');
    Route::post('withdraw-wire-transactions/transaction/{id}/change-status', 'Backoffice\OperationController@changeStatus')->name('backoffice.withdraw.transaction.change.status')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION, \App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS]));
    Route::post('withdraw-wire-transactions/{id}/confirm', 'Backoffice\WithdrawWireController@confirmTransaction')->name('backoffice.confirm.withdraw.wire.transaction');
    Route::post('withdraw-wire-transactions/{id}/add-bank-detail', 'Backoffice\WithdrawWireController@addBankDetail')->name('backoffice.wire.add.bank.detail');


    Route::post('block-wallet', 'Backoffice\WalletController@blockWallet')->name('backoffice.wallet.block');
    Route::post('unblock-wallet', 'Backoffice\WalletController@unblockWallet')->name('backoffice.wallet.unblock');

    Route::get('backoffice-download-ticket-message-pdf-file/{file}', 'Backoffice\TicketsController@downloadTicketMessagePdfFile')->name('backoffice.download.ticket.message.pdf.file');
    Route::post('transactions/transaction/{operation}/approve-operation', 'Backoffice\OperationController@approveOperation')->name('backoffice.approve.operation.status');

    Route::get('get-payment-systems', 'Backoffice\CreditCardProviderController@getPaymentSystem');
    Route::get('provider/{account}', 'Backoffice\DashboardController@account')->name('dashboard.account');
    Route::get('collected-crypto-fee', 'Backoffice\CollectedCryptoFeeController@collectedCryptoFee')->name('collected.fee')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_COLLECTED_CRYPTO_FEES]));

    Route::post('withdraw/collected-crypto-fee', 'Backoffice\CollectedCryptoFeeController@withdrawCollectedFee')->name('make.withdraw.collected.fees')
        ->middleware('check.permissions:' . implode('-', [ \App\Enums\BUserPermissions::WITHDRAW_COLLECTED_CRYPTO_FEES]));

    Route::post('provider/make-provider-operation', 'Backoffice\DashboardController@createProviderOperation')->name('backoffice.make.provider.operation')
        ->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT]));

    Route::post('provider/get-from-accounts-by-currency', 'Backoffice\OperationController@getProviderAccountsByCurrency')->name('backoffice.provider.from.accounts.by.type');
    Route::post('provider/change-account-status', 'Backoffice\AccountController@changeAccountStatus')->name('backoffice.change.provider.account.status');

    Route::post('withdraw-card-to-payment', 'Backoffice\DashboardController@withdraw')->name('dahboard.withdraw.card.to.payment');
    Route::get('to-provider-accounts/{provider}/{currency}', 'Backoffice\DashboardController@toProviderAccounts')->name('dashboard.toprovider.accounts');


    Route::get('provider-operation/{operation}/{account?}', 'Backoffice\DashboardController@showOperation')->name('dashboard.provider.operation.details');

    Route::get('merchant-operations', 'Backoffice\OperationController@showMerchantOperationsCsvFilterPage')->name('backoffice.merchant.operations.pdf')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));
    Route::post('merchant-operations', 'Backoffice\OperationController@generateCsvForMerchantsOperations')->name('backoffice.merchant.operations.pdf.post')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));
    Route::post('merchant-operations-export', 'Backoffice\ReportController@generateCsvForMerchantsOperations')->name('backoffice.merchant.operations.export')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));
    Route::post('backoffice-operation-report-pdf', 'Backoffice\ReportController@downloadOperationReportPdf')
        ->name('backoffice.download.operation.report.pdf')->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_OPERATION]));

    Route::any('cratos-sandbox', 'Backoffice\CratosSandboxController@index')->name('cratos.sandbox');

    Route::get('download-operation-report-pdf', 'Backoffice\OperationController@downloadOperationReportPdf')
        ->name('cabinet.download.operation.report.pdf');
    Route::any('whalex-sandbox', 'Backoffice\WhalexSandboxController@index')->name('whalex.sandbox');

    Route::get('/api-clients/token', 'Backoffice\ApiClientsController@generateToken')
        ->name('api_clients.generate_token');
    Route::resource('/api-clients', 'Backoffice\ApiClientsController');
    Route::resource('cards', 'Backoffice\WallesterController', ['as' => 'backoffice.wallester']);
    Route::get('details/{id?}', 'Backoffice\WallesterController@viewCardDetails')->name('backoffice.wallester.card.details');

    Route::post('block-card/{id}', 'Backoffice\WallesterController@blockCardByAdmin')->name('wallester.block.card.admin');
    Route::post('unblock-card/{id}', 'Backoffice\WallesterController@unblockCardByAdmin')->name('wallester.unblock.card.admin');

});


Route::post('payment/form/login', 'Cabinet\PaymentFormController@loginUser')->name('payment.form.login.user');
Route::post('payment/form/save-initial-data/{paymentForm}', 'Cabinet\PaymentFormController@saveInitialData')->name('payment.form.save.initial.data');
Route::get('payment/form/pay/{operationId}', 'Cabinet\PaymentFormController@redirectToTrustPaymentsPage')->name('redirect.trust.payments');
Route::get('payment/form/{paymentForm}', 'Cabinet\PaymentFormController@index');
Route::post('submit/payment/form/{paymentForm}', 'Cabinet\PaymentFormController@createOperation')->name('create.operation.by.payment.form');
Route::post('payment-verify-phone', 'Cabinet\PaymentFormController@verifyPhoneNumber')->name('verify.phone.number.payment.form');
Route::post('payment-verify-sms-code', 'Cabinet\PaymentFormController@confirmCode')->name('verify.sms.code.payment.form');
Route::post('payment-verify-email', 'Cabinet\PaymentFormController@verifyEmail')->name('verify.email.payment.form');
Route::post('payment-verify-email-code', 'Cabinet\PaymentFormController@verifyEmailCode')->name('verify.email.code.payment.form');
Route::post('payment-verify-compliance-status', 'Cabinet\PaymentFormController@verifyComplianceStatus')->name('verify.compliance.status.payment.form');
Route::post('payment-form-reset-url', 'Cabinet\PaymentFormController@resetPaymentForm')->name('verify.compliance.reset.payment.form');
Route::post('payment-verify-wallet-address', 'Cabinet\PaymentFormController@verifyWalletAddress')->name('verify.wallet.address.payment.form');
Route::post('get-compliance-data-url', 'Cabinet\PaymentFormController@getComplianceData')->name('payment.form.compliance.data');
Route::post('get-min-payment-amount/{paymentForm}', 'Cabinet\PaymentFormController@getMinPaymentAmount')->name('payment.form.get.min.amount');
Route::post('crypto/get-min-payment-amount/{paymentForm}', 'Cabinet\PaymentFormCryptoController@getMinCryptoPaymentAmount')->name('payment.form.crypto.get.min.amount');
Route::post('get-changed-payment-amount/{paymentForm}', 'Cabinet\PaymentFormCryptoController@getChangedCryptoPaymentAmount')->name('payment.form.crypto.get.change.amounts');
Route::post('verify-payment-form', 'Cabinet\PaymentFormController@verifyPaymentForm')->name('payment.form.verify.url');


Route::get('crypto/payment/form/{paymentForm}', 'Cabinet\PaymentFormCryptoController@index')->name('payment.form.crypto');
Route::post('crypto/payment/form/save-initial-data/{paymentForm}', 'Cabinet\PaymentFormCryptoController@saveInitialData')->name('payment.form.crypto.save.initial.data');
Route::post('crypto/payment/form/save-payer-data', 'Cabinet\PaymentFormCryptoController@savePayerData')->name('save.payer.data.payment.form.crypto');

Route::post('crypto/check', 'Cabinet\PaymentFormCryptoController@checkPayment')->name('check.crypto.to.crypto.pf.payment');

Route::get('compliance/change/{token}', 'Cabinet\API\ComplianceController@updateComplianceLevelWithToken')->name('api.v1.cabinet.compliance.token.post');
