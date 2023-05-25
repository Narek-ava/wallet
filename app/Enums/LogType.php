<?php


namespace App\Enums;


use function Complex\sec;

class LogType extends Enum
{
    const TYPE_B_USER_LOGIN = 1;
    const TYPE_C_PROFILE_STATUS_CHANGE = 2;
    const TYPE_C_PROFILE_COMPLIANCE_INIT = 3;
    const TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT = 4;
    const TYPE_C_PROFILE_COMPLIANCE_REQUEST_NOT_FOUND = 5;
    const TYPE_C_PROFILE_COMPLIANCE_REQUEST_C_PROFILE_NOT_FOUND = 6;
    const TYPE_C_PROFILE_COMPLIANCE_LEVEL_UP = 7;
    const TYPE_C_PROFILE_COMPLIANCE_REQUEST_STATUS_CHANGE = 8;
    const TYPE_RATES_CREATE = 9;
    const TYPE_RATES_UPDATE = 10;
    const TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_RETRY = 11;
    const TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY = 12;
    const TYPE_C_PROFILE_COMPLIANCE_REQUEST_DATE_RENEW = 13;
    const TYPE_COMPLIANCE_SUCCESS_MAIL = 14;
    const TYPE_COMPLIANCE_FAIL_MAIL = 15;
    const TYPE_ADD_NEW_WALLET = 16;
    const TYPE_TOP_UP_CRYPTO_SUCCESS = 17;
    const TYPE_TOP_UP_CRYPTO_FAIL = 18;
    const TYPE_TOP_UP_CRYPTO_MAIL_SUCCESS = 19;
    const TYPE_TOP_UP_CRYPTO_MAIL_FAIL = 20;
    const TRANSACTION_ADDED_SUCCESS = 21;
    const TRANSACTION_ADDED_FAIL = 22;
    const EXCHANGE_ADDED = 23;


    const TYPE_USER_BANK_ACCOUNT_ADDED = 24;
    const TYPE_WIRE_ACCOUNT_DETAIL_ADDED = 25;
    const TYPE_USER_BANK_ACCOUNT_UPDATED = 26;
    const TYPE_WIRE_ACCOUNT_DETAIL_UPDATED = 27;
    const TYPE_USER_BANK_ACCOUNT_DELETED = 28;
    const TYPE_USER_CRYPTO_ACCOUNT_ADDED = 29;
    const TYPE_CRYPTO_ACCOUNT_DETAIL_ADDED = 30;
    const TYPE_USER_CRYPTO_ACCOUNT_NOT_ADDED = 31;

    const STATUS_CHANGED_SUCCESS = 32;
    const STATUS_CHANGED_FAIL = 33;

    const TYPE_WALLET_BLOCKED = 34;
    const TYPE_WALLET_UNBLOCKED = 35;

    const TYPE_C_PROFILE_PASSWORD_CHANGE = 36;
    const TYPE_C_PROFILE_PASSWORD_RESTORE = 37;
    const TYPE_C_PROFILE_INFORMATION_CHANGE_BACKOFFICE = 38;
    const TYPE_C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE = 39;
    const TYPE_C_PROFILE_MANAGER_CHANGE_BACKOFFICE = 40;
    const TYPE_C_PROFILE_EMAIL_CHANGE_BACKOFFICE = 41;
    const TYPE_CRYPTO_WALLET_ADDED_BACKOFFICE = 42;
    const WITHDRAW_FAILED= 43;
    const TYPE_NEW_TICKET_ADDED_BACKOFFICE = 44;
    const TYPE_TICKET_ANSWER_BACKOFFICE = 45;
    const TYPE_COMPLIANCE_REQUEST_CANCELED = 46;
    const TYPE_2FA_EMAIL_ENABLED = 47;
    const TYPE_2FA_EMAIL_DISABLED = 48;
    const TYPE_2FA_GOOGLE_ENABLED = 49;
    const TYPE_2FA_GOOGLE_DISABLED = 50;
    const TYPE_NEW_TICKET_MESSAGE_ADDED_BACKOFFICE = 51;
    const TYPE_TICKET_CLOSED_BACKOFFICE = 52;
    const TYPE_NEW_TICKET_ADDED_CABINET = 53;
    const TYPE_NEW_TICKET_MESSAGE_ADDED_CABINET = 54;
    const TYPE_TICKET_CLOSED_CABINET = 55;
    const TYPE_EXCHANGE_WITHDRAW = 56;
    const TYPE_RATE_TEMPLATE_CHANGED = 57;

