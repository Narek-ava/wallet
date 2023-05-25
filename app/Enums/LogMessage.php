<?php


namespace App\Enums;


class LogMessage
{
    const B_USER_LOGIN_SUCCESS = 'log_message_b_user_login_success';
    const B_USER_LOGIN_FAILED = 'log_message_b_user_login_failed';
    const C_PROFILE_STATUS_CHANGE = 'log_message_c_profile_status_change';
    const C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE = 'log_message_c_profile_compliance_officer_change';
    const C_PROFILE_MANAGER_CHANGE_BACKOFFICE = 'log_message_c_profile_manager_change';
    const C_PROFILE_EMAIL_CHANGE_BACKOFFICE = 'log_message_c_profile_email_change';
    const C_USER_COMPLIANCE_PAGE_INIT = 'log_message_c_profile_compliance_page_init';
    const C_USER_COMPLIANCE_REQUEST_SUCCESS = 'log_message_c_profile_compliance_request_success';
    const C_USER_COMPLIANCE_REQUEST_SUCCESS_MAIL = 'log_message_c_profile_compliance_request_success_mail';
    const C_USER_COMPLIANCE_REQUEST_FAIL_MAIL = 'log_message_c_profile_compliance_request_fail_mail';
    const C_USER_COMPLIANCE_REQUEST_DOCUMENTS_UPLOADED = 'log_message_c_profile_compliance_documents_uploaded';
    const C_USER_COMPLIANCE_REQUEST_RETRY = 'log_message_c_profile_compliance_request_retry';
    const C_USER_COMPLIANCE_REQUEST_RETRY_SUMSUB = 'log_message_c_profile_compliance_request_retry_sumsub';
    const COMPLIANCE_REQUEST_NOT_FOUND = 'log_message_c_profile_compliance_request_not_found';
    const COMPLIANCE_REQUEST_C_PROFILE_NOT_FOUND = 'log_message_c_profile_compliance_request_c_profile_not_found';
    const COMPLIANCE_LEVEL_UP = 'log_message_c_profile_compliance_level_up';
    const COMPLIANCE_REQUESTED_DOCS_UPLOADED = 'log_message_c_profile_compliance_requested_docs_uploaded';
    const COMPLIANCE_REQUEST_STATUS_CHANGE = 'log_message_c_profile_compliance_request_status_change';
    const COMPLIANCE_REQUEST_CANCELED = 'log_message_c_profile_compliance_request_cancel' ;
    const COMPLIANCE_REQUEST_CANCELED_WITH_OPERATION_NUMBER = 'log_message_c_profile_compliance_request_cancel_with_operation_number_operation' ;
    const C_USER_COMPLIANCE_LEVEL_MANUAL_CHANGE = 'log_message_c_profile_compliance_level_manual_change';
    const RATES_CREATE = 'log_message_rates_create';
    const RATES_UPDATE = 'log_message_rates_update';
    const COMPLIANCE_REQUEST_DOCUMENTS_RETRY = 'log_message_c_profile_compliance_request_documents_retry';
    const COMPLIANCE_DOCUMENTS_AUTO_DELETE = 'log_message_c_profile_compliance_documents_auto_delete';
    const NOTIFY_USER_BEFORE_SUSPEND = 'log_message_c_profile_compliance_notify_before_suspend';
    const SUSPEND_USER = 'log_message_c_profile_compliance_suspend_user';
    const RENEW_COMPLIANCE_REQUEST_DATE = 'log_message_c_profile_compliance_request_date_renew';
    const CREATE_NEW_WALLET_REQUEST = 'log_message_create_wallet_request_success';
    const TOP_UP_CRYPTO_SUCCESS = 'log_message_top_up_crypto_success';
    const TOP_UP_CRYPTO_FAIL = 'log_message_top_up_crypto_fail';
    const TRANSACTION_ADDED_SUCCESSFULLY = 'log_message_transaction_added_success';
    const CARD_ACCOUNT_ADDED_SUCCESSFULLY = 'log_message_card_account_added_success';
    const TRANSACTION_BITGO_ADDED_SUCCESSFULLY = 'log_message_transaction_bitgo_added_success';
    const TRANSACTION_ADDED_FAILED = 'log_message_transaction_added_fail';

    const STATUS_CHANGED_SUCCESSFULLY = 'log_message_status_changed_success';
    const STATUS_CHANGED_FAILED = 'log_message_status_changed_fail';

    const EXCHANGE_SUCCESSFULLY = 'log_message_exchanged';
    const EXCHANGE_WITHDRAW = 'log_message_exchange_withdraw';
    const EXCHANGE_FAILED = 'log_message_exchanged_failed';
    const WITHDRAW_FAILED = 'enum_log_type_withdraw_failed';
    const USER_BANK_ACCOUNT_ADDED = 'log_user_bank_account_added';
    const USER_BANK_DETAILS_ADDED = 'log_user_bank_details_added';
    const USER_BANK_ACCOUNT_UPDATED = 'log_user_bank_account_updated';
    const USER_BANK_DETAILS_UPDATED = 'log_user_bank_details_updated';
    const USER_BANK_ACCOUNT_DELETED = 'log_user_bank_account_deleted';
    const USER_CRYPTO_ACCOUNT_ADDED = 'log_user_crypto_account_added';
    const USER_CRYPTO_DETAILS_ADDED = 'log_user_crypto_account_detail_added';
    const USER_CRYPTO_ACCOUNT_NOT_ADDED = 'log_user_crypto_account_not_added';
    const WALLET_UNBLOCKED = 'log_user_crypto_wallet_unblocked';
    const WALLET_BLOCKED = 'log_user_crypto_wallet_blocked';
    const UPDATE_PASSWORD = 'log_message_password_updated';
    const RESET_PASSWORD = 'log_message_password_reset';

