<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\TopUpCardRequest;
use App\Http\Resources\Cabinet\API\v1\TrustPaymentsForCardOperationResource;
use App\Services\CardProviders\TrustPaymentService;
use App\Services\ProjectService;
use App\Services\TopUpCardService;
use function config;
use function getCProfile;
use function resolve;
use function response;
use function t;

class CardTransferController extends Controller
{

    /**
     * @OA\Post (
     *     path="/api/operation/card",
     *     summary="Create Top Up Card operation",
     *     description="This API call is used to create a new Top Up Card operation.",
     *     tags={"010. Top Up by Card"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Internal wallet id for top up operation.
                                    To get all wallets, make API call to /api/wallets endpoint",
     *                     property="wallet_id",
     *                     type="string",
     *                     example="3asf4baf-al68-49f7-la36-86f3eed92947",
     *                 ),
     *                 @OA\Property(
     *                     description="Currency",
     *                     property="currency",
     *                     type="string",
     *                     example="USD",
     *                     enum={"USD", "EUR", "GBP"}
     *                 ),
     *                 @OA\Property(
     *                     description="Operation Amount",
     *                     property="amount",
     *                     type="number",
     *                     example=10,
     *                 ),
     *                 required={"wallet_id", "currency", "amount"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="To continue to the payment page you should send post request to the url that you got from
                       repsonse, and as request body parameters set other field values left in the response.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="cardPaymentProperties",
     *                  description="Payment details to send to Trust Payments with parametors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="url",
     *                      description="Payment Url with parametors.",
     *                      type="string"
     *                  ),
     *              ),
     *             @OA\Examples(example="result", value={
     *              "cardPaymentProperties": {
     *                  "url": "https://payments.securetrading.net/process/payments/choice?sitereference=test_test79829&stprofile=default&stdefaultprofile=st_paymentcardonly&strequiredfields=nameoncard&currencyiso3a=EUR&mainamount=40&orderreference=223465fd-935a-42340-8234f-be39cc796873&version=2&ruleidentifier=STR-8&successfulurlnotification=https://app.cratos.net/webhook/payments/trust-payment",
     *              }
     *              },summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Something went wrong",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="amount",
     *                              type="string",
     *                              description="The amount is required"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "amount": "The amount is required."
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function createCardTransfer(TopUpCardRequest $request)
    {
        $cProfile = getCProfile();

        $account = $cProfile->accounts()->find($request->wallet_id);
        if (!$account) {
            return response()->json([
                'errors' => [
                    'wallet' => t('account_not_found')
                ]
            ], 422);
        }

        $topUpCardService = resolve(TopUpCardService::class);
        /* @var TopUpCardService $topUpCardService*/

        //account limit validation
        if (!$topUpCardService->validateCardOperationLimits($cProfile, $request->currency, $request->amount)) {
            return response()->json([
                'errors' => [
                    'transfer_limit_fail' => t('ui_card_transfer_limit_fail_validation')
                ]
            ], 422);
        }

        $profileCommission = $cProfile->operationCommission(CommissionType::TYPE_CARD, Commissions::TYPE_INCOMING, $request->currency);
        if ($request->amount < $profileCommission->min_amount) {
            return response()->json([
                'errors' => [
                    'amount' => t('ui_card_transfer_min_amount_fail_validation', [
                        'minAmount' => $profileCommission->min_amount ?? 0,
                        'currency' => $request->currency,
                    ])]
            ], 422);
        }

        $operation = $topUpCardService->createTopUpCardOperation($cProfile->id, $request->amount, $request->currency, $account->currency, null, $account->id);

        if (!$operation) {
            return response()->json([
                'errors' => [
                    'amount' => t('ui_card_transfer_limit_fail')
                ]
            ], 422);
        }

        $purpose = t('operation_type_top_up_card'). $operation->amount . ' ' .  $operation->from_currency;
        $trustPaymentService = new TrustPaymentService();
        $trustPaymentService->setTransactionDetails($operation->id, ' ', $operation->amount, $operation->from_currency, $purpose);
        $formData = $trustPaymentService->getPaymentFormData();

        $projectService = resolve(ProjectService::class);
        /* @var ProjectService $projectService */

        $apiSettings = $projectService->getCardApiSettings($operation->cProfile->cUser->project);

        $configKey = 'cardproviders.' . $apiSettings['api'] . '.' . $apiSettings['api_account'] . '.sitereference';
        $siteReference = config($configKey);

        return response()->json([
            'cardPaymentProperties' => (new TrustPaymentsForCardOperationResource($formData))->setOperationId($operation->id)->setSiteReference($siteReference)
        ]);
    }


}
