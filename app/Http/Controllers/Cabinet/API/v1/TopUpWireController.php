<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\TopUpWireRequest;
use App\Http\Requests\GetProvidersByApiRequest;
use App\Http\Resources\Cabinet\API\v1\ProviderAccountResource;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Services\AccountService;
use App\Services\OperationService;

class TopUpWireController extends Controller
{
    /**
     * @OA\Post (
     *     path="/api/operation/topup/wire",
     *     summary="Create Top Up Wire operation",
     *     description="This API call is used to create a new Top Up Wire operation.",
     *     tags={"011. Top Up Wire"},
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
     *                     example="3asf4baf-al68-49f7-la36-86f3eed92947",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="wire type is  TOP UP SEPA = 1; TOP UP SWIFT = 0",
     *                     property="wire_type",
     *                     type="integer",
     *                     example="1",
     *                     enum={"0", "1"}
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
     *                 @OA\Property(
     *                     description="Provider id
                                To get all providers, make API call to /api/providers/{currency}/{country}/{wireType} endpoint",
     *                     property="provider_id",
     *                     type="string",
     *                     example="7msf4baf-al68-49f7-la36-83A3eed92947",
     *                 ),
     *                 required={"wallet_id", "wire_type", "currency", "amount", "provider_id"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  description="If operation was created successfully",
     *                  type="boolean"
     *              ),
     *             @OA\Examples(example="result", value={
     *              "success": true
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
    public function createTopUpWire(TopUpWireRequest $request, OperationService $operationService)
    {
        $cProfile = auth()->user()->cProfile;
        $type = OperationOperationType::API_TOP_UP_WIRE[$request->wire_type];

        /* @var CProfile $cProfile */

        $commission = $cProfile->operationCommission($type, Commissions::TYPE_INCOMING, $request->currency);
        if ($commission && $commission->min_amount > $request->amount) {

            return response()->json([
                'errors' => [
                    'amount' => t('invalid_amount_message_text', [
                        'minAmount' => $commission->min_amount,
                        'currency' => $request->currency,
                    ])
                ]
            ], 422);
        }

        $account = $cProfile->accounts()->find($request->wallet_id);

        if (!$account) {
            return response()->json([
                'errors' => [
                    'wallet_id' => t('wallet_not_found')
                ]
            ], 422);
        }

        $isTypeSwift = in_array($type, OperationOperationType::SWIFT_TYPES);
        $accountType = $isTypeSwift ? AccountType::TYPE_WIRE_SWIFT : AccountType::TYPE_WIRE_SEPA;

        try {
            $providerAccount = Account::getActiveAccountById($request->provider_id);
        } catch (\Exception $exception) {
            return response()->json([
                'errors' => [
                    'provider' => t('account_not_found')
                ]
            ], 422);
        }

        if ($providerAccount->account_type !== $accountType) {
            return response()->json([
                'errors' => [
                    'provider' => t('provider_account_type_mismatch')
                ]
            ], 422);
        }

        $provider_id = $providerAccount->provider->id;

        $cprofileId = $cProfile->id;
        $operation = $operationService->createOperation(
            $cprofileId, $type, $request->amount, $request->currency, $account->currency,
            null, $account->id, OperationStatuses::PENDING,
            $provider_id, null, null, $providerAccount->id
        );

        /* @var CUser $user */
        $user = auth()->user();


        if ($operation->isLimitsVerified()) {
            EmailFacade::sendInvoicePaymentSepaOrSwift($user, $operation->operation_id, OperationOperationType::getName($operation->operation_type), $operation->amount, $operation->from_currency, $operation->id);
        } else {
            EmailFacade::sendVerificationRequestFromTheManager($user, $operation);
        }