    const TYPE_C_PROFILE_COMPLIANCE_LEVEL_MANUAL_CHANGE = 58;

    const TYPE_NEW_CARD_ACCOUNT_ADDED = 59;
    const TYPE_NEW_CARD_OPERATION_ADDED = 60;
    const TYPE_CARD_OPERATION_FAILED = 61;
    const TYPE_CARD_OPERATION_SUCCESS = 62;
    const TYPE_CARD_OPERATION_PAYMENT_RESPONSE_SUCCESS = 63;
    const TYPE_CARD_OPERATION_REFUND = 64;
    const TYPE_CARD_OPERATION_CHARGEBACK = 65;
    const TYPE_WITHDRAW_CRYPTO_SUCCESS = 66;
    const TYPE_WITHDRAW_CRYPTO_FAIL = 67;
    const TYPE_WITHDRAW_WIRE_SUCCESS = 68;
    const TYPE_CONFIRM_TRANSACTION_SUCCESS = 69;
    const TYPE_APPROVE_TRANSACTION_SUCCESS = 70;
    const TYPE_APPROVE_WITHDRAW_CRYPTO_SUCCESS = 71;
    const TYPE_APPROVE_TOP_UP_CRYPTO_SUCCESS = 72;
    const TYPE_ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS = 73;

    const TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND = 74;
    const TYPE_PAYMENT_FORM_MISSING_TO_ACCOUNT = 75;
    const TYPE_PAYMENT_FORM_VALIDATE_CARD_OPERATION_LIMITS = 76;
    const TYPE_PAYMENT_FORM_AMOUNT_LOWER_AMOUNT = 77;
    const TYPE_PAYMENT_FORM_OPERATION_SAVE_ERROR = 78;
    const TYPE_PAYMENT_FORM_OPERATION_SAVE_SUCCESS = 79;
    const TYPE_PAYMENT_FORM_VERIFY_PHONE_ERROR = 80;
    const TYPE_PAYMENT_FORM_CONFIRM_VERIFY_CODE_ERROR = 81;
    const TYPE_PAYMENT_FORM_VERIFY_EMAIL_ERROR = 82;
    const TYPE_PAYMENT_FORM_VERIFY_EMAIL_CODE_ERROR = 83;
    const TYPE_PAYMENT_FORM_VERIFY_COMPLIANCE_STATUS_ERROR = 84;
    const TYPE_PAYMENT_FORM_USER_LOGIN_ERROR = 85;

    const TYPE_C_PROFILE_INFORMATION_CHANGE_CABINET = 86;
    const TYPE_C_PROFILE_EMAIL_CHANGE_CABINET = 87;
    const TYPE_C_PROFILE_EMAIL_SEND_CABINET = 88;
    const TRANSACTION_ADDED = 89;

    const TYPE_ADMIN_2FA_GOOGLE_ENABLED = 90;
    const TYPE_ADMIN_2FA_GOOGLE_DISABLED = 91;
    const TYPE_NEW_OPERATION_CREATED = 100;
    const TYPE_ADD_NEW_FIAT_WALLET = 101;


