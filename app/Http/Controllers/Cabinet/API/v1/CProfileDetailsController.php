<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\ComplianceLevel;
use App\Enums\OperationStatuses;
use App\Enums\RateTemplatesStatuses;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cabinet\API\v1\RateResource;
use App\Models\Cabinet\CProfile;
use App\Models\Limit;
use App\Services\ActivityLogService;
use App\Services\ComplianceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CProfileDetailsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/rates",
     *     summary="Get rates",
     *     description="This api call is used to get rates",
     *     tags={"005. Compliance"},
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
     *         description="Rate was found",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="commissions",
     *                      type="object",
     *                      @OA\Property(
     *                          property="USD",
     *                          type="object",
     *                          @OA\Property(
     *                              property="incomingFeeSepa",
     *                              description="Incoming fee(%) for Top Up/Withdraw Sepa (USD)",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="outgoingFeeSepa",
     *                              description="Outgoing fee for Top Up/Withdraw Sepa",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="incomingFeeSwift",
     *                              description="Incoming fee for Top Up/Withdraw Swift",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="outgoingFeeSwift",
     *                              description="Outgoing fee for Top Up/Withdraw Swift",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="bankCard",
     *                              description="Fee for Card Payment",
     *                              type="number",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="LTC",
     *                          type="object",
     *                          @OA\Property(
     *                              property="incomingFeeCrypto",
     *                              description="Incoming fee(%) for Top Up/Withdraw Crypto",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="outgoingFeeCrypto",
     *                              description="Outgoing fee for Top Up/Withdraw Crypto",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="incomingFeeBlockchain",
     *                              description="Withdraw Blockchain Fee",
     *                              type="number",
     *                          ),
     *                          @OA\Property(
     *                              property="outgoingFeeBlockchain",
     *                              description="Withdraw Blockchain Fee",
     *                              type="number",
     *                          ),
     *                      ),
     *              ),
     *             @OA\Examples(example="result", value={
     *                  "limits": {
     *                      "level": 1,
     *                      "transactionLimit": 1000,
     *                      "monthlyLimit": 5000,
     *                      "availableMonthlyAmount": 4500,
     *                  },
     *                  "commissions": {
     *                      "Fiat": {
     *                          {
     *                          "Currency": "USD",
     *                          "incomingFeeSepa": 1.2,
     *                          "outgoingFeeSepa": 1.2,
     *                          "incomingFeeSwift": 1.5,
     *                          "outgoingFeeSwift": 1.5,
     *                          "bankCard": 0
     *                          },
     *                          {
     *                          "Currency": "EUR",
     *                          "incomingFeeSepa": 3,
     *                          "outgoingFeeSepa": 1.7,
     *                          "incomingFeeSwift": 0,
     *                          "outgoingFeeSwift": 0,
     *                          "bankCard": 0
     *                          },
     *                          {
     *                          "Currency": "GBP",
     *                          "incomingFeeSepa": 0,
     *                          "outgoingFeeSepa": 0,
     *                          "incomingFeeSwift": 0,
     *                          "outgoingFeeSwift": 0,
     *                          "bankCard": 0
     *                          }
     *                      },
     *                      "Crypto": {
     *                          {
     *                          "Currency": "BTC",
     *                          "incomingFeeCrypto": 0,
     *                          "outgoingFeeCrypto": 1,
     *                          "incomingFeeBlockchain": 0.0001,
     *                          "outgoingFeeBlockchain": 0.0001
     *                          },
     *                          {
     *                          "Currency": "LTC",
     *                          "incomingFeeCrypto": 0,
     *                          "outgoingFeeCrypto": 1,
     *                          "incomingFeeBlockchain": 0.0001,
     *                          "outgoingFeeBlockchain": 0.0001
     *                          },
     *                          {
     *                          "Currency": "BCH",
     *                          "incomingFeeCrypto": 0,
     *                          "outgoingFeeCrypto": 1,
     *                          "incomingFeeBlockchain": 0.0001,
     *                          "outgoingFeeBlockchain": 0.0001
     *                          }
     *                      }
     *                  },
     *              "accountMinAmounts": {
     *                  "Fiat": {
     *                      {
     *                          "Currency": "USD",
     *                          "withdrawSepa": 10,
     *                          "topUpSepa": 10,
     *                          "withdrawSwift": 1000,
     *                          "topUpSwift": 1000,
     *                          "topUpBankCard": 0
     *                      },
     *                      {
     *                          "Currency": "EUR",
     *                          "withdrawSepa": 50,
     *                          "topUpSepa": 50,
     *                          "withdrawSwift": 0,
     *                          "topUpSwift": 0,
     *                          "topUpBankCard": 0
     *                      },
     *                      {
     *                          "Currency": "GBP",
     *                          "withdrawSepa": 0,
     *                          "topUpSepa": 0,
     *                          "withdrawSwift": 0,
     *                          "topUpSwift": 0,
     *                          "topUpBankCard": 0
     *                      }
     *                  },
     *                  "Crypto": {
     *                      {
     *                          "Currency": "BTC",
     *                          "withdrawCrypto": 0.01
     *                      },
     *                      {
     *                          "Currency": "LTC",
     *                          "withdrawCrypto": 0.01
     *                      },
     *                      {
     *                          "Currency": "BCH",
     *                          "withdrawCrypto": 0.01
     *                      }
     *                      }
     *              }
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Rates not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                              property="rates",
     *                              type="string",
     *                              description="Rates not found"
     *                     ),
     *                     ),
     *                      example={
     *                        "errors" : {"rates": "Rates not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getRates()
    {
        /* @var CProfile $profile*/
        $profile = Auth::user()->cProfile;

        $rateTemplate = \auth()->user()->cProfile->rateTemplate()->with(['commissions' => function($qc){
            return $qc->where('is_active', RateTemplatesStatuses::STATUS_ACTIVE)->orderBy('commission_type')->orderBy('type')->orderBy('currency', 'desc');
        }, 'limits' => function($q){
            return $q->orderBy('level');
        }])->first();

        $limit = $rateTemplate->limits()->where('level', $profile->compliance_level)->first();

        if (!$rateTemplate || !$limit) {
            return response()->json([
                "errors" => ['rates' => t('rate_not_found')]
            ], 404);
        }

        $availableMonthlyAmount = $this->availableMonthlyAmount($profile, $limit);

        return response()->json([
            'limits' => [
                'level' => $profile->compliance_level,
                'transactionLimit' => $limit['transaction_amount_max'] ?? 0,
                'monthlyLimit' => $limit['monthly_amount_max'] ?? 0,
                'availableMonthlyAmount' => $availableMonthlyAmount,
            ],
            'commissions' => new RateResource($rateTemplate),
            'accountMinAmounts' => (new RateResource($rateTemplate))->setAccountMinAmount(true),
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/compliance",
     *     summary="Get compliance data",
     *     description="This api call is used to get compliance data",
     *     tags={"005. Compliance"},
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
     *         description="Compliance data found.",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="complianceData",
     *                      type="object",
     *                      @OA\Property(
     *                          property="level",
     *                          type="integer",
     *                          description="Current compliance level.",
     *                      ),
     *                      @OA\Property(
     *                          property="nextLevelName",
     *                          type="string",
     *                          description="Next compliance level name.",
     *                      ),
     *                      @OA\Property(
     *                          property="nextLevelType",
     *                          type="string",
     *                          description="Can be Update or Retry.",
     *                      ),
     *              ),
     *             @OA\Examples(example="result", value={
     *                     "complianceData": {
     *                          "level": 1,
     *                          "nextLevelName": "Level 2",
     *                          "nextLevelType": "Update",
     *                      },
     *             }, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function getComplianceData()
    {
        /* @var CProfile $profile*/
        $profile = Auth::user()->cProfile;

        $retryComplianceRequest = $profile->retryComplianceRequest();

        $nextLevelType = $retryComplianceRequest ? 'Retry' : 'Update';
        $nextComplianceLevel = $retryComplianceRequest ? $profile->compliance_level : $profile->compliance_level + 1;

        return response()->json([
           'complianceData' => [
               'level' => $profile->compliance_level,
               'nextLevelName' => ComplianceLevel::getName($nextComplianceLevel),
               'nextLevelType' => $nextLevelType,
           ]
       ]);
    }


    /**
     * @OA\Get(
     *     path="/api/compliance/get/token",
     *     summary="Get url for compliance level update",
     *     description="This api call is used to get url for compliance level update",
     *     tags={"005. Compliance"},
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
     *         description="Success.",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="url",
     *                      type="string",
     *                      description="Compliance start url.",
     *              ),
     *             @OA\Examples(example="result", value={
     *                 "url": "https://app.cratos.net/compliance/change/olCXAGn7yiTgcx9P4bc7834e366bab19419fce8070bf3a7c",
     *             }, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function getTokenForCompliance(ComplianceService $complianceService)
    {
        /* @var CProfile $profile*/
        $profile = Auth::user()->cProfile;

        $token = Str::random() . md5(uniqid($profile->id));

        $complianceService->putTokenIntoCache($token, ['profile_id' => $profile->id]);

        return [
            'url' => route('api.v1.cabinet.compliance.token.post', ['token' => $token]),
        ];
    }

    /**
     * @param CProfile $cProfile
     * @param Limit $limits
     * @return float
     */
    protected function availableMonthlyAmount(CProfile $cProfile, Limit $limit): float
    {
        $receivedAmountForCurrentMonth = $cProfile->operations()
            ->whereIn('status', [OperationStatuses::SUCCESSFUL, OperationStatuses::PENDING])
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount_in_euro');

        $availableMonthlyAmount = $limit->monthly_amount_max - round($receivedAmountForCurrentMonth, 2);
        return $availableMonthlyAmount > 0 ? $availableMonthlyAmount : 0;
    }
}
