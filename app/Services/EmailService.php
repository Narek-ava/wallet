<?php

namespace App\Services;

use App\Enums\{AccountType,
    Country,
    CProfileStatuses,
    Gender,
    NotificationRecipients,
    OperationOperationType,
    OperationType,
    PaymentFormTypes,
    TransactionStatuses};
use App\Facades\EmailFacade;
use App\Models\{Account,
    Backoffice\BUser,
    Cabinet\CProfile,
    Cabinet\CUser,
    Cabinet\CUserTemporaryRegisterData,
    EmailVerification,
    Operation,
    Project,
    Ticket,
    Transaction};
use App\Http\Controllers\Cabinet\TransferController;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Cache, Log, Mail, Password, URL};
use Illuminate\Support\Str;


class EmailService
{
    private NotificationService $notificationService;
    private NotificationUserService $notificationUserService;
    private $project = null;
    private $logo = null;
    private SmsCodeService $smsCodeService;


    public function __construct()
    {
        $this->notificationService = resolve(NotificationService::class);
        $this->notificationUserService = resolve(NotificationUserService::class);
        $this->smsCodeService = resolve(SmsCodeService::class);
    }

    public function send($emailTemplate, $subject, $data, $to, $from = null, $attachment = null): bool
    {
        $name = config('mail.from.name');
        if ($this->project) {
            $emailProvider = $this->project->emailProvider ?? null;
            if ($emailProvider) {
                $key = 'mail.email_providers.' . $emailProvider->key . '.address';
                $from = config($key);

                $name = $this->project->name;
                $data['logoPng'] = $this->project->logoPng ?? null;
                $data['projectName'] = $this->project->name;
            } else {
                logger()->error(t('email_provider_not_found', ['project' => $this->project->name]));
            }
            config()->set('cratos.company_details',  (array) $this->project->companyDetails);

        }

        if (!$from) {
            $from = config('mail.from.address');
        }

        // @todo $message becomes reserved var name
        try {
            Mail::send($emailTemplate, $data, function ($message) use ($from, $to, $subject, $attachment, $name) {
                if ($attachment) {
                    $message->attach($attachment, array(
                            'mime' => 'application/pdf')
                    );
                }
                $message->from($from, $name);
                $message->subject($subject);
                $message->to($to);
            });
        } catch (\Throwable $e) {
            logger()->error('EmailSendError', [$e->getMessage(), $e->getTraceAsString(), $emailTemplate, $data, $to]);
        }

        return true;
    }

    /**
     * @param $operationId
     * @return bool|string
     */
    public function getPdfFile($operationId)
    {
        $operationService = new OperationService();
        $pdfGeneratorService = new PdfGeneratorService();
        $operation = $operationService->getOperationById($operationId);

        if ($operation) {
            return $pdfGeneratorService->savePdfDepositFile($operation, $operation->provider_account_id);
        }

        return false;
    }

    public function getTransactionReportPdfPath($operation)
    {
        return (new PdfGeneratorService())->saveTransactionReportPdfFile($operation);
    }