        return response()->json([
            'success' => true,
            'id' => $operation->id
        ]);
    }


    /**
     * @OA\Get (
     *     path="/api/providers",
     *     summary="Get providers",
     *     description="This API call is used to get providers.",
     *     tags={"006. Providers"},
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
     *     @OA\Parameter(
     *         description="Country code where provider is available.",
     *         in="query",
     *         name="country",
     *         example="bg",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="wire type is  TOP UP SEPA = 1; TOP UP SWIFT = 2; WITHDRAW WIRE SEPA = 8; WITHDRAW WIRE SWIFT = 9",
     *         in="query",
     *         name="wireType",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           enum={1, 2, 8, 9}
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Default = 1, For fiat = 2",
     *         in="query",
     *         name="fiatType",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           enum={1, 2}
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Currency",
     *         in="query",
     *         name="currency",
     *         example="USD",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *           enum={"USD", "EUR", "GBP"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="providers",
     *                  description="Providers",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of provider",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Status of provider",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Name of provider",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="currency",
     *                      description="Currency of provider account",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="providerLocation",
     *                      description="The country code of the provider location",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="availableCountryCodes",
     *                      description="Country codes where provider is available.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="wireDetails",
     *                      type="object",
     *                      @OA\Property(
     *                          property="accountHolder",
     *                          description="Holder of account",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="accountNumber",
     *                          description="Account number",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="type",
     *                          description="Account type",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="iban",
     *                          description="IBAN",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="swift",
     *                          description="swift",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="bankName",
     *                          description="Name of bank",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="bankAddress",
     *                          description="Address of bank",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="correspondentBank",
     *                          description="Correspondent bank",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="correspondentBankSwift",
     *                          description="Correspondent bank swift",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="intermediaryBank",
     *                          description="Intermediary bank",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="intermediaryBankSwift",
     *                          description="Intermediary bank swift",
     *                          type="string"
     *                      ),
     *                  ),
     *              ),
     *             @OA\Examples(example="result", value={
     *              "providers": {
     *                  {
     *                      "id": "f3ab6d19-4934-4633-6687-ed744b26b5f7",
     *                      "status": "Active",
     *                      "name": "Provider name",
     *                      "currency": "USD",
     *                      "providerLocation": "bg",
     *                      "wireDetails": {
     *                           "accountHolder": "(your beneficiary account name)",
     *                           "accountNumber": "220148",
     *                           "beneficiaryAddress": "P.O. Box 283 42343 Road Rd. New York 87454",
     *                           "type": "Swift",
     *                           "iban": "SD47969000000123280738595",
     *                           "swift": "FE34343R43985",
     *                           "bankName": "Centric Bank",
     *                           "bankAddress": "4320 Linglestown Road, Harrisburg, PA 17112",
     *                           "correspondentBank": "CITIBANK N. A., NEW YORK",
     *                           "correspondentBankSwift": "CITIUS33",
     *                           "intermediaryBank": "",
     *                           "intermediaryBankSwift": ""
     *                       },
     *                      "availableCountryCodes": {
     *                         "bg", "lt", "cz",  "gb", "sk",
     *                         "es", "lu", "cy",  "se", "fr",
     *                         "dk", "ro", "be"
     *                      }
     *                  },
     *                  {
     *                      "id": "f3ab6d19-4934-4633-6687-ed744b26b5f7",
     *                      "status": "Active",
     *                      "name": "Provider name",
     *                      "currency": "USD",
     *                      "providerLocation": "bg",
     *                      "wireDetails": {
     *                           "accountHolder": "(your beneficiary account name)",
     *                           "accountNumber": "2342534",
     *                           "beneficiaryAddress": "P.O. Box 283 1478 Road Rd. New York 9851",
     *                           "type": "Swift",
     *                           "iban": "SD479690000001523452345738595",
     *                           "swift": "FE3t524trt3985",
     *                           "bankName": "Centric Bank USA",
     *                           "bankAddress": "6867 New York, Harrisburg, PA 63534543",
     *                           "correspondentBank": "CITIBANK N. A., NEW YORK",
     *                           "correspondentBankSwift": "CITIUS33",
     *                           "intermediaryBank": "",
     *                           "intermediaryBankSwift": ""
     *                       },
     *                      "availableCountryCodes": {
     *                         "bg", "lt", "cz",  "gb", "sk",
     *                         "es", "lu", "cy",  "se", "fr",
     *                         "dk", "ro", "be"
     *                      }
     *                  }
     *              }
     *              },summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Account not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                              property="accounts",
     *                              type="string",
     *                              description="Accounts not found"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "accounts": "Accounts not found.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Invalid properties",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="currency",
     *                              type="string",
     *                              description="Currency is required",
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "currency": "Currency is required.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getProvidersByCountry(GetProvidersByApiRequest $request, AccountService $accountService)
    {
        $isTypeSwift = in_array($request->wireType, OperationOperationType::SWIFT_TYPES);
        $accountType = $isTypeSwift ? AccountType::TYPE_WIRE_SWIFT : AccountType::TYPE_WIRE_SEPA;

        $cUser = auth()->user();
        $projectId = $cUser->project_id;

        $accounts = $accountService->getPaymentProviderAccounts(
            $accountType,
            $request->country,
            $request->currency,
            $request->wireType,
            $cUser->cProfile->account_type,
            $projectId,
            $request->fiatType
        );
        if (!$accounts) {
            return response()->json([
                "errors" => ['accounts' => t('account_not_found')]
            ], 404);
        }

        return response()->json([
            'accounts' => ProviderAccountResource::collection($accounts)
        ]);
    }

}
