<?php


namespace App\Facades;

use App\Models\Account;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\Cabinet\CUserTemporaryRegisterData;
use App\Models\EmailVerification;
use App\Models\Operation;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Services\EmailService;
use Illuminate\Support\Facades\Facade;
use \Illuminate\Contracts\Mail\Mailable;

/**
 * @method static bool send(Mailable|string|array $emailTemplate,string $subject,array $data,string $to,string $from = null)
 * @method static void sendSettingUpdate(CUser $cUser,CProfile $profile)
 * @method static void sendPasswordUpdateEmail(CUser $cUser)
 * @method static void sendPasswordSetEmailAdmin($token, $email)
 * @method static string sendPasswordRecovery(CUser $cUser)
 * @method static void sendSettingUpdateToManager(CProfile $profile,array $dirtyAttributes, string $fullName)
 * @method static void sendSuccessfulLogin(CUser $cUser)
 * @method static void sendSuccessfulExchangeCredittingSepaOrSwift(Operation $operation, $cryptoAmount)
 * @method static void sendStatusAccount(CUser $cUser, $cause, $status)
 * @method static void sendEmailUpdate(CUser $cUser, $newEmail,EmailVerification $emailVerification)
 * @method static void sendEmailRegistrationConfirm(CUser $cUser, EmailVerification $emailVerification)
 * @method static void sendInvoicePaymentSepaOrSwift(CUser $cUser,int $operationOperationId, $type, $count, $currency,int $operationId)
 * @method static void sendC2CTransactionDetectedMessageToUser(Transaction $transaction)
 * @method static void sendC2CTransactionDetectedMessageToMerchant(Transaction $transaction)
 * @method static void sendC2CTransactionApprovedMessageToUser(Transaction $transaction)
 * @method static void sendC2CTransactionApprovedMessageToMerchant(Transaction $transaction)
 * @method static void sendCreatedNewAccount(CUser $cUser)
 * @method static void sendCreatingNewWalletForClient(CUser $cUser, $coin,string $address)
 * @method static void sendCreatingNewFiatWalletForClient(CUser $cUser, $currency)
 * @method static void sendAddingPaymentTemplate(CUser $cUser, $account, $wireAccountDetail)
 * @method static void sendChangingLimits($rateTemplateId, $limits = true)
 * @method static void sendChangingRateForUser(CUser $cUser)
 * @method static void sendBinding2FA(CUser $cUser)
 * @method static void send2FACode(CUser $cUser, $code)
 * @method static void sendUnlink2FA(CUser $cUser)
 * @method static void sendAccountSuspendedEmail($cUser, $profile)
 * @method static void sendDocsAutoDeleteEmail(CUser $cUser, CProfile $profile, string $documentNames)
 * @method static void sendNotifyBeforeSuspendingUserEmail(CUser $cUser, CProfile $profile)
 * @method static void sendSuspendUserEmail(CUser $cUser, CProfile $profile)
 * @method static void sendInfoEmail(CUser $cUser, CProfile $profile, string $subject, string $text, array $replacement = [])
 * @method static void sendSuccessfulVerification(CUser $cUser)
 * @method static void sendUnsuccessfulVerification(CUser $cUser, $cause)
 * @method static void sendAdditionalVerificationRequest(CUser $cUser,int $applicantId)
 * @method static void sendVerificationRequestFromTheManager(CUser $cUser,Operation $operation)
 * @method static void sendUpdateLevelRequest(CUser $cUser,Operation $operation)
 * @method static void replyTicketFromCratosManager(Ticket $ticket)
 * @method static void ticketClosureNotification(CUser $cUser, $ticketId)
 * @method static void sendVerificationConfirmationFromTheManager(CUser $cUser,Operation $operation)
 * @method static void sendUnsuccessfulConfirmationVerificationFromTheManager(CUser $cUser,Operation $operation)
 * @method static void sendUpdatingDocuments(CUser $cUser,int $applicantId)
 * @method static void sendRejectedSepaSwiftTopUpTransaction(Operation $operation)
 * @method static void sendRefundedSepaSwiftTopUpTransaction(Operation $operation)
 * @method static void sendmailSuccessfulSepaSwiftWithdrawalApplication(Operation $operation)
 * @method static void sendAddingCryptoWallet(CUser $cUser,string $address)
 * @method static void sendUnsuccessfulAddingCryptoWallet(CUser $cUser,string $address)
 * @method static void sendLoginFromNewDevice(CUser $cUser)
 * @method static void sendCompletedRequestForWithdrawalViaSepaOrSwift(Operation $operation, $fiatAmount)
 * @method static void sendSuccessfulIncomingCryptocurrencyPayment(Operation $operation)
 * @method static void sendUnsuccessfulIncomingCryptocurrencyPayment(Operation $operation)
 * @method static void sendUnsuccessfulIncomingCryptocurrencyPaymentByCard(Operation $operation)
 * @method static void sendSuccessfulChargebackPaymentByCard(Operation $operation)
 * @method static void sendSuccessfulWithdrawalOfCryptocurrencyToCryptoWallet(Operation $operation, $amount)
 * @method static void sendUnsuccessfulWithdrawalCryptocurrencyCryptoWallet(Operation $operation)
 * @method static void sendSuccessfulCryptoExchange(Operation $operation)
 * @method static void sendSuccessfulReplenishmentOfWalletByBankCard(Operation $operation)
 * @method static void sendUnsuccessfulReplenishmentOfWalletByBankCard(Operation $operation)
 * @method static void sendNotificationForManager(CUser $CUser, $operationId)
 * @method static void sendNewTicket(CUser $CUser, $ticketId)
 * @method static void sendNewTicketClient(BUser $bUser, CUser $CUser, $ticketId)
 * @method static void sendNewTicketMessage(CUser $CUser, $ticketId)
 * @method static void sendUpdatePersonalInformation(CProfile $cProfile)
 * @method static void sendManagerRenewDate(CProfile $cProfile)
 * @method static void sendSuccessfulPasswordResetMessage(CUser $user)
 * @method static void sendSuccessUpdatePersonalInformationMessage( $profile , $dirtyAttributes)
 * @method static void sendChangingRatesToAllUser($rateTemplateId)
 * @method static void sendChangingLimitsToAllUsers($rateTemplateId)
 * @method static void sendChangedEmailToOldEmail(CUser $cUser, $oldEmail)
 * @method static void sendNewTicketMessageClient(BUser $bUser, CUser $cUser, $ticketId)
 * @method static void sendPaymentProviderAccountBalanceLower(BUser $bUser, Account $account)
 * @method static void sendNewTopUpCardOperationMessage(Operation $operation)
 * @method static void sendNewPaymentFormOperationMessage(Operation $operation)
 * @method static void sendSuccessfulTopUpCardOperationMessage(Operation $operation, $cryptoAmount)
 * @method static void sendUnsuccessfulTopUpCardOperationBankResponseFail(Operation $operation)
 * @method static void sendUnsuccessfulTopUpCardOperationTimeLimitReached(Operation $operation)
 * @method static void sendUnsuccessfulTopUpCardOperationPersonalInfoError(Operation $operation)
 * @method static void sendRefundTopUpCardPaymentMessageToManager(CProfile $cProfile, $operation)
 * @method static void sendTopUpCardPaymentInvalidAmountMessageToManager(CProfile $cProfile, $operation, $paidAmount)
 * @method static void sendChargebackTopUpCardPaymentMessageToManager(CProfile $cProfile, $operation)
 * @method static void sendTopUpCardPaymentInvalidCurrencyMessageToManager(CProfile $cProfile, Operation $operation, $paidByCurrency)
 * @method static void sendTopUpCardPaymentInvalidLimitsMessageToManager(CProfile $cProfile, Operation $operation)
 * @method static void sendTopUpCardPaymentInvalidPersonalInfoMessageToManager(CProfile $cProfile, $operation)
 * @method static void sendUnsuccessfulTopUpCardOperationTimeLimitReachedToManager(CProfile $cProfile, $operation)
 * @method static void sendUpdatedSubStatusMessageToManager(CProfile $cProfile,Operation $operation, string $message = '')
 * @method static void sendPaymentFormEmailConfirm(EmailVerification $emailVerification)
 * @method static void sendUpgradeLevelEmail(Operation $operation)
 * @method static void sendWalletVerificationFailedEmail(CProfile $cProfile, $address)
 * @method static void sendPaymentFormUnsuccessfulPaymentEmail(Operation $operation, $error)
 * @method static void sendPaymentFormNewDeviceLoginEmail(Operation $operation)
 * @method static void sendPaymentFormAuthenticationCodeEmail(Operation $operation, $token)
 * @method static void sendSuccessVerificationRegistrationConfirmEmail(CUser $cUser)
 * @method static void sendPaymentFormGenerateNewPasswordEmail(CUser $cUser)
 * @method static void sendWallesterCardOrderInProgressMessage(CUser $cUser, string $cardType)
 * @method static void sendWallesterCardOrderOperationCreatedMessage(CUser $cUser, Operation $operation)
 * @method static void sendWallesterCardOrderSuccessMessage(CUser $cUser, string $cardId)
 * @method static void sendVerifyPhoneReminder(CUserTemporaryRegisterData $temporaryData)
 *
 * @see EmailService
 */
class EmailFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'EmailFacade';
    }
}