    /**
     * @param $cUser
     * @param $profile
     */
    public function sendSettingUpdate($cUser, $profile)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t('mail_client_setting_update_subject', ['name' => $fullName]);
        $replacement = ['name' => $fullName];
        $this->send('cabinet.emails.settings-verify', $subject, ['replacements' => $replacement], $cUser->email);
    }

    /**
     * @param $cUser
     * @param $profile
     */
    public function sendPasswordUpdateEmail($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $link = '<a class="btn" href="'.route('dashboard.reset.client.password', ['id' => $cUser->id]).'">'.t('ui_password_reset_header').'</a>';
        $body = t('mail_email_change_password_body', ['reset' => $link]);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_change_password_header'), [
            'h1Text' => t('disposable_email_change_password_header'),
            'body' => $body,
            'logoPng' => $this->logo,
        ], $cUser->email);
    }

    /**
     * @param $bUser
     */
    public function sendPasswordSetEmailAdmin($token, $email)
    {
        $link = '<a class="btn" href="'.route('b-user.new.password', ['token' => $token]).'">'.t('b_user_set_password_header').'</a>';
        $body = t('b_user_set_password_body', ['link' => $link]);
        $this->send('cabinet.emails.cuser-custom-email',
            t('b_user_set_password_header'), [
                'h1Text' => t('b_user_set_password_header'),
                'body' => $body
            ], $email);
    }

    public function sendPasswordRecovery($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $token = Str::random(32);
        Cache::put(CUserService::USER_PASSWORD_RESET_CACHE.$cUser->id, $token, now()->addHour());
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $cUser->email,
        ], false));
        $button = '<a class="btn" href="'.$url.'">'.t('ui_password_reset_header').'</a>';
        $body = t('mail_email_password_recovery_body', ['reset-button' => $button]);
        $email = $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_password_recovery_header'), [
            'h1Text' => t('disposable_email_password_recovery_header'),
            'body' => $body,
            'logoPng' => $this->logo
        ], $cUser->email);
        if ($email) {
            return Password::RESET_LINK_SENT;
        } else {
            return Password::INVALID_USER;
        }
    }

    /**
     * @param $cUser
     * @param $profile
     * @param $dirtyAttributes
     */
    public function sendSettingUpdateToManager($profile, $dirtyAttributes, $fullName)
    {

        if (isset($dirtyAttributes['interface_language'])) {
            $dirtyAttributes['interface_language'] = $profile->getLanguageName();
        }
        if (isset($dirtyAttributes['country'])) {
            $dirtyAttributes['country'] = $profile->getCountryName();
        }
        if (isset($dirtyAttributes['date_of_birth'])) {
            $dirtyAttributes['date_of_birth'] = Carbon::parse($profile->date_of_birth)->format('Y-m-d');
        }
        if (isset($dirtyAttributes['beneficial_owners'])) {
            $dirtyAttributes['beneficial_owners'] = implode(', ', $dirtyAttributes['beneficial_owners']);
        }
        if (isset($dirtyAttributes['ceos'])) {
            $dirtyAttributes['ceos'] = implode(', ', $dirtyAttributes['ceos']);
        }

        if (isset($dirtyAttributes['shareholders'])) {
            $dirtyAttributes['shareholders'] = implode(', ', $dirtyAttributes['shareholders']);
        }

        if (isset($dirtyAttributes['gender'])) {
            $dirtyAttributes['gender'] = Gender::getName($dirtyAttributes['gender']);
        }

        $changedData = '';
        foreach ($dirtyAttributes as $attributeName => $newValue) {
            $changedData .= ' <span style="font-weight: bold"> ' . t('ui_cprofile_' . $attributeName) . ' </span> <br> <span> ' . $newValue . ' </span> <br> ';
        }
        $replacements = ['changed-attributes' => $changedData];

            //to manager
            $this->sendUpdatePersonalInformation($profile,$replacements);
    }

    public function sendSuccessUpdatePersonalInformationMessage( $profile , $dirtyAttributes)
    {
        $project = $profile->cUser->project ?? null;
        $this->setCurrentProject($project);

        $logoPng = $this->logo;

        if (array_key_exists('phone', $dirtyAttributes)) {
            $body = t('mail_email_change_phone_body');
            $this->send('cabinet.emails.cuser-custom-email',
                t('disposable_email_change_phone_header'), [
                    'h1Text' => t('disposable_email_change_phone_header'),
                    'body' => $body,
                    'logoPng' => $logoPng
                ], $profile->cUser->email);
            $this->notificationUserService->addDisposableNotification($profile->cUser->id, 'disposable_email_change_phone_header','mail_email_change_phone_body');
            $message = br2nl(t('mail_email_change_phone_sms_body', ['name' => $profile->getFullName() ?? ' client']));

            if ($project) {
                $this->smsCodeService->send($message, $profile->cUser->phone, $project->id);
            }
        }
        $changedData = '';
        foreach($dirtyAttributes as $attributeName => $newValue){
            if($attributeName == 'country') {
                $newValue =  Country::getName($newValue);
            }
            if($attributeName == 'status') {
                $newValue =  CProfileStatuses::getName($newValue);
            }

            $changedData .= ' <span style="font-weight: bold"> ' . t('ui_cprofile_'.$attributeName) . ' </span> <br> <span> ' . $newValue . ' </span> <br> ';
        }
        $replacements = ['changed-attributes' => $changedData];
        $body = t('mail_email_changing_account_information_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_changing_account_information_header'), [
                'h1Text' => t('disposable_email_changing_account_information_header'),
                'body' => $body,
                'logoPng' => $logoPng
            ], $profile->cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_changing_account_information_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_changing_account_information_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($profile->cUser->id, $notificationId);
    }

    public function sendSuccessfulLogin($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $geoData = $this->getGeoLocation();
        $time = Carbon::now()->format('Y-m-d H:i');
        $replacements = array_merge($geoData, ['time' => $time]);
        $body = t('mail_email_successful_login_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_login_header'), [
            'h1Text' => t('disposable_email_successful_login_header'),
            'body' => $body,
            'logoPng' => $this->logo
        ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_login_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_login_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulExchangeCredittingSepaOrSwift(Operation $operation, $cryptoAmount)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $address = $operation->toAccount->cryptoAccountDetail->address ?? '';
        $replacements = ['number' => $operation->operation_id, 'to-currency' => $operation->to_currency, 'amount-fiat' => $operation->amount, 'address' => $address, 'crypto-amount' => $cryptoAmount];
        $body = t('mail_email_successful_exchange_andcreditting_by_sepa_or_swift_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header', ['number' => $operation->operation_id]), [
            'h1Text' => t('disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header', ['number' => $operation->operation_id]),
            'body' => $body,
            'logoPng' => $this->logo
        ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_exchange_andcreditting_by_sepa_or_swift_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_exchange_andcreditting_by_sepa_or_swift_header', ['number' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendStatusAccount($cUser, $cause, $status, $oldStatus)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $logoPng = $this->logo;
        $body = $h1Texcont = '';
        if ((int)$status === CProfile::STATUS_BANNED) {
            $replacements = ['cause' => $cause];
            $body = t('mail_email_account_ban_body', $replacements);
            $h1Text = t('disposable_email_account_ban_header');
            $notificationId = $this->notificationService->createNotification(
                'mail_email_account_ban_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_account_ban_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        } elseif ((int)$status === CProfile::STATUS_SUSPENDED) {
            $replacements = ['cause' => $cause];
            $body = t('mail_email_freeze_account_body', $replacements);
            $h1Text = t('disposable_email_freeze_account_header');
            $notificationId = $this->notificationService->createNotification(
                'mail_email_freeze_account_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_freeze_account_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        } elseif ((int)$status === CProfile::STATUS_ACTIVE &&
            ($oldStatus === CProfileStatuses::STATUS_SUSPENDED || $oldStatus === CProfileStatuses::STATUS_BANNED)) {
            $login = '<a class="btn" href="'.route('cabinet.login.get').'">Login</a>';
            $replacements = ['login' => $login];
            $body = t('mail_email_account_recovery_body', $replacements);
            $h1Text = t('disposable_email_account_recovery_header');
            $replacements['login'] = '';
            $notificationId = $this->notificationService->createNotification(
                'mail_email_account_recovery_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_account_recovery_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        }
        if ($body && $h1Text) {
            $this->send('cabinet.emails.cuser-custom-email', $h1Text,
                ['h1Text' => $h1Text, 'body' => $body, 'logoPng' => $logoPng], $cUser->email);
        }
    }

    /**
     * @param $cUser
     * @param $newEmail
     * @param $emailVerification
     * @return void
     */
    public function sendEmailUpdate($cUser, $newEmail, $emailVerification)
    {

        $this->setCurrentProject($cUser->project ?? null);
        $verifyUrl = route('verify.email', ['token' => $emailVerification->token, 'id' => $emailVerification->id]);

        $link = '<a href="'.$verifyUrl.'">'.t('mail_email_change_verify_btn').'</a>';
        $body = t('mail_email_change_email_body', ['link' => $link]);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_change_email_header'), [
            'h1Text' => t('disposable_email_change_email_header'),
            'body' => $body,
            'logoPng' => $this->logo ?? null
        ], $newEmail);
    }

    public function sendEmailRegistrationConfirm(CUser $cUser, EmailVerification $emailVerification)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $newEmail = $cUser->email;
        $verifyUrl = route('verify.email', ['token' => $emailVerification->token, 'id' => $emailVerification->id]);
        $replacement = ['email' => $newEmail, 'email-confirmation' => "<a href='{$verifyUrl}'>{$verifyUrl}</a>"];
        $body = t('mail_email_confirm_body', $replacement);
        $h1Text = t('disposable_email_confirmation_header');
        $this->send('cabinet.emails.cuser-custom-email', $h1Text,
            ['h1Text' => $h1Text, 'body' => $body, 'logoPng' => $this->logo], $newEmail);
    }

    public function sendPaymentFormEmailConfirm(EmailVerification $emailVerification)
    {
        $newEmail = $emailVerification->new_email;
        $token = $emailVerification->token;

        $h1Text = t('payment_form_header');
        $this->setCurrentProject($emailVerification->cUser->project ?? Project::getCurrentProject());

        $this->send('cabinet.emails.payment-form-verify-email', strip_tags($h1Text) ,
            ['h1Text' => $h1Text, 'token' => $token, 'logoPng' => $this->logo], $newEmail);
    }

    public function sendUpgradeLevelEmail(Operation $operation)
    {
        $cProfile = $operation->cProfile;
        $limits = TransferController::getLimits($cProfile);
        $transactionLimit = eur_format($limits->transaction_amount_max ?? '15000');
        $monthlyLimit = eur_format($limits->monthly_amount_max ?? '5000');
        $this->setCurrentProject($operation->cProfile->cUser->project ?? null);
        $logoPng = $this->logo;

        $h1Text = t('payment_form_upgrade_verification_level');
        $this->send('cabinet.emails.payment-form-email8', strip_tags($h1Text) ,
            compact('transactionLimit', 'monthlyLimit', 'logoPng'), $cProfile->cUser->email);
    }

    public function sendWalletVerificationFailedEmail(CProfile $cProfile, $address)
    {
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $h1Text = t('payment_form_wallet_verification_failed');
        $logoPng = $this->logo;
        $this->send('cabinet.emails.payment-form-email7', $h1Text,
            compact('address', 'logoPng'), $cProfile->cUser->email);
    }

    public function sendPaymentFormUnsuccessfulPaymentEmail(Operation $operation, $error = '')
    {
//        $payerDetails = $operation->merchantOperationsInformation;
//
//        $h1Text = t('payment_form_unsuccessful_payment');
//        $this->send('cabinet.emails.payment-form-email6', strip_tags($h1Text) ,
//            ['error' => $error], $payerDetails->email);
    }

    public function sendPaymentFormNewDeviceLoginEmail(Operation $operation)
    {
        $geoData = $this->getGeoLocation();
        $cUser = $operation->cProfile->cUser ?? null;
        $this->setCurrentProject($cUser->project ?? null);
        if ($cUser) {
            $geoData = array_merge($geoData, [
                'logoPng' => $this->logo,
            ]);
        }
        $payerDetails = $operation->merchantOperationsInformation;

        $h1Text = t('payment_form_login_new_device');
        $this->send('cabinet.emails.payment-form-email5', strip_tags($h1Text) ,
            $geoData, $payerDetails->email);
    }

    public function sendPaymentFormAuthenticationCodeEmail(Operation $operation, $token = '')
    {
        $payerDetails = $operation->merchantOperationsInformation;

        $h1Text = t('payment_form_authentication_code');
        $this->setCurrentProject($operation->cProfile->cUser->project ?? null);
        $this->send('cabinet.emails.payment-form-email3', strip_tags($h1Text) ,
            [
                'token' => $token,
                'logoPng' => $this->logo,
            ], $payerDetails->email);
    }

    public function sendSuccessVerificationRegistrationConfirmEmail(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $email = $cUser->email;
        $url = url(route('cabinet.dashboard'));
        $profile = $cUser->cProfile;
        $paymentForm = $cUser->paymentForm;

        if (in_array($paymentForm->type, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rateTemplateId = $paymentForm->rate_template_id;
        } else {
            $rateTemplateId = $paymentForm->cProfile->rate_template_id;
        }

        $paymentFormsService = resolve(PaymentFormsService::class);
        /* @var PaymentFormsService $paymentFormsService */

        $limit = app(CommissionsService::class)->limits($rateTemplateId, $paymentFormsService->getComplianceLevel($profile));
        $maxPaymentAmount = eur_format($limit->transaction_amount_max ?? 0);

        $h1Text = t('payment_form_welcome_aboard');
        $this->send('cabinet.emails.payment-form-email2', strip_tags($h1Text),
            [
                'monthlyAmountMaxLimit' => $maxPaymentAmount,
                'logoPng' => $this->logo,
                'url' => $url, 'complianceUrl' => route('cabinet.compliance')],
            $email);
    }


    public function sendPaymentFormGenerateNewPasswordEmail(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $token = Str::random(32);
        Cache::put(CUserService::USER_PASSWORD_RESET_CACHE.$cUser->id, $token, now()->addHour());
        $email = $cUser->email;
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
            'payment_form' => true,
        ], false));

        $button = '<a class="btn" href="'.$url.'">'.t('payment_form_generate_new_password').'</a>';
        $body = t('mail_email_generate_new_password_body', ['reset-button' => $button]);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_generate_new_password_header'), [
                'h1Text' => t('disposable_email_generate_new_password_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);

    }

    public function sendInvoicePaymentSepaOrSwift($cUser, $operationOperationId, $type, $count, $currency, $operationId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $logoPng = $this->logo;
        $link = '<a href="'.route('client.download.pdf.operation', ['operationId' => $operationId]).'">'.t('ui_bo_c_profile_page_download_btn').' pdf</a>';
        $replacement = ['number' => $operationOperationId, 'type' => $type, 'count' => $count . ' ' . $currency, 'link' => $link];
        $body = t('mail_email_invoice_for_payment_by_sepa_or_swift_body', $replacement);
        $h1Text = t('disposable_email_invoice_for_payment_by_sepa_or_swift_header', ['number' => $operationOperationId]);
        $attachment = $this->getPdfFile($operationId);
        $this->send('cabinet.emails.cuser-custom-email', $h1Text,
            ['h1Text' => $h1Text, 'body' => $body, 'logoPng' => $logoPng], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_invoice_for_payment_by_sepa_or_swift_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_invoice_for_payment_by_sepa_or_swift_header', ['number' => $operationOperationId], $replacement);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        $this->sendNotificationForManager($cUser, $operationOperationId);
    }

    public function sendCreatedNewAccount(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $btnLogin = "<a href='".route('cabinet.login.get')."'><button class='btn'>".t('ui_cprofile_login')."</button></a>";
        $btnVerification = "<a href='".route('cabinet.settings.get')."'><button class='btn'>".t('ui_cprofile_verification')."</button></a>";
        $replacement = ['btn-login' => $btnLogin, 'btn-verification' => $btnVerification];
        $body = t('mail_email_new_account_body', $replacement);
        $h1Text = t('disposable_email_new_account_header');
        $logoPng = $this->logo;
        $this->send('cabinet.emails.cuser-custom-email', $h1Text, ['h1Text' => $h1Text, 'body' => $body, 'logoPng' => $logoPng], $cUser->email);
        $replacement['btn-login'] = '';
        $notificationId = $this->notificationService->createNotification(
            'mail_email_new_account_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_new_account_header', [], $replacement);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendCreatingNewWalletForClient($cUser, $coin, $address)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['currency' => $coin, 'address' => $address];
        $body = t('mail_email_creating_a_new_wallet_for_a_client_body', $replacements);
        $h1Text = t('disposable_email_creating_a_new_wallet_for_a_client_header');
        $logoPng = $this->logo;
        $this->send('cabinet.emails.cuser-custom-email', $h1Text, ['h1Text' => $h1Text, 'body' => $body, 'logoPng' => $logoPng], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_creating_a_new_wallet_for_a_client_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_creating_a_new_wallet_for_a_client_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendCreatingNewFiatWalletForClient($cUser, $currency)
    {
        $replacements = ['currency' => $currency];
        $body = t('mail_email_creating_a_new_fiat_wallet_for_a_client_body', $replacements);
        $h1Text = t('disposable_email_creating_a_new_fiat_wallet_for_a_client_header');
        $this->send('cabinet.emails.cuser-custom-email', $h1Text, ['h1Text' => $h1Text, 'body' => $body], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_creating_a_new_fiat_wallet_for_a_client_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_creating_a_new_fiat_wallet_for_a_client_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendAddingPaymentTemplate($cUser, $account, $wireAccountDetail)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $requisites = '<span style="font-weight: bold;">'.t('template_name').'</span><br>
                        <span>'. $account->name .'</span><br>
                        <span style="font-weight: bold;">'.t('country_of_clients_bank').'</span><br>
                        <span>'. Country::getName($account->country) .'</span><br>
                        <span style="font-weight: bold;">'.t('currency').'</span><br>
                        <span>'. $account->currency .'</span><br>
                        <span style="font-weight: bold;">'.t('method').'</span><br>
                        <span>'. t(AccountType::getName($account->account_type)) .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_applicable_iban').'</span><br>
                        <span>'. $wireAccountDetail->iban .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_swift_bic').'</span><br>
                        <span>'. $wireAccountDetail->swift .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_account_holder_placeholder').'</span><br>
                        <span>'. $wireAccountDetail->account_beneficiary .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_account_number_placeholder').'</span><br>
                        <span>'. $wireAccountDetail->account_number .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_bank_name_placeholder').'</span><br>
                        <span>'. $wireAccountDetail->bank_name .'</span><br>
                        <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_bank_address_placeholder').'</span><br>
                        <span>'. $wireAccountDetail->bank_address .'</span>';
        $replacements = ['requisites' => $requisites];
        $h1Text = t('disposable_email_adding_a_payment_template_header');
        $this->send('cabinet.emails.cuser-custom-email', $h1Text, [
                'h1Text' => $h1Text,
                'body' => 'mail_email_adding_a_payment_template_body',
                'account' => $account,
                'wireAccountDetail' => $wireAccountDetail,
                'logoPng' => $this->logo
        ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_adding_a_payment_template_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_adding_a_payment_template_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendChangingLimits($rateTemplateId, $limits = true)
    {
        $cUserIds = CUser::whereHas('cProfile', function ($q) use ($rateTemplateId) {
            $q->where(['rate_template_id' => $rateTemplateId, 'status' => CProfileStatuses::STATUS_ACTIVE]);
        })->whereNotNull('email_verified_at')->pluck('id', 'email')->toArray();
        foreach ($cUserIds as $email => $id) {
            if ($limits) {
                $notificationId = $this->notificationService->createNotification(
                    'mail_email_changing_limits_body',
                    NotificationRecipients::CURRENT_CLIENT,
                    'disposable_email_changing_limits_header', [], []);
                $this->notificationUserService->addDisposableNotificationExists($id, $notificationId);
            }
            $notificationId = $this->notificationService->createNotification(
                'mail_email_changing_rates_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_changing_rates_header', [], []);
            $this->notificationUserService->addDisposableNotificationExists($id, $notificationId);
        }
    }

    public function sendChangingRatesToAllUser($rateTemplateId)
    {
        $cUserIds = CUser::whereHas('cProfile', function ($q) use ($rateTemplateId) {
            $q->where(['rate_template_id' => $rateTemplateId, 'status' => CProfileStatuses::STATUS_ACTIVE]);
        })->whereNotNull('email_verified_at')->pluck('id', 'email')->toArray();
        foreach ($cUserIds as $email => $id) {
            $notificationId = $this->notificationService->createNotification(
                'mail_email_changing_rates_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_changing_rates_header', [], []);
            $this->notificationUserService->addDisposableNotification($id, 'disposable_email_changing_rates_header', 'mail_email_changing_rates_body');
        }
    }

    public function sendChangingLimitsToAllUsers($rateTemplateId)
    {
        $cUserIds = CUser::whereHas('cProfile', function ($q) use ($rateTemplateId) {
            $q->where(['rate_template_id' => $rateTemplateId, 'status' => CProfileStatuses::STATUS_ACTIVE]);
        })->whereNotNull('email_verified_at')->pluck('id', 'email')->toArray();
        foreach ($cUserIds as $email => $id) {
            $notificationId = $this->notificationService->createNotification(
                'mail_email_changing_limits_body',
                NotificationRecipients::CURRENT_CLIENT,
                'disposable_email_changing_limits_header', [], []);
            $this->notificationUserService->addDisposableNotification($id, 'disposable_email_changing_limits_header', 'mail_email_changing_limits_body');
        }
    }

    public function sendChangingRateForUser($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $body = t('mail_email_changing_limits_body');
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_changing_limits_header'), [
                'h1Text' => t('disposable_email_changing_limits_header'),
                'body' => $body,
                'logoPng' => $this->logo,
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_changing_limits_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_changing_limits_header', [], []);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendBinding2FA(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $body = t('mail_email_binding_2fa_body');
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_binding_2fa_header'), [
            'h1Text' => t('disposable_email_binding_2fa_header'),
            'body' => $body,
            'logoPng' => $this->logo
        ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_binding_2fa_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_binding_2fa_header', [], []);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function send2FACode(CUser $cUser, $code)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $body = t('mail_email_2fa_code_email_body', ['code' => $code]);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_2fa_code_email_header'), [
            'h1Text' => t('disposable_email_2fa_code_email_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
    }

    public function sendUnlink2FA(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $geoData = $this->getGeoLocation();
        $body = t('mail_email_unlink_2fa_email_body', $geoData);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unlink_2fa_header'), [
            'h1Text' => t('disposable_email_unlink_2fa_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unlink_2fa_email_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unlink_2fa_header', [], $geoData);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }


    /**
     *  Notify user that account has been suspended
     * @param $cUser
     * @param $profile
     */
    public function sendAccountSuspendedEmail($cUser, $profile)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t('mail_client_account_suspended_subject', ['name' => $fullName]);
        $replacement = ['name' => $fullName, 'h2Text' => t('mail_client_account_suspended_title', ['name' => $fullName])];
        $this->send('cabinet.emails.info', $subject, ['replacements' => $replacement],
            $cUser->email);
    }


    /**
     * Send Documents Auto delete info email
     * @param CUser $cUser
     * @param CProfile $profile
     * @param string $documentNames
     */
    public function sendDocsAutoDeleteEmail(CUser $cUser, CProfile $profile, string $documentNames)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t('mail_documents_auto_delete_subject', ['name' => $fullName]);
        $h2Text = t('mail_documents_auto_delete_title', ['name' => $fullName, 'documentNames' => $documentNames]);
        $replacement = [
            'name' => $fullName,
            'h2Text' => $h2Text,
            'buttons' => [['url' => route('cabinet.compliance'), 'text' => t('mail_documents_auto_delete_provide_docs_button')]]
        ];
        $this->send('cabinet.emails.info', $subject, $replacement, $cUser->email);
        //$this->sendManagerRenewDate($cUser->cProfile);
    }

    /**
     * Send Notification email before changing user status to suspended
     * @param CUser $cUser
     * @param CProfile $profile
     */
    public function sendNotifyBeforeSuspendingUserEmail(CUser $cUser, CProfile $profile)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t('mail_notify_before_suspend_subject', ['name' => $fullName]);
        $h2Text = t('mail_notify_before_suspend_title', ['name' => $fullName]);
        $replacement = [
            'name' => $fullName,
            'h2Text' => $h2Text,
            'buttons' => [['url' => route('cabinet.compliance'), 'text' => t('mail_notify_before_suspend_title_button')]]
        ];
        $this->send('cabinet.emails.info', $subject, $replacement, $cUser->email);
    }
    /**
     *
     * Send Notification email changing user status to suspended
     * @param CUser $cUser
     * @param CProfile $profile
     */
    public function sendSuspendUserEmail(CUser $cUser, CProfile $profile)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t('mail_suspend_user_subject', ['name' => $fullName]);
        $h2Text = t('mail_suspend_user_title', ['name' => $fullName]);
        $replacement = [
            'name' => $fullName,
            'h2Text' => $h2Text,
        ];
        $this->send('cabinet.emails.info', $subject, $replacement, $cUser->email);
    }

    /**
     *
     * Send info email
     * @param CUser $cUser
     * @param CProfile $profile
     * @param string $subject
     * @param string $text
     * @param array $replacement
     */
    public function sendInfoEmail(CUser $cUser, CProfile $profile, string $subject, string $text, array $replacement = [])
    {
        $this->setCurrentProject($cUser->project ?? null);
        $fullName = $profile->getFullName();
        $subject = t($subject, ['name' => $fullName] + $replacement);
        $h2Text = t($text, ['name' => $fullName] + $replacement);
        $replacement = [
            'name' => $fullName,
            'h2Text' => $h2Text,
        ];
        $this->send('cabinet.emails.info', $subject, $replacement, $cUser->email);
    }

    public function sendSuccessfulVerification($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $clientLimit = $cUser->limit;
        $limit = $clientLimit->transaction_amount_max ?? null;
        $monthLimit = $clientLimit->monthly_amount_max ?? null;
        $replacements = ['level' => $cUser->cProfile->compliance_level, 'limit' => $limit, 'month-limit' => $monthLimit];
        $body = t('mail_email_successful_verification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_verification_header'), [
            'h1Text' => t('disposable_email_successful_verification_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_verification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_verification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        $this->sendLevelUpClientComplianceManager($cUser);
    }

    private function sendLevelUpClientComplianceManager(CUser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $managers = $cUser->cProfile->getManagers();
        foreach ($managers as $manager) {
            if (!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $subjectReplacements = ['clientId' => $cUser->cProfile->profile_id];
            $replacements = ['level' => $cUser->cProfile->compliance_level, 'clientId' => $cUser->cProfile->profile_id];
            $body = t('ui_client_levelup_compliance_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('ui_client_levelup_compliance_header', $subjectReplacements), [
                    'h1Text' => t('ui_client_levelup_compliance_header', $subjectReplacements),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'ui_client_levelup_compliance_body',
                NotificationRecipients::MANAGER,
                'ui_client_levelup_compliance_header', $subjectReplacements, $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
  }

    public function sendUnsuccessfulVerification($cUser, $cause)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $again = '<a class="btn" href="'.route('cabinet.compliance').'">'.t('try_again').'</a>';
        $replacements = ['cause' => $cause, 'again' => $again];
        $body = t('mail_email_unsuccessful_verification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_verification_header'), [
            'h1Text' => t('disposable_email_unsuccessful_verification_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $replacements['again'] = '';
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_verification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_verification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendAdditionalVerificationRequest($cUser, $applicantId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $provide = '<a class="btn" href="'.route('cabinet.compliance').'">'.t('ui_menu_compliance').'</a>';
        $replacements = ['documents' => $this->getDocuments($applicantId), 'provide' => $provide];
        $body = t('mail_email_additional_verification_request_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_additional_verification_request_header'), [
            'h1Text' => t('disposable_email_additional_verification_request_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_additional_verification_request_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_additional_verification_request_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendVerificationRequestFromTheManager($cUser, $operation)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $number = $operation->operation_id;
        $type = OperationOperationType::getName($operation->operation_type);
        $count = $operation->amount . ' ' . $operation->from_currency;
        $clientLimit = $cUser->limit;
        $limit = $clientLimit->transaction_amount_max ?? null;
        $monthLimit = $clientLimit->monthly_amount_max ?? null;
        $replacements = ['number' => $number, 'type' => $type, 'count' => $count, 'limit' => $limit, 'month-limit' => $monthLimit, 'levelup' => ++$cUser->cProfile->compliance_level];
        $body = t('mail_email_verification_request_from_the_manager_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_verification_request_from_the_manager_header'), [
            'h1Text' => t('disposable_email_verification_request_from_the_manager_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_verification_request_from_the_manager_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_verification_request_from_the_manager_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);

    }

    public function sendUpdateLevelRequest($cUser, $operation)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $number = $operation->operation_id;
        $type = OperationOperationType::getName($operation->operation_type);
        $count = $operation->amount . ' ' . $operation->from_currency;
        $clientLimit = $cUser->limit;
        $limit = $clientLimit->transaction_amount_max ?? null;
        $monthLimit = $clientLimit->monthly_amount_max ?? null;
        $replacements = ['number' => $number, 'type' => $type, 'count' => $count, 'limit' => $limit, 'month-limit' => $monthLimit, 'levelup' => ++$cUser->cProfile->compliance_level, 'link' => route('cabinet.compliance')];
        $body = t('mail_email_verification_update_request_from_the_manager_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_verification_request_from_the_manager_header'), [
                'h1Text' => t('disposable_email_verification_request_from_the_manager_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_verification_update_request_from_the_manager_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_verification_request_from_the_manager_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);

    }

    public function replyTicketFromCratosManager(Ticket $ticket)
    {
        $cUser = $ticket->user;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['number' => $ticket->ticket_id, 'subject' => $ticket->subject];
        $body = t('mail_email_reply_to_ticket_from_cratos_manager_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_reply_to_ticket_from_cratos_manager_header', $replacements), [
                'h1Text' => t('disposable_email_reply_to_ticket_from_cratos_manager_header', $replacements),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_reply_to_ticket_from_cratos_manager_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_reply_to_ticket_from_cratos_manager_header', $replacements, $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function ticketClosureNotification($cUser, $ticketId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['number' => $ticketId];
        $body = t('mail_email_ticket_closure_notification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_ticket_closure_notification_header', $replacements), [
                'h1Text' => t('disposable_email_ticket_closure_notification_header', $replacements),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_ticket_closure_notification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_ticket_closure_notification_header', $replacements, $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendVerificationConfirmationFromTheManager($cUser, $operation)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $number = $operation->operation_id;
        $type = $operation->operation_type == OperationOperationType::TYPE_TOP_UP_SWIFT ? 'SWIFT' : 'SEPA';
        $count = $operation->amount . ' ' . $operation->to_currency;
        $clientLimit = $cUser->limit;
        $limit = $clientLimit->transaction_amount_max ?? null;
        $monthLimit = $clientLimit->monthly_amount_max ?? null;
        $replacements = ['number' => $number, 'type' => $type, 'count' => $count, 'level' => $cUser->cProfile->compliance_level, 'limit' => $limit, 'month-limit' => $monthLimit];
        $body = t('mail_email_verification_confirmation_from_the_manager_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_verification_confirmation_from_the_manager_header'), [
            'h1Text' => t('disposable_email_verification_confirmation_from_the_manager_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_verification_confirmation_from_the_manager_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_verification_confirmation_from_the_manager_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulConfirmationVerificationFromTheManager($cUser, $operation)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $number = $operation->operation_id;
        $type = OperationOperationType::getName($operation->operation_type);
        $count = $operation->amount . ' ' . $operation->to_currency;
        $replacements = ['number' => $number, 'type' => $type, 'count' => $count];
        $body = t('mail_email_unsuccessful_confirmation_of_verification_from_the_manager_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_confirmation_of_verification_from_the_manager_header'), [
            'h1Text' => t('disposable_email_unsuccessful_confirmation_of_verification_from_the_manager_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_confirmation_of_verification_from_the_manager_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_confirmation_of_verification_from_the_manager_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUpdatingDocuments($cUser, $applicantId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $link = '<a class="btn" href="'.route('cabinet.compliance').'">'.t('ui_menu_compliance').'</a>';
        $replacements = ['documents' => $this->getDocuments($applicantId), 'link' => $link];
        $body = t('mail_email_updating_documents_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_updating_documents_header'), [
            'h1Text' => t('disposable_email_updating_documents_header'),
            'body' => $body,
            'logoPng' => $this->logo

            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_updating_documents_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_updating_documents_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    private function getDocuments($applicantId)
    {
        $nameWithIds = [];
        $info = (new \App\Services\SumSubService())->getRequiredDocs($applicantId);
        foreach ($info as $documentName => $documentData) {
            if (isset($documentData['imageIds'])) {
                $nameWithIds[] = str_replace('_', ' ', ucfirst(strtolower($documentName)));
            }
        }
        return implode(',', $nameWithIds);
    }

    public function sendRejectedSepaSwiftTopUpTransaction(Operation $operation)
    {
        // todo cause
        $cProfile = (new CProfileService)->getProfileById($operation->c_profile_id);
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $replacements = ['number' => $operation->operation_id, 'count' => formatMoney($operation->amount, $operation->from_currency) . ' ' . $operation->from_currency, 'cause' => $operation->comment ?? '-'];
        $body = t('mail_email_rejected_sepa_or_swift_top_up_transaction_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id]), [
            'h1Text' => t('disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id]),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cProfile->cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_rejected_sepa_or_swift_top_up_transaction_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cProfile->cUser->id, $notificationId);
    }

    public function sendRefundedSepaSwiftTopUpTransaction(Operation $operation)
    {
        $cProfile = (new CProfileService)->getProfileById($operation->c_profile_id);
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $replacements = ['number' => $operation->operation_id, 'count' => formatMoney($operation->amount, $operation->from_currency) . ' ' . $operation->from_currency, 'cause' => $operation->comment ?? '-'];
        $body = $operation->operation_type == OperationOperationType::TYPE_TOP_UP_CRYPTO ?
                t('mail_email_refunded_sepa_or_swift_top_up_crypto_transaction_body', $replacements) :
                t('mail_email_refunded_sepa_or_swift_top_up_transaction_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cProfile->cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_refunded_sepa_or_swift_top_up_transaction_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_rejected_sepa_or_swift_top_up_transaction_header', ['number' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cProfile->cUser->id, $notificationId);
    }

    public function sendmailSuccessfulSepaSwiftWithdrawalApplication(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = [
            'number' => $operation->operation_id,
            'count' => $operation->amount . ' ' . $operation->from_currency,
            'type' => OperationOperationType::getName($operation->operation_type)
        ];
        $body = t('mail_email_successful_sepa_or_swift_withdrawal_application_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_sepa_or_swift_withdrawal_application_header', ['number' => $operation->operation_id]), [
            'h1Text' => t('disposable_email_successful_sepa_or_swift_withdrawal_application_header', ['number' => $operation->operation_id]),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_sepa_or_swift_withdrawal_application_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_sepa_or_swift_withdrawal_application_header', ['number' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
        $this->sendNotificationForManager($cUser, $operation->operation_id);
    }

    public function sendAddingCryptoWallet($cUser, $address)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['address' => $address];
        $body = t('mail_email_adding_a_crypto_wallet_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_adding_a_crypto_wallet_header'), [
            'h1Text' => t('disposable_email_adding_a_crypto_wallet_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_adding_a_crypto_wallet_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_adding_a_crypto_wallet_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulAddingCryptoWallet($cUser, $address)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['address' => $address];
        $body = t('mail_email_unsuccessful_adding_of_a_crypto_wallet_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_adding_of_a_crypto_wallet_header'), [
            'h1Text' => t('disposable_email_unsuccessful_adding_of_a_crypto_wallet_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_adding_of_a_crypto_wallet_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_adding_of_a_crypto_wallet_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendLoginFromNewDevice($cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $time = Carbon::now()->format('Y-m-d H:i');
        $geoData = $this->getGeoLocation();
        $replacements = array_merge($geoData, ['time' => $time]) ;
        $body = t('mail_email_login_from_new_device_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_login_from_new_device_header'), [
            'h1Text' => t('disposable_email_login_from_new_device_header'),
            'body' => $body,
            'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_login_from_new_device_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_login_from_new_device_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendCompletedRequestForWithdrawalViaSepaOrSwift(Operation $operation, $fiatAmount)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['number' => $operation->operation_id];
        $replacementBody = ['number' => $operation->operation_id, 'cryptoSum' => $operation->amount, 'fiatName' => $operation->to_currency, 'fiatSum' => $fiatAmount];
        $body = t('mail_email_completed_request_for_withdrawal_via_sepa_or_swift_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_completed_request_for_withdrawal_via_sepa_or_swift_header', $replacementTitle), [
                'h1Text' => t('disposable_email_completed_request_for_withdrawal_via_sepa_or_swift_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_completed_request_for_withdrawal_via_sepa_or_swift_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_completed_request_for_withdrawal_via_sepa_or_swift_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulIncomingCryptocurrencyPayment(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['name' => $operation->toAccount->cryptoAccountDetail->label, 'operation-number' => $operation->operation_id];
        $replacementBody = ['walletName' => $operation->toAccount->cryptoAccountDetail->label, 'sum' => $operation->amount, 'date' => $operation->created_at->format('Y-m-d H:i')];
        $body = t('mail_email_successful_incoming_cryptocurrency_payment_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_incoming_cryptocurrency_payment_header', $replacementTitle), [
                'h1Text' => t('disposable_email_successful_incoming_cryptocurrency_payment_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_incoming_cryptocurrency_payment_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_incoming_cryptocurrency_payment_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulIncomingCryptocurrencyPayment(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['wallet' => $operation->toAccount->cryptoAccountDetail->label, 'number' => $operation->operation_id];
        $replacementBody = ['walletName' => $operation->toAccount->cryptoAccountDetail->label, 'cause' => $operation->comment, 'sum' => $operation->amount, 'date' => Carbon::now()->format('Y-m-d H:i')];
        $body = !$operation->comment?
            t('mail_email_unsuccessful_incoming_cryptocurrency_payment_body_without_comment', $replacementBody)
            :t('mail_email_unsuccessful_incoming_cryptocurrency_payment_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_header', $replacementTitle), [
                'h1Text' => t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_incoming_cryptocurrency_payment_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_incoming_cryptocurrency_payment_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulIncomingCryptocurrencyPaymentByCard(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['wallet' => $operation->toAccount->cryptoAccountDetail->label, 'operationId' => $operation->operation_id];
        $replacementBody = ['walletName' => $operation->toAccount->cryptoAccountDetail->label, 'sum' => $operation->amount ];
        $body = t('mail_email_unsuccessful_incoming_cryptocurrency_payment_by_card_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle), [
                'h1Text' => t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_incoming_cryptocurrency_payment_by_card_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulChargebackPaymentByCard(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['wallet' => $operation->toAccount->cryptoAccountDetail->label, 'operationId' => $operation->operation_id];
        $replacementBody = ['walletName' => $operation->toAccount->cryptoAccountDetail->label, 'sum' => $operation->amount ];
        $body = t('mail_email_successful_chargeback_incoming_cryptocurrency_payment_by_card_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle), [
                'h1Text' => t('disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_chargeback_incoming_cryptocurrency_payment_by_card_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_incoming_cryptocurrency_payment_by_card_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulWithdrawalOfCryptocurrencyToCryptoWallet(Operation $operation, $amount)
    {
        // todo params
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = [
            'wallet' => $operation->fromAccount->cryptoAccountDetail->label,
            'number' => $operation->operation_id
        ];
        $replacementBody = [
            'walletName' => $operation->fromAccount->cryptoAccountDetail->label,
            'address' => $operation->toAccount->cryptoAccountDetail->address,
            'blockchainLink' => '',
            'sendedSum' => $amount,
            'date' => $operation->created_at->format('Y-m-d')
        ];
        $body = t('mail_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle), [
                'h1Text' => t('disposable_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body',
            NotificationRecipients::CURRENT_CLIENT,
            t('disposable_email_successful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle) , [] , $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulWithdrawalCryptocurrencyCryptoWallet(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['wallet' => $operation->toAccount->cryptoAccountDetail->label, 'number' => $operation->operation_id];
        $replacementBody = ['walletName' => $operation->toAccount->cryptoAccountDetail->label, 'cause' => $operation->comment, 'sendedSum' => $operation->amount, 'date' => $operation->created_at->format('Y-m-d H:i')];
        $body = t('mail_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle), [
                'h1Text' => t('disposable_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_withdrawal_of_cryptocurrency_to_a_crypto_wallet_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulCryptoExchange(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementBody = ['cryptoNameFrom' => $operation->from_currency, 'cryptoNameTo' => $operation->from_to, 'cryptoSum' => $operation->amount, 'date' => $operation->created_at->format('Y-m-d H:i')];
        $body = t('mail_email_successful_crypto_exchange_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_crypto_exchange_header', []), [
                'h1Text' => t('disposable_email_successful_crypto_exchange_header', []),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_crypto_exchange_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_crypto_exchange_header', [], $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendSuccessfulReplenishmentOfWalletByBankCard(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['number' => $operation->operation_id];
        $replacementBody = ['number' => $operation->operation_id, 'fiatSum' => $operation->amount,  'cryptoName' => $operation->to_currency, 'sum' => $operation->amount_in_euro];
        $attachment = $this->getTransactionReportPdfPath($operation);
        $body = t('mail_email_successful_replenishment_of_the_wallet_by_bank_card_body', $replacementBody);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_successful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle), [
                'h1Text' => t('disposable_email_successful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_successful_replenishment_of_the_wallet_by_bank_card_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_successful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulReplenishmentOfWalletByBankCard(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementTitle = ['number' => $operation->operation_id];
        $replacementBody = ['number' => $operation->operation_id, 'sum' => $operation->amount];
        $body = t('mail_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_body', $replacementBody);
        $attachment = $this->getTransactionReportPdfPath($operation);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle), [
                'h1Text' => t('disposable_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_unsuccessful_replenishment_of_the_wallet_by_bank_card_header', $replacementTitle, $replacementBody);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendNotificationForManager(CUser $cUser, $operationId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['clientId' => $cUser->cProfile->profile_id, 'operationId' => $operationId];
        $managers = $cUser->cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_new_transaction_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('add_manager_new_transaction_header'), [
                    'h1Text' => t('add_manager_new_transaction_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_new_transaction_body',
                NotificationRecipients::MANAGER,
                'add_manager_new_transaction_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);

            $operation = Operation::query()->where('operation_id', $operationId)->first();
            $this->notificationService->setOperationUrlForNotification($operation->id, $notificationId);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
    }

    public function sendNewTicket(Cuser $cUser, $ticketId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['clientId' => $cUser->cProfile->profile_id, 'ticketId' => $ticketId];
        $replacementsHeader = ['clientId' => $cUser->cProfile->profile_id];
        $managers = $cUser->cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
               continue;
            }
            $body = t('ui_client_new_ticket_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('ui_client_new_ticket_header_for_manager', $replacementsHeader), [
                    'h1Text' => t('ui_client_new_ticket_header_for_manager', $replacementsHeader),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'ui_client_new_ticket_body',
                NotificationRecipients::MANAGER,
                'ui_client_new_ticket_header_for_manager', $replacementsHeader, $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
   }

    public function sendNewTicketClient(BUser $bUser, CUser $cUser, $ticketId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['managerEmail' => $bUser->email, 'ticketId' => $ticketId];
        $body = t('ui_client_new_ticket_body_from_manager', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('ui_client_new_ticket_header'), [
                'h1Text' => t('ui_client_new_ticket_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'ui_client_new_ticket_body_from_manager',
            NotificationRecipients::CURRENT_CLIENT,
            'ui_client_new_ticket_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId, CUser::class);
    }

    public function sendNewTicketMessage(Cuser $cUser, $ticketId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['clientId' => $cUser->cProfile->profile_id, 'ticketId' => $ticketId];
        $replacementsHeader = ['clientId' => $cUser->cProfile->profile_id];
        $managers = $cUser->cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('ui_client_new_message_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('ui_client_new_message_header_for_manager', $replacementsHeader), [
                    'h1Text' => t('ui_client_new_message_header_for_manager', $replacementsHeader),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'ui_client_new_message_body',
                NotificationRecipients::MANAGER,
                'ui_client_new_message_header_for_manager', $replacementsHeader, $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendNewTicketMessageClient(BUser $bUser, Cuser $cUser, $ticketId)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['managerEmail' => \C\SUPPORT_EMAIL , 'ticketId' => $ticketId];
        $body = t('ui_client_new_message_body_from_manager', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('ui_client_new_message_header'), [
                'h1Text' => t('ui_client_new_message_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'ui_client_new_message_body_from_manager',
            NotificationRecipients::CURRENT_CLIENT,
            'ui_client_new_message_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId, CUser::class);
    }

    public function sendUpdatePersonalInformation(CProfile $cProfile, $replacements)
    {
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $subjectReplacements  = ['clientId' => $cProfile->profile_id];
        $replacements['clientId']  = $cProfile->profile_id;
        $managers = $cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('ui_client_update_personal_info_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('ui_client_update_personal_info_header', $subjectReplacements), [
                    'h1Text' => t('ui_client_update_personal_info_header', ['clientId' => $cProfile->profile_id]),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'ui_client_update_personal_info_body',
                NotificationRecipients::MANAGER,
                'ui_client_update_personal_info_header', $subjectReplacements, $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
  }

    private function sendManagerRenewDate(CProfile $cProfile)
    {
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $replacements = ['clientId' => $cProfile->profile_id];
        $managers = $cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('ui_client_renew_date_body', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('ui_client_renew_date_header', $replacements), [
                    'h1Text' => t('ui_client_renew_date_header', $replacements),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'ui_client_renew_date_body',
                NotificationRecipients::MANAGER,
                'ui_client_renew_date_header', $replacements, $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendSuccessfulPasswordResetMessage(CUser $user)
    {
        $this->setCurrentProject($user->project ?? null);
        $this->send('cabinet.emails.cuser-custom-email',
            t('ui_password_update'), [
                'h1Text' => '',
                'body' => t('ui_password_reset_new_finish_text',['date' => Carbon::now()->format('Y-m-d H:i')]),
                'logoPng' => $this->logo
            ], $user->email);

        $notificationId = $this->notificationService->createNotification(
            t('ui_password_reset_new_finish_text', ['date' => Carbon::now()->format('Y-m-d H:i')]),
            NotificationRecipients::CURRENT_CLIENT,
            'ui_password_reset_label_new', [], []);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId);

    }

    public function sendChangedEmailToOldEmail(CUser $cUser, $oldEmail)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_change_email_header'), [
                'h1Text' => t('disposable_email_change_email_header'),
                'body' => t('mail_email_change_body_to_old_email', ['newEmail' => $cUser->email]),
                'logoPng' => $this->logo
            ], $oldEmail);
    }

    public function sendDefaultRateTemplateChangedClient(Cuser $cUser)
    {
        $this->setCurrentProject($cUser->project ?? null);
        $this->notificationService->createNotification(
            'mail_clients_default_rate_template_changed_subject',
            NotificationRecipients::CURRENT_CLIENT,
            'mail_clients_default_rate_template_changed_header', [], []);
        $this->notificationUserService->addDisposableNotification($cUser->id, 'mail_clients_default_rate_template_changed_header', 'mail_clients_default_rate_template_changed_subject');
    }

    public function sendSuccessfulCardVerificationMessage(CUser $user, $number)
    {
        $this->setCurrentProject($user->project ?? null);
        $replacements = ['number' => $number];
        $body = t('mail_email_card_successful_verification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_successful_verification_header'), [
                'h1Text' => t('disposable_email_card_successful_verification_header'),
                'body' => $body
            ], $user->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_successful_verification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_successful_verification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId, CUser::class);
    }

    public function sendUnsuccessfulCardVerificationMessage(CUser $user, $number)
    {
        $this->setCurrentProject($user->project ?? null);
        $replacements = ['number' => $number];
        $body = t('mail_email_card_unsuccessful_verification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_unsuccessful_verification_header'), [
                'h1Text' => t('disposable_email_card_unsuccessful_verification_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $user->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_unsuccessful_verification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_unsuccessful_verification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId, CUser::class);
    }

    public function sendPaymentProviderAccountBalanceLower(BUser $bUser, Account $account)
    {
        $replacements = ['name' => $account->name];
        $body = t('mail_email_payment_provider_minimum_balance_alert_lower_body', $replacements);
        $title = t('disposable_email_payment_provider_minimum_balance_alert_lower_header', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            $title, [
                'h1Text' => $title,
                'body' => $body,
            ], $bUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_payment_provider_minimum_balance_alert_lower_body',
            NotificationRecipients::MANAGER,
            'disposable_email_payment_provider_minimum_balance_alert_lower_header', $replacements, $replacements);
        $this->notificationUserService->addDisposableNotificationExists($bUser->id, $notificationId, BUser::class);
    }

    public function sendSuccessfulTopUpCardOperationMessage(Operation $operation, $cryptoAmount)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['operationNumber' => $operation->operation_id, 'fiatAmount' => $operation->amount, 'cryptocurrency' => $operation->to_currency, 'cryptoAmount' => $cryptoAmount];
        $body = t('mail_email_card_successful_top_up_operation_message', $replacements);
        $attachment = $this->getTransactionReportPdfPath($operation);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_successful_top_up_operation_header', ['operationNumber' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_card_successful_top_up_operation_header', ['operationNumber' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_successful_top_up_operation_message',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_successful_top_up_operation_header', ['operationNumber' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendC2CTransactionDetectedMessageToUser(Transaction $transaction)
    {
        $replacementsPayer = [
            'amount' => $transaction->trans_amount,
            'merchantName' => $transaction->operation->cProfile->getFullName(),
            'status' => TransactionStatuses::getName($transaction->status),
            'transactionId' => $transaction->tx_id,
            'currency' => $transaction->operation->to_currency,
            'link' => $transaction->operation->getCryptoExplorerUrl(),
            'date' => $transaction->updated_at->toDateTimeString(),
        ];
        $body = t('mail_email_c2c_transaction_detected_message', $replacementsPayer);
        $this->setCurrentProject($transaction->operation->cProfile->cUser->project ?? null);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_c2c_crypto_transaction_detected_header'), [
                'h1Text' => t('disposable_email_c2c_crypto_transaction_detected_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $transaction->operation->paymentFormAttempt->email);
    }

    public function sendC2CTransactionDetectedMessageToMerchant(Transaction $transaction)
    {
        $operation = $transaction->operation;
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);

        $replacementsMerchant = [
            'payerName' => $operation->paymentFormAttempt->getPayerFullName(),
            'payerEmail' => $operation->paymentFormAttempt->email,
            'payerPhone' => $operation->paymentFormAttempt->phone,
            'amount' => $transaction->trans_amount,
            'currency' => $transaction->operation->to_currency,
            'status' => TransactionStatuses::getName($transaction->status),
            'transactionId' => $transaction->tx_id,
            'link' => $transaction->operation->getCryptoExplorerUrl(),
            'date' => $transaction->updated_at->toDateTimeString(),
        ];
        $body = t('mail_email_c2c_transaction_detected_message_for_merchant', $replacementsMerchant);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_c2c_crypto_transaction_detected_header'), [
                'h1Text' => t('disposable_email_c2c_crypto_transaction_detected_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_c2c_transaction_detected_message_for_merchant',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_c2c_crypto_transaction_detected_header', [], $replacementsMerchant);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendC2CTransactionApprovedMessageToUser(Transaction $transaction)
    {
        $replacements = [
            'amount' => $transaction->trans_amount,
            'merchantName' => $transaction->operation->cProfile->getFullName(),
            'status' => TransactionStatuses::getName($transaction->status),
            'currency' => $transaction->operation->to_currency,
            'transactionId' => $transaction->tx_id,
            'link' => $transaction->operation->getCryptoExplorerUrl(),
            'date' => $transaction->updated_at->toDateTimeString(),
        ];
        $body = t('mail_email_c2c_transaction_approved_message', $replacements);
        $this->setCurrentProject($transaction->operation->cProfile->cUser->project ?? null);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_c2c_crypto_transaction_approved_header'), [
                'h1Text' => t('disposable_email_c2c_crypto_transaction_approved_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $transaction->operation->paymentFormAttempt->email);
    }

    public function sendC2CTransactionApprovedMessageToMerchant(Transaction $transaction)
    {
        $operation = $transaction->operation;
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacementsMerchant = [
            'payerName' => !empty($operation->paymentFormAttempt) ? $operation->paymentFormAttempt->getPayerFullName() : '',
            'payerEmail' => $operation->paymentFormAttempt->email,
            'payerPhone' => $operation->paymentFormAttempt->phone,
            'amount' => $transaction->trans_amount,
            'currency' => $transaction->operation->to_currency,
            'status' => TransactionStatuses::getName($transaction->status),
            'transactionId' => $transaction->tx_id,
            'date' => $transaction->updated_at->toDateTimeString(),
            'link' => $transaction->operation->getCryptoExplorerUrl(),
        ];
        $body = t('mail_email_c2c_transaction_approved_message_for_merchant', $replacementsMerchant);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_c2c_crypto_transaction_approved_header'), [
                'h1Text' => t('disposable_email_c2c_crypto_transaction_approved_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);

        $notificationId = $this->notificationService->createNotification(
            'mail_email_c2c_transaction_approved_message_for_merchant',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_c2c_crypto_transaction_approved_header', [], $replacementsMerchant);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendNewTopUpCardOperationMessage(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['operationNumber' => $operation->operation_id, 'fiatAmount' => $operation->amount, 'currency' => $operation->from_currency];
        $body = t('mail_email_card_new_top_up_operation_message', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_new_top_up_operation_header', ['operationNumber' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_card_new_top_up_operation_header', ['operationNumber' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_new_top_up_operation_message',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_new_top_up_operation_header', ['operationNumber' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);

    }

    public function sendNewPaymentFormOperationMessage(Operation $operation)
    {
        $this->setCurrentProject($operation->cProfile->cUser->project ?? null);
        $address = $operation->toAccount->cryptoAccountDetail->address;
        $cryptoAmount = $operation->credited;
        $this->setCurrentProject($operation->cProfile->cUser->project ?? null);
        $replacements = [
            'fiatAmount' => $operation->amount,
            'currency' => $operation->from_currency,
            'cryptoCurrency' => $operation->to_currency,
            'cryptoAmount' => $cryptoAmount,
        ];

        $this->send('cabinet.emails.payment-form-confirm-email',
            t('disposable_email_card_new_top_up_operation_header',
                ['operationNumber' => $operation->operation_id]),
            [
                'payment_details_info' => t('payment_details_info', $replacements),
                'walletAddress' =>  t('sent_to_wallet_address', ['walletAddress' => $address]),
                'dashboardUrl' => route('cabinet.dashboard'),
                'support_team_link' => t('support_team_link', [ 'link' => t('contact_to_support_link')])
            ],
            $operation->cProfile->cUser->email);

    }


    /**
     * @param Operation $operation
     */
    public function sendUnsuccessfulTopUpCardOperationBankResponseFail(Operation $operation): void
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['operationNumber' => $operation->operation_id, 'fiatAmount' => $operation->amount];
        $body = t('mail_email_card_unsuccessful_top_up_operation_cause_of_bank', $replacements);
        $attachment = $this->getTransactionReportPdfPath($operation);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_unsuccessful_top_up_operation_cause_of_bank',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    /**
     * @param Operation $operation
     */
    public function sendUnsuccessfulTopUpCardOperationTimeLimitReached(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['operationNumber' => $operation->operation_id, 'fiatAmount' => $operation->amount];
        $body = t('mail_email_card_unsuccessful_top_up_operation_time_limit_reached', $replacements);
        $attachment = $this->getTransactionReportPdfPath($operation);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_unsuccessful_top_up_operation_time_limit_reached',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendUnsuccessfulTopUpCardOperationPersonalInfoError(Operation $operation)
    {
        $cUser = $operation->cProfile->cUser;
        $this->setCurrentProject($cUser->project ?? null);
        $replacements = ['operationNumber' => $operation->operation_id, 'fiatAmount' => $operation->amount];
        $body = t('mail_email_card_unsuccessful_top_up_operation', $replacements);
        $attachment = $this->getTransactionReportPdfPath($operation);
        $this->send('cabinet.emails.cuser-custom-email',
            t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]), [
                'h1Text' => t('disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $cUser->email, null, $attachment);
        if($attachment) {
            unlink($attachment);
        }
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_unsuccessful_top_up_operation',
            NotificationRecipients::CURRENT_CLIENT,
            'disposable_email_card_unsuccessful_top_up_operation_header', ['operationNumber' => $operation->operation_id], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($cUser->id, $notificationId);
    }

    public function sendTopUpCardPaymentInvalidAmountMessageToManager(CProfile $cProfile, $operation, $paidAmount)
    {
        $replacements = ['clientId' => $cProfile->profile_id,  'paidAmount' => $paidAmount, 'expectedAmount' => $operation->amount ,'operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_invalid_amount', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_invalid_card_operation_header'), [
                    'h1Text' => t('manager_invalid_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_invalid_amount',
                NotificationRecipients::MANAGER,
                'manager_invalid_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
  }

    public function sendRefundTopUpCardPaymentMessageToManager(CProfile $cProfile, $operation)
    {
        $replacements = ['name' => $cProfile->getFullName(), 'operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_refund', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_refunded_card_operation_header'), [
                    'h1Text' => t('manager_refunded_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_refund',
                NotificationRecipients::MANAGER,
                'manager_refunded_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendChargebackTopUpCardPaymentMessageToManager(CProfile $cProfile, $operation)
    {
        $replacements = ['operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_chargeback', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_chargeback_card_operation_header'), [
                    'h1Text' => t('manager_chargeback_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_chargeback',
                NotificationRecipients::MANAGER,
                'manager_chargeback_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
}

    public function sendTopUpCardPaymentInvalidCurrencyMessageToManager(CProfile $cProfile, Operation $operation, $paidByCurrency)
    {
        $replacements = ['clientId' => $cProfile->profile_id,  'paidByCurrency' => $paidByCurrency, 'expectedByCurrency' => $operation->from_currency ,'operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_invalid_currency', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_invalid_card_operation_header'), [
                    'h1Text' => t('manager_invalid_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_invalid_currency',
                NotificationRecipients::MANAGER,
                'manager_invalid_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
  }

    public function sendTopUpCardPaymentInvalidLimitsMessageToManager(CProfile $cProfile, Operation $operation)
    {
        $replacements = ['clientId' => $cProfile->profile_id, 'operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_invalid_limits', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_invalid_card_operation_header'), [
                    'h1Text' => t('manager_invalid_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_invalid_limits',
                NotificationRecipients::MANAGER,
                'manager_invalid_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendTopUpCardPaymentInvalidPersonalInfoMessageToManager(CProfile $cProfile, $operation)
    {
        $replacements = ['clientId' => $cProfile->profile_id,  'operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_invalid_personal_info', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_invalid_card_operation_header'), [
                    'h1Text' => t('manager_invalid_card_operation_header'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'add_manager_card_operation_invalid_personal_info',
                NotificationRecipients::MANAGER,
                'manager_invalid_card_operation_header', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendUnsuccessfulTopUpCardOperationTimeLimitReachedToManager(CProfile $cProfile, $operation)
    {
        $replacements = ['operationId' => $operation->operation_id];
        $managers = $cProfile->getManagers();
        $this->setCurrentProject($cProfile->cUser->project ?? null);

        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
                continue;
            }
            $body = t('add_manager_card_operation_payment_time_limit_reached', $replacements);
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_declined_card_operation'), [
                    'h1Text' => t('manager_declined_card_operation'),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                'manager_declined_card_operation',
                NotificationRecipients::MANAGER,
                'manager_declined_card_operation', [], $replacements);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    public function sendUpdatedSubStatusMessageToManager(CProfile $cProfile, Operation $operation, string $message = '')
    {
        $this->setCurrentProject($cProfile->cUser->project ?? null);
        $managers = $cProfile->getManagers();
        foreach ($managers as $manager) {
            if(!$this->hasPermissionToReceived($manager)) {
               continue;
            }
            $body = $message;
            $this->send('cabinet.emails.cuser-custom-email',
                t('manager_card_operation_new_substatus', [ 'operationId' => $operation->operation_id]), [
                    'h1Text' => t('manager_card_operation_new_substatus', [ 'operationId' => $operation->operation_id]),
                    'body' => $body,
                    'logoPng' => $this->logo
                ], $manager->email);
            $notificationId = $this->notificationService->createNotification(
                $message,
                NotificationRecipients::MANAGER,
                t('manager_card_operation_new_substatus', [ 'operationId' => $operation->operation_id]), [], []);
            $this->notificationUserService->addDisposableNotificationExists($manager->id, $notificationId, BUser::class);
            $this->sendManager(t('sms_notification_message', ['url' => route('backoffice.notifications.show', ['notificationId' => $notificationId])]) , $manager->phone);
        }
 }

    private function getGeoLocation(): array
    {
        $ip = \C\getUserIp();
        $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip={$ip}");
        $geo = (!empty($xml->geoplugin_city) ? $xml->geoplugin_city . ', ' : '') . $xml->geoplugin_countryName;
        $browser = \request()->header('User-Agent');

        return compact('ip', 'geo', 'browser');
    }


    public function sendWallesterCardOrderInProgressMessage(CUser $user, string $cardType)
    {
        $this->setCurrentProject($user->project ?? null);
        $replacements = ['cardType' => $cardType];
        $body = t('mail_email_card_order_in_progress_notification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('mail_email_card_order_in_progress_notification_header', ['cardType' => $cardType]), [
                'h1Text' => t('mail_email_card_order_in_progress_notification_header', ['cardType' => $cardType]),
                'body' => $body,
                'logoPng' => $this->logo
            ], $user->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_order_in_progress_notification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'mail_email_card_order_in_progress_notification_header', ['cardType' => $cardType], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId, CUser::class);
    }

    public function sendWallesterCardOrderOperationCreatedMessage(CUser $user, Operation $operation)
    {
        $this->setCurrentProject($user->project ?? null);
        $replacements = ['amount' => $operation->amount, 'currency' => $operation->from_currency, 'operationNumber' => $operation->operation_id];
        $body = t('mail_email_card_order_in_progress_operation_created_notification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('mail_email_card_order_in_progress_operation_notification_header'), [
                'h1Text' => t('mail_email_card_order_in_progress_operation_notification_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $user->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_order_in_progress_operation_created_notification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'mail_email_card_order_in_progress_operation_notification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId, CUser::class);
    }


    public function sendWallesterCardOrderSuccessMessage(CUser $user, string $cardId)
    {
        $this->setCurrentProject($user->project ?? null);
        $replacements = ['link' => route('wallester.card.details', ['id' => $cardId])];
        $body = t('mail_email_card_order_success_notification_body', $replacements);
        $this->send('cabinet.emails.cuser-custom-email',
            t('mail_email_card_order_success_notification_header'), [
                'h1Text' => t('mail_email_card_order_success_notification_header'),
                'body' => $body,
                'logoPng' => $this->logo
            ], $user->email);
        $notificationId = $this->notificationService->createNotification(
            'mail_email_card_order_success_notification_body',
            NotificationRecipients::CURRENT_CLIENT,
            'mail_email_card_order_success_notification_header', [], $replacements);
        $this->notificationUserService->addDisposableNotificationExists($user->id, $notificationId, CUser::class);
    }

    private function setCurrentProject($project = null)
    {
        $this->project = $project ?? config()->get('projects.currentProject') ?? null;

        if($this->project) {
            URL::forceRootUrl($this->project->domainFullPath());
            $this->logo = $this->project->logoPng ?? null;
        }
    }
    public function sendVerifyPhoneReminder(CUserTemporaryRegisterData $temporaryData)
    {
        $url = URL::signedRoute('cabinet.register.complete', ['temporaryDataId' => $temporaryData->id]);
    }
    private function hasPermissionToReceived(BUser $manager): bool
    {
        if(!$this->project) {
            return false;
        }
        $projectIds= $manager->getAvailableProjectsByPermissions([\App\Enums\BUserPermissions::RECEIVE_NOTIFICATIONS]);
        return in_array($this->project->id, $projectIds);
    }



    private function sendManager($message, $phone)
    {
        if(config('cratos.enable_send_notification_sms')) {
            $this->smsCodeService->send($message, $phone, $this->project->id ?? null);
        }
    }
}
