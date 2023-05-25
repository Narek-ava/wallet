<?php


namespace App\Http\Controllers\Cabinet;


use App\Enums\{ComplianceLevel, OperationOperationType, OperationStatuses};
use App\Facades\EmailFacade;
use App\Http\{Controllers\Controller, Requests\Cabinet\TopUpFromFiatRequest};
use App\Models\CryptoAccountDetail;
use App\Services\{OperationService, TwoFAService, WalletService};
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Auth, Facades\Session, Str};

class BuyCryptoFromFiatController extends Controller
{

    public function buyCryptoFromFiat(string $id, Request $request, WalletService $walletService)
    {
        $currentId = Str::uuid()->toString();
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $currentId;
        $request->session()->put('operationIds', $operationIds);

        $cUser = Auth::user();
        $cryptoAccountDetail = CryptoAccountDetail::where('id', $id)->first();
        $cProfile = $cUser->cProfile;
        $fiatWallets = $walletService->getFiatWallets($cProfile);
        $restrictOperations = $cProfile->compliance_level === ComplianceLevel::VERIFICATION_LEVEL_0;

        return view('cabinet.wallets.buy-crypto-from-fiat', compact(
            'currentId', 'fiatWallets', 'cryptoAccountDetail', 'restrictOperations', 'cUser'
        ));
    }

    public function createTopUpFromFiat($id, TwoFAService $twoFAService, TopUpFromFiatRequest $request, OperationService $operationService)
    {
        $operationIds = $request->session()->pull('operationIds', []);
        $cUser = Auth::user();
        if (in_array($request->operation_id, $operationIds) && (!$cUser->two_fa_type || $twoFAService->checkSession())) {
            $cProfile = $cUser->cProfile;
            $cryptoAccountDetail = $request->cryptoAccountDetail;
            $cryptoAccount = $cryptoAccountDetail->account;

            $fiatWallet = $request->fiatWallet;

            $cprofileId = $cProfile->id;
            $operation = $operationService->createOperation(
                $cprofileId, OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT, $request->amountFiat,
                $fiatWallet->currency, $cryptoAccountDetail->coin,
                $fiatWallet->id, $cryptoAccount->id, OperationStatuses::PENDING,
                null, null , $request->operation_id, null, $cUser->project->id,
            );

            if ($operation->isLimitsVerified()) {
                EmailFacade::sendNewTopUpCardOperationMessage($operation);
            } else {
                //$complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
                EmailFacade::sendVerificationRequestFromTheManager($cUser, $operation);
            }
            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('operationCreated', t('top_up_wire_operation_created_successfully'));

        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
        }
        return redirect()->route('cabinet.wallets.show', $id);

    }


}