    const USER_PERSONAL_INFORMATION_UPDATED_BACKOFFICE = 'log_user_personal_info_updated';
    const USER_CRYPTO_WALLET_ADDED_BACKOFFICE = 'log_user_crypto_wallet_added';
    const USER_NEW_TICKET_ADDED_BACKOFFICE = 'log_user_new_ticket_added';
    const USER_NEW_TICKET_MESSAGE_ADDED_BACKOFFICE = 'log_user_new_ticket_message_added';
    const USER_TICKET_CLOSED_BACKOFFICE = 'log_user_ticket_closed';
    const USER_TICKET_ANSWER_BACKOFFICE = 'log_user_answer_to_ticket';

    const USER_2FA_EMAIL_WAS_ENABLED = 'message_2fa_email_enable_success';
    const USER_2FA_EMAIL_WAS_DISABLED = 'message_2fa_email_disable_success';
    const USER_2FA_GOOGLE_WAS_ENABLED = 'message_2fa_google_enable_success';
    const USER_2FA_GOOGLE_WAS_DISABLED = 'message_2fa_google_disable_success';
    const USER_RATE_TEMPLATE_WAS_CHANGED = 'message_rate_template_was_changed_success';
    const ADMIN_2FA_GOOGLE_WAS_DISABLED = 'message_admin_2fa_google_disable_success';
    const ADMIN_2FA_GOOGLE_WAS_ENABLED = 'message_admin_2fa_google_enable_success';

    const USER_ADDED_CARD_OPERATION = 'log_message_new_card_operation_added';
    const CARD_OPERATION_SUCCESSFUL_PAYMENT_RESPONSE = 'log_message_card_operation_successful_payment_response';
    const CARD_OPERATION_FAILED_CAUSE_OF_BANK_RESPONSE = 'log_message_card_operation_failed_cause_bank_response';
    const CARD_OPERATION_FAILED_CAUSE_OF_TIME_LIMIT = 'log_message_card_operation_failed_cause_time_limit';
    const CARD_OPERATION_REFUNDED = 'log_message_card_operation_refunded';
    const CARD_OPERATION_FAILED_CAUSE_OF_NOT_MATCHING_PERSONAL_INFO = 'log_message_card_operation_failed_cause_not_matching_personal_info';
    const CARD_OPERATION_FAILED_CAUSE_OF_INVALID_AMOUNT = 'log_message_card_operation_failed_cause_invalid_amount';
    const CARD_OPERATION_FAILED_CAUSE_OF_INVALID_CURRENCY = 'log_message_card_operation_failed_cause_invalid_currency';
    const CARD_OPERATION_FAILED_CAUSE_OF_INVALID_LIMITS = 'log_message_card_operation_failed_cause_invalid_limits';
    const CARD_OPERATION_SUCCESS = 'log_message_card_operation_success';
    const CARD_OPERATION_CHARGEBACK_SUCCESS = 'log_message_card_operation_chargeback_success';

    const PAYMENT_FORM_REQUEST_NOT_FOUND = 'enum_log_type_payment_form_request_not_found';
    const PAYMENT_FORM_MISSING_TO_ACCOUNT = 'enum_log_type_payment_form_missing_to_account';
    const PAYMENT_FORM_VALIDATE_CARD_OPERATION_LIMITS = 'enum_log_type_payment_form_validate_card_operation_limits';
    const PAYMENT_FORM_AMOUNT_LOWER_AMOUNT = 'enum_log_type_payment_form_amount_lower_amount';
    const PAYMENT_FORM_OPERATION_SAVE_ERROR = 'enum_log_type_payment_form_operation_save_error';
    const PAYMENT_FORM_OPERATION_SAVE_SUCCESS = 'enum_log_type_payment_form_operation_save_success';
    const PAYMENT_FORM_VERIFY_PHONE_ERROR = 'enum_log_type_payment_form_verify_phone_error';
    const PAYMENT_FORM_CONFIRM_VERIFY_CODE_ERROR = 'enum_log_type_payment_form_confirm_verify_code_error';
    const PAYMENT_FORM_VERIFY_COMPLIANCE_STATUS_ERROR = 'enum_log_type_payment_form_verify_compliance_status_error';
    const USER_PERSONAL_INFORMATION_UPDATED_CABINET = 'log_user_personal_info_updated';
    const C_PROFILE_EMAIL_CHANGE_CABINET  = 'log_message_c_profile_email_change';
    const C_PROFILE_EMAIL_SEND_CABINET  = 'log_message_c_profile_email_send';

    const WITHDRAW_CRYPTO_SUCCESS = 'log_message_withdraw_crypto_success';
    const WITHDRAW_CRYPTO_FAIL = 'log_message_withdraw_crypto_fail';
    const WITHDRAW_WIRE_SUCCESS = 'log_message_withdraw_wire_success';
    const CONFIRM_TRANSACTION_SUCCESS = 'log_message_confirm_transaction_success';
    const APPROVE_TRANSACTION_SUCCESS = 'log_message_approve_transaction_success';
    const APPROVE_WITHDRAW_CRYPTO_SUCCESSFULLY = 'log_message_withdraw_crypto_operation_success';
    const ADD_TOP_UP_CRYPTO_TRANSACTION_SUCCESS = 'log_message_add_top_up_crypto_transaction_success';
    const TRANSACTION_ADDED_SUCCESS = 'log_message_transaction_added';
    const NEW_OPERATION_CREATED = 'log_message_new_operation_created';
    const CREATE_NEW_FIAT_WALLET_REQUEST = 'log_message_create_fiat_wallet_request_success';
}
