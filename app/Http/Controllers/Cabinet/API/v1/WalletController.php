<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\AddWalletRequest;
use App\Http\Resources\Cabinet\API\v1\AccountResource;
use App\Services\BitGOAPIService;
use App\Services\WalletService;

class WalletController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/wallets",
     *     summary="Get wallets",
     *     description="This API call is used to get user wallets",
     *     tags={"002. Wallets"},
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
     *                      property="isBlocked",
     *                      description="Wallet block status",
     *                      type="boolean"
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
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "isBlocked": true,
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *                  {
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 316,
     *                      "status": "Active",
     *                      "name": "Good Invest LTC",
     *                      "currency": "LTC",
     *                      "isBlocked": false,
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *              }
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     * )
     */
    public function wallets()
    {
        $cProfile = auth()->user()->cProfile;
        $accounts = $cProfile->accounts()
            ->where('is_external', '!=', AccountType::ACCOUNT_EXTERNAL)
            ->where('account_type', AccountType::TYPE_CRYPTO)
            ->whereHas('cryptoAccountDetail')->get();

        return response()->json([
            'wallets' => AccountResource::collection($accounts, function (AccountResource $resource) {
                $resource->setWithAnyRelation(false);
                $resource->setWalletBlockedStatus(true);
            }),
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/wallet/{id}",
     *     summary="Get wallet",
     *     description="This API call is used to get the wallet",
     *     tags={"002. Wallets"},
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
     *                      property="cryptoDetails",
     *                      type="object",
     *                      @OA\Property(
     *                          property="label",
     *                          description="Wallet name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="address",
     *                          description="Wallet address",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="isBlocked",
     *                      description="Wallet block status",
     *                      type="boolean"
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
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "cryptoDetails": {
     *                          "label": "Hoper Invest BCH",
     *                          "address": "4Aty6cyHl2spu5OifAHtV6cQTUWdeqhmg4",
     *                      },
     *                      "isBlocked": true,
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
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
     */
    public function show(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        $account = $cProfile->accounts()->where('id', $id)->first();

        if (!$account) {
            return response()->json([
                "errors" => ['wallet' => t('wallet_not_found'),]
            ], 404);
        }

        return response()->json([
            'wallet' => (new AccountResource($account))->setWalletBlockedStatus(true),
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/wallet",
     *     summary="Create wallet",
     *     description="This API call is used to create a new wallet.",
     *     tags={"002. Wallets"},
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
     *                     description="Coin for new wallet. BTC LTC or BCH",
     *                     property="cryptocurrency",
     *                     type="string",
     *                     example="BTC",
     *                     enum={"BTC", "LTC", "BCH"}
     *                 ),
     *                 required={"cryptocurrency"},
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
     *                      property="cryptoDetails",
     *                      type="object",
     *                      @OA\Property(
     *                          property="label",
     *                          description="Wallet name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="address",
     *                          description="Wallet address",
     *                          type="string"
     *                      ),
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
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "cryptoDetails": {
     *                          "label": "Hoper Invest BCH",
     *                          "address": "4Aty6cyHl2spu5OifAHtV6cQTUWdeqhmg4",
     *                      },
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
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
     *                              property="cryptocurrency",
     *                              type="string",
     *                              description="The cryptocurrency field is required.",
     *                         ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "cryptocurrency": "The cryptocurrency field is required.",
     *                          }
     *                     }
     *                 ),
     *             ),
     *         }
     *     ),
     *  )
     */
    public function addWallet(AddWalletRequest $request, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {
        $addedAccount = $walletService->addNewWallet($bitGOAPIService, $request->cryptocurrency, auth()->user()->cProfile);

        if (!$addedAccount) {
            return response()->json([
                'errors' => [
                    'wallet' => t('wallet_not_added'),
                ]
            ], 422);
        }

        $addedAccount->refresh();

        return response()->json([
            'wallet' => new AccountResource($addedAccount),
        ]);
    }

}
