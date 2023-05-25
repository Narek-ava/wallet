<?php


namespace App\Enums;


class Notification extends Enum
{
    const NOTIFICATION_COMPLIANCE = 'notification_compliance';
    const NOTIFICATION_COMPLIANCE_BODY = 'notification_compliance_body';
    const NOTIFICATION_COMPLIANCE_LEVEL_SUCCESSFUL_CHANGE = 'notification_compliance_level_successfully_changed';
    const NOTIFICATION_COMPLIANCE_LEVEL_SUCCESSFUL_CHANGE_BODY = 'notification_compliance_level_successfully_changed_body';
    const NOTIFICATION_COMPLIANCE_REQUEST_PASSED_SUCCESSFULLY = 'notification_compliance_request_passed_successfully';
    const NOTIFICATION_COMPLIANCE_LEVEL_CHANGE_FAILED = 'notification_compliance_level_change_failed';
    const NOTIFICATION_COMPLIANCE_LEVEL_CHANGE_FAILED_BODY = 'notification_compliance_level_change_failed_body';
    const NOTIFICATION_WALLET_ADDED_SUCCESSFULLY= 'notification_wallet_added_successfully';
    const NOTIFICATION_WALLET_ADDED_SUCCESSFULLY_BODY = 'notification_wallet_added_successfully_body';
    const NOTIFICATION_WALLET_ADDED_FAILED = 'notification_wallet_added_failed';
    const NOTIFICATION_WALLET_ADDED_FAILED_BODY = 'notification_wallet_added_failed_body';
    const TOP_UP_CRYPTO_SUCCESSFUL= 'top_up_crypto_successful';
    const TOP_UP_CRYPTO_SUCCESSFUL_BODY = 'top_up_crypto_successful_body';
    const TOP_UP_CRYPTO_FAILED = 'top_up_crypto_failed';
    const TOP_UP_CRYPTO_FAILED_BODY = 'top_up_crypto_failed_body';
    const SEND_TRANSACTION_FAILED_BODY = 'send_transaction_failed_body';
    const SEND_TRANSACTION_SUCCESSFUL_BODY = 'send_transaction_successful_body';
    const SEND_TRANSACTION_FAILED = 'send_transaction_failed';
    const SEND_TRANSACTION_SUCCESSFUL = 'send_transaction_successful';

    const MESSAGES = [
        self::NOTIFICATION_COMPLIANCE_BODY,
        self::NOTIFICATION_COMPLIANCE_LEVEL_SUCCESSFUL_CHANGE_BODY,
        self::NOTIFICATION_COMPLIANCE_REQUEST_PASSED_SUCCESSFULLY,
        self::NOTIFICATION_COMPLIANCE_LEVEL_CHANGE_FAILED_BODY,
        self::NOTIFICATION_WALLET_ADDED_SUCCESSFULLY_BODY,
        self::NOTIFICATION_WALLET_ADDED_FAILED_BODY,
        self::NOTIFICATION_WALLET_ADDED_FAILED_BODY,
        self::SEND_TRANSACTION_FAILED_BODY,
        self::SEND_TRANSACTION_SUCCESSFUL_BODY,

    ];

    const TITLES = [
        self::NOTIFICATION_COMPLIANCE,
        self::NOTIFICATION_COMPLIANCE_LEVEL_SUCCESSFUL_CHANGE,
        self::NOTIFICATION_COMPLIANCE_LEVEL_CHANGE_FAILED,
        self::NOTIFICATION_WALLET_ADDED_SUCCESSFULLY,
        self::NOTIFICATION_WALLET_ADDED_FAILED,
        self::SEND_TRANSACTION_FAILED,
        self::SEND_TRANSACTION_SUCCESSFUL,
    ];

    const DISPOSABLE_TITLES = [
            'disposable_email_new_account_header',
            'disposable_email_binding_2fa_header',
            'disposable_email_unlink_2fa_header',
            'disposable_email_successful_login_header',
            'disposable_email_changing_account_information_header',
            'disposable_email_freeze_account_header',
            'disposable_email_account_ban_header',
            'disposable_email_account_recovery_header',
            'disposable_email_change_phone_header',
            'disposable_email_successful_verification_header',
            'disposable_email_unsuccessful_verification_header',
            'disposable_email_additional_verification_request_header',
            'disposable_email_verification_request_from_the_manager_header',
            'disposable_email_verification_confirmation_from_the_manager_header',
            'disposable_email_unsuccessful_confirmation_of_verification_from_the_manager_header',
            'disposable_email_updating_documents_header',
            'disposable_email_changing_limits_header',
            'disposable_email_changing_rates_header',
            'disposable_email_invoice_for_payment_by_sepa_or_swift_header',
            'disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header',
            'disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header',
            'disposable_email_rejected_sepa_or_swift_top_up_transaction_header',
            'disposable_email_successful_sepa_or_swift_withdrawal_application_header',
            'disposable_email_completed_request_for_withdrawal_via_sepa_or_swift_header',
            'disposable_email_successful_incoming_cryptocurrency_payment_header',
            'disposable_email_unsuccessful_incoming_cryptocurrency_payment_header',
            'disposable_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header',
            'disposable_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header',
            'disposable_email_successful_crypto_exchange_header',
            'disposable_email_successful_replenishment_of_the_wallet_by_bank_card_header',
            'disposable_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_header',
            'disposable_email_adding_a_payment_template_header',
            'disposable_email_adding_a_crypto_wallet_header',
            'disposable_email_unsuccessful_adding_of_a_crypto_wallet_header',
            'disposable_email_creating_a_new_wallet_for_a_client_header',
            'disposable_email_general_system_notifications_header',
            'disposable_email_reply_to_ticket_from_cratos_manager_header',
            'disposable_email_ticket_closure_notification_header',
            'disposable_email_monthly_reports_header',
            'disposable_email_annual_reports_header',
    ];
}
