<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\Providers;
use App\DataObjects\Payments\Wallester\WallesterLimits;
use App\Enums\WallesterCardOrderPaymentMethods;
use App\Enums\WallesterCardTypes;
use App\Facades\EmailFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\CardPaymentByWireRequest;
use App\Http\Requests\API\v1\WallesterOrderCryptoPaymentRequest;
use App\Http\Requests\Cabinet\API\v1\WallesterCardRequest;
use App\Http\Resources\WallesterAccountDetailsResource;
use App\Models\Account;
use App\Models\WallesterAccountDetail;
use App\Services\CountryService;
use App\Services\ExchangeRatesBitstampService;
use App\Services\OperationService;
use App\Services\PdfGeneratorService;
use App\Services\SettingService;
use App\Services\Wallester\Api;
use App\Services\Wallester\WallesterPaymentService;
use Illuminate\Http\JsonResponse;
use Throwable;

class WallesterController extends Controller
{

    /**
     *
     * @OA\Post(
     * path="/api/card/order",
     * summary="Create wallester account detail",
     * description="Create account in wallester",
     * operationId="createWallesterAccount",
     * tags={"card order"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"cardType","security","cardDeliveryAddress"},
     *       @OA\Property(
     *          property="cardType",
     *          type="string",
     *          format="cardType",
     *          example="plastic"
     *      ),
     *       @OA\Property(
     *          property="security",
     *          description="Card security",
     *          type="object",
     *          example={
     *             "password3DS" : "string",
     *             "internetPurchases" : true,
     *             "overallLimitsEnabled" : true,
     *             "contactlessPurchases" : true,
     *             "atmWithdrawals" : true
     *          }
     *     ),
     *      @OA\Property(
     *          property="limits",
     *          type="object",
     *          example={
     *               "daily_purchase": 0,
     *               "daily_withdrawal": 0,
     *               "daily_internet_purchase": 0,
     *               "daily_contactless_purchase": 0,
     *               "weekly_purchase": 0,
     *               "weekly_withdrawal": 0,
     *               "weekly_internet_purchase": 0,
     *               "weekly_contactless_purchase": 0,
     *               "monthly_purchase": 0,
     *               "monthly_withdrawal": 0,
     *               "monthly_internet_purchase": 0,
     *               "monthly_contactless_purchase": 0,
     *               "transaction_purchase": 0,
     *               "transaction_withdrawal": 0,
     *               "transaction_internet_purchase": 0,
     *               "transaction_contactless_purchase": 0,
     *           }
     *      ),
     *       @OA\Property(
     *          property="cardDeliveryAddress",
     *          type="object",
     *          example={
     *               "firstName" : "string",
     *               "lastName" : "string",
     *               "address_1" : "string",
     *               "address_2" : "string",
     *               "zipCode" : "number",
     *               "city" : "string",
     *               "countryCode" : "string"
     *          }
     *      ),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *    @OA\JsonContent(
     *       required={"cardType","security","cardDeliveryAddress"},
     *       @OA\Property(
     *          property="cardAccountDetail",
     *          type="object",
     *          format="cardAccountDetail",
     *          example={
     *                "id": "4f83df2f-5d3e-4709-9a9e-ff59270f9cbc",
     *                "account_id": "1772bae8-450b-44ac-accc-2a73d0814091",
     *                "name": "Marat g",
     *                "wallester_account_id": null,
     *                "card_type": 1,
     *                "status": 0,
     *                "contactless_purchases": 1,
     *                "atm_withdrawals": 1,
     *                "internet_purchases": 1,
     *                "overall_limits_enabled": 1,
     *                "password_3ds": "string",
     *                "payment_method": null,
     *                "is_confirmed": 0,
     *                "card_mask": null,
     *               "limits":{
     *               "daily_purchase": 0,
     *               "daily_withdrawal": 0,
     *               "daily_internet_purchase": 0,
     *               "daily_contactless_purchase": 0,
     *               "weekly_purchase": 0,
     *               "weekly_withdrawal": 0,
     *               "weekly_internet_purchase": 0,
     *               "weekly_contactless_purchase": 0,
     *               "monthly_purchase": 0,
     *               "monthly_withdrawal": 0,
     *               "monthly_internet_purchase": 0,
     *               "monthly_contactless_purchase": 0,
     *               "transaction_purchase": 0,
     *               "transaction_withdrawal": 0,
     *               "transaction_internet_purchase": 0,
     *               "transaction_contactless_purchase": 0,
     *                 },
     *                "created_at": "2022-10-22T14:12:55.000000Z",
     *                "updated_at": "2022-10-22T14:12:55.000000Z",
     *                "wallester_card_id": null,
     *                "is_blocked": 0,
     *                "is_paid": 0,
     *                "operation_id": null,
     *           },
     *      ),
     *     ),
     *   ),
     * @OA\Response(
     *    response=422,
     *    description="Card account detail not found",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Card account detail not found")
     *        ),
     *     ),
     * ),
 * @return \Illuminate\Http\JsonResponse
     */

