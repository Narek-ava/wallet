<?php

namespace App\Http\Controllers\Cabinet;

use App\DataObjects\Payments\Wallester\WallesterLimits;
use App\DataObjects\Payments\Wallester\WallesterSecure;
use App\DataObjects\Payments\Wallester\WallesterSecurity;
use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\Providers;
use App\Enums\TwoFAType;
use App\Enums\WallesterCardBlockTypes;
use App\Enums\WallesterCardOrderPaymentMethods;
use App\Enums\WallesterCardStatuses;
use App\Enums\WallesterCardTypes;
use App\Facades\EmailFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCardLimitsRequest;
use App\Http\Requests\WallesterCardDetailsRequest;
use App\Http\Requests\WallesterCardSecureUpdate;
use App\Http\Requests\WallesterConfirmCardDeliveryRequest;
use App\Http\Requests\WallesterDeliveryRequest;
use App\Http\Requests\WallesterOrderCryptoPaymentRequest;
use App\Http\Requests\WallesterOrderWirePaymentRequest;
use App\Http\Requests\WallesterPaymentMethodRequest;
use App\Models\Account;
use App\Models\BankAccountTemplate;
use App\Models\CryptoAccountDetail;
use App\Models\Project;
use App\Models\WallesterAccountDetail;
use App\Services\CardService;
use App\Services\CommissionsService;
use App\Services\CountryService;
use App\Services\ExchangeInterface;
use App\Services\ExchangeRatesBitstampService;
use App\Services\KrakenService;
use App\Services\OperationService;
use App\Services\ProviderService;
use App\Services\PdfGeneratorService;
use App\Services\SettingService;
use App\Services\TwoFAService;
use App\Services\Wallester\Api;
use App\Services\Wallester\WallesterPaymentService;
use DateTime;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;
use function C\c_user;

class WallesterController extends Controller
{

    /**
     * SWAGGER COMMENTS
     *
    /**
     * @OA\Get(
     *     path="/api/wallester/card/prices",
     *     summary="Get Wallester card prices",
     *     description="This API call is used to get Wallester card prices",
     *     tags={"Wallester Card Prices"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wallester Card Prices",
     *         @OA\JsonContent(
     *     @QA\Property(
     *           property="prices",
     *           description="Wallester Card Prices",
     *           type="object"
     *         @OA\Property(
     *                     property="plastic",
     *                     description="Plastic card price",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="virtual",
     *                     description="Virtual card price",
     *                     type="number"
     *                 ),
     *          ),
     *             @OA\Examples(example="result", value={
     *                "prices":{
     *                  "plastic": 5000,
     *                  "virtual": 2000,
     *                  }
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Wallester card prices  not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="wallester_card_prices_error",
     *                              type="string",
     *                              description="Wallester card prices not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"wallester_card_prices_error": "Wallester Card prices not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     * Display a listing of the resource
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ProviderService $providerService)
    {
        if (!config('cratos.wallester.enabled')) {
            abort(404);
        }
        $project = Project::getCurrentProject();
        $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING);
        if (!$cardIssuingProvider) {
            abort(404);
        }
        $cProfile = getCProfile();
        $cards = $cProfile->wallesterAccountDetail;
        return view('cabinet.cards.index', compact('cards'));
    }

    /**
     * @return Application|Factory|View
     */
    public function create(WallesterPaymentService $wallesterPaymentService)
    {
        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $project = Project::getCurrentProject();
        $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);
        $amountInEuroPlastic = $cardIssuingProvider->plastic_card_amount;
        $amountInEuroVirtual = $cardIssuingProvider->virtual_card_amount;


        $steps = $wallesterPaymentService->getSteps();

        $orderVirtualUrl = route('show.order', ['type' => WallesterCardTypes::CARD_TYPES_LOWER[WallesterCardTypes::TYPE_VIRTUAL]]);
        $orderPlasticUrl = route('show.order', ['type' => WallesterCardTypes::CARD_TYPES_LOWER[WallesterCardTypes::TYPE_PLASTIC]]);

