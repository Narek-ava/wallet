<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\{OperationOperationType, OperationStatuses};
use App\Facades\EmailFacade;
use App\Http\{Controllers\Controller, Requests\WithdrawWireRequest};
use App\Models\{Account, Cabinet\CProfile, Cabinet\CUser, CryptoAccountDetail, Operation};
use App\Services\{ComplianceService, OperationService, TwoFAService, WithdrawCryptoService};
use Carbon\Carbon;
use Illuminate\{Contracts\View\Factory,
    Http\Request,
    Support\Facades\Auth,
    Support\Facades\Session,
    Support\Str,
    View\View};

class WithdrawWireController extends Controller
{
    /**
     * @param CryptoAccountDetail $cryptoAccountDetail
     * @param Request $request
     * @param WithdrawCryptoService $withdrawCryptoService
     * @return Factory|View
     */
    public function showWithdrawWire(CryptoAccountDetail $cryptoAccountDetail, Request $request, WithdrawCryptoService $withdrawCryptoService)
    {
        //start create operation with session
        $currentId = Str::uuid()->toString();
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $currentId;
        $request->session()->put('operationIds', $operationIds);

        //get limits and commissions for cProfile
        $cUser = auth()->user();
        $cProfile = $cUser->cProfile;
        $commissions = $withdrawCryptoService->getCommissions($cProfile->rate_template_id, $cryptoAccountDetail->coin);
        $limits = $withdrawCryptoService->getLimits($cProfile);

        $receivedAmountForCurrentMonth = Operation::where('c_profile_id', $cProfile->id)
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->where('from_currency', $cryptoAccountDetail->coin)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');

        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        if ($availableMonthlyAmount < 0) {
            $availableMonthlyAmount = 0;
        }
        $account = $cProfile->accounts()->findOrFail($cryptoAccountDetail->account_id);
        /* @var Account $account*/
        $availableCurrentAmount = formatMoney($account->getAvailableBalance(), $account->currency);

        return view('cabinet.wallets.send_wire', compact(
            'cUser', 'cryptoAccountDetail', 'currentId', 'commissions',
            'limits', 'availableMonthlyAmount', 'availableCurrentAmount'
        ));
    }

    public function withdrawWireOperation(TwoFAService $twoFAService, WithdrawWireRequest $request, OperationService $operationService, ComplianceService $complianceService, $id)
    {
        $cUser = Auth::user();

        $cProfile = $cUser->cProfile;
        /* @var CProfile $cProfile */

        $cryptoAccountDetail = $cProfile->cryptoAccountDetail()->findOrFail($id);
        /* @var  CryptoAccountDetail $cryptoAccountDetail */

        $account = $cryptoAccountDetail->account;
        $validator = $account->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($validator);
        }
        $operationIds = $request->session()->pull('operationIds', []);
        $operationType = $request->type;

        $bankAccountData = $request->only(['template_name', 'country', 'currency', 'type']);
        $bankAccountData['type'] = OperationOperationType::ACCOUNT_OPERATION_TYPES[$operationType];
        if ($request->bank_template == '0') {
            $toAccount = $operationService->createBankDetailAccount($bankAccountData, $cProfile->id);
            $operationService->createWireAccountDetail($request->only(['iban', 'swift', 'bank_name', 'bank_address', 'account_holder', 'account_number']), $toAccount);
        } else {
            $toAccount = $cProfile->accounts()->findOrFail($request->bank_template);
        }

        /* @var CUser $cUser*/
        if (in_array($request->operation_id, $operationIds) && (!$cUser->two_fa_type || $twoFAService->checkSession())) {
            $operation = $operationService->createOperation(
                $cProfile->id, $operationType, $request->amount, $cryptoAccountDetail->coin, $toAccount->currency, $cryptoAccountDetail->account->id, $toAccount->id, OperationStatuses::PENDING,
                $request->bank_detail, null, $request->operation_id, $request->provider_account_id);

            if ($operationService->getCurrentMonthOperationsAmountSum(auth()->user()->cProfile) < auth()->user()->limit->monthly_amount_max) {
                EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
            } else {
                $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
                EmailFacade::sendNotificationForManager($cUser, $operation->operation_id);
//                EmailFacade::sendVerificationRequestFromTheManager(auth()->user(), $operation);
            }

            if ($operation) {
                Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
                Session::flash('operationCreated', t('withdraw_wire_operation_created_successfully'));
            } else {
                Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            }

            return redirect()->route('cabinet.wallets.show', $id);
        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            return redirect()->route('cabinet.wallets.show', $id);
        }
    }

}