    const NAMES = [
        self::TYPE_B_USER_LOGIN => 'enum_log_type_b_user_login',
        self::TYPE_C_PROFILE_STATUS_CHANGE => 'enum_log_type_c_profile_status_change',
        self::TYPE_C_PROFILE_PASSWORD_CHANGE => 'enum_log_type_c_profile_password_change',
        self::TYPE_C_PROFILE_INFORMATION_CHANGE_BACKOFFICE => 'enum_log_type_c_profile_information_change',
        self::TYPE_C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE => 'enum_log_type_c_profile_compliance_officer_change',
        self::TYPE_C_PROFILE_MANAGER_CHANGE_BACKOFFICE => 'enum_log_type_c_profile_manager_change',
        self::TYPE_C_PROFILE_EMAIL_CHANGE_BACKOFFICE => 'enum_log_c_profile_email_change',
        self::TYPE_CRYPTO_WALLET_ADDED_BACKOFFICE => 'enum_log_user_crypto_account_added',
        self::TYPE_NEW_TICKET_ADDED_BACKOFFICE => 'enum_log_user_new_ticket_added',
        self::TYPE_NEW_TICKET_ADDED_CABINET => 'enum_log_user_new_ticket_added',
        self::TYPE_NEW_TICKET_MESSAGE_ADDED_BACKOFFICE => 'enum_log_user_new_ticket_message_added',
        self::TYPE_NEW_TICKET_MESSAGE_ADDED_CABINET => 'enum_log_user_new_ticket_message_added',
        self::TYPE_TICKET_CLOSED_BACKOFFICE => 'enum_log_user_ticket_closed',
        self::TYPE_TICKET_CLOSED_CABINET => 'enum_log_user_ticket_closed',
        self::TYPE_TICKET_ANSWER_BACKOFFICE => 'enum_log_answer_to_ticket',
        self::TYPE_C_PROFILE_PASSWORD_RESTORE => 'enum_log_type_c_profile_password_restore',
        self::TYPE_C_PROFILE_COMPLIANCE_INIT => 'enum_log_type_c_profile_compliance_init',
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT => 'enum_log_type_c_profile_compliance_request_submit',
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_NOT_FOUND => 'enum_log_type_c_profile_compliance_request_not_found',
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_C_PROFILE_NOT_FOUND => 'enum_log_type_c_profile_compliance_request_c_profile_not_found',
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_STATUS_CHANGE => 'enum_log_type_c_profile_compliance_request_status_changed',
        self::TYPE_C_PROFILE_COMPLIANCE_LEVEL_UP => 'enum_log_type_c_profile_compliance_level_up',
        self::TYPE_RATES_CREATE => 'enum_log_type_rates_create',
        self::TYPE_RATES_UPDATE => 'enum_log_type_rates_update',
        self::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_RETRY => 'enum_log_type_c_profile_compliance_documents_retry',
        self::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY => 'enum_log_type_c_profile_compliance_documents_auto_retry',
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_DATE_RENEW => 'enum_log_type_c_profile_compliance_request_date_renew',
        self::TYPE_COMPLIANCE_REQUEST_CANCELED =>  'enum_log_type_c_profile_compliance_request_cancel',
        self::TYPE_2FA_EMAIL_ENABLED =>  'enum_log_type_2fa_email_enabled',
        self::TYPE_2FA_EMAIL_DISABLED =>  'enum_log_type_2fa_email_disabled',
        self::TYPE_2FA_GOOGLE_ENABLED =>  'enum_log_type_2fa_google_enabled',
        self::TYPE_ADMIN_2FA_GOOGLE_ENABLED =>  'enum_log_type_admin_2fa_google_enabled',
        self::TYPE_2FA_GOOGLE_DISABLED =>  'enum_log_type_2fa_google_disabled',
        self::TYPE_ADMIN_2FA_GOOGLE_DISABLED =>  'enum_log_type_admin_2fa_google_disabled',
        self::TYPE_COMPLIANCE_SUCCESS_MAIL => 'enum_log_type_c_profile_compliance_request_successfully_passed',
        self::TYPE_COMPLIANCE_FAIL_MAIL => 'enum_log_type_c_profile_compliance_request_failed',
        self::TYPE_ADD_NEW_WALLET => 'enum_log_type_add_new_wallet_passed',
        self::TYPE_TOP_UP_CRYPTO_SUCCESS => 'enum_log_type_top_up_crypto_success',
        self::TYPE_TOP_UP_CRYPTO_FAIL => 'enum_log_type_top_up_crypto_fail',
        self::TRANSACTION_ADDED_SUCCESS => 'enum_log_type_add_transaction_success',
        self::TRANSACTION_ADDED_FAIL => 'enum_log_type_add_transaction_fail',
        self::EXCHANGE_ADDED => 'enum_log_type_exchange_added',

        self::STATUS_CHANGED_SUCCESS => 'enum_log_type_status_changed_success',
        self::STATUS_CHANGED_FAIL => 'enum_log_type_status_changed_fail',

        self::TYPE_USER_BANK_ACCOUNT_ADDED => 'enum_log_type_user_bank_account_added',
        self::TYPE_WIRE_ACCOUNT_DETAIL_ADDED => 'enum_log_type_wire_account_detail_added',
        self::TYPE_USER_BANK_ACCOUNT_UPDATED => 'enum_log_type_bank_account_detail_updated',
        self::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED => 'enum_log_type_wire_account_detail_updated',
        self::TYPE_USER_BANK_ACCOUNT_DELETED => 'enum_log_type_user_bank_account_deleted',
        self::TYPE_USER_CRYPTO_ACCOUNT_ADDED => 'enum_log_type_user_crypto_account_added',
        self::TYPE_CRYPTO_ACCOUNT_DETAIL_ADDED => 'enum_log_type_user_crypto_account_detail_added',
        self::TYPE_USER_CRYPTO_ACCOUNT_NOT_ADDED => 'enum_log_type_user_crypto_account_not_added',
        self::WITHDRAW_FAILED => 'enum_log_type_user_crypto_account_not_added',
        self::TYPE_WALLET_BLOCKED => 'enum_log_type_user_crypto_wallet_blocked',
        self::TYPE_WALLET_UNBLOCKED => 'enum_log_type_user_crypto_wallet_unblocked',
        self::TYPE_EXCHANGE_WITHDRAW => 'log_message_exchange_withdraw',
        self::TYPE_RATE_TEMPLATE_CHANGED => 'log_message_rate_template_change',

        self::TYPE_C_PROFILE_COMPLIANCE_LEVEL_MANUAL_CHANGE => 'log_message_compliance_level_manual_change',

        self::TYPE_NEW_CARD_ACCOUNT_ADDED => 'enum_log_type_new_card_account_added',
        self::TYPE_NEW_CARD_OPERATION_ADDED => 'enum_log_type_new_card_operation_added',
        self::TYPE_CARD_OPERATION_FAILED => 'enum_log_type_card_operation_failed',
        self::TYPE_CARD_OPERATION_SUCCESS => 'enum_log_type_card_operation_success',
        self::TYPE_CARD_OPERATION_PAYMENT_RESPONSE_SUCCESS => 'enum_log_type_card_operation_payment_response_success',
        self::TYPE_CARD_OPERATION_REFUND => 'enum_log_type_card_operation_refund',
        self::TYPE_CARD_OPERATION_CHARGEBACK => 'enum_log_type_card_operation_chargeback',

        self::TYPE_C_PROFILE_INFORMATION_CHANGE_CABINET => 'enum_log_type_c_profile_information_change',
        self::TYPE_C_PROFILE_EMAIL_CHANGE_CABINET => 'enum_log_c_profile_email_change',
        self::TYPE_C_PROFILE_EMAIL_SEND_CABINET => 'enum_log_c_profile_email_send',
        self::TYPE_WITHDRAW_CRYPTO_SUCCESS => 'enum_log_type_withdraw_crypto_success',
        self::TYPE_WITHDRAW_WIRE_SUCCESS => 'enum_log_type_withdraw_wire_success',
        self::TYPE_CONFIRM_TRANSACTION_SUCCESS => 'enum_log_type_confirm_transaction_success',
        self::TYPE_APPROVE_TRANSACTION_SUCCESS => 'enum_log_type_approve_transaction_success',
        self::TRANSACTION_ADDED => 'enum_log_type_add_transaction_success',
        self::TYPE_NEW_OPERATION_CREATED => 'enum_log_type_new_operation_created',
    ];