        return view('cabinet.cards.order', compact('steps', 'orderPlasticUrl', 'orderVirtualUrl', 'amountInEuroPlastic', 'amountInEuroVirtual'));
    }

    public function orderStepTwo(string $type, WallesterPaymentService $wallesterPaymentService, Api $wallesterApi)
    {
        $cardType = array_search($type, WallesterCardTypes::CARD_TYPES_LOWER);
        if (!$cardType) {
            return redirect()->back();
        }

        $cProfile = getCProfile();

        $currentOrderData = $wallesterPaymentService->getDataFromSession(WallesterCardTypes::getName($cardType) . '_' . $cProfile->id);
        $limits = $wallesterApi->getCardDefaultLimits();
        $steps = $wallesterPaymentService->getSteps(2);
        $cardType = array_search($type, WallesterCardTypes::CARD_TYPES_LOWER);

        $prevPageUrl = route('wallester-cards.create');
        return view('cabinet.cards.order-limits', compact('steps', 'limits', 'cardType', 'prevPageUrl', 'currentOrderData'));
    }

    public function saveLimits(Request $request, WallesterPaymentService $wallesterPaymentService)
    {
        $cProfile = getCProfile();
        $currentOrderData = $wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id);

        $deliveryData = $currentOrderData['delivery'] ?? [];
        $currentOrderData = array_merge($deliveryData, $request->all());

        $wallesterPaymentService->putDataIntoSession(WallesterCardTypes::getName($request->type) . '_' . $cProfile->id, $currentOrderData);

        return response()->json([
            'success' => true
        ]);
    }

    public function saveDeliveryData(Request $request, WallesterPaymentService $wallesterPaymentService, CountryService $countryService)
    {
        $cProfile = getCProfile();
        $currentOrderData = $wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id);
        $currentOrderData['delivery'] = $request->all();
        $currentOrderData['delivery']['country'] = $request->country_code;

        $wallesterPaymentService->putDataIntoSession(WallesterCardTypes::getName($request->type) . '_' . $cProfile->id, $currentOrderData);

        return response()->json([
            'success' => true,
            'orderData' => $currentOrderData
        ]);
    }


    public function orderStepThree(WallesterCardDetailsRequest $request, WallesterPaymentService $wallesterPaymentService, SettingService $settingService)
    {
        $cProfile = getCProfile();
        $currentOrderData = $wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id);
        if (isset($currentOrderData['delivery'])) {
            $delivery = ['delivery' => $currentOrderData['delivery']];
        }
        $wallesterPaymentService->putDataIntoSession(WallesterCardTypes::getName($request->type) . '_' . $cProfile->id, array_merge($request->validated(), $delivery ?? []));

        $steps = $wallesterPaymentService->getSteps(3);

        if ($request->type == WallesterCardTypes::TYPE_PLASTIC) {
            //delivery for plastic card
            return redirect()->route('show.order.step.3.plastic');
        }

        //summary for virtual card
        $currentOrderData = $request->validated();
        $prevPageUrl = route('show.order', ['type' => WallesterCardTypes::CARD_TYPES_LOWER[WallesterCardTypes::TYPE_VIRTUAL]]);

        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $project = Project::getCurrentProject();

        $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);

        $issuingFee = $cardIssuingProvider->virtual_card_amount ?: 0;

        $account = $wallesterPaymentService->orderCardFromWallester($cProfile, $currentOrderData);

        if (!$account) {
            session()->flash('error', t('virtual_order_error'));
            return redirect()->route('wallester-cards.index');
        }

        $wallesterAccountDetail = $account->wallesterAccountDetail;
        $wallesterAccountDetail->additional_data = json_encode($wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id));
        $wallesterAccountDetail->save();

        $wallesterAccountDetailId = $wallesterAccountDetail->id;

        return view('cabinet.cards.order-summary', compact('steps', 'currentOrderData', 'prevPageUrl', 'issuingFee', 'account', 'wallesterAccountDetailId'));
    }

    public function confirmCardOrderSummary(Request $request, WallesterPaymentService $wallesterPaymentService, SettingService $settingService)
    {
        $cProfile = getCProfile();
        EmailFacade::sendWallesterCardOrderInProgressMessage($cProfile->cUser, WallesterCardTypes::getName($request->type));
        return response()->json([
            'messageSent' => 'true',
        ]);
    }

    public function orderStepThreePlastic(WallesterPaymentService $wallesterPaymentService, CountryService $countryService)
    {
        $steps = $wallesterPaymentService->getSteps(3);
        $cProfile = getCProfile();

        $currentOrderData = $wallesterPaymentService->getDataFromSession(WallesterCardTypes::getName(WallesterCardTypes::TYPE_PLASTIC) . '_' . $cProfile->id);
        if (empty($currentOrderData)) {
            session()->flash('error', t('plastic_order_error'));
            return redirect()->route('wallester-cards.create');
        }
        $prevPageUrl = route('show.order', ['type' => WallesterCardTypes::CARD_TYPES_LOWER[WallesterCardTypes::TYPE_PLASTIC]]);

        $countries = $countryService->getCountriesInISO3Codes();

        return view('cabinet.cards.order-plastic-delivery', compact('steps', 'countries', 'prevPageUrl', 'currentOrderData'));
    }


    public function confirmPlasticOrderDelivery(WallesterDeliveryRequest $request, WallesterPaymentService $wallesterPaymentService, CountryService $countryService, SettingService $settingService)
    {
        $cProfile = getCProfile();
        $currentOrderData = $wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id);
        if (empty($currentOrderData)) {
            session()->flash('error', t('plastic_order_error'));
            return redirect()->route('wallester-cards.create');
        }
        $currentOrderData['delivery'] = $request->validated();
        $country = $countryService->getCountry(['code' => $request->country_code]);
        $currentOrderData['delivery']['country_code'] = strtoupper($country->code_ISO3);
        $currentOrderData['delivery']['country'] = $request->country_code;

        $wallesterPaymentService->putDataIntoSession(WallesterCardTypes::getName($request->type) . '_' . $cProfile->id, $currentOrderData);
        $steps = $wallesterPaymentService->getSteps(4);


        $prevPageUrl = route('show.order.step.3.plastic');
        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $project = Project::getCurrentProject();

        $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);
        $issuingFee = $cardIssuingProvider->plastic_card_amount ?: 0;


        $account = $wallesterPaymentService->orderCardFromWallester($cProfile, $currentOrderData);

        if (!$account) {
            session()->flash('error', t('virtual_order_error'));
            return redirect()->route('wallester-cards.index');
        }

        $wallesterAccountDetail = $account->wallesterAccountDetail;
        $wallesterAccountDetail->additional_data = json_encode($wallesterPaymentService->getDataFromSession( WallesterCardTypes::getName($request->type) . '_' . $cProfile->id));
        $wallesterAccountDetail->save();
        $wallesterAccountDetailId = $wallesterAccountDetail->id;

        return view('cabinet.cards.order-summary', compact('steps', 'currentOrderData', 'prevPageUrl', 'issuingFee', 'account', 'wallesterAccountDetailId'));
    }


    public function confirmPlasticOrder(WallesterPaymentMethodRequest $request, CommissionsService $commissionsService)
    {
        $cProfile = getCProfile();
        $paymentMethod = $request->paymentMethod;
        $wallesterAccountDetail = WallesterAccountDetail::findOrFail($request->id);

        $account = $wallesterAccountDetail->account;
        if (!$account) {
            session()->flash('error', t('plastic_order_error'));
            return redirect()->route('wallester-cards.index');
        }

        $wallesterAccountDetail->payment_method = $paymentMethod;

        $wallesterAccountDetail->save();

        $wallets = [];
        switch ($request->paymentMethod) {
            case WallesterCardOrderPaymentMethods::CRYPTOCURRENCY:
                foreach ($cProfile->cryptoAccountDetail as $wallet) {
                    $wallets[$wallet->id] = $wallet->account->currency . ' - ' . generalMoneyFormat($wallet->account->balance, $wallet->account->currency);
                }
                break;
            case WallesterCardOrderPaymentMethods::SEPA:
                break;
            case WallesterCardOrderPaymentMethods::BANK_CARD:
                break;
        }

        return view('cabinet.cards._pay', compact('wallesterAccountDetail', 'wallets', 'account', 'paymentMethod'));

    }

    public function confirmCardPaymentByCrypto(
        WallesterOrderCryptoPaymentRequest $request,
        OperationService $operationService,
        WallesterPaymentService $wallesterPaymentService,
        SettingService $settingService,
        CommissionsService $commissionsService
    )
    {
        $krakenService = resolve(ExchangeInterface::class);//todo

        $cProfile = getCProfile();
        $fromWallet = $cProfile->cryptoAccountDetail()->where('crypto_account_details.id', $request->fromWallet)->first();
        if (!$fromWallet) {
            session()->flash('error', 'Invalid wallet provided');
            return redirect()->route('wallester-cards.index');
        }
        $operationType = OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO;
        try {
            $account = Account::find($request->id);
            $wallesterAccountDetail = $account->wallesterAccountDetail;

            /* @var ProviderService $providerService */
            $providerService = resolve(ProviderService::class);
            $project = Project::getCurrentProject();
            $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);

            $amountInEuro = $cardIssuingProvider->getCardOrderAmountByCardType($wallesterAccountDetail->card_type);

            if (!$amountInEuro) {
                logger()->error('cardOrderIssuingFeeError', [
                    'message' => t('card_issuing_fee_error'),
                    'account_id' => $account->id
                ]);
                session()->flash('error', t('something_went_wrong'));
                return redirect()->route('wallester-cards.index');
            }

            config()->set('projects.project', $project);
            $rate = KrakenFacade::getRateCryptoFiat($fromWallet->coin, Currency::CURRENCY_EUR, 1);


            $operationAmount = $amountInEuro / $rate;
            $commission = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $fromWallet->coin, Commissions::TYPE_OUTGOING);
            $krakenFee = $krakenService->getTradeVolume(
                Currency::KRAKEN_ASSETS[$fromWallet->coin] ?? $fromWallet->coin,
                Currency::CURRENCY_EUR,
                $operationAmount
            );
            $walletProviderFee = $commissionsService->calculateCommissionAmount($commission, $operationAmount);
            $minBalance  = $operationAmount +  $operationAmount * (float)$krakenFee / 100;
            $minBalance += $commission->blockchain_fee;
            $minBalance += $walletProviderFee;

            $projectId = $cProfile->cUser->project_id;
            $liquidityProviderAccount = Account::getProviderAccount(Currency::CURRENCY_EUR, Providers::PROVIDER_LIQUIDITY);
            if (!$liquidityProviderAccount) {
                logger()->error('cardOrderError', [
                    'message' => t('card_order_liquidity_account_not_found'),
                    'account_id' => $account->id
                ]);
                session()->flash('error', t('something_went_wrong'));
                return redirect()->route('wallester-cards.index');
            }

            if ($fromWallet->account->balance < $minBalance) {
                session()->flash('error', t('card_order_not_enough_balance'));
                return redirect()->route('wallester-cards.index');
            }

            $operation = $operationService->createOperation($cProfile->id, $operationType, $minBalance, $fromWallet->coin, Currency::CURRENCY_EUR, $fromWallet->account->id, $liquidityProviderAccount->id);
            $operation->additional_data = json_encode([
                'wallester_account_detail_id' => $account->wallesterAccountDetail->id,
                'wallester_card_info' => json_decode($wallesterAccountDetail->additional_data,true)
            ]);
            $operation->save();
            EmailFacade::sendWallesterCardOrderOperationCreatedMessage($cProfile->cUser, $operation);
            $wallesterPaymentService->executePayment(WallesterCardOrderPaymentMethods::CRYPTOCURRENCY, $operation);

        } catch (Throwable $exception) {
            logger()->error('WallesterOrderCardCryptoPaymentFail', ['message' => $exception->getMessage()]);
            session()->flash('error', t('card_order_operation_fail'));
            return redirect()->route('wallester-cards.index');
        }

        session()->flash('newCardOperationSuccess', t('card_order_operation_success'));
        return redirect()->route('wallester-cards.index');
    }

    public function showCryptoPaymentSummary(WallesterOrderCryptoPaymentRequest $request, CommissionsService $commissionsService, SettingService $settingService)
    {
        $krakenService = resolve(ExchangeInterface::class);//todo

        $cProfile = getCProfile();
        $fromWallet = $cProfile->cryptoAccountDetail()->where('crypto_account_details.id', $request->fromWallet)->first();
        $account = Account::find($request->id);
        $wallesterAccountDetail = $account->wallesterAccountDetail;

        if (!$fromWallet) {
            return response()->json([
                'errors' => [
                    'fromWallet' => 'Invalid wallet provided',
                ],
            ], 403);
        }

        $commission = $commissionsService->commissions($cProfile->rate_template_id, CommissionType::TYPE_CRYPTO, $fromWallet->coin, Commissions::TYPE_OUTGOING);
        $limits = $commissionsService->limits($cProfile->rate_template_id, $cProfile->compliance_level);

        /* @var OperationService $operationService */
        $operationService = resolve(OperationService::class);
        $availableMonthlyAmount = $operationService->availableMonthlyAmount($cProfile);


        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $project = Project::getCurrentProject();
        $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);
        $cardAmountInEuro = $cardIssuingProvider->getCardOrderAmountByCardType($wallesterAccountDetail->card_type);

        if (!$cardAmountInEuro) {
            logger()->error('cardOrderIssuingFeeError', [
                'message' => t('card_issuing_fee_error'),
            ]);
            return response()->json([
                'errors' => [
                    'fromWallet' => t('something_went_wrong'),
                ],
            ], 403);
        }

        $rate = KrakenFacade::getRateCryptoFiat($fromWallet->coin, Currency::CURRENCY_EUR, 1);
        $cardAmountInCrypto = $cardAmountInEuro / $rate;
        $krakenFee = $krakenService->getTradeVolume(
             Currency::KRAKEN_ASSETS[$fromWallet->coin] ??  $fromWallet->coin,
            Currency::CURRENCY_EUR,
            $cardAmountInCrypto
        );
        $walletProviderFee = $commissionsService->calculateCommissionAmount($commission, $cardAmountInCrypto);
        $cardAmountInCrypto  = $cardAmountInCrypto +  $cardAmountInCrypto * (float)$krakenFee / 100;
        $cardAmountInCrypto += $commission->blockchain_fee;
        $cardAmountInCrypto += $walletProviderFee;

        if ($fromWallet->account->balance < $cardAmountInCrypto) {
            session()->flash('error', t('card_order_not_enough_balance'));
            return response()->json([
                'errors' => [
                    'fromWallet' => t('card_order_not_enough_balance'),
                ],
            ], 403);
        }


        return response()->json([
            'withdrawFee' =>  $commission->percent_commission . '%',
            'blockchainFee' =>  formatMoney($commission->blockchain_fee, $fromWallet->coin) . ' ' . $fromWallet->coin,
            'trxLimit' =>  t('limit_eq') . ' ' . eur_format($limits->transaction_amount_max),
            'availableLimit' =>  t('limit_eq') . ' ' .  ($availableMonthlyAmount > 0 ? eur_format($availableMonthlyAmount) : eur_format(0)),
            'cardAmountInEuro' => eur_format($cardAmountInEuro),
            'cardAmountCrypto' => generalMoneyFormat($cardAmountInCrypto, $fromWallet->coin, true)
        ]);
    }

    public function confirmCardPaymentByWire(WallesterOrderWirePaymentRequest $request, OperationService $operationService, WallesterPaymentService $wallesterPaymentService, SettingService $settingService, PdfGeneratorService $pdfGeneratorService)
    {
        $cProfile = getCProfile();

        $paymentProviderAccount = Account::getActiveAccountById($request->provider_account_id);
        $provider_id = $paymentProviderAccount->provider->id;

        if ($paymentProviderAccount->account_type !== AccountType::TYPE_WIRE_SEPA) {
            logger()->error('cardOrderError', ['message' => t('card_order_unsupported_type')]);
            session()->flash('error', t('something_went_wrong'));
            return redirect()->back();
        }

        $operationType = $paymentProviderAccount->account_type == AccountType::TYPE_WIRE_SEPA ? OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA : OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT;

        try {
            $account = Account::find($request->id);
            $wallesterAccountDetail = $account->wallesterAccountDetail;

            /* @var ProviderService $providerService */
            $providerService = resolve(ProviderService::class);
            $project = Project::getCurrentProject();
            $cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id);
            $amountInEuro = $cardIssuingProvider->getCardOrderAmountByCardType($wallesterAccountDetail->card_type);

            $operationAmount = $amountInEuro;
            if ($request->currency == Currency::CURRENCY_USD) {
                /* @var ExchangeRatesBitstampService $bitstampService */
                $bitstampService = resolve(ExchangeRatesBitstampService::class);
                $operationAmount = $bitstampService->rate($amountInEuro, strtolower(Currency::CURRENCY_EUR), strtolower(Currency::CURRENCY_USD));
            }

            $projectId = $cProfile->cUser->project_id ?? null;
            $liquidityProviderAccount = Account::getProviderAccount($request->currency, Providers::PROVIDER_LIQUIDITY, null, null, $projectId);
            if (!$liquidityProviderAccount) {
                logger()->error('cardOrderError', ['message' => t('liquidity_provider_account_not_found')]);
                session()->flash('error', t('something_went_wrong'));
                return redirect()->back();
            }

            $operation = $operationService->createOperation($cProfile->id, $operationType, $operationAmount,
                $request->currency, $request->currency, null, $liquidityProviderAccount->id,
                OperationStatuses::PENDING, $provider_id, $amountInEuro, null, $paymentProviderAccount->id);

            $operation->additional_data = json_encode([
                'wallester_account_detail_id' => $account->wallesterAccountDetail->id,
                'wallester_card_info' => json_decode($wallesterAccountDetail->additional_data,true)
            ]);
            $operation->save();

            EmailFacade::sendWallesterCardOrderOperationCreatedMessage($cProfile->cUser, $operation);

        } catch (Throwable $exception) {
            logger()->error('FailToCreateCardOrderWireOperation', [
                'message' => $exception->getMessage(),
            ]);
            session()->flash('error', t('something_went_wrong'));
            return redirect()->back();
        }

        session()->flash('newCardOperationSuccess', t('card_order_operation_success'));
        return redirect()->route('wallester-cards.index');
    }

    public function confirmDelivery(WallesterConfirmCardDeliveryRequest $request, Api $api)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($request->id);

        try {
            $card = $api->activateCard($wallesterAccountDetail->wallester_card_id);

            $wallesterAccountDetail->status = WallesterCardStatuses::STATUSES_FROM_RESPONSE[$card['card']['status']];
            $wallesterAccountDetail->is_confirmed = true;
            $wallesterAccountDetail->save();

        } catch (Exception $exception) {
            session()->flash('error', t('something_went_wrong'));
            return redirect()->route('wallester-cards.index');
        }

        session()->flash('success', t('plastic_confirm_delivery_success'));
        return redirect()->route('wallester-cards.index');
    }

    public function viewCardDetails(?string $id, WallesterPaymentService $wallesterPaymentService, Api $wallesterApi, Request $request)
    {
        $cUser = auth()->user();
        $card = WallesterAccountDetail::find($id);

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
        } catch (Exception $exception) {
            session()->flash('error', t('wallester_card_not_found'));
            return redirect()->back();
        }

        if (!$card->wallester_card_id) {
            $card->wallester_card_id = $cardWallester['card']['id'];
        }

        $card->status = WallesterCardStatuses::STATUSES_FROM_RESPONSE[$cardWallester['card']['status']];
        $card->save();
        $blockUrl = '';
        if ($card->status == WallesterCardStatuses::STATUS_BLOCKED) {
            $blockUrl = route('wallester.unblock.card', ['id' => $card->id]);
        } else if ($card->status == WallesterCardStatuses::STATUS_ACTIVE) {
            $blockUrl = route('wallester.block.card', ['id' => $card->id]);
        }

        $data = $request->only(['from_record', 'records_count', 'from_date', 'to_date', 'merchant_name', 'type']);
        $cardTransactions = $wallesterPaymentService->getCardTransactions($card->wallester_card_id, $data);

        try {
            $limits = $wallesterApi->getCardLimits($card->wallester_card_id);
            $defaultLimits = $wallesterApi->getCardDefaultLimitsCached();

            $accountDataInWallester = $wallesterApi->getAccount($card->wallester_account_id);
            $bankTemplate = $card->bankAccountTemplate;
            $cardTopUpDetails = $accountDataInWallester['account']['top_up_details'] ?? [];
        } catch (Throwable $exception) {
            session()->flash('error', t('card_limit_issue'));
            return redirect()->back();
        }

        $account = $card->account;
        $account->balance = $accountDataInWallester['account']['available_amount'];
        $account->save();

        $topUpDetailsToCopy = $wallesterPaymentService->getCardDetailsForCopy($card, $bankTemplate);
        $twoFaDisabled = TwoFAType::NONE;
        return view('cabinet.cards.card-detail', compact('card', 'cUser', 'limits', 'defaultLimits', 'cardTransactions', 'blockUrl', 'cardTopUpDetails', 'topUpDetailsToCopy', 'bankTemplate','twoFaDisabled'));
    }

    public function showCardEncryptedDetails(Request $request, TwoFAService $twoFAService, Api $wallesterApi)
    {
        $card = WallesterAccountDetail::find($request->card_id);
        if (!$card) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }

        $cardWallester = $wallesterApi->getCardByExternalId($card->id);

        if (!$card->wallester_card_id) {
            $card->wallester_card_id = $cardWallester['card']['id'];
            $card->save();
        }

        try {
            $cardNumber = $wallesterApi->getEncryptedCardNumber($card->wallester_card_id);
            $cardCVV = $wallesterApi->getEncryptedCVV($card->wallester_card_id);
        } catch (Throwable $throwable) {
            sleep(1);
            try {
                $cardNumber = $wallesterApi->getEncryptedCardNumber($card->wallester_card_id);
                $cardCVV = $wallesterApi->getEncryptedCVV($card->wallester_card_id);
            } catch (Throwable $exception) {
                return response()->json([
                    'card_id' => t('encryption_error')
                ], 403);
            }
        }


        $expiryDate = (new DateTime($cardWallester['card']['expiry_date']))->format('m/y');

        return response()->json([
            'cvv' => $cardCVV,
            'expiryDate' => $expiryDate,
            'cardNumber' => $cardNumber
        ]);

    }

    public function updateCardLimits(string $id, UpdateCardLimitsRequest $request, Api $wallesterApi)
    {
        //todo update limits
        $wallesterAccountDetails = WallesterAccountDetail::findOrFail($id);
        $limits = $request->validated();
        $wallesterCardLimits = new WallesterLimits($limits['limits']);
        try {
            $wallesterApi->updateCardLimits($wallesterAccountDetails->wallester_card_id, $wallesterCardLimits);
            session()->flash('success', t('card_limit_update_success'));
            return redirect()->back();
        } catch (Throwable $exception) {
            session()->flash('error', t('card_limit_update_fail'));
            return redirect()->back();
        }

    }

    public function blockCard(string $card_id, Api $wallesterApi)
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
            $wallesterApi->blockCard($wallesterAccountDetail->wallester_card_id, WallesterCardBlockTypes::BLOCKED_BY_CARDHOLDER);
            $wallesterAccountDetail->status = WallesterCardStatuses::STATUS_BLOCKED;
            $wallesterAccountDetail->save();
            session()->flash('success', t('wallester_card_block_success'));
            return redirect()->back();
        } catch (Throwable $exception) {
            session()->flash('error', t('wallester_card_was_not_blocked'));
            return redirect()->back();
        }
    }

    public function unblockCard(string $id, Api $wallesterApi)
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
             if ($card['card']['block_type'] !== WallesterCardBlockTypes::BLOCKED_BY_CARDHOLDER) {
                session()->flash('error', t('card_unblocking_access_error'));
                return redirect()->back();
            }
            $wallesterApi->unblockCard($wallesterAccountDetail->wallester_card_id);
            $wallesterAccountDetail->status = WallesterCardStatuses::STATUS_ACTIVE;
            $wallesterAccountDetail->save();
            session()->flash('success', t('wallester_card_unblock_success'));
            return redirect()->back();
        } catch (Throwable $exception) {
            session()->flash('error', t('wallester_card_was_not_unblocked'));
            return redirect()->back();
        }
    }


    public function getPinCode(string $id, Api $wallesterApi)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($id);

        if (!$wallesterAccountDetail) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }

        try {
            $pin = $wallesterApi->getEncryptedPIN($wallesterAccountDetail->wallester_card_id);
            return response()->json([
                'pin' => $pin,
            ]);
        } catch (Throwable $exception) {
            if ($exception->getCode() == 422) {
                return response()->json([
                    'error' => t('card_does_not_have_pin_code'),
                ], $exception->getCode());
            }

            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode());
        }
    }

    public function getCVVCode(string $id, Api $wallesterApi)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($id);

        if (!$wallesterAccountDetail) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }

        try {
            $cvv = $wallesterApi->getEncryptedCVV($wallesterAccountDetail->wallester_card_id);
            return response()->json([
                'cvv' => $cvv,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode());
        }

    }

    public function updateCardSecurity(string $id, Api $wallesterApi, WallesterCardSecureUpdate $request)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($id);

        if (!$wallesterAccountDetail) {
            session()->flash('error', t('wallester_card_not_found'));
            return redirect()->back();
        }

        $cProfile = getCProfile();

        try {
            $wallesterSecurity = new WallesterSecurity([
                'contactless_enabled' => $request->contactless_purchases,
                'withdrawal_enabled' => $request->atm_withdrawals,
                'internet_purchase_enabled' => $request->internet_purchases,
                'overall_limits_enabled' => $request->overall_limits_enabled,
            ]);

            $wallesterApi->updateSecurity($wallesterAccountDetail->wallester_card_id, $wallesterSecurity);

            $wallesterAccountDetail->fill([
                'contactless_purchases' => $wallesterSecurity->contactless_enabled,
                'atm_withdrawals' => $wallesterSecurity->withdrawal_enabled,
                'internet_purchases' => $wallesterSecurity->internet_purchase_enabled,
                'overall_limits_enabled' => $wallesterSecurity->overall_limits_enabled
            ]);

            if ($wallesterAccountDetail->password_3ds != $request->password) {
                $wallesterSecure = new WallesterSecure([
                    'type' => 'SMSOTPAndStaticPassword',
                    'mobile' => '+' . $cProfile->cUser->phone,
                    'password' => $request->password,
                ]);

                $wallesterApi->update3DSPassword($wallesterAccountDetail->wallester_card_id, $wallesterSecure);
                $wallesterAccountDetail->password_3ds = $wallesterSecure->password;
            }
            $wallesterAccountDetail->save();

        } catch (Throwable $exception) {
            session()->flash('error', t('wallester_secure_settings_not_changed'));
            return redirect()->back();
        }
        session()->flash('success', t('secure_update_success'));
        return redirect()->back();
    }

    public function remind3dsPassword(string $id, Api $wallesterApi)
    {
        $wallesterAccountDetail = WallesterAccountDetail::find($id);

        if (!$wallesterAccountDetail) {
            return response()->json([
                'card_id' => t('wallester_card_not_found')
            ], 403);
        }

        try {
            $password = $wallesterApi->getEncrypted3dsPassword($wallesterAccountDetail->wallester_card_id);
            return response()->json([
                'password' => $password
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'error' => t('encryption_error'),
            ], $exception->getCode());
        }
    }

    /**
     * @param CardService $cardService
     * @param SettingService $settingService
     * @return JsonResponse
     */
    public function wallesterCardPrices(CardService $cardService, SettingService $settingService): JsonResponse
    {
        $prices = $cardService->wallesterCardPrices($settingService);

        return response()->json([
            'prices' => $prices
        ]);
    }
}
