<?php

namespace App\Http\Controllers\Backoffice;

use App\DataObjects\OperationTransactionData;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\OperationOperationType;
use App\Enums\WallesterCardBlockTypes;
use App\Enums\WallesterCardStatuses;
use App\Enums\WallesterCardTypes;
use App\Enums\WallesterOrderCardSettingKeys;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BaseTransactionRequest;
use App\Http\Requests\Backoffice\ChangeWallesterCardOrderAmountsRequest;
use App\Http\Requests\Backoffice\WallesterCardRequest;
use App\Models\BankAccountTemplate;
use App\Models\Cabinet\CProfile;
use App\Models\Commission;
use App\Models\Limit;
use App\Models\Operation;
use App\Models\Setting;
use App\Models\WallesterAccountDetail;
use App\Operations\OrderCardByCrypto;
use App\Services\ComplianceService;
use App\Services\ExchangeInterface;
use App\Services\OperationService;
use App\Services\SettingService;
use App\Services\Wallester\Api;
use App\Services\Wallester\WallesterPaymentService;
use Illuminate\Http\Request;
use App\Services\Wallester\Api as WallesterApi;

class WallesterController extends Controller
{
    /**
     * Display a listing of the resource
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(WallesterPaymentService $wallesterPaymentService)
    {
        $steps = $wallesterPaymentService->getSteps();
        return view('cabinet.cards.order', compact('steps'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param WallesterCardRequest $request
     * @param WallesterPaymentService $paymentService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(WallesterCardRequest $request, WallesterPaymentService $wallesterPaymentService)
    {
        $cProfile = CProfile::find($request->cProfileId);
        $wallesterPaymentService->createCardByManager($request->validated(), $cProfile);

        session()->flash('success', t('card_successfully_created', ['card' => WallesterCardTypes::getName($request->cardType)]));
        return redirect()->to(url()->previous() . '#cards')->with(['success' => 'Success']);
    }

    public function viewCardDetails(?string $id, Request $request, WallesterPaymentService $wallesterPaymentService, WallesterApi $wallesterApi)
    {
        $card = WallesterAccountDetail::findOrFail($id);
        $profile = $card->account->cProfile;

        if (!$card) {
            session()->flash('error', t('wallester_card_not_found'));
            return redirect()->back();
        }
        if (!$card->is_paid) {
            session()->flash('error', t('wallester_card_waiting_payment'));
            return redirect()->back();
        }

        try {
            $cardWallester = $wallesterApi->getCardByExternalId($card->id);
        } catch (\Exception $exception) {
            session()->flash('error', t('wallester_card_not_found'));
            return redirect()->back();
        }

        if (!$card->wallester_card_id) {
            $card->wallester_card_id = $cardWallester['card']['id'];
        }

        $card->status = WallesterCardStatuses::STATUSES_FROM_RESPONSE[$cardWallester['card']['status']];

        $card->save();

        $cUser = $profile->cUser;
        $data = $request->only(['from_record', 'records_count', 'from_date', 'to_date', 'merchant_name', 'type']);
        $cardTransactions = $wallesterPaymentService->getCardTransactions($card->wallester_card_id, $data);

        $blockUrl = '';
        if ($card->status == \App\Enums\WallesterCardStatuses::STATUS_BLOCKED) {
            $blockUrl = route('wallester.unblock.card.admin', ['id' => $card->id]);
        } else if ($card->status == \App\Enums\WallesterCardStatuses::STATUS_ACTIVE) {
            $blockUrl = route('wallester.block.card.admin', ['id' => $card->id]);
        }

        try {
            $limits = $wallesterApi->getCardLimits($card->wallester_card_id);
            $defaultLimits = $wallesterApi->getCardDefaultLimitsCached();

            $accountDataInWallester = $wallesterApi->getAccount($card->wallester_account_id);
            $cardTopUpDetails = $accountDataInWallester['account']['top_up_details'] ?? [];
            $bankTemplate = $card->bankAccountTemplate;
        } catch (\Throwable $exception) {
            session()->flash('error', t('card_limit_issue'));
            return redirect()->back();
        }

        $account = $card->account;
        $account->balance = $accountDataInWallester['account']['available_amount'];
        $account->save();

        $topUpDetailsToCopy = $wallesterPaymentService->getCardDetailsForCopy($card, $bankTemplate);


        return view('backoffice.cProfile.cards.show', compact('card', 'profile', 'cardTransactions', 'cUser', 'limits', 'defaultLimits', 'blockUrl', 'topUpDetailsToCopy', 'bankTemplate'));
    }


    /**
     * @param string $card_id
     * @param WallesterApi $wallesterApi
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function blockCardByAdmin(string $card_id, Api $wallesterApi)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($card_id);

        if (!$wallesterAccountDetail) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }

        try {
            $card = $wallesterApi->getCardByExternalId($wallesterAccountDetail->id);
            if (WallesterCardStatuses::STATUSES_FROM_RESPONSE[$card['card']['status']] !== WallesterCardStatuses::STATUS_ACTIVE) {
                session()->flash('error', 'card_blocking_status_error');
                return redirect()->back();
            }
            $wallesterApi->blockCard($wallesterAccountDetail->wallester_card_id, WallesterCardBlockTypes::BLOCKED_BY_CLIENT);
            $wallesterAccountDetail->status = WallesterCardStatuses::STATUS_BLOCKED;
            $wallesterAccountDetail->save();
            session()->flash('success', t('wallester_card_block_success'));
            return redirect()->back();
        } catch (\Throwable $exception) {
            session()->flash('error', t('wallester_card_was_not_blocked'));
            return redirect()->back();
        }
    }

    /**
     * @param string $id
     * @param WallesterApi $wallesterApi
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function unblockCardByAdmin(string $id, Api $wallesterApi)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($id);
        if (!$wallesterAccountDetail) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }
        try {
            $card = $wallesterApi->getCardByExternalId($wallesterAccountDetail->id);
            if (WallesterCardStatuses::STATUSES_FROM_RESPONSE[$card['card']['status']] !== WallesterCardStatuses::STATUS_BLOCKED) {
                session()->flash('error', t('card_unblocking_status_error'));
                return redirect()->back();
            }

            if (!in_array($card['card']['block_type'], [WallesterCardBlockTypes::BLOCKED_BY_CARDHOLDER, WallesterCardBlockTypes::BLOCKED_BY_CLIENT])) {
                session()->flash('error', t('card_unblocking_access_error'));
                return redirect()->back();
            }

            $wallesterApi->unblockCard($wallesterAccountDetail->wallester_card_id);
            $wallesterAccountDetail->status = WallesterCardStatuses::STATUS_ACTIVE;
            $wallesterAccountDetail->save();
            session()->flash('success', t('wallester_card_unblock_success'));
            return redirect()->back();
        } catch (\Throwable $exception) {
            session()->flash('error', t('wallester_card_was_not_unblocked'));
            return redirect()->back();
        }
    }

    /**
     * @param $id
     * @param $operationService
     * @param $exchangeService
     * @param $complianceService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showTransaction($id, OperationService $operationService, ComplianceService $complianceService, Request $request)
    {
        $operation = Operation::findOrFail($id);
        /* @var Operation $operation*/
        $allowedMaxAmount = $operation->calculateOperationMaxAmount();
        $cProfile = $operation->cProfile;
        $accounts = $cProfile->bankDetailAccounts;
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $nextComplianceLevels = $complianceService->getNextComplianceLevels($cProfile);
        $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);

        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        $commissions = $this->commissions($cProfile->rate_template_id, [
            'operationType' => $operation->operation_type,
            'currency' => $operation->from_currency
        ], Commissions::TYPE_INCOMING);

        if ($limits) {
            $passCompliance = $operation->isLimitsVerified();
            $availableMonthlyAmount = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableMonthlyAmount < 0) {
                $availableMonthlyAmount = 0;
            }
        }

        $paymentProviderAccount = $operation->getProviderAccount();
        $liquidityAccount = $operation->getLiquidityAccount();
        $credited = $operation->credited;
        $exchangeFee = $operation->getExchangeFeeAmount();

        $txTransactionLink = $operation->getCryptoExplorerUrl();

        $operationLogs = $operationService->searchOperationLogs([
            "operationId" => $operation->id,
            "logFrom" => $request->logFrom,
            "logTo" => $request->logTo,
        ]);

        $selectedExchangeProvider = $operation->getSelectedExchangeProvider();


        return view('backoffice.cards.wallester.transaction_show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'operation' => $operation,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'cProfile' => $cProfile,
            'nextComplianceLevels' => $nextComplianceLevels,
            'passCompliance' => $passCompliance ?? false,
            'availableMonthlyAmount' => $availableMonthlyAmount ?? 0,
            'limits' => $limits,
            'commissions' => $commissions,
            'paymentProviderAccount' => $paymentProviderAccount,
            'credited' => $credited,
            'exchangeFee' => $exchangeFee,
            'liquidityAccount' => $liquidityAccount,
            'link' => $txTransactionLink,
            'steps' => $operation->stepInfo(),
            'operationLogs' => $operationLogs,
            'logFrom' => $request->logFrom,
            'logTo' => $request->logTo,
            'api' => $selectedExchangeProvider->api ?? null,
            'isCardOrderOperation' => in_array($operation->operation_type, OperationOperationType::CARD_ORDER_OPERATIONS),
        ]);
    }

    protected function commissions($rateTemplateId, $request, $type = Commissions::TYPE_OUTGOING)
    {
        $operationType = $request['operationType'];
        $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$operationType] ?? null;
        $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$accountType];

        $commissions = Commission::where('rate_template_id', $rateTemplateId)
            ->where('type', $type)
            ->where('commission_type', $commissionType)
            ->where('currency', $request['currency'])
            ->where('is_active', Commissions::COMMISSION_ACTIVE)
            ->first();
        return $commissions;
    }

    public function makeTransaction(Operation $operation, BaseTransactionRequest $request)
    {
        try {
            $currentStep = $operation->step;
            $orderCardByCrypto = new OrderCardByCrypto($operation, new OperationTransactionData($request->all()));
            $orderCardByCrypto->execute();

            return redirect()->route('backoffice.buy.fiat.from.crypto.transaction', $operation->id)->with(['success' => t('transaction_added_successfully')]);
        } catch (\Exception $exception) {
            $operation->step = $currentStep;
        }
        $operation->save();
        return redirect()->route('backoffice.buy.fiat.from.crypto.transaction', $operation->id)->with(['warning' => $exception->getMessage()]);

    }
}
