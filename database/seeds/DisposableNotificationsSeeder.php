<?php

use Illuminate\Database\Seeder;

class DisposableNotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Notification::insert([
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_new_account_header',
                'body_message' => 'mail_email_new_account_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_binding_2fa_header',
                'body_message' => 'mail_email_binding_2fa_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unlink_2fa_header',
                'body_message' => 'mail_email_unlink_2fa_email_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_login_header',
                'body_message' => 'mail_email_successful_login_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_changing_account_information_header',
                'body_message' => 'mail_email_changing_account_information_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_freeze_account_header',
                'body_message' => 'mail_email_freeze_account_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_account_ban_header',
                'body_message' => 'mail_email_account_ban_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_account_recovery_header',
                'body_message' => 'mail_email_account_recovery_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_change_phone_header',
                'body_message' => 'mail_email_change_phone_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_verification_header',
                'body_message' => 'mail_email_successful_verification_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_verification_header',
                'body_message' => 'mail_email_unsuccessful_verification_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_additional_verification_request_header',
                'body_message' => 'mail_email_additional_verification_request_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_verification_request_from_the_manager_header',
                'body_message' => 'mail_email_verification_request_from_the_manager_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_verification_confirmation_from_the_manager_header',
                'body_message' => 'mail_email_verification_confirmation_from_the_manager_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_confirmation_of_verification_from_the_manager_header',
                'body_message' => 'mail_email_unsuccessful_confirmation_of_verification_from_the_manager_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_updating_documents_header',
                'body_message' => 'mail_email_updating_documents_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_changing_limits_header',
                'body_message' => 'mail_email_changing_limits_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_changing_rates_header',
                'body_message' => 'mail_email_changing_rates_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_invoice_for_payment_by_sepa_or_swift_header',
                'body_message' => 'mail_email_invoice_for_payment_by_sepa_or_swift_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header',
                'body_message' => 'mail_email_successful_exchange_andcreditting_by_sepa_or_swift_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header',
                'body_message' => 'mail_email_successful_exchange_andcreditting_by_sepa_or_swift_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_rejected_sepa_or_swift_top_up_transaction_header',
                'body_message' => 'mail_email_rejected_sepa_or_swift_top_up_transaction_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_sepa_or_swift_withdrawal_application_header',
                'body_message' => 'mail_email_successful_sepa_or_swift_withdrawal_application_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_completed_request_for_withdrawal_via_sepa_or_swift_header',
                'body_message' => 'mail_email_completed_request_for_withdrawal_via_sepa_or_swift_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_incoming_cryptocurrency_payment_header',
                'body_message' => 'mail_email_successful_incoming_cryptocurrency_payment_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_incoming_cryptocurrency_payment_header',
                'body_message' => 'mail_email_unsuccessful_incoming_cryptocurrency_payment_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header',
                'body_message' => 'mail_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header',
                'body_message' => 'mail_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_crypto_exchange_header',
                'body_message' => 'mail_email_successful_crypto_exchange_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_successful_replenishment_of_the_wallet_by_bank_card_header',
                'body_message' => 'mail_email_successful_replenishment_of_the_wallet_by_bank_card_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_header',
                'body_message' => 'mail_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_adding_a_payment_template_header',
                'body_message' => 'mail_email_adding_a_payment_template_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_adding_a_crypto_wallet_header',
                'body_message' => 'mail_email_adding_a_crypto_wallet_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_unsuccessful_adding_of_a_crypto_wallet_header',
                'body_message' => 'mail_email_unsuccessful_adding_of_a_crypto_wallet_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_creating_a_new_wallet_for_a_client_header',
                'body_message' => 'mail_email_creating_a_new_wallet_for_a_client_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_general_system_notifications_header',
                'body_message' => 'mail_email_general_system_notifications_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_reply_to_ticket_from_cratos_manager_header',
                'body_message' => 'mail_email_reply_to_ticket_from_cratos_manager_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_ticket_closure_notification_header',
                'body_message' => 'mail_email_ticket_closure_notification_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_monthly_reports_header',
                'body_message' => 'mail_email_monthly_reports_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ],
            [
                'recepient' => \App\Enums\NotificationRecipients::ALL_CLIENTS,
                'title_message' => 'disposable_email_annual_reports_header',
                'body_message' => 'mail_email_annual_reports_body',
                'title_params' => '[]',
                'body_params' => '[]',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]
        ]);
    }
}
