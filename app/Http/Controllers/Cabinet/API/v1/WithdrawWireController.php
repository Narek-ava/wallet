<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\WithdrawWireRequest;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\CryptoAccountDetail;
use App\Models\PaymentProvider;
use App\Services\AccountService;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\TwoFAService;

class WithdrawWireController extends Controller
{

    /**
     * @OA\Post (
     *     path="/api/operation/withdraw/wire",
     *     summary="Create Withdraw Wire operation",
     *     description="This API call is used to create a new Withdraw by Wire operation.",
     *     tags={"013. Withdraw wire"},
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
     * )
     */
    public function withdrawWireOperation(WithdrawWireRequest $request, OperationService $operationService, ComplianceService $complianceService, AccountService $accountService, TwoFAService $twoFAService)
    {
        /* @var CUser $cUser */
        $cUser = auth()->user();

        /* @var CProfile $cProfile */
        $cProfile = $cUser->cProfile;

        /* @var Account $account */
        $account = $cProfile->accounts()->find($request->from_wallet);

        /* @var CryptoAccountDetail $cryptoAccountDetail */
        $cryptoAccountDetail = $account->cryptoAccountDetail;

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

        $operationType = OperationOperationType::API_WITHDRAW_WIRE[$request->wire_type];
        $isTypeSwift = in_array($operationType, OperationOperationType::SWIFT_TYPES);
        $accountType = $isTypeSwift ? AccountType::TYPE_WIRE_SWIFT : AccountType::TYPE_WIRE_SEPA;


        $providerAccount = $accountService->getPaymentProviderById($request->provider_id, $accountType, $toAccount->country, $toAccount->currency, $operationType, auth()->user()->cProfile->account_type);
        if (!$providerAccount) {
            $isAnyProviderWithGivenId = Account::where('id', $request->provider_id)->exists();
            return response()->json([
                'errors' => [
                    'provider' => $isAnyProviderWithGivenId ? t('invalid_provider_account_provided') : t('provider_account_not_found')
                ]
            ], 422);
        }

        if($cUser->two_fa_type && !($twoFAService->verifyToken($request->twoFaToken, $cUser))){
            return response()->json([
                'errors' => [
                    'wrong_token' => t('error_2fa_operation_wrong_token')
                ]
            ], 422);
        }

        /* @var PaymentProvider $provider */
        $provider = $providerAccount->provider;

        $operation = $operationService->createOperation(
            $cProfile->id, $operationType, $request->amount, $cryptoAccountDetail->coin, $toAccount->currency, $account->id, $toAccount->id, OperationStatuses::PENDING,
            $provider->id, null, null, $providerAccount->id);

        if ($operationService->getCurrentMonthOperationsAmountSum($cUser->cProfile) < $cUser->limit->monthly_amount_max) {
            EmailFacade::sendmailSuccessfulSepaSwiftWithdrawalApplication($operation);
        } else {
            $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
            EmailFacade::sendVerificationRequestFromTheManager($cUser, $operation);
        }

        if (!$operation) {
            return response()->json([
                'errors' => [
                    'withdrawal_crypto_fail' => t('withdrawal_crypto_fail')
                ]
            ], 422);
        }

        return response()->json([
            'success' => true
        ]);
    }

}
