<?php


namespace App\Http\Controllers\Cabinet;

use App\Facades\EmailFacade;
use App\Facades\ExchangeRatesBitstampFacade;
use App\Facades\KrakenFacade;
use App\Enums\{Commissions,
    CommissionType,
    Country,
    Currency,
    OperationOperationType,
    OperationStatuses,
    OperationSubStatuses,
    OperationType};
use App\Http\{Controllers\Controller, Requests\TransferRequest};
use App\Models\{Account, Commission, CryptoAccountDetail, Limit, Operation};
use App\Services\{CommissionsService, ComplianceService, EmailService, OperationService, PdfGeneratorService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Session, Str};


class TransferController extends Controller
{
    public function showWireTransfer($id, Request $request)
    {
        $cProfile = auth()->user()->cProfile;
        $cryptoAccountDetail = CryptoAccountDetail::where('id', $id)->first();
        $allAllowedCoins = Currency::getBitGoAllowedCurrencies($cProfile->cUser->project_id);
        $countries = \App\Models\Country::getCountries(false);


        $commissionsService = resolve(CommissionsService::class);
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO , $cryptoAccountDetail->coin,Commissions::TYPE_OUTGOING);

        $operationService = resolve(OperationService::class);
        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);

        $limits = $this->getLimits($cProfile);
        if ($limits) {
            $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableMonthlyAmount < 0) {
                $availableMonthlyAmount = 0;
            }
        }

        //start create operation with session
        $currentId = Str::uuid()->toString();
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $currentId;
        $request->session()->put('operationIds', $operationIds);

        $rate = ExchangeRatesBitstampFacade::rate();
        $blockChainFee = $commissions->blockchain_fee * OperationOperationType::OPERATION_BLOCKCHAIN_FEE_COUNT[OperationOperationType::TYPE_TOP_UP_SEPA];

        return view('cabinet.wallets.wire_transfer')->with([
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'allAllowedCoins' => $allAllowedCoins,
            'countries' => $countries,
            'limits' => $limits,
            'commissions' => $commissions,
            'availableMonthlyAmount' => $availableMonthlyAmount ?? 0,
            'currentId' => $currentId,
            'rate' => $rate,
            'blockChainFee' => $blockChainFee,
        ]);
    }


    public function createWireTransfer(TransferRequest $request, OperationService $operationService, PdfGeneratorService $pdfGeneratorService, ComplianceService $complianceService,$id)
    {
        $operationIds = $request->session()->pull('operationIds', []);

        if (in_array($request->operation_id, $operationIds)) {
            $cryptoAccountDetail = CryptoAccountDetail::findOrFail($id);
            $providerAccount = Account::getActiveAccountById($request->bank_detail);
            $provider_id = $providerAccount->provider->id;
            $account = $cryptoAccountDetail->account;

            $cProfile = auth()->user()->cProfile;
            $cprofileId = $cProfile->id;
            $operation = $operationService->createOperation(
                $cprofileId, $request->wire_type, $request->amount, $request->currency, $request->exchange_to,
                null, $account->id, OperationStatuses::PENDING,
                $provider_id, null , $request->operation_id, $providerAccount->id
            );

            if ($operation->isLimitsVerified()) {
                EmailFacade::sendInvoicePaymentSepaOrSwift(auth()->user(), $operation->operation_id, OperationOperationType::getName($operation->operation_type), $operation->amount, $operation->from_currency, $operation->id);
            } else {
                //$complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
                EmailFacade::sendVerificationRequestFromTheManager(auth()->user(), $operation);
            }
            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('operationCreated', t('top_up_wire_operation_created_successfully'));

            return $pdfGeneratorService->getPdfDepositFile($operation, $providerAccount->id);
        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            return redirect()->route('cabinet.wallets.show', $id);
        }

    }

    public static function getLimits($cProfile, $complianceLevel = null)
    {
        $rateTemplateId = $cProfile->rate_template_id;
        $complianceLevel = $complianceLevel ?? $cProfile->compliance_level;

        $limits = Limit::where('rate_template_id', $rateTemplateId)
            ->where('level', $complianceLevel)
            ->first();

        return $limits;
    }

    /**
     * @param Operation $operation
     * @return \Illuminate\Http\RedirectResponse
     * Changing the status of operation to declined from cabinet
     */
    public function declineOperation(Operation $operation)
    {

        if ($operation->transactions()->count()) {
            return back()->with([ 'warning' => t('operation_request_canceled_failed')]);
        }

        $operation->status = OperationStatuses::DECLINED;
        $operation->save();

        $messageType = 'success';
        $message = t('operation_request_canceled_successfully');

        return back()->with([ $messageType => $message]);
    }
}
