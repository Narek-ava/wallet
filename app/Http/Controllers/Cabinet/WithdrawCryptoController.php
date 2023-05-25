<?php

namespace App\Http\Controllers\Cabinet;

use App\DataObjects\OperationTransactionData;
use App\Enums\{AccountType,
    AnalyticSystems,
    Commissions,
    CommissionType,
    Currency,
    OperationStatuses,
    OperationSubStatuses,
    Providers,
    TransactionType,
    TwoFAType};
use App\Facades\EmailFacade;
use App\Http\{Controllers\Controller, Requests\WithdrawCryptoRequest};
use App\Models\{Account, Cabinet\CProfile, Cabinet\CUser, CryptoAccountDetail, Limit, Project};
use App\Operations\WithdrawCrypto;
use App\Rules\BCHAddressFormat;
use App\Services\{AccountService,
    ChainalysisService,
    CommissionsService,
    ComplianceService,
    CryptoAccountService,
    ProviderService,
    SumSubService,
    TwoFAService,
    WithdrawCryptoService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class WithdrawCryptoController extends Controller
{
    /**
     * @param string $id
     * @param CommissionsService $commissionsService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendCrypto(string $id, CommissionsService $commissionsService, ProviderService $providerService)
    {
        $cUser = auth()->user();
        $cProfile = $cUser->cProfile;

        /* @var CProfile $cProfile */
        $cryptoAccountDetail = $cProfile->cryptoAccountDetail()->findOrFail($id);
        $allowedCoinsForAccount = CryptoAccountDetail::getAllowedCoinsForAccount();
        $accountIds = $cProfile->accounts()
            ->where('is_external', AccountType::ACCOUNT_EXTERNAL)
            ->where('currency',$cryptoAccountDetail->coin)
            ->pluck('id');
        $accountCoins = CryptoAccountDetail::query()->whereIn('account_id', $accountIds)->get();
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $cryptoAccountDetail->coin, Commissions::TYPE_OUTGOING);
        $limits = $commissionsService->limits($cProfile->rate_template_id, $cProfile->compliance_level);
        $blockChainFee = $commissions->blockchain_fee ?? null;
        $availableMonthlyAmount = $this->availableMonthlyAmount($cProfile, $limits);
        /* @var CryptoAccountDetail $cryptoAccountDetail*/
        $account = $cryptoAccountDetail->account;
        $availableCurrentAmount = formatMoney($account->getAvailableBalance(), $account->currency);
        $hasB2CProvider = Account::isPaymentProviderForWithdrawOperation($cProfile);

        $project = Project::getCurrentProject();
        $paymentProviderExists = $providerService->checkProjectProviderExistsByType($project->id, Providers::PROVIDER_PAYMENT);
        $hasB2CProvider = Account::isPaymentProviderForWithdrawOperation($cProfile);

        return view('cabinet.wallets.send_crypto', compact(
            'cryptoAccountDetail', 'allowedCoinsForAccount', 'accountCoins', 'commissions', 'limits',
            'availableMonthlyAmount', 'availableCurrentAmount', 'blockChainFee', 'cUser', 'hasB2CProvider','paymentProviderExists'
        ));
    }

    /**
     * @param WithdrawCryptoRequest $request
     * @param WithdrawCryptoService $sendCryptoService
     * @param AccountService $accountService
     * @param CryptoAccountService $cryptoAccountService
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createSendCrypto(WithdrawCryptoRequest $request,
                                     TwoFAService $twoFAService,
                                     $id)
    {
        logger()->error('createSendCrypto', $request->all());
        try {
            $code = \C\twoFACode('2fa-confirm-code');
            $cUser = \C\c_user();

            if ($cUser->two_fa_type && !$twoFAService->verify($code, $cUser)) {
                if ($cUser->two_fa_type === TwoFAType::EMAIL) {
                    $twoFAService->generateIfNeeded($cUser);
                }
                session()->flash('open2FAModal', true);
                session()->put('request', $request->all());
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function withdrawalPost(Request $request, TwoFAService $twoFAService, WithdrawCryptoService $sendCryptoService, AccountService $accountService, ComplianceService $complianceService)
    {
        logger()->error('withdrawalPost', $request->all());

        $cUser = auth()->user();
        /* @var CUser $cUser */

        $withdrawalRequest = $request->all();

        $fromCryptoAccount = $cUser->cProfile->cryptoAccountDetail()->findOrFail($withdrawalRequest['crypto_account_detail_id']);
        /* @var CryptoAccountDetail $fromCryptoAccount*/
        $fromAccount = $fromCryptoAccount->account;
        $validator = $fromAccount->amountValidator($withdrawalRequest['amount']);
        if ($validator && $validator->fails()) {
            logger()->error('withdrawalPostValidationFail', $withdrawalRequest);
            return redirect()->back()->withInput($withdrawalRequest)->withErrors($validator);
        }
        if ($cUser->two_fa_type) {
            if (!$twoFAService->checkSession()) {
                logger()->error('withdrawalPost2FA', $withdrawalRequest);
                return redirect()->back()->withInput($withdrawalRequest)->with('two_fa_error', t('error_2fa_wrong_code'));
            }
            $twoFAService->removeSession();
        }

        unset($withdrawalRequest['_token']);

        if (!empty($withdrawalRequest['wallet_address'])) {

            $validator = Validator::make(['wallet_address' => $withdrawalRequest['wallet_address']], ['wallet_address' => new BCHAddressFormat($withdrawalRequest['wallet_address'], false)]);
            if ($validator->fails()) {
                return redirect()->back()->withInput($withdrawalRequest)->withErrors($validator);
            }

            $toAccount = $accountService->addWalletToClient($withdrawalRequest['wallet_address'], $fromCryptoAccount->coin, $cUser->cProfile, $withdrawalRequest['allowSaveDraft'] ?? false, $withdrawalRequest['amount']);

            if (!$toAccount) {
                logger()->error('WithdrawNoAccount', $withdrawalRequest);
                return redirect()->back()->withInput($withdrawalRequest)->withErrors(['to_wallet' => t('send_crypto_wallet_fail')]);
            }
            if (!$toAccount->cryptoAccountDetail->isAllowedRisk()) {
                logger()->error('withdrawalPostRisk', $withdrawalRequest);
                return redirect()->back()->withInput($withdrawalRequest)->withErrors(['to_wallet' => t('high_score_failure')]);
            }
        } elseif (!empty($withdrawalRequest['to_wallet'])) {
            /* @var CryptoAccountDetail $toCryptoAccount*/
            $toCryptoAccount = CryptoAccountDetail::query()->where('id', $withdrawalRequest['to_wallet'])->first();
            $toAccount = $accountService->addWalletToClient($toCryptoAccount->address, $fromCryptoAccount->coin, $cUser->cProfile, $withdrawalRequest['allowSaveDraft'] ?? false, $withdrawalRequest['amount']);
        } else {
            logger()->error('withdrawalPostRedirectElse', $withdrawalRequest);
            return redirect()->back()->withInput($withdrawalRequest)->withErrors(['to_wallet' => t('send_crypto_wallet_fail')]);
        }

        $operation = $sendCryptoService->createOperation($cUser->cProfile, $withdrawalRequest, $fromCryptoAccount, $toAccount->cryptoAccountDetail, $fromAccount, $toAccount);
        $operation->refresh();

        if ($operation->isLimitsVerified()) {
            $operationData = new OperationTransactionData([
                'date' => date('Y-m-d'),
                'transaction_type' => TransactionType::CRYPTO_TRX,
                'from_type' => Providers::CLIENT,
                'to_type' => Providers::CLIENT,
                'from_currency' => $operation->from_currency,
                'from_account' => $operation->from_account,
                'to_account' => $operation->to_account,
                'currency_amount' => $operation->amount
            ]);

            try {
                if (config('cratos.automatic_withdrawal') && $toAccount->cryptoAccountDetail->isAllowedRisk()) {

                    $withdrawCrypto = new WithdrawCrypto($operation, $operationData);
                    $withdrawCrypto->execute();
                } else {
                    EmailFacade::sendNotificationForManager($operation->cProfile->cUser, $operation->operation_id);
                }
            } catch (\Exception $exception) {
                $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                $operation->error_message = $exception->getMessage();
                $operation->save();
                logger()->error('WithdrawByCryptoErrorCabinet.', [
                    'operationId' => $operation->id,
                    'message' =>  $exception->getMessage()
                ]);
                session()->flash('error', t('withdraw_crypto_error_message'));
                return redirect()->route('cabinet.wallets.index');
            }
            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('walletAddress', $toAccount->cryptoAccountDetail->address);
        } else {
            $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cUser->cProfile);
            EmailFacade::sendNotificationForManager($cUser, $operation->operation_id);
            //EmailFacade::sendVerificationRequestFromTheManager(auth()->user(), $operation);
            //Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('walletAddress', $toAccount->cryptoAccountDetail->address);
        }

        return redirect()->route('cabinet.wallets.show', $withdrawalRequest['crypto_account_detail_id']);
    }


    /**
     * @param CProfile $cProfile
     * @param Limit $limits
     * @return float
     */
    public function availableMonthlyAmount(CProfile $cProfile, Limit $limits): float
    {
        $receivedAmountForCurrentMonth = $cProfile->operations()
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');

        $availableMonthlyAmount = $limits->monthly_amount_max - round($receivedAmountForCurrentMonth, 2);
        return $availableMonthlyAmount > 0 ? $availableMonthlyAmount : 0;
    }

    public function sendWire($id)
    {
        $allowedCoinsForAccount = CryptoAccountDetail::getAllowedCoinsForAccount(); //????

        $project = Project::getCurrentProject();
        $allAllowedCoins = Currency::getBitGoAllowedCurrencies($project->id);

        $cryptoAccountDetail = CryptoAccountDetail::where('id', $id)->first();
        return view('cabinet.wallets.send_wire')->with([
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'allowedCoinsForAccount' => $allowedCoinsForAccount,
            'allAllowedCoins' => $allAllowedCoins,
        ]);
    }


}