    public function cardOrder (
        WallesterCardRequest $request,
        WallesterPaymentService $wallesterPaymentService
    ) {
        $cProfile = getCProfile();
        $isCardTypePlastic = $request->cardType == WallesterCardTypes::CARD_TYPES_LOWER[WallesterCardTypes::TYPE_PLASTIC];
        $contactlessPurchases = $request->security['contactlessPurchases'];
        $atmWithdrawals = $request->security['atmWithdrawals'];
        $wallesterPaymentService->createPersonInWallester($cProfile);
        $cProfile->refresh();
        if ($request->cardType == WallesterCardTypes::TYPE_VIRTUAL){
            $contactlessPurchases =false;
            $atmWithdrawals = false;
        }
        $purchases = [
            'contactless_purchases' => $contactlessPurchases ?? null,
            'atm_withdrawals' => $atmWithdrawals ?? null,
            'internet_purchases' => $request->security['internetPurchases'],
            'overall_limits_enabled' => $request->security['overallLimitsEnabled'],
        ];
        $wallesterAccount = $wallesterPaymentService->createAccountForWallester(
            $cProfile,
            'Wallester ' . $cProfile->getFullName(),
            array_search ($request->cardType,WallesterCardTypes::CARD_TYPES_LOWER),
            $request->security['password3DS'],
            $request->paymentMethod,
            $purchases,
        );
        if (!$wallesterAccount) {
            return response()->json([
                'error'=> (t('virtual_order_error'))
            ]);
        }
        $additionalData ['limits']= $request->limits;
        $additionalData ['type']= array_search($request->cardType,WallesterCardTypes::CARD_TYPES_LOWER);
        $additionalData ['internet_purchases']= $request->security['internetPurchases'];
        $additionalData ['overall_limits_enabled']= $request->security['overallLimitsEnabled'];
        $additionalData ['password']= $request->security['password3DS'];
        $additionalData ['contactless_purchases']= $request->security['contactlessPurchases'];
        $additionalData ['atm_withdrawals']= $request->security['atmWithdrawals'];

        if ($isCardTypePlastic) {
           $wallesterPaymentService->createCardDeliveryAddress(
                $wallesterAccount->wallesterAccountDetail,
                $request->cardDeliveryAddress['countryCode'],
                $request->cardDeliveryAddress['firstName'],
                $request->cardDeliveryAddress['lastName'],
                $request->cardDeliveryAddress['address_1'],
                $request->cardDeliveryAddress['address_2'],
                $request->cardDeliveryAddress['zipCode'],
                $request->cardDeliveryAddress['city']
            );
            $additionalData ['delivery'] = $request->cardDeliveryAddress;
        }
        $wallesterAccountDetail = $wallesterAccount->wallesterAccountDetail;
        $wallesterAccountDetail->additional_data = json_encode($additionalData);
        $wallesterAccountDetail->save();
        $wallesterAccountDetail = new WallesterAccountDetailsResource($wallesterAccount->wallesterAccountDetail->where('account_id',$wallesterAccount->id)->first());
        return response()->json([
            'cardAccountDetail' => $wallesterAccountDetail,
        ]);
    }
    /**
     *
     * @OA\Post(
     * path="/api/card/payment/wire",
     * summary="Wallester card payment by wire",
     * description="CardPaymentBywire",
     * operationId="CardPaymentBywire",
     * tags={"card payment"},
     * @OA\RequestBody(
     *    required=true,
     *    description="WallesterAccountId,ProvideAccountId,Currency",
     *    @OA\JsonContent(
     *       required={"wallesterAccountId","providerAccountId","currency"},
     *       @OA\Property(
     *          property="wallesterAccountId",
     *          type="string",
     *          format="Id wallester credits that we pay",
     *          example="f652f282-e76b-48f1-a9a3-3dc3af5a42b6",
     *      ),
     *       @OA\Property(
     *          property="providerAccountId",
     *          description="Id bank card from which money should be debited",
     *          type="string",
     *          example="444fb102-0469-4b8c-be75-206e1eadac78",
     *     ),
     *       @OA\Property(
     *          property="currency",
     *          description="Currency in which we pay",
     *          type="string",
     *          example="USD",
     *      ),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *    @OA\JsonContent(
     *       required={"cardType","security","cardDeliveryAddress"},
     *       @OA\Property(
     *          property="message",
     *          type="string",
     *          format="message",
     *          example="We have successfully received your order for issuing a card."
     *      ),
     *     ),
     *   ),
     * @OA\Response(
     *    response=422,
     *    description="Liquidity provider account not found",
     *    @OA\JsonContent(
     *       @OA\Property(property="error", type="string", example="Liquidity provider account not found")
     *        ),
     *     ),
     * ),
     *
     * @param CardPaymentByWireRequest $request
     * @param OperationService $operationService
     * @param WallesterPaymentService $wallesterPaymentService
     * @param SettingService $settingService
     * @param PdfGeneratorService $pdfGeneratorService
     * @return JsonResponse
     */
    public function confirmCardPaymentByWire(
        CardPaymentByWireRequest $request,
        OperationService $operationService,
        WallesterPaymentService $wallesterPaymentService,
        SettingService $settingService,
        PdfGeneratorService $pdfGeneratorService
    ): JsonResponse
    {
        $cProfile = getCProfile();

        $paymentProviderAccount = Account::getActiveAccountById($request->providerAccountId);
        $provider_id = $paymentProviderAccount->provider->id;

        if ($paymentProviderAccount->account_type !== AccountType::TYPE_WIRE_SEPA) {
            return response()->json([
               'error'=> (t('card_order_unsupported_type'))
            ]);
        }

        $operationType = $paymentProviderAccount->account_type == AccountType::TYPE_WIRE_SEPA ? OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SEPA : OperationOperationType::TYPE_CARD_ORDER_PAYMENT_SWIFT;

        try {
            $account = Account::find($request->wallesterAccountId);
            $wallesterAccountDetail = $account->wallesterAccountDetail;
            $amountInEuro = $settingService->getSettingContentByKey(WallesterAccountDetail::CARD_SETTING_KEYS[$wallesterAccountDetail->card_type]);

            $operationAmount = $amountInEuro;
            if ($request->currency == Currency::CURRENCY_USD) {
                /* @var ExchangeRatesBitstampService $bitstampService */
                $bitstampService = resolve(ExchangeRatesBitstampService::class);
                $operationAmount = $bitstampService->rate($amountInEuro, strtolower(Currency::CURRENCY_EUR), strtolower(Currency::CURRENCY_USD));
            }

            $liquidityProviderAccount = Account::getProviderAccount($request->currency, Providers::PROVIDER_LIQUIDITY);
            if (!$liquidityProviderAccount) {
                return response()->json([
                    'error'=> (t('liquidity_provider_account_not_found'))
                ]);
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
            return response()->json([
                'message'=> $exception->getMessage()
            ]);
        }

        return response()->json([
            'message' => t('card_order_operation_success')
         ]);
    }
    /**
     *
     * @OA\Post(
     * path="/api/card/payment/crypto",
     * summary="Card payment by crypto",
     * description="Pay wallester card order",
     * operationId="cardPaymentByCrypto",
     * tags={"card payment"},
     * @OA\RequestBody(
     *    required=true,
     *    description="BankCardId, fromCryptoWalletId, limits",
     *    @OA\JsonContent(
     *       required={"bankCardId","fromCryptoWalletId","limits"},
     *       @OA\Property(
     *          property="bankCardId",
     *          type="string",
     *          format="bankCardId",
     *          example="f652f282-e76b-48f1-a9a3-3dc3af5a42b6"
     *      ),
     *       @OA\Property(
     *          property="fromCryptoWalletId",
     *          description="From crypto wallet ID",
     *          type="object",
     *          example="8577c9a3-6f3c-42b5-9cfd-f5f3cbb20119",
     *     ),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *    @OA\JsonContent(
     *       required={"bankCardId","fromCryptoWalletId","limits"},
     *       @OA\Property(
     *          property="message",
     *          type="object",
     *          example={
     *                "message":"We have successfully received your order for issuing a card.",
     *           },
     *      ),
     *     ),
     *   ),
     * @OA\Response(
     *    response=422,
     *    description="Card order failed",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="error",
     *          type="object",
     *          example={
     *              "error":"Card order failed."
     *         },
     *     ),
     *        ),
     *     ),
     * ),
     */

    public function confirmCardPaymentByCrypto(
        WallesterOrderCryptoPaymentRequest $request,
        OperationService                   $operationService,
        WallesterPaymentService            $wallesterPaymentService,
        SettingService                     $settingService,
        Api $wallesterApi
    ): JsonResponse
    {
        $cProfile = getCProfile();
        $fromWallet = $cProfile->cryptoAccountDetail()->where('crypto_account_details.id', $request->fromCryptoWalletId)->first();
        if (!$fromWallet) {
            return response()->json([
                'error' => 'Invalid wallet provided'
            ]);
        }
        $operationType = OperationOperationType::TYPE_CARD_ORDER_PAYMENT_CRYPTO;
        try {
            $account = Account::find($request->bankCardId);
            $wallesterAccountDetail = $account->wallesterAccountDetail;
            $amountInEuro = $settingService->getSettingContentByKey(WallesterAccountDetail::CARD_SETTING_KEYS[$wallesterAccountDetail->card_type]);

            if (!$amountInEuro) {
                return response()->json([
                    'error' => t('something_went_wrong')
                ]);
            }

            $rate = KrakenFacade::getRateCryptoFiat($fromWallet->coin, Currency::CURRENCY_EUR, 1);
            $operationAmount = $amountInEuro / $rate;


            $liquidityProviderAccount = Account::getProviderAccount(Currency::CURRENCY_EUR, Providers::PROVIDER_LIQUIDITY);
            if (!$liquidityProviderAccount) {
                return response()->json([
                    'error' => t('something_went_wrong')
                ]);
            }

            if ($fromWallet->account->balance < $operationAmount) {
                return response()->json([
                    'error' => t('card_order_not_enough_balance')
                ]);
            }

            $operation = $operationService->createOperation($cProfile->id, $operationType, $operationAmount, $fromWallet->coin, Currency::CURRENCY_EUR, $fromWallet->account->id, $liquidityProviderAccount->id);
            $operation->additional_data = json_encode([
                'wallester_account_detail_id' => $account->wallesterAccountDetail->id,
                'wallester_card_info' => json_decode($wallesterAccountDetail->additional_data, true)
            ]);
            $operation->save();

            EmailFacade::sendWallesterCardOrderOperationCreatedMessage($cProfile->cUser, $operation);
            $wallesterPaymentService->executePayment(WallesterCardOrderPaymentMethods::CRYPTOCURRENCY, $operation);
            $data = [
                'message' => t('card_order_operation_success'),
            ];
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => t('card_order_operation_fail')
            ]);
        }