    const USER_LOG_TYPES = [
        self::TYPE_NEW_OPERATION_CREATED,
        self::TYPE_C_PROFILE_COMPLIANCE_INIT,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_NOT_FOUND,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_C_PROFILE_NOT_FOUND,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_STATUS_CHANGE,
        self::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY,
        self::TYPE_C_PROFILE_COMPLIANCE_LEVEL_UP,
        self::TYPE_COMPLIANCE_SUCCESS_MAIL,
        self::TYPE_COMPLIANCE_FAIL_MAIL,
        self::TYPE_ADD_NEW_WALLET,
//        self::TYPE_TOP_UP_CRYPTO_SUCCESS,
//        self::TYPE_TOP_UP_CRYPTO_FAIL,
        self::EXCHANGE_ADDED,
        self::TYPE_USER_CRYPTO_ACCOUNT_ADDED,
        self::TYPE_CRYPTO_ACCOUNT_DETAIL_ADDED,
        self::TYPE_USER_CRYPTO_ACCOUNT_NOT_ADDED,
//        self::TYPE_WALLET_BLOCKED,
//        self::TYPE_WALLET_UNBLOCKED,
        self::TYPE_C_PROFILE_PASSWORD_CHANGE,
        self::TYPE_C_PROFILE_PASSWORD_RESTORE,
        self::TYPE_2FA_EMAIL_ENABLED,
        self::TYPE_2FA_EMAIL_DISABLED,
        self::TYPE_2FA_GOOGLE_ENABLED,
        self::TYPE_2FA_GOOGLE_DISABLED,
        self::TYPE_NEW_TICKET_ADDED_CABINET,
        self::TYPE_NEW_TICKET_MESSAGE_ADDED_CABINET,
        self::TYPE_TICKET_CLOSED_CABINET,
        self::TYPE_RATE_TEMPLATE_CHANGED,
        self::TYPE_NEW_CARD_ACCOUNT_ADDED,
        self::TYPE_NEW_CARD_OPERATION_ADDED,
        self::TYPE_CARD_OPERATION_FAILED,
        self::TYPE_CARD_OPERATION_SUCCESS,
        self::TYPE_C_PROFILE_INFORMATION_CHANGE_CABINET,
        self::TYPE_C_PROFILE_EMAIL_CHANGE_CABINET,
        self::TYPE_C_PROFILE_EMAIL_SEND_CABINET,
        self::TYPE_USER_BANK_ACCOUNT_ADDED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_ADDED,
        self::TYPE_USER_BANK_ACCOUNT_UPDATED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED,
        self::TYPE_USER_BANK_ACCOUNT_DELETED,
        self::TYPE_USER_CRYPTO_ACCOUNT_ADDED,
        self::TYPE_CRYPTO_ACCOUNT_DETAIL_ADDED,
        self::TYPE_USER_CRYPTO_ACCOUNT_NOT_ADDED,
        self::TYPE_ADD_NEW_FIAT_WALLET,
    ];

