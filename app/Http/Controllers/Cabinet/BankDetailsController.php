<?php
namespace App\Http\Controllers\Cabinet;

use App\Enums\AccountStatuses;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\SumsubCrypto;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankDetailRequest;
use App\Http\Requests\BankDetailUpdateRequest;
use App\Http\Requests\Cabinet\DeleteAccountRequest;
use App\Http\Requests\CheckWalletAddressRequest;
use App\Services\AccountService;
use App\Services\ActivityLogService;
use App\Services\ComplianceService;
use App\Services\CryptoAccountService;
use App\Services\EmailService;
use App\Services\OperationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BankDetailsController extends Controller
{
    public function index(AccountService $accountService, ComplianceService $complianceService)
    {
        $cProfile = getCProfile();
        $complianceProvider = $complianceService->getComplianceProvider(auth()->user()->project);
        $sumSubApiUrl = $complianceProvider->getApiUrl();
        $accounts = $accountService->getUserBankAccountsByCProfileId($cProfile->id);
        $accountsCrypto = $accountService->getUserCryptoAccountsByCProfileId($cProfile->id);
        $timezone = $cProfile->timezone;
        return view('cabinet.bank-details.index', compact(
            'accounts',
            'accountsCrypto',
            'sumSubApiUrl',
            'timezone'
        ));
    }

    public function store(BankDetailRequest $request, OperationService $operationService)
    {
        $account = $operationService->createBankDetailAccount($request->only(['template_name', 'country', 'currency', 'type']), auth()->user()->cProfile->id);
        ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_ADDED, ['account_id' => $account->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_ADDED, null , (auth()->id()) );
        $wireAccountDetail = $operationService->createWireAccountDetail($request->only(['iban', 'swift', 'bank_name', 'bank_address', 'account_holder', 'account_number', 'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']), $account);
        ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_ADDED, ['wire_account_detail_id' => $wireAccountDetail->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_ADDED, null ,(auth()->id()));
        EmailFacade::sendAddingPaymentTemplate(auth()->user(), $account, $wireAccountDetail);
        return redirect()->back()->with(['success' => 'Success']);
    }

    public function update(BankDetailUpdateRequest $request, AccountService $accountService)
    {
        $account = $accountService->getAccountById($request->u_account_id);

        if ($account->cProfile->id != auth()->user()->cProfile->id) {
            return redirect()->back()->withErrors([
                'success' => false,
                'account_id' => t('account_not_found'),
            ], 422);
        }

        if ($account) {
            $accountData = [
                'name' => $request->u_template_name,
                'country' => $request->u_country,
                'currency' => $request->u_currency,
                'account_type' => $request->u_type,
            ];
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_UPDATED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_UPDATED, null, (auth()->id()));

            $wireData = [
                'iban' => $request->u_iban,
                'swift' => $request->u_swift,
                'bank_name' => $request->u_bank_name,
                'bank_address' => $request->u_bank_address,
                'account_beneficiary' => $request->u_account_holder,
                'account_number' => $request->u_account_number,
            ];
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_UPDATED, ['wire_account_detail_id' => $account->wire->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED, null, (auth()->id()));

            $account->update($accountData);
            $account->wire()->update($wireData);
        }
        return redirect()->back();
    }

    public function delete(DeleteAccountRequest $request, AccountService $accountService)
    {
        $account = $accountService->getAccountById($request->account_id);

        if ($account->cProfile->id != auth()->user()->cProfile->id) {
            return redirect()->back()->withErrors([
                'account_id' => t('account_not_found'),
            ]);
        }

        if ($account && $account->wire) {
            $account->status = AccountStatuses::STATUS_DISABLED;
            $account->save();
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_DELETED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_DELETED, (auth()->id()));
        }
        return redirect()->back();
    }

    public function getAccountWithWire(AccountService $accountService, $id)
    {
        return $accountService->getAccountById($id);
    }

    public function checkWalletAddress(CheckWalletAddressRequest $request, AccountService $accountService)
    {
        if (in_array($request->crypto_currency, Currency::getList())) {
            $account = $accountService->disabledAccount($request->crypto_currency, $request->wallet_address, auth()->user()->cProfile->id);
            if ($account) {
                $account->update(['status' => AccountStatuses::STATUS_ACTIVE]);
            } else {
                $account = $accountService->addWalletToClient($request->wallet_address, $request->crypto_currency, auth()->user()->cProfile, true);
            }
            if ($account && $account->status == AccountStatuses::STATUS_ACTIVE && $account->cryptoAccountDetail->isAllowedRisk()) {
                return redirect()->back()->with(['success' => t('crypto_wallet_added_successfully')]);
            }
        }
        EmailFacade::sendUnsuccessfulAddingCryptoWallet(auth()->user(), $request->wallet_address);
        return redirect()->back()->with('warning', t('not_supported_wallet_address'));
    }
}