        return response()->json($data);
    }
    /**
     * @OA\Get(
     *     path="/api/users/wallester/payment/methods",
     *     summary="Get wallester card order payment methods",
     *     description="This API call is used to get wallester card order payment methods",
     *     tags={"Wallester card order payment methods"},
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
     *         description="Wallester card order payment methods",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="availablePaymentMethods",
     *                     description="Payment methods",
     *                     type="object"
     *              ),
     *               @OA\Property(
     *                     property="1",
     *                     description="Bank Card",
     *                     type="string"
     *                 ),
     *             @OA\Property(
     *                     property="2",
     *                     description="Sepa",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                       property="3",
     *                       description="Crypto Currency",
     *                       type="string"
     *                 ),
     *             @OA\Examples(example="result", value={
     *                    "availablePaymentMethods": {
     *                       "1": "Bank Card",
     *                       "2": "SEPA",
     *                       "3": "Cryptocurrency"
     *                       }
     *                   }, summary="An result object."),
     *         ),
     *     ),
     *
     *             )
     *         }
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getAvailablePaymentMethods(): JsonResponse
    {
        $paymentMethods = WallesterCardOrderPaymentMethods::NAMES;
        $data = [];
        foreach ($paymentMethods as $key => $value){
            $data [$key] = t($value);
        }
        return response()->json([
            'availablePaymentMethods' => $data
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/wallester/card/prices",
     *     summary="GGet Wallester card prices",
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
     *         description="Wallester card order payment methods",
     *         @OA\JsonContent(
     *         @OA\Property(
     *           property="prices",
     *           description="Wallester Card Prices",
     *           type="object"
     *              ),
     *               @OA\Property(
     *                     property="plastic",
     *                     description="Plastic card price",
     *                     type="number"
     *                 ),
     *             @OA\Property(
     *                     property="virtual",
     *                     description="Virtual card price",
     *                     type="number"
     *                 ),
     *
     *             @OA\Examples(example="result", value={
     *               "prices":{
     *                  "plastic": 5000,
     *                  "virtual": 2000,
     *                       }
     *               }, summary="An result object."),
     *         ),
     *     ),
     *
     *             )
     *         }
     *     ),
     * ),
     * @return int[]
     */
    public function wallesterCardPrices(SettingService $settingService): array
    {
        $plasticCardOrderAmount = $settingService->getSettingContentByKey(WallesterAccountDetail::CARD_SETTING_KEYS[WallesterCardTypes::TYPE_PLASTIC]) ?: 0;
        $virtualCardOrderAmount = $settingService->getSettingContentByKey(WallesterAccountDetail::CARD_SETTING_KEYS[WallesterCardTypes::TYPE_VIRTUAL]) ?: 0;

        return [
            'plastic' => $plasticCardOrderAmount,
            'virtual' => $virtualCardOrderAmount
        ];
    }
}
