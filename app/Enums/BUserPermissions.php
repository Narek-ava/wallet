<?php


namespace App\Enums;


class BUserPermissions extends Enum
{

    //don't change values
    const VIEW_OPERATION = 'View Operation';
    const ADD_TRANSACTION = 'Add Transaction';
    const CHANGE_OPERATION_STATUS = 'Change Operation Status';
    const VIEW_CLIENTS = 'View Clients';
    const ADD_EDIT_CLIENTS = 'Add/Edit Clients';
    const REQUEST_COMPLIANCE = 'Request Compliance';
    const ADD_EDIT_BANK_DETAILS_FOR_CLIENT = 'Add/Edit Bank Details';
    const ADD_ANSWER_TICKETS = 'View/Edit Tickets';
    const ADD_BANK_CARD_FOR_CLIENT = 'Add Bank Cards';
    const VIEW_PAYMENT_FORMS = 'View Payment Forms';
    const ADD_AND_EDIT_PAYMENT_FORMS = 'Add/Edit Payment Forms';
    const VIEW_PROVIDERS = 'View Providers';
    const TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT = 'Make Withdraw/TopUp For Providers';
    const ADD_EDIT_PROVIDERS = 'Add/Edit Providers';
    const EDIT_CARD_ISSUERS = 'Edit Card Issuers';
    const VIEW_COUNTRIES = 'View Countries';
    const ADD_AND_EDIT_COUNTRIES = 'Add/Edit Countries';
    const VIEW_CLIENT_RATES = 'View Client Rates';
    const ADD_AND_EDIT_CLIENT_RATES = 'Add/Edit Client Rates';
    const VIEW_API_CLIENTS = 'View Api Clients';
    const ADD_AND_EDIT_API_CLIENTS = 'Add/Edit Api clients';
    const VIEW_COLLECTED_CRYPTO_FEES = 'View Collected Crypto Fees';
    const WITHDRAW_COLLECTED_CRYPTO_FEES = 'Withdraw Collected Crypto Fees';
    const VIEW_CLIENT_WALLETS = 'View Shared System Wallets';
    const EDIT_CLIENT_WALLETS = 'Edit Shared System Wallets';
    const VIEW_PROJECTS = 'View Projects';
    const EDIT_PROJECTS = 'Edit Projects';
    const VIEW_ROLES = 'View Roles';
    const ADD_AND_EDIT_ROLES = 'Add/Edit Roles';
    const VIEW_ADDRESS_SETTINGS = 'View Address Settings';
    const ADD_AND_UPDATE_ADDRESS_SETTINGS = 'Add/Edit Address Settings';
    const VIEW_NEW_NOTIFICATIONS = 'View Notifications';
    const ADD_NEW_NOTIFICATIONS = 'Add Notifications';
    const RECEIVE_NOTIFICATIONS = 'Receive Notifications';

    const NAMES = [
        self::VIEW_ROLES,
        self::ADD_AND_EDIT_ROLES,
        self::VIEW_OPERATION,
        self::ADD_TRANSACTION,
        self::CHANGE_OPERATION_STATUS,
        self::VIEW_PROVIDERS,
        self::TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT,
        self::VIEW_CLIENTS,
        self::ADD_EDIT_CLIENTS,
        self::REQUEST_COMPLIANCE,
        self::ADD_EDIT_BANK_DETAILS_FOR_CLIENT,
        self::ADD_ANSWER_TICKETS,
        self::ADD_BANK_CARD_FOR_CLIENT,
        self::VIEW_PAYMENT_FORMS,
        self::ADD_AND_EDIT_PAYMENT_FORMS,
        self::ADD_NEW_NOTIFICATIONS,
        self::VIEW_NEW_NOTIFICATIONS,
        self::VIEW_ADDRESS_SETTINGS,
        self::ADD_AND_UPDATE_ADDRESS_SETTINGS,
        self::VIEW_CLIENT_WALLETS,
        self::EDIT_CLIENT_WALLETS,
        self::VIEW_COLLECTED_CRYPTO_FEES,
        self::WITHDRAW_COLLECTED_CRYPTO_FEES,
        self::VIEW_PROJECTS,
        self::EDIT_PROJECTS,
        self::VIEW_API_CLIENTS,
        self:: ADD_AND_EDIT_API_CLIENTS,
        self:: VIEW_CLIENT_RATES,
        self:: ADD_AND_EDIT_CLIENT_RATES,
        self::VIEW_COUNTRIES,
        self::ADD_AND_EDIT_COUNTRIES ,
        self::EDIT_CARD_ISSUERS,
        self::ADD_EDIT_PROVIDERS,
        self::RECEIVE_NOTIFICATIONS,
    ];

     const PERMISSIONS_WITH_GROUPS = [
         'ui_permission_clients' => [
             self::VIEW_CLIENTS,
             self::ADD_EDIT_CLIENTS,
             self::REQUEST_COMPLIANCE,
             self::ADD_EDIT_BANK_DETAILS_FOR_CLIENT,
             self::ADD_ANSWER_TICKETS,
             self::ADD_BANK_CARD_FOR_CLIENT,
             self::VIEW_NEW_NOTIFICATIONS,
             self::ADD_NEW_NOTIFICATIONS,
             self::RECEIVE_NOTIFICATIONS,
         ],
         'ui_permission_operations' => [
             self::VIEW_OPERATION,
             self::ADD_TRANSACTION,
             self::CHANGE_OPERATION_STATUS,
         ],
         'ui_permission_addresses' => [
             self::VIEW_ADDRESS_SETTINGS,
             self::ADD_AND_UPDATE_ADDRESS_SETTINGS,
         ],
         'ui_permission_roles' => [
             self::VIEW_ROLES,
             self::ADD_AND_EDIT_ROLES,
         ],
         'ui_permission_projects' => [
             self::VIEW_PROJECTS,
             self::EDIT_PROJECTS,
         ],
         'ui_permission_client_wallets' =>  [
             self::VIEW_CLIENT_WALLETS,
             self::EDIT_CLIENT_WALLETS,
         ],
         'ui_permission_collected_crypto_fee' => [
             self::VIEW_COLLECTED_CRYPTO_FEES,
             self::WITHDRAW_COLLECTED_CRYPTO_FEES
         ],
         'ui_permission_country' => [
             self::VIEW_COUNTRIES,
             self::ADD_AND_EDIT_COUNTRIES,
         ],
         'ui_permission_providers' => [
             self::VIEW_PROVIDERS,
             self::TOP_UP_AND_WITHDRAW_BY_PROVIDERS_ACCOUNT,
             self::ADD_EDIT_PROVIDERS,
             self::EDIT_CARD_ISSUERS
         ],
         'ui_permission_payment_form' => [
             self::VIEW_PAYMENT_FORMS,
             self::ADD_AND_EDIT_PAYMENT_FORMS
         ],

         'ui_permission_api_client' => [
             self::VIEW_API_CLIENTS,
             self::ADD_AND_EDIT_API_CLIENTS
         ],
         'ui_permission_rate' => [
             self::VIEW_CLIENT_RATES,
             self::ADD_AND_EDIT_CLIENT_RATES
         ]

     ];
}
