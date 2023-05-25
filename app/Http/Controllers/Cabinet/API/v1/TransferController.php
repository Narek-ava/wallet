<?php


namespace App\Http\Controllers\Cabinet\API\v1;


use App\Enums\{AccountStatuses,
    AccountType,
    Commissions,
    CommissionType,
    Currency,
    Exchange,
    OperationOperationType,
    OperationStatuses,
    OperationType,
    Providers};
use App\Facades\ExchangeRatesBitstampFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Models\{Account,
    AccountCountry,
    Cabinet\CProfile,
    Commission,
    Limit,
    Operation,
    PaymentProvider,
    Project,
    RateTemplate,
    Transaction};
use App\Services\{CommissionsService, OperationService, ProviderService};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\XSession;


class TransferController extends Controller
{
    /**
     * @param Request $request
     * @param ProviderService $providerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function providersByCountry(Request $request, ProviderService $providerService)
    {
        $cProfile = getCProfile();
        /* @var CProfile $cProfile */

        $isTypeSwift = in_array($request->accountType, OperationOperationType::SWIFT_TYPES);
        $accountType = $isTypeSwift ? AccountType::TYPE_WIRE_SWIFT : AccountType::TYPE_WIRE_SEPA;
        $providers = $providerService->getFilteredPaymentProviders(
            $accountType,
            $request->country,
            $request->currency,
            $request->accountType,
            $cProfile->account_type,
            $request->get('fiatType', AccountType::PAYMENT_PROVIDER_FIAT_TYPE_DEFAULT)
        );

        if (isset($request->validateMinAmount)) {
            $commission = $cProfile->operationCommission($request->accountType,Commissions::TYPE_INCOMING,$request->currency );
            if($commission) {
                $isAmountValid = $commission->min_amount <= $request->amount;
                $invalidAmountMessage = t('invalid_amount_message_text', [
                    'minAmount' => $commission->min_amount,
                    'currency' => $request->currency,
                ]);
            }
        }