    const MANAGER_LOG_TYPES = [
        self::TRANSACTION_ADDED_SUCCESS,
        self::TYPE_B_USER_LOGIN,
        self::TYPE_C_PROFILE_STATUS_CHANGE,
//        self::TYPE_RATES_CREATE,
//        self::TYPE_RATES_UPDATE,
        self::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_RETRY,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_DATE_RENEW,
        self::TYPE_C_PROFILE_INFORMATION_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_MANAGER_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_EMAIL_CHANGE_BACKOFFICE,
        self::TYPE_USER_BANK_ACCOUNT_ADDED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_ADDED,
        self::TYPE_USER_BANK_ACCOUNT_UPDATED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED,
        self::TYPE_USER_BANK_ACCOUNT_DELETED,
        self::STATUS_CHANGED_SUCCESS,
        self::STATUS_CHANGED_FAIL,
        self::TYPE_CRYPTO_WALLET_ADDED_BACKOFFICE,
        self::TYPE_COMPLIANCE_REQUEST_CANCELED,
        self::TYPE_NEW_TICKET_ADDED_BACKOFFICE,
        self::TYPE_TICKET_ANSWER_BACKOFFICE,
        self::TYPE_NEW_TICKET_MESSAGE_ADDED_BACKOFFICE,
        self::TYPE_TICKET_CLOSED_BACKOFFICE,
        self::TRANSACTION_ADDED_SUCCESS,
        self::TRANSACTION_ADDED_FAIL,
        self::TYPE_EXCHANGE_WITHDRAW,
        self::TYPE_C_PROFILE_COMPLIANCE_LEVEL_MANUAL_CHANGE,
        self::TYPE_CARD_OPERATION_PAYMENT_RESPONSE_SUCCESS,
        self::TYPE_CARD_OPERATION_REFUND,
        self::TYPE_CARD_OPERATION_CHARGEBACK,
        self::TYPE_WITHDRAW_CRYPTO_SUCCESS,
        self::TYPE_WITHDRAW_CRYPTO_FAIL,
        self::TYPE_WITHDRAW_WIRE_SUCCESS,
        self::TYPE_CONFIRM_TRANSACTION_SUCCESS,
        self::TYPE_APPROVE_TRANSACTION_SUCCESS,
        self::TYPE_APPROVE_WITHDRAW_CRYPTO_SUCCESS,
        self::TYPE_APPROVE_TOP_UP_CRYPTO_SUCCESS,
        self::TYPE_ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS,
        self::TYPE_ADMIN_2FA_GOOGLE_DISABLED,
        self::TYPE_ADMIN_2FA_GOOGLE_ENABLED,
    ];

