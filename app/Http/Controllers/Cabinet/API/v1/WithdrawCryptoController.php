<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\DataObjects\OperationTransactionData;
use App\Enums\OperationSubStatuses;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\WithdrawCryptoRequest;
use App\Models\Account;
use App\Models\Cabinet\CUser;
use App\Models\CryptoAccountDetail;
use App\Operations\WithdrawCrypto;
use App\Services\ComplianceService;
use App\Services\TwoFAService;
use App\Services\WithdrawCryptoService;
use Illuminate\Http\Request;

class WithdrawCryptoController extends Controller
{

    /**
     * @OA\Post (
     *     path="/api/operation/withdraw/crypto",
     *     summary="Create Withdraw Crypto operation",
     *     description="This API call is used to create a new Withdraw Crypto operation.",
     *     tags={"012. Withdraw crypto"},
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
     *                     description="This parameter is needed for 2FA verification. It is required, if the client's 2FA enabled. For get it, at first call /api/2fa/create endpoint.",
     *                     property="twoFaToken",
     *                     type="string",
     *			           example="JLfsSaXcjHwyHC3tejl3KAJxOWKXOIZIFQrelxFgfDj4loUYYkn4HtK2EvnL"
     *                 ),
     *                 @OA\Property(
     *                     description="Clinet external account id for withdraw operation.
                                To get all crypto accounts, make API call to /api/accounts/crypto endpoint
                                or to create a new account,  make API call to /api/account/crypto endpoint",
     *                     property="to_crypto_account",
     *                     type="string",
     *			           example="s4a8s4a7-4848-5262-s45d-296b1bd288e1"
     *                 ),
     *                 required={"from_wallet", "amount", "to_crypto_account"},

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
     *                              description="The amount may not be greater than 10"
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "amount": "The amount may not be greater than 10."
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function withdrawalPost(WithdrawCryptoRequest $request, WithdrawCryptoService $sendCryptoService, ComplianceService $complianceService, TwoFAService $twoFAService)
    {
        logger()->error('withdrawalPost', $request->all());

        $cUser = auth()->user();
        /* @var CUser $cUser */

        $withdrawalRequest = $request->all();

        $fromAccount = $cUser->cProfile->accounts()->find($withdrawalRequest['from_wallet']);

        $toAccount = $cUser->cProfile->accounts()->find($withdrawalRequest['to_crypto_account']);

        if ($fromAccount->currency != $toAccount->currency) {
            return response()->json([
                'errors' => [
                    'to_crypto_account' => t('withdrawal_account_invalid_currency'),
                ]
            ], 422);
        }

        $fromCryptoAccount = $fromAccount->cryptoAccountDetail;
        /* @var CryptoAccountDetail $fromCryptoAccount*/

        $validator = $fromAccount->amountValidator($withdrawalRequest['amount']);

        if ($validator && $validator->fails()) {
            logger()->error('withdrawalPostValidationFail', $withdrawalRequest);
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


        $operation = $sendCryptoService->createOperation($cUser->cProfile, $withdrawalRequest, $fromCryptoAccount, $toAccount->cryptoAccountDetail, $fromAccount, $toAccount);

        if ($operation->isLimitsVerified()) {
            $operationData = new OperationTransactionData([
                'date' => date('Y-m-d'),
                'transaction_type' => TransactionType::CRYPTO_TRX,
                'from_type' => Providers::CLIENT,
                'to_type' => Providers::CLIENT,
                'from_currency' => $operation->from_currency,
                'from_account' => $operation->from_account,
                'to_account' => $operation->to_account,
                'currency_amount' => $operation->amount
            ]);
            try {
                $withdrawCrypto = new WithdrawCrypto($operation, $operationData);
                $withdrawCrypto->execute();
            } catch (\Exception $exception) {
                logger()->error('WithdrawByCryptoErrorAPI.', [
                    'operationId' => $operation->id,
                    'message' => $exception->getMessage()
                ]);

                $operation->substatus = OperationSubStatuses::RUNTIME_ERROR;
                $operation->error_message = $exception->getMessage();
                $operation->save();

                return response()->json([
                    'errors' => [
                        'crypto_error' => t('withdraw_crypto_error_message')
                    ]
                ], 422);
            }
        } else {
            $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cUser->cProfile);
//            EmailFacade::sendVerificationRequestFromTheManager(auth()->user(), $operation);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
