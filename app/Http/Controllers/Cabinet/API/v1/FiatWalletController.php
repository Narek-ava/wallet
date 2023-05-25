<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Enums\Commissions;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\BuyCryptoFromFiatRequest;
use App\Http\Requests\API\v1\BuyFiatFromCryptoRequest;
use App\Http\Requests\API\v1\FiatTopUpWireRequest;
use App\Http\Requests\API\v1\FiatWithdrawWireRequest;
use App\Http\Requests\API\v1\TopUpWireRequest;
use App\Http\Requests\API\v1\WithdrawWireRequest;
use App\Http\Requests\FiatWalletRequest;
use App\Http\Requests\WithdrawToFiatRequest;
use App\Http\Resources\Cabinet\API\v1\AccountResource;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\CryptoAccountDetail;
use App\Models\PaymentProvider;
use App\Services\AccountService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\TwoFAService;
use App\Services\WalletService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Throwable;
use App\Models\Account;

class FiatWalletController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/fiat-wallets",
     *     summary="Get fiat wallets",
     *     description="This API call is used to get user fiat wallets",
     *     tags={"019. Fiat Wallets"},
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
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="wallets",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of wallet",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="accountId",
     *                      description="Number of account",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Account status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Account name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="currency",
     *                      description="Account currency",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="country",
     *                      description="Wallet country",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="balance",
     *                      description="Wallet balance",
     *                      type="number"
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Account creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Account update date",
     *                      type="string"
     *                  ),
     *              ),
     *             @OA\Examples(example="result", value={
     *              "wallets": {
     *                  {
     *                     "id": "ce3a2e23-c5e9-47c6-be94-ff831ae4ea59",
     *                     "accountId": 939,
     *                     "status": "Active",
     *                     "name": "Sky Man GBP",
     *                     "currency": "GBP",
     *                     "country": null,
     *                     "balance": 9871.3,
     *                     "createdAt": "2022-12-16 07:46:23",
     *                     "updatedAt": "2023-01-10 13:54:04"
     *                  },
     *                  {
     *                      "id": "fb04694f-e512-4db1-a3ed-06264e1ac830",
     *                      "accountId": 929,
     *                      "status": "Active",
     *                      "name": "Vopi Vopiman USD",
     *                      "currency": "USD",
     *                      "country": null,
     *                      "balance": 99,
     *                      "createdAt": "2022-12-07 12:27:31",
     *                      "updatedAt": "2023-01-10 13:54:04"
     *                  },
     *              }
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     * )
     * @param WalletService $walletService
     * @return \Illuminate\Http\JsonResponse
     */
    public function wallets(WalletService $walletService)
    {
        $cProfile = getCProfile();
        $fiatWallets = $walletService->getFiatWallets($cProfile);
        return response()->json([
            'wallets' => AccountResource::collection($fiatWallets, function (AccountResource $resource) {
                $resource->setWithAnyRelation(false);
                $resource->setWalletBlockedStatus(false);
            }),
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/fiat-wallet/{id}",
     *     summary="Get fiat wallet",
     *     description="This API call is used to get the fiat wallet",
     *     tags={"019. Fiat Wallets"},
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
     *         description="Id of the wallet",
     *         in="path",
     *         name="id",
     *         example ="s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *         required=true,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="wallet",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of wallet",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="accountId",
     *                      description="Number of account",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Account status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Account name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="currency",
     *                      description="Account currency",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="country",
     *                      description="Wallet country",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="balance",
     *                      description="Wallet balance",
     *                      type="number"
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Account creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Account update date",
     *                      type="string"
     *                  ),
     *              ),
     *             @OA\Examples(example="result", value={
     *              "wallet": {
     *                      "id": "fb04694f-e512-4db1-a3ed-06264e1ac830",
     *                      "accountId": 929,
     *                      "status": "Active",
     *                      "name": "Vopi Vopiman USD",
     *                      "currency": "USD",
     *                      "country": null,
     *                      "balance": 99,
     *                      "createdAt": "2022-12-07 12:27:31",
     *                      "updatedAt": "2023-01-10 14:06:52"
     *                  },
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Wallet not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                              property="wallet",
     *                              type="string",
     *                              description="Wallet not found"
     *                         ),
     *                     ),
     *                      example={
     *                       "errors" : {"wallet": "Wallet not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id, WalletService $walletService)
    {
        $cProfile = auth()->user()->cProfile;
        $account = $walletService->getFiatWallet($cProfile, $id);

        if (!$account) {
            return response()->json([
                "errors" => ['wallet' => t('wallet_not_found'),]
            ], 404);
        }

        return response()->json([
            'wallet' => (new AccountResource($account))->setWalletBlockedStatus(false)->setWithAnyRelation(false),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/fiat-wallet",
     *     summary="Create wallet",
     *     description="This API call is used to create a new fiat wallet.",
     *     tags={"019. Fiat Wallets"},
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
     *                     description="Currency for new wallet. USD, EUR, GBP, AUD, CAD",
     *                     property="currency",
     *                     type="string",
     *                     example="USD",
     *                     enum={"USD", "EUR", "GBP", "AUD", "CAD"}
     *                 ),
     *                 required={"currency"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="wallet",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of wallet",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="accountId",
     *                      description="Number of account",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Account status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="name",
     *                      description="Account name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="currency",
     *                      description="Account currency",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="balance",
     *                      description="Wallet balance",
     *                      type="number"
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Account creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Account update date",
     *                      type="string"
     *                  ),
     *              ),
     *             @OA\Examples(example="result", value={
     *              "wallet": {
     *                      "id": "649a6b2e-be55-42a7-ae61-b5cf61b048c4",
     *                      "accountId": 954,
     *                      "status": "Active",
     *                      "name": "Sky Man CAD",
     *                      "currency": "CAD",
     *                      "country": null,
     *                      "balance": 0,
     *                      "createdAt": "2023-01-10 15:48:03",
     *                      "updatedAt": "2023-01-10 15:48:03"
     *                  },
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Wallet is not added",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Validation errors",
     *                         @OA\Property(
     *                              property="currency",
     *                              type="string",
     *                              description="The currency field is required.",
     *                         ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "currency": "The currency field is required.",
     *                          }
     *                     }
     *                 ),
     *             ),
     *         }
     *     ),
     *  )
     * @param FiatWalletRequest $request
     * @param WalletService $walletService
     * @return \Illuminate\Http\JsonResponse
     */
    public function addWallet(FiatWalletRequest $request, WalletService $walletService)
    {
        $cProfile = getCProfile();
        $addedAccount = $walletService->createFiatWallet($cProfile, $request->currency);

        $addedAccount->refresh();

        return response()->json([
            'wallet' => (new AccountResource($addedAccount))->setWalletBlockedStatus(false)->setWithAnyRelation(false),
        ]);
    }


    /**
     * @OA\Post (
     *     path="/api/fiat-wallet/topup-wire",
     *     summary="Create Top Up Wire operation",
     *     description="This API call is used to create a new Top Up Wire operation.",
     *     tags={"019. Fiat Wallets"},
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
     *              @OA\Property(
     *                  property="id",
     *                  description="Operation ID",
     *                  type="string"
     *              ),
     *             @OA\Examples(example="result", value={
     *              "success": true, "id": "7msf4baf-al68-49f7-la36-83A3eed92947"
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
     *     @OA\Response(
     *         response=403,
     *         description="Wallet not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="wallet_id",
     *                              type="string",
     *                              description="Wallet not found"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "wallet_id": "Wallet not found."
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Incorrect provider account type",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="provider",
     *                              type="string",
     *                              description="Incorrect provider account type"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "provider": "Incorrect provider account type."
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function createTopUpByWire(FiatTopUpWireRequest $request, OperationService $operationService, WalletService $walletService)
    {
        $cProfile = getCProfile();
        $wireType = OperationOperationType::API_TOP_UP_WIRE[$request->wire_type];

        /* @var CProfile $cProfile */

        try {
            $account = $walletService->getFiatWallet($cProfile, $request->wallet_id);
        } catch (Throwable $exception) {
            return response()->json([
                'errors' => [
                    'wallet_id' => t('wallet_not_found')
                ]
            ], 422);
        }

        try {
            $providerAccount = Account::getActiveAccountById($request->provider_id);
        } catch (Exception $exception) {
            return response()->json([
                'errors' => [
                    'provider' => t('account_not_found')
                ]
            ], 422);
        }

        if ($providerAccount->fiat_type !== AccountType::PAYMENT_PROVIDER_FIAT_TYPE_FIAT) {
            return response()->json([
                'errors' => [
                    'provider' => t('provider_account_type_mismatch')
                ]
            ], 422);
        }

        $provider_id = $providerAccount->provider->id;

        /* @var CUser $user */
        $user = auth()->user();

        $operation = $operationService->createOperation(
            $cProfile->id, OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE, $request->amount, $account->currency, $account->currency,
            null, $account->id, OperationStatuses::PENDING,
            $provider_id, null , null, $providerAccount->id, $cProfile->cUser->project->id
        );

        $operation->additional_data = json_encode([
            'payment_method' => $wireType,
        ]);

        $operation->save();

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
     * @OA\Post (
     *     path="/api/fiat-wallet/withdraw/wire",
     *     summary="Create Fiat Wallet Withdraw Wire operation",
     *     description="This API call is used to create a new Withdraw by Wire operation.",
     *     tags={"019. Fiat Wallets"},
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
     *                     description="Internal wallet id for withdraw operation.
    To get all wallets, make API call to /api/wallets endpoint",
     *                     property="from_wallet",
     *                     type="string",
     *			           example="3asf4baf-al68-49f7-la36-86f3eed92947"
     *                 ),
     *                 @OA\Property(
     *                     description="Operation amount",
     *                     property="amount",
     *                     type="number",
     *			           example=0.5
     *                 ),
     *                 @OA\Property(
     *                     description="Clinet external wire account id for withdraw operation.
    To get all wire accounts make API call to /api/accounts/wire endpoint
    or to create a new account make API call to /api/account/wire endpoint",
     *                     property="to_wire_account",
     *                     type="string",
     *			           example="s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                 ),
     *                 @OA\Property(
     *                     description="wire type is  WITHDRAW WIRE SEPA = 1; WITHDRAW WIRE SWIFT = 0",
     *                     property="wire_type",
     *                     type="integer",
     *			           example="1",
     *                     enum={"0", "1"}
     *                 ),
     *                 @OA\Property(
     *                     description="To get available providers, make API call to /api/providers/{currency}/{country}/{wireType} endpoint. Currency and country should be same as in wire account.",
     *                     property="provider_id",
     *                     type="string",
     *			           example="7msf4baf-al68-49f7-la36-83A3eed92947",
     *                 ),
     *                @OA\Property(
     *                     description="This parameter is needed for 2FA verification. It is required, if the client's 2FA enabled. For get it, at first call /api/2fa/create endpoint.",
     *                     property="twoFaToken",
     *                     type="string",
     *			           example="JLfsSaXcjHwyHC3tejl3KAJxOWKXOIZIFQrelxFgfDj4loUYYkn4HtK2EvnL"
     *                 ),
     *                 required={"from_wallet", "amount", "to_wire_account", "wire_type", "provider_id"},
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
     *              @OA\Property(
     *                  property="id",
     *                  description="Operation ID",
     *                  type="string"
     *              ),
     *             @OA\Examples(example="result", value={
     *              "success": true, "id": "3asf4baf-al68-49f7-la36-86f3eed92947"
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
     *                              description="The amount must be a number"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "amount": "The amount must be a number.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Operation is failed",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="withdrawal_crypto_fail",
     *                              type="string",
     *                              description="Operation is failed"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "withdrawal_crypto_fail": "Operation is failed, please contact to Support!",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="provider",
     *                              type="string",
     *                              description="Account not found"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "provider": "Liquidity provider account not found",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function withdrawWireOperation(FiatWithdrawWireRequest $request, WalletService $walletService, OperationService $operationService, ComplianceService $complianceService, AccountService $accountService, TwoFAService $twoFAService)
    {
        /* @var CUser $cUser */
        $cUser = auth()->user();

        /* @var CProfile $cProfile */
        $cProfile = $cUser->cProfile;

        /* @var Account $account */
        $account = $walletService->getFiatWallet($cProfile, $request->from_wallet);

        /* @var Account $toAccount */
        $toAccount = $cProfile->accounts()->findOrFail($request->to_wire_account);


        $validator = $account->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json([
                'errors' => array_map(function ($error) {
                    return $error[0] ?? $error;
                }, $errors)
            ], 422);
        }

        $wireType = OperationOperationType::API_WITHDRAW_WIRE[$request->wire_type];
        $isTypeSwift = in_array($wireType, OperationOperationType::SWIFT_TYPES);
        $accountType = $isTypeSwift ? AccountType::TYPE_WIRE_SWIFT : AccountType::TYPE_WIRE_SEPA;


        $providerAccount = $accountService->getPaymentProviderById(
            $request->provider_id,
            $accountType,
            $toAccount->country,
            $toAccount->currency,
            OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET,
            $cProfile->account_type,
            $cUser->project_id,
            AccountType::PAYMENT_PROVIDER_FIAT_TYPE_FIAT
        );
        if (!$providerAccount) {
            $isAnyProviderWithGivenId = Account::where('id', $request->provider_id)->exists();
            return response()->json([
                'errors' => [
                    'provider' => $isAnyProviderWithGivenId ? t('invalid_provider_account_provided') : t('provider_account_not_found')
                ]
            ], 422);
        }

        if ($cUser->two_fa_type && !($twoFAService->verifyToken($request->twoFaToken, $cUser))) {
            return response()->json([
                'errors' => [
                    'wrong_token' => t('error_2fa_operation_wrong_token')
                ]
            ], 422);
        }

        /* @var PaymentProvider $provider */
        $provider = $providerAccount->provider;

        $operation = $operationService->createOperation(
            $cProfile->id, OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET, $request->amount, $account->currency, $account->currency, $account->id, $toAccount->id, OperationStatuses::PENDING,
            $provider->id, null, null, $providerAccount->id, $cUser->project->id);

        $operation->additional_data = json_encode([
            'payment_method' => $wireType,
        ]);

        $operation->save();

        if ($operation->isLimitsVerified()) {
            EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
        } else {
            $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
        }

        if (!$operation) {
            return response()->json([
                'errors' => [
                    'withdrawal_crypto_fail' => t('withdrawal_crypto_fail')
                ]
            ], 422);
        }

        return response()->json([
            'success' => true,
            'id' => $operation->id
        ]);
    }

    /**
     * @OA\Post (
     *     path="/api/fiat-wallet/buy-fiat-from-crypto",
     *     summary="Create Buy fiat from crypto operation",
     *     description="This API call is used to buy a fiat from crypto operation.",
     *     tags={"019. Fiat Wallets"},
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
     *                     description="Fiat wallet id for buy operation.
    To get all fiat wallets, make API call to /api/fiat-wallets endpoint",
     *                     property="fiat_wallet_id",
     *                     type="string",
     *			           example="3asf4baf-al68-49f7-la36-86f3eed92947"
     *                 ),
     *                 @OA\Property(
     *                     description="Operation amount",
     *                     property="amount",
     *                     type="number",
     *			           example=0.5
     *                 ),
     *                 @OA\Property(
     *                     description="Clinet crypto account id.
    To get all crypto accounts make GET API call to /api/accounts/crypto endpoint
    or to create a new account make POST API call to /api/account/crypto endpoint",
     *                     property="crypto_account_id",
     *                     type="string",
     *			           example="s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                 ),
     *                @OA\Property(
     *                     description="This parameter is needed for 2FA verification. It is required, if the client's 2FA enabled. For get it, at first call /api/2fa/create endpoint.",
     *                     property="twoFaToken",
     *                     type="string",
     *			           example="JLfsSaXcjHwyHC3tejl3KAJxOWKXOIZIFQrelxFgfDj4loUYYkn4HtK2EvnL"
     *                 ),
     *                 required={"fiat_wallet_id", "amount", "crypto_account_id"},
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
     *              @OA\Property(
     *                  property="id",
     *                  description="Operation ID",
     *                  type="string"
     *              ),
     *             @OA\Examples(example="result", value={
     *              "success": true, "id": "3asf4baf-al68-49f7-la36-86f3eed92947"
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
     *                              description="The amount must be a number"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "amount": "The amount must be a number.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Operation is failed",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="buy_fiat_fail",
     *                              type="string",
     *                              description="Operation is failed"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "buy_fiat_fail": "Operation is failed, please contact to Support!",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Account not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="provider",
     *                              type="string",
     *                              description="Account not found"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "provider": "Liquidity provider account not found",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function createBuyFiatFromCryptoOperation(BuyFiatFromCryptoRequest $request, WalletService $walletService, OperationService $operationService, ComplianceService $complianceService, AccountService $accountService, TwoFAService $twoFAService)
    {
        $cUser = Auth::user();
        $cProfile = getCProfile();
        /* @var  Account $account */
        $account = $cProfile->accounts()->findOrFail($request->crypto_account_id);

        $cryptoAccountDetail = $account->cryptoAccountDetail;

        $validator = $account->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json([
                'errors' => array_map(function ($error) {
                    return $error[0] ?? $error;
                }, $errors)
            ], 422);
        }

        /* @var Account $fiatWallet */
        $fiatWallet = $walletService->getFiatWallet($cProfile, $request->fiat_wallet_id);

        if ($cUser->two_fa_type && !($twoFAService->verifyToken($request->twoFaToken, $cUser))) {
            return response()->json([
                'errors' => [
                    'wrong_token' => t('error_2fa_operation_wrong_token')
                ]
            ], 422);
        }

        $operation = $operationService->createOperation(
            $cProfile->id,
            OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO,
            $request->amount,
            $cryptoAccountDetail->coin,
            $fiatWallet->currency,
            $cryptoAccountDetail->account->id,
            $fiatWallet->id,
            OperationStatuses::PENDING,
            $request->bank_detail,
            null,
            $request->operation_id,
            $request->provider_account_id,
            $cUser->project->id
        );

        if ($operation->isLimitsVerified()) {
            EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
        } else {
            $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
        }

        return response()->json([
            'success' => true,
            'id' => $operation->id
        ]);
    }

    /**
     * @OA\Post (
     *     path="/api/fiat-wallet/buy-crypto-from-fiat",
     *     summary="Create Buy crypto from fiat operation",
     *     description="This API call is used to buy a crypto by fiat operation.",
     *     tags={"019. Fiat Wallets"},
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
     *                     description="Fiat wallet id.
    To get all fiat wallets, make API call to /api/fiat-wallets endpoint",
     *                     property="fiat_wallet_id",
     *                     type="string",
     *			           example="3asf4baf-al68-49f7-la36-86f3eed92947"
     *                 ),
     *                 @OA\Property(
     *                     description="Operation amount",
     *                     property="amount",
     *                     type="number",
     *			           example=0.5
     *                 ),
     *                 @OA\Property(
     *                     description="Clinet crypto account id.
    To get all crypto accounts make GET API call to /api/accounts/crypto endpoint
    or to create a new account make POST API call to /api/account/crypto endpoint",
     *                     property="crypto_account_id",
     *                     type="string",
     *			           example="s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                 ),
     *                @OA\Property(
     *                     description="This parameter is needed for 2FA verification. It is required, if the client's 2FA enabled. For get it, at first call /api/2fa/create endpoint.",
     *                     property="twoFaToken",
     *                     type="string",
     *			           example="JLfsSaXcjHwyHC3tejl3KAJxOWKXOIZIFQrelxFgfDj4loUYYkn4HtK2EvnL"
     *                 ),
     *                 required={"fiat_wallet_id", "amount", "crypto_account_id"},
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
     *              @OA\Property(
     *                  property="id",
     *                  description="Operation ID",
     *                  type="string"
     *              ),
     *             @OA\Examples(example="result", value={
     *              "success": true, "id": "3asf4baf-al68-49f7-la36-86f3eed92947"
     *              },summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
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
     *                              description="The amount must be a number"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "amount": "The amount must be a number.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Operation is failed",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="buy_crypto_fail",
     *                              type="string",
     *                              description="Operation is failed"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "buy_crypto_fail": "Operation is failed, please contact to Support!",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Account not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="wrong_token",
     *                              type="string",
     *                              description="Account not found"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "wrong_token": "Unable to verify 2FA authentication. Please, contact to your support.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function createBuyCryptoFromFiatOperation(BuyCryptoFromFiatRequest $request, WalletService $walletService, OperationService $operationService, ComplianceService $complianceService, AccountService $accountService, TwoFAService $twoFAService)
    {
        $cUser = Auth::user();
        $cProfile = getCProfile();
        /* @var  Account $cryptoAccount */
        $cryptoAccount = $cProfile->accounts()->findOrFail($request->crypto_account_id);
        /* @var Account $fiatWallet */
        $fiatWallet = $walletService->getFiatWallet($cProfile, $request->fiat_wallet_id);

        $cryptoAccountDetail = $cryptoAccount->cryptoAccountDetail;

        $validator = $fiatWallet->amountValidator($request->amount);
        if ($validator && $validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json([
                'errors' => array_map(function ($error) {
                    return $error[0] ?? $error;
                }, $errors)
            ], 422);
        }



        if ($cUser->two_fa_type && !($twoFAService->verifyToken($request->twoFaToken, $cUser))) {
            return response()->json([
                'errors' => [
                    'wrong_token' => t('error_2fa_operation_wrong_token')
                ]
            ], 422);
        }

        $operation = $operationService->createOperation(
            $cProfile->id, OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT, $request->amount,
            $fiatWallet->currency, $cryptoAccountDetail->coin,
            $fiatWallet->id, $cryptoAccount->id, OperationStatuses::PENDING,
            null, null , $request->operation_id, null, $cUser->project->id,
        );

        if ($operation->isLimitsVerified()) {
            EmailFacade::sendNewTopUpCardOperationMessage($operation);
        } else {
            EmailFacade::sendVerificationRequestFromTheManager($cUser, $operation);
        }

        return response()->json([
            'success' => true,
            'id' => $operation->id
        ]);
    }

}