    const MANAGER_HISTORY_LOG_TYPES = [
        self::TYPE_USER_BANK_ACCOUNT_ADDED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_ADDED,
        self::TYPE_USER_BANK_ACCOUNT_UPDATED,
        self::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED,
        self::TYPE_USER_BANK_ACCOUNT_DELETED,
        self::TYPE_CRYPTO_WALLET_ADDED_BACKOFFICE,
        self::TRANSACTION_ADDED,
        self::STATUS_CHANGED_SUCCESS,
        self::STATUS_CHANGED_FAIL,
        self::TRANSACTION_ADDED_FAIL,
        self::TYPE_EXCHANGE_WITHDRAW,
        self::TYPE_CARD_OPERATION_PAYMENT_RESPONSE_SUCCESS,
        self::TYPE_CARD_OPERATION_REFUND,
        self::TYPE_CARD_OPERATION_CHARGEBACK,
        self::TYPE_WITHDRAW_CRYPTO_SUCCESS,
        self::TYPE_WITHDRAW_CRYPTO_FAIL,
        self::TYPE_WITHDRAW_WIRE_SUCCESS,
        self::TYPE_CONFIRM_TRANSACTION_SUCCESS,
        self::TYPE_APPROVE_TRANSACTION_SUCCESS,
        self::TYPE_APPROVE_WITHDRAW_CRYPTO_SUCCESS,
        self::TYPE_APPROVE_TOP_UP_CRYPTO_SUCCESS,
        self::TYPE_ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS,
        self::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_RETRY,
        self::TYPE_C_PROFILE_COMPLIANCE_REQUEST_DATE_RENEW,
        self::TYPE_C_PROFILE_INFORMATION_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_MANAGER_CHANGE_BACKOFFICE,
        self::TYPE_C_PROFILE_EMAIL_CHANGE_BACKOFFICE,
    ];
}