        return response()->json([
            'providers' => $providers,
            'accountExist' => !empty($providers),
            'isAmountValid' => $isAmountValid ?? null,
            'invalidAmountMessage' => $invalidAmountMessage ?? null,
            'isTypeSwift' => $isTypeSwift
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommissions(Request $request)
    {
        $rateTemplateId = CProfile::where('id', $request->c_profile_id)->pluck('rate_template_id');
        $commission = Commission::where('rate_template_id', $rateTemplateId)->where('type', Commissions::TYPE_OUTGOING)->get();
        return response()->json([
            'commission' => $commission,
        ]);
    }


    /**
     * @param Request $request
     * @param CommissionsService $commissionsService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLimits(Request $request, CommissionsService $commissionsService, OperationService $operationService)
    {
        $cProfile = Auth::user()->cProfile;
        /* @var CProfile $cProfile*/

        //get transaction in some period of time
        $transactionsPerDay = null;
        $transactionsPerMonth = null;

        // @todo change query with relations and auth user id
        $operationIds = $cProfile->operations()->pluck('id');
        if ($operationIds) {
            $transactions = Transaction::whereIn('operation_id', $operationIds);
            $dailyTransactions = $transactions->whereDate('creation_date', Carbon::today())->get()->pluck('id');
            $monthlyTransactions = $transactions->whereMonth('creation_date', '=', Carbon::now()->month)->pluck('id');

            if ($dailyTransactions || $monthlyTransactions) {
                $transactionsPerDay = count($dailyTransactions);
                $transactionsPerMonth = count($monthlyTransactions);
            }
            //available transactions for month
            $receivedAmountForCurrentMonth = Operation::where('c_profile_id', $cProfile->id)
                ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount_in_euro');
        } else {
            $transactionsPerDay = 0;
            $transactionsPerMonth = 0;
            $receivedAmountForCurrentMonth = 0;
        }

        //get limits of transaction   //TODO check this
        if($request->operationType){
            $operationType = $request->operationType;
            $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$operationType] ?? null;
            $commissionType = CommissionType::ACCOUNT_TYPES_MAP[$accountType];
            $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->fromCurrency, Commissions::TYPE_INCOMING);

            if ($request->toCurrency && $request->wireType == OperationType::FIAT_TOP_UP_BY_WIRE) {
                $commissionType = CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE;
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE, $request->toCurrency);
                $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->fromCurrency, Commissions::TYPE_INCOMING);
                $exchangeCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_EXCHANGE, $request->toCurrency, Commissions::TYPE_INCOMING);
            } elseif ($request->toCurrency && $request->wireType == OperationType::WITHDRAW_WIRE_FIAT) {
                $commissionType = CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE;
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE, $request->toCurrency);
                $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->fromCurrency, Commissions::TYPE_OUTGOING);
            } elseif ($request->toCurrency && $request->wireType == OperationType::TOP_UP_WIRE) {
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $request->toCurrency, Commissions::TYPE_OUTGOING);
                $liquidityProviderFee = $operationService->calculateFeeWithLiqProviderCommission($request->toCurrency, $request->fromCurrency, $request->amount, $request->wireType, $commissions);
            } elseif ($request->fromCurrency && $request->wireType == OperationType::WITHDRAW_WIRE) {
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->fromCurrency, Commissions::TYPE_OUTGOING);
                $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->toCurrency, Commissions::TYPE_OUTGOING);
                $exchangeCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_EXCHANGE, $request->toCurrency, Commissions::TYPE_OUTGOING);
                $liquidityProviderFee = $operationService->calculateFeeWithLiqProviderCommission($request->fromCurrency, $request->toCurrency, $request->amount, $request->wireType);
            } elseif ($request->toCurrency && $request->wireType == OperationType::TOP_UP_CARD) {
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $request->toCurrency, Commissions::TYPE_OUTGOING);
                $commissions = $commissionsService->commissions($cProfile->rate_template_id, $commissionType, $request->fromCurrency, Commissions::TYPE_INCOMING);
                $exchangeCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_EXCHANGE, $request->toCurrency, Commissions::TYPE_INCOMING);
            } elseif ($request->toCurrency && $request->wireType == OperationType::TOP_UP_FROM_FIAT) {
                $toAccountCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $request->toCurrency);
                $commissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET, $request->fromCurrency, Commissions::TYPE_OUTGOING);
                $exchangeCommissions = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_EXCHANGE, $request->toCurrency, Commissions::TYPE_INCOMING);
            }
        }
        $limits = $commissionsService->limits($cProfile->rate_template_id, $cProfile->compliance_level);

        if (!$limits) {
            return response()->json([
                'message' => 'failed'
            ]);
        } else {
            if ($request->fromCurrency && $request->wireType == OperationType::WITHDRAW_WIRE &&
                in_array($request->fromCurrency, Currency::getList())){
                $getOutgoingCommission = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $request->fromCurrency, Commissions::TYPE_OUTGOING);
                $blockChainFee = $getOutgoingCommission->blockchain_fee;
                $blockChainFeeCurrency = $getOutgoingCommission->currency;
            }elseif(isset($toAccountCommissions)){
                $blockChainFee = $toAccountCommissions->blockchain_fee ?? null;
                $blockChainFeeCurrency = $toAccountCommissions->currency;
            }
            $availableAmountForMonth = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            $availableAmountForMonth = $availableAmountForMonth > 0 ? $availableAmountForMonth : 0;
            $rate = ExchangeRatesBitstampFacade::rate();

            $project = Project::getCurrentProject();
            $projectId = $project->id;
            $cardProviderAccount = Account::getProviderAccountsQuery($request->fromCurrency, Providers::PROVIDER_CARD)
                ->whereHas('provider', function ($q) use($projectId) {
                    return $q->queryByProject($projectId);
                })
                ->first();

            return response()->json([
                'limits' => $limits,
                'transactionsPerDay' => eur_format($transactionsPerDay),
                'transactionsPerMonth' => eur_format($transactionsPerMonth),
                'availableAmountForMonth' => eur_format($availableAmountForMonth),
                'transactionLimit' => eur_format($limits->transaction_amount_max),
                'cProfile' => $cProfile,
                'toAccountCommissions' => $toAccountCommissions ?? null,
                'commissions' => $commissions ?? null,
                'rate' => $rate,
                'blockChainFee' => number_format($blockChainFee ?? null, 8, '.', ''),
                'blockChainFeeCurrency' => $blockChainFeeCurrency ?? null,
                'exchangeCommissions' => $exchangeCommissions ?? null,
                'isCardProviderStatusSuspended' => !isset($cardProviderAccount),
                'liquidityProviderFee' => $liquidityProviderFee ?? 0
            ]);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProvider(Request $request)
    {
        $provider = PaymentProvider::where('id', $request->payment_provider_id)->with(['accounts' => function ($query) use ($request) {
            $query->where('status', \App\Enums\PaymentProvider::STATUS_ACTIVE)
                ->where('currency', $request->currency);
        }])->with('accounts.wire')->first();

        return response()->json([
            'provider' => $provider
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviderAccount(Request $request)
    {
        $account = Account::query()->with('wire')->findOrFail($request->provider_account_id);
        return response()->json([
            'account' => $account,
        ]);
    }


    public function getWithdrawFee(Request $request)
    {

        $commissionPercent = $request->percent_commission;
        $commissionFixed = $request->fixed_commission;
        $commissionMin = $request->min_commission;
        $commissionMax = $request->max_commission;

        $result = $request->amount * $commissionPercent / 100 + ($commissionFixed ?? 0);

        if ($commissionMax && $result >= $commissionMax) {
            return response()->json([
                'result' => $commissionMax
            ]);
        } elseif ($commissionMin && $result <= $commissionMin) {
            return response()->json([
                'result' => $commissionMin
            ]);
        }

        return response()->json([
            'result' => $result
        ]);

    }

    public function getBankTemplates(Request $request)
    {
        $wireType = $request->accountType;
        $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$wireType];
        $cProfile = CProfile::findOrFail($request->c_profile_id);
        $cProfileAccounts = $cProfile->accounts()
            ->where([
                'status' => AccountStatuses::STATUS_ACTIVE,
                'currency' => $request->currency,
                'account_type' => $accountType
            ])
            ->get();

        return response()->json([
            'accounts' => $cProfileAccounts
        ]);

    }

    public function getBankTemplate(Request $request)
    {
        $account = Account::findOrFail($request->account_id);
        $wireAccountDetail = $account->wire;
        $country = \App\Models\Country::getCountryNameByCode($account->country);

        return response()->json([
            'wireAccountDetail' => $wireAccountDetail,
            'account' => $account,
            'country' => $country
        ]);
    }

    public function getAvailableCountries(Request $request)
    {
        $accountType = OperationOperationType::ACCOUNT_OPERATION_TYPES[$request->accountType];
        $availableCountriesTranslated = [];
        $accounts = Account::where('currency', $request->currency)->where('account_type', $accountType);
        $accountCountries = $accounts->pluck('country')->toArray();
        $accountIds = $accounts->pluck('id');
        $countries = AccountCountry::whereIn('account_id', $accountIds)->pluck('country')->toArray();

        $availableCountries = array_unique(array_merge($accountCountries, $countries));
        foreach (\App\Models\Country::getCountries(false) as $key => $country) {
            if (in_array($key, $availableCountries)) {
                $availableCountriesTranslated[$key] = $country;
            }
        }

        return response()->json([
            'availableCountries' => $availableCountriesTranslated,
            'currencies' => array_values(Currency::FIAT_CURRENCY_NAMES)
        ]);
    }


    public function getRateCryptoFiat(Request $request)
    {
        $type = Exchange::EXCHANGE_TYPE_SELL;
        return KrakenFacade::getRateCryptoFiat($request->from, $request->to, $request->amount);
    }

    public function getRateMaxPaymentAmount(Request $request)
    {
        return ExchangeRatesBitstampFacade::rate($request->amount, $request->from, Currency::CURRENCY_EUR);
    }

    public function getBlockChainFee(string $currency, CommissionsService $commissionsService)
    {
        $rateTemplateId = Auth::user()->cProfile->rate_template_id;
        /* @var  RateTemplate $rateTemplate */
        $commissions = $commissionsService->commissions($rateTemplateId, CommissionType::TYPE_CRYPTO, $currency, Commissions::TYPE_OUTGOING);
        $blockchainFee = $commissions->blockchain_fee ?? 0;
        return response()->json(['blockchainFee' => $blockchainFee]);
    }

    public function getcProfileLimits($cProfile)
    {
        $rateTemplateId = $cProfile->rate_template_id;
        $complianceLevel = $cProfile->compliance_level;

        $limits = Limit::where('rate_template_id', $rateTemplateId)
            ->where('level', $complianceLevel)
            ->first();

        return $limits;
    }
    public function declineOperationData(Request $request)
    {
       $operation = Operation::findOrFail($request->operation_id);
       $type = OperationOperationType::getName($operation->operation_type) ?? '-';

       return response()->json([
           'operation' => $operation,
           'type' => $type
       ]);
    }
}
