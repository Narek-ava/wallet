<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\AccountStatuses;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\SumsubCrypto;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\BackofficeBankDetailRequest;
use App\Http\Requests\BackofficeBankDetailUpdateRequest;
use App\Http\Requests\CheckWalletAddressRequest;
use App\Services\AccountService;
use App\Services\ActivityLogService;
use App\Services\CProfileService;
use App\Services\CryptoAccountService;
use App\Services\EmailService;
use App\Services\OperationService;
use Illuminate\Http\Request;

class BankDetailsController extends Controller
{
    public function store(BackofficeBankDetailRequest $request,
                          OperationService $operationService,
                          CProfileService $cProfileService)
    {
        $profile = $cProfileService->getProfileById($request->c_profile_id);
        $account = $operationService->createBankDetailAccount($request->only(['template_name', 'country', 'currency', 'type']), $request->c_profile_id);
        ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_ADDED, ['account_id' => $account->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_ADDED, null, $profile->cUser->id);
        $wireAccountDetail = $operationService->createWireAccountDetail($request->only(['iban', 'swift', 'bank_name', 'bank_address', 'account_holder', 'account_number', 'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']), $account);
        ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_ADDED, ['wire_account_detail_id' => $wireAccountDetail->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_ADDED, null, $profile->cUser->id);
        EmailFacade::sendAddingPaymentTemplate($profile->cUser, $account, $wireAccountDetail);
        return redirect()->to(url()->previous() . '#bankSettings')->with(['success' => 'Success']);
    }

    public function update(BackofficeBankDetailUpdateRequest $request,
                           OperationService $operationService,
                           AccountService $accountService,
                           CProfileService $cProfileService)
    {
        $profile = $cProfileService->getProfileById($request->c_profile_id);
        $account = $accountService->getAccountById($request->u_account_id);
        if ($account) {
            $accountData = [
                'name' => $request->u_template_name,
                'country' => $request->u_country,
                'currency' => $request->u_currency,
                'account_type' => $request->u_type,
            ];
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_UPDATED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_UPDATED, null, $profile->cUser->id);

            $wireData = [
                'iban' => $request->u_iban,
                'swift' => $request->u_swift,
                'bank_name' => $request->u_bank_name,
                'bank_address' => $request->u_bank_address,
                'account_beneficiary' => $request->u_account_holder,
                'account_number' => $request->u_account_number,
                'correspondent_bank' => $request->u_correspondent_bank,
                'correspondent_bank_swift' => $request->u_correspondent_bank_swift,
                'intermediary_bank' => $request->u_intermediary_bank,
                'intermediary_bank_swift' => $request->u_intermediary_bank_swift,
            ];
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_UPDATED, ['wire_account_detail_id' => $account->wire->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED, null, $profile->cUser->id);


            $account->update($accountData);
            $account->wire()->update($wireData);
        }
        return redirect()->to(url()->previous() . '#bankSettings')->with(['success' => 'Success']);
    }

    public function delete(Request $request,
                           AccountService $accountService,
                           CProfileService $cProfileService)
    {
        $profile = $cProfileService->getProfileById($request->c_profile_id);
        $account = $accountService->getAccountById($request->account_id);
        if ($account && $account->wire) {
            $account->status = AccountStatuses::STATUS_DISABLED;
            $account->save();
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_DELETED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_DELETED, null, $profile->cUser->id);
        }
        return redirect()->to(url()->previous() . '#bankSettings')->with(['success' => 'Success']);
    }

    public function getAccountWithWire(AccountService $accountService, $id)
    {
        return $accountService->getAccountById($id);
    }

    public function checkWalletAddress(CheckWalletAddressRequest $request, AccountService $accountService, CProfileService $cProfileService)
    {
        $profile = $cProfileService->getProfileById($request->c_profile_id);
        if (in_array($request->crypto_currency, Currency::getList())) {
            $account = $accountService->disabledAccount($request->crypto_currency, $request->wallet_address, $profile->id);
            if ($account) {
                $account->update(['status' => AccountStatuses::STATUS_ACTIVE]);
            } else {
                $account = $accountService->addWalletToClient($request->wallet_address, $request->crypto_currency, $profile, true);
            }
            if ($account && $account->cryptoAccountDetail->isAllowedRisk()) {
                ActivityLogFacade::saveLog(LogMessage::USER_CRYPTO_WALLET_ADDED_BACKOFFICE, [],
                    LogResult::RESULT_SUCCESS, LogType::TYPE_CRYPTO_WALLET_ADDED_BACKOFFICE, null, $profile->cUser->id);
                return redirect()->to(url()->previous() . '#bankSettings')->with(['success' => t('crypto_wallet_added_successfully')]);
            }
        }
        return redirect()->to(url()->previous() . '#bankSettings')->with('warning', t('not_valid_wallet_address_or_high_risk_score'));
    }

    public function dropAccount(Request $request, AccountService $accountService)
    {
        $account = $accountService->getAccountById($request->accountId);
        if ($account) {
            $account->update(['status' => AccountStatuses::STATUS_DISABLED]);
            return true;
        }
        return false;
    }
}
