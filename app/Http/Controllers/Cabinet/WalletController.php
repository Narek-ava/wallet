<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\Currency;
use App\Enums\Providers;
use App\Facades\KrakenFacade;
use App\Http\Requests\Cabinet\AddWalletRequest;
use App\Http\Requests\FiatWalletRequest;
use App\Models\Account;
use App\Models\CryptoAccountDetail;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\BitGOAPIService;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\EmailVerificationService;
use App\Services\OperationService;
use App\Services\ProviderService;
use App\Services\WalletService;
use Illuminate\Http\Request;


class WalletController extends Controller
{
    public function wallets(EmailVerificationService $emailVerificationService, ComplianceService $complianceService, WalletService $walletService)
    {
        $cProfile = auth()->user()->cProfile;
        $cryptoAccountDetails = $cProfile->cryptoAccountDetail;
        $allowedCoinsForAccount = CryptoAccountDetail::getAllowedCoinsForAccount();
        $allowedFiatForAccount = $walletService->getAllowedFiatForNewWallets($cProfile);
        $steps = $cProfile->stepInfo();
        $isEmailVerificationSent = $emailVerificationService->isEmailVerificationSent($cProfile->id);
        $fiatWallets = $walletService->getFiatWallets($cProfile);

        $hasB2CProvider = Account::isPaymentProviderForWithdrawOperation($cProfile);
        $hasComplianceProvider = !empty($complianceService->getComplianceProviderAccount());

        return view('cabinet.wallets.index')->with([
            'cryptoAccountDetails' => $cryptoAccountDetails,
            'allowedCoinsForAccount' => $allowedCoinsForAccount,
            'cProfile' =>  $cProfile,
            'steps' =>  $steps,
            'isEmailVerificationSent' => $isEmailVerificationSent,
            'hasB2CProvider' => $hasB2CProvider,
            'hasComplianceProvider' => $hasComplianceProvider,
            'allowedFiatForAccount' => $allowedFiatForAccount,
            'fiatWallets' => $fiatWallets
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @param OperationService $operationService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(string $id, Request $request, OperationService $operationService)
    {
        $cProfile = auth()->user()->cProfile;
        $cryptoAccountDetail = $cProfile->cryptoAccountDetail()->where('crypto_account_details.id', $id)->first();
        if (!$cryptoAccountDetail) {
            abort(404);
        }
        $limits = TransferController::getLimits($cProfile);

        $operations = $operationService->getClientOperationsPaginationWithFilter($request, $cryptoAccountDetail->account_id);
        $balance = $cryptoAccountDetail->account->getAvailableBalance();
        $rateForUSD = KrakenFacade::getRateCryptoFiat($cryptoAccountDetail->coin, Currency::CURRENCY_USD, $balance);
        $rateForEUR = KrakenFacade::getRateCryptoFiat($cryptoAccountDetail->coin, Currency::CURRENCY_EUR, $balance);

        return view('cabinet.wallets.view')->with([
                'cryptoAccountDetail' => $cryptoAccountDetail,
                'operations' => $operations,
                'limits' => $limits,
                'rateForUSD' => $rateForUSD,
                'rateForEUR' => $rateForEUR,
                'operationFee' => $operationFee ?? null,
        ]);
    }

    public function showFiatWallet(string $id, Request $request, OperationService $operationService, WalletService $walletService)
    {
        $cProfile = getCProfile();
        $fiatWallet = $walletService->getFiatWallet($cProfile, $id);
        $limits = TransferController::getLimits($cProfile);

        $operations = $operationService->getClientOperationsPaginationWithFilter($request, $fiatWallet->id);

        return view('cabinet.wallets.fiat-wallet-view')->with([
            'fiatWallet' => $fiatWallet,
            'operations' => $operations,
            'limits' => $limits,
            'operationFee' => $operationFee ?? null,
        ]);
    }

    public function walletExchange($id)
    {
        $allowedCoinsForAccount = CryptoAccountDetail::getAllowedCoinsForAccount(); //????

        $cryptoAccountDetail = CryptoAccountDetail::where('id', $id)->first();
        $project = Project::getCurrentProject();
        $allAllowedCoins = Currency::getBitGoAllowedCurrencies($project->id);

        $cprofile = getCProfile();
        $cryptoAccountDetail = $cprofile->getCryptoAccountDetailById($id);
        return view('cabinet.wallets.exchange')->with([
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'allowedCoinsForAccount' => $allowedCoinsForAccount,
            'allAllowedCoins' => $allAllowedCoins,
        ]);
    }

    public function topUpCrypto($id, ProviderService $providerService)
    {
        $cprofile = getCProfile();
        $cryptoAccountDetail = $cprofile->getCryptoAccountDetailById($id);
        $project = Project::getCurrentProject();
        return view('cabinet.wallets.top_up_crypto')->with([
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'restrictOperations' => auth()->user()->cProfile->compliance_level === \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_0,
            'cardProviderExists' => $providerService->checkProjectProviderExistsByType($project->id ,Providers::PROVIDER_CARD),
            'paymentProviderExists' => $providerService->checkProjectProviderExistsByType($project->id ,Providers::PROVIDER_PAYMENT)
        ]);
    }

    public function addWallet(AddWalletRequest $request, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {
        $walletService->addNewWallet($bitGOAPIService, $request->cryptocurrency, auth()->user()->cProfile);
        return redirect()->route('cabinet.wallets.index');
    }

    public function addFiat(FiatWalletRequest $request, WalletService $walletService)
    {
        $cProfile = getCProfile();
        $walletService->createFiatWallet($cProfile, $request->fiat);
        session()->flash('success', t('fiat_wallet_added_successfully'));
        return redirect()->route('cabinet.wallets.index');
    }

    public function hideCurrencyCard(Request $request)
    {
        $cprofile = getCProfile();
        $cryptoAccountDetail = $cprofile->getCryptoAccountDetailById($request->crypto_id);
        $cryptoAccountDetail->update(['is_hidden' => CryptoAccountDetail::CURRENCY_HIDDEN]);

        return redirect()->route('cabinet.wallets.index')->with(['is_hidden' => $cryptoAccountDetail->is_hidden]);
    }
}
