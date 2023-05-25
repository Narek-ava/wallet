<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\{AccountType, OperationOperationType, OperationStatuses};
use App\Facades\EmailFacade;
use App\Http\{Controllers\Controller, Requests\WithdrawWireRequest};
use App\Models\{Account, Cabinet\CProfile, Cabinet\CUser, CryptoAccountDetail, Operation};
use App\Services\{ComplianceService, FiatWithdrawWireService, OperationService, TwoFAService, WithdrawCryptoService};
use Carbon\Carbon;
use Illuminate\{Contracts\View\Factory,
    Http\Request,
    Support\Facades\Auth,
    Support\Facades\Session,
    Support\Str,
    View\View};

class FiatWithdrawController extends Controller
{
    /**
     * @param CryptoAccountDetail $cryptoAccountDetail
     * @param Request $request
     * @param WithdrawCryptoService $withdrawCryptoService
     * @return Factory|View
     */
    public function showWithdrawWire(Account $fiatAccount, Request $request, FiatWithdrawWireService $withdrawWireService)
    {
        //start create operation with session
        $currentId = Str::uuid()->toString();
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $currentId;
        $request->session()->put('operationIds', $operationIds);

        //get limits and commissions for cProfile
        $cUser = auth()->user();
        $cProfile = $cUser->cProfile;
        $commissions = $withdrawWireService->getCommissions($cProfile->rate_template_id, $fiatAccount->currency);
        $limits = $withdrawWireService->getLimits($cProfile);

        $receivedAmountForCurrentMonth = Operation::where('c_profile_id', $cProfile->id)
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->where('from_currency', $fiatAccount->currency)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');

        $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
        if ($availableMonthlyAmount < 0) {
            $availableMonthlyAmount = 0;
        }
        $account = $cProfile->accounts()->findOrFail($fiatAccount->id);
        /* @var Account $account*/
        $availableCurrentAmount = formatMoney($account->getAvailableBalance(), $account->currency);

        return view('cabinet.wallets.send_fiat_wire', compact(
            'cUser', 'fiatAccount', 'currentId', 'commissions',
            'limits', 'availableMonthlyAmount', 'availableCurrentAmount'
        ));
    }

    public function withdrawWireOperation(TwoFAService $twoFAService, WithdrawWireRequest $request, OperationService $operationService, ComplianceService $complianceService, $id)
    {
        $cUser = Auth::user();

        $cProfile = $cUser->cProfile;
        /* @var CProfile $cProfile */

        $fiatAccount = $cProfile->getAccountById($id, AccountType::TYPE_FIAT);
        /* @var  Account $fiatAccount */

        $validator = $fiatAccount->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($validator);
        }
        $operationIds = $request->session()->pull('operationIds', []);
        $wireType = $request->type;

        $bankAccountData = $request->only(['template_name', 'country', 'currency', 'type']);
        $bankAccountData['type'] = OperationOperationType::ACCOUNT_OPERATION_TYPES[$wireType];
        if ($request->bank_template == '0') {
            $toAccount = $operationService->createBankDetailAccount($bankAccountData, $cProfile->id);
            $operationService->createWireAccountDetail($request->only(['iban', 'swift', 'bank_name', 'bank_address', 'account_holder', 'account_number']), $toAccount);
        } else {
            $toAccount = $cProfile->accounts()->findOrFail($request->bank_template);
        }

        /* @var CUser $cUser*/
        if (in_array($request->operation_id, $operationIds) && (!$cUser->two_fa_type || $twoFAService->checkSession())) {
            $operation = $operationService->createOperation(
                $cProfile->id, OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET, $request->amount, $fiatAccount->currency, $toAccount->currency, $fiatAccount->id, $toAccount->id, OperationStatuses::PENDING,
                $request->bank_detail, null, $request->operation_id, $request->provider_account_id, $cUser->project->id);

            $operation->additional_data = json_encode([
                'payment_method' => $request->type,
            ]);

            $operation->save();

            if ($operation->isLimitsVerified()) {
                EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
            } else {
                $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
            }

            if ($operation) {
                Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
                Session::flash('operationCreated', t('withdraw_wire_operation_created_successfully'));
            } else {
                Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            }

            return redirect()->route('cabinet.wallets.index');
        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            return redirect()->route('cabinet.wallets.show', $id);
        }
    }

}
