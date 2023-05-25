<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Requests\TopUpCardRequest;
use App\Models\Cabinet\CUser;
use App\Enums\{Commissions, CommissionType, OperationOperationType, Providers};
use App\Facades\ExchangeRatesBitstampFacade;
use App\Models\CryptoAccountDetail;
use App\Http\Controllers\Controller;
use App\Services\{CardProviders\TrustPaymentService,
    CommissionsService,
    OperationService,
    ProjectService,
    ProviderService,
    TopUpCardService};
use http\Client\Curl\User;
use Illuminate\{Http\Request, Support\Str};

class CardTransferController extends Controller
{

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCardTransfer($id, Request $request)
    {
        $cProfile = auth()->user()->cProfile;
        $cryptoAccountDetail = CryptoAccountDetail::find($id);
        //change commissions
        $commissionsService = resolve(CommissionsService::class);
        $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $cryptoAccountDetail->coin, Commissions::TYPE_INCOMING);

        $operationService = resolve(OperationService::class);
        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);

        $limits = TransferController::getLimits($cProfile);

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

        return view('cabinet.wallets.top_up_by_card')->with([
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'limits' => $limits,
            'commissions' => $commissions,
            'availableMonthlyAmount' => $availableMonthlyAmount ?? 0,
            'currentId' => $currentId,
            'rate' => $rate,
            'blockChainFee' => $blockChainFee,
        ]);
    }

    /**
     * @param Request $request
     * @param string $id
     */
    public function createCardTransfer(TopUpCardRequest $request, string $id, ProviderService $providerService)
    {
        $cProfile = getCProfile();
        $project = $cProfile->cUser->project;

        $userId = $cProfile->cUser->id;
        $cryptoAccountDetail = $cProfile->cryptoAccountDetail()->findOrFail($id);
        $topUpCardService = resolve(TopUpCardService::class);
        /* @var TopUpCardService $topUpCardService*/

        //account limit validation
        if (!$topUpCardService->validateCardOperationLimits($cProfile, $request->currency, $request->amount)) {
            return redirect()->back()->with('error', t('ui_card_transfer_limit_fail_validation'));
        }

        $profileCommission = $cProfile->operationCommission(CommissionType::TYPE_CARD, Commissions::TYPE_INCOMING, $request->currency);
        if ($request->amount < $profileCommission->min_amount) {
            return redirect()->back()->withErrors(['amount'=> t('ui_card_transfer_min_amount_fail_validation', ['currency' => $request->currency, 'minAmount' => $profileCommission->min_amount])]);
        }

        // @todo get payment_provider_id and payment_provider_account for creating operation
        $operation = $topUpCardService->createTopUpCardOperation($cProfile->id, $request->amount, $request->currency, $request->exchange_to, null, $cryptoAccountDetail->account_id);

        if (!$operation) {
            return redirect()->back()->with('error', t('ui_card_transfer_limit_fail'));
        }
        $purpose = t('operation_type_top_up_card'). $operation->amount . ' ' .  $operation->from_currency;
        $trustPaymentService = new TrustPaymentService();
        $trustPaymentService->setTransactionDetails($operation->id, ' ', $operation->amount, $operation->from_currency, $purpose);
        $formData = $trustPaymentService->getPaymentFormData();
        $projectService = resolve(ProjectService::class);
        /* @var ProjectService $projectService */

        $apiSettings = $projectService->getCardApiSettings($project);

        $configKey = 'cardproviders.' . $apiSettings['api'] . '.' . $apiSettings['api_account'] . '.sitereference';
        $siteReference = config($configKey);

        return view('cabinet.wallets.payment-forms.trustpayment', compact('formData', 'operation', 'siteReference', 'userId'));

    }




 }
