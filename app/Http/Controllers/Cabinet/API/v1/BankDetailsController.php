<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\AccountStatuses;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankDetailRequest;
use App\Http\Requests\BankDetailUpdateRequest;
use App\Http\Requests\Cabinet\DeleteAccountRequest;
use App\Http\Requests\CheckWalletAddressRequest;
use App\Http\Requests\GetWireAccountsApiRequest;
use App\Http\Resources\Cabinet\API\v1\AccountResource;
use App\Models\Cabinet\CUser;
use App\Services\AccountService;
use App\Services\OperationService;

class BankDetailsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/accounts/wire",
     *     summary="Get all wire accounts",
     *     description="This API call is used to get all wire accounts",
     *     tags={"004. Bank details"},
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
     *         description="Account Country code.",
     *         in="query",
     *         name="country",
     *         example="bg",
     *         required=false,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="account type is SWIFT = 0; SEPA = 1;",
     *         in="query",
     *         name="accountType",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           enum={0, 1}
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Currency",
     *         in="query",
     *         name="currency",
     *         example="USD",
     *         required=false,
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
     *                  property="accounts",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of account",
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
     *              "accounts": {
     *                  {
     *                      "id": "s4a8s4a7-9948-5262-s45d-296b1b3588e1",
     *                      "accountId": 213,
     *                      "status": "Active",
     *                      "name": "Good Invest USD",
     *                      "currency": "USD",
     *                      "country": "bg",
     *                      "wireDetails": {
     *                           "accountHolder": "(your beneficiary account name)",
     *                           "accountNumber": "220148",
     *                           "beneficiaryAddress": "P.O. Box 283 42343 Road Rd. New York 87454",
     *                           "type": "SWIFT",
     *                           "iban": "SD47969000000123280738595",
     *                           "swift": "FE34343R43985",
     *                           "bankName": "Centric Bank",
     *                           "bankAddress": "4320 Linglestown Road, Harrisburg, PA 17112",
     *                           "correspondentBank": "CITIBANK N. A., NEW YORK",
     *                           "correspondentBankSwift": "CITIUS33",
     *                           "intermediaryBank": "",
     *                           "intermediaryBankSwift": ""
     *                       },
     *                      "balance": 1000,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *                  {
     *                      "id": "m4a8s4a7-9948-5262-s45d-296b1a3588e1",
     *                      "accountId": 214,
     *                      "status": "Active",
     *                      "name": "Good Invest USD",
     *                      "currency": "USD",
     *                      "country": "bg",
     *                      "wireDetails": {
     *                           "accountHolder": "(your beneficiary account name)",
     *                           "accountNumber": "220148",
     *                           "beneficiaryAddress": "P.O. Box 283 42343 Road Rd. New York 87454",
     *                           "type": "SWIFT",
     *                           "iban": "SD47969000000123280738595",
     *                           "swift": "FE34343R43985",
     *                           "bankName": "Centric Bank",
     *                           "bankAddress": "4320 Linglestown Road, Harrisburg, PA 17112",
     *                           "correspondentBank": "CITIBANK N. A., NEW YORK",
     *                           "correspondentBankSwift": "CITIUS33",
     *                           "intermediaryBank": "",
     *                           "intermediaryBankSwift": ""
     *                       },
     *                      "balance": 1000,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *              }
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="404",
     *         description="Accounts not found",
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
     *                         ),
     *                     ),
     *                      example={
     *                       "errors" : {"accounts": "Accounts not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getWireAccounts(GetWireAccountsApiRequest $request, AccountService $accountService)
    {
        $accounts = $accountService->getUserBankAccountsByCProfileId(auth()->user()->cProfile->id, $request->validated());

        if (!$accounts) {
            return response()->json([
                "errors" => ['accounts' => t('account_not_found')]
            ], 404);
        }
        return response()->json([
            'accounts' => AccountResource::collection($accounts, function (AccountResource $resource) {
                $resource->setWithWireDetails(true);
            }),
        ]);

    }

    /**
     * @OA\Get(
     *     path="/api/accounts/crypto",
     *     summary="Get all external crypto accounts",
     *     description="This API call is used to get all external crypto accounts",
     *     tags={"003. Crypto wallets"},
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
     *                  property="accounts",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of account",
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
     *                      @OA\Property(
     *                          property="verifiedAt",
     *                          description="Crypto account verification date",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="expiry",
     *                          description="Crypto account expiry days",
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
     *              "accounts": {
     *                  {
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "country": "bg",
     *                      "cryptoDetails": {
     *                          "label": "Hoper Invest BCH",
     *                          "address": "4Aty6cyHl2spu5OifAHtV6cQTUWdeqhmg4",
     *                          "verifiedAt": "2021-09-14 18:40:00",
     *                          "expiry": "25 days"
     *                      },
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *                  {
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "country": "bg",
     *                      "cryptoDetails": {
     *                          "label": "Hoper Invest BCH",
     *                          "address": "4Aty6cyHl2spu5OifAHtV6cQTUWdeqhmg4",
     *                          "verifiedAt": "2021-09-14 18:40:00",
     *                          "expiry": "21 days"
     *                      },
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *              }
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Accounts not found",
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
     *                         ),
     *                     ),
     *                      example={
     *                        "errors" : {"accounts": "Accounts not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getCryptoAccounts(AccountService $accountService)
    {
        $accounts = $accountService->getUserCryptoAccountsByCProfileId(auth()->user()->cProfile->id);
        if (!$accounts) {
            return response()->json([
                "errors" => ['accounts' => t('account_not_found')]
            ], 404);
        }
        return response()->json([
            'accounts' => AccountResource::collection($accounts)
        ]);

    }


    /**
     * @OA\Post(
     *     path="/api/account/wire",
     *     summary="Create wire account",
     *     description="This API call is used to create a new wire account. ",
     *     tags={"004. Bank details"},
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
     *                     description="Bank template name",
     *                     property="template_name",
     *                     type="string",
     *                     example="Good Invest USD",
     *                 ),
     *                 @OA\Property(
     *                     description="Account country code",
     *                     property="country",
     *                     type="string",
     *                     example="bg",
     *                 ),
     *                 @OA\Property(
     *                     description="Account currency",
     *                     property="currency",
     *                     type="string",
     *                     example="USD",
     *                     enum={"USD", "EUR", "GBP"}
     *                 ),
     *                 @OA\Property(
     *                     description="Account type. SEPA = 1 , SWIFT = 0",
     *                     property="type",
     *                     type="integer",
     *                     default=1,
     *                     enum={0, 1}
     *                 ),
     *                 @OA\Property(
     *                     description="iban (required when type is SWIFT)",
     *                     property="iban",
     *                     type="string",
     *                     example="SD47969000000123280738595",
     *                 ),
     *                 @OA\Property(
     *                     description="Swift",
     *                     property="swift",
     *                     type="string",
     *                     example="FE34343R43985",
     *                 ),
     *                 @OA\Property(
     *                     description="Account holder",
     *                     property="account_holder",
     *                     type="string",
     *                     example="(your beneficiary account name)",
     *                 ),
     *                 @OA\Property(
     *                     description="Account number",
     *                     property="account_number",
     *                     type="string",
     *                     example="220148",
     *                 ),
     *                 @OA\Property(
     *                     description="Bank name",
     *                     property="bank_name",
     *                     type="string",
     *                     example="Centric Bank",
     *                 ),
     *                 @OA\Property(
     *                     description="Bank address",
     *                     property="bank_address",
     *                     type="string",
     *                     example="4320 Linglestown Road, Harrisburg, PA 17112",
     *                 ),
     *                 @OA\Property(
     *                     description="Correspondent bank. For SWIFT accounts",
     *                     property="correspondent_bank",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Correspondent bank SWIFT. For SWIFT accounts",
     *                     property="correspondent_bank_swift",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Intermediary bank. For SWIFT accounts",
     *                     property="intermediary_bank",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Intermediary bank SWIFT. For SWIFT accounts",
     *                     property="intermediary_bank_swift",
     *                     type="string",
     *                 ),
     *                 required={"template_name", "bank_address","bank_name","country", "currency", "type", "swift", "account_holder", "account_number"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of account",
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
     *             @OA\Examples(example="result", value={
     *                      "id": "s4a8s4a7-9948-5262-s45d-296b1b3588e1",
     *                      "accountId": 213,
     *                      "status": "Active",
     *                      "name": "Good Invest USD",
     *                      "currency": "USD",
     *                      "country": "bg",
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
     *                      "balance": 100,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="422",
     *         description="Account is not created",
     *          content={
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
     *                         ),
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
    public function storeWireAccount(BankDetailRequest $request, OperationService $operationService)
    {
        $account = $operationService->createBankDetailAccount($request->only(['template_name', 'country', 'currency', 'type']), auth()->user()->cProfile->id);
        $account->refresh();

        $cUser = auth()->user();
        /* @var CUser $cUser */

        ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_ADDED, ['account_id' => $account->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_ADDED, null , (auth()->id()));

        $wireAccountDetail = $operationService->createWireAccountDetail($request->only(['iban', 'swift', 'bank_name', 'bank_address', 'account_holder', 'account_number', 'correspondent_bank', 'correspondent_bank_swift', 'intermediary_bank', 'intermediary_bank_swift']), $account);

        ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_ADDED, ['wire_account_detail_id' => $wireAccountDetail->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_ADDED, null ,(auth()->id()));
        EmailFacade::sendAddingPaymentTemplate($cUser, $account, $wireAccountDetail);

        if (!$account) {
            return response()->json([
                'errors' => [
                    'account' => t('account_not_created'),
                ]
            ], 422);
        }

        return response()->json((new AccountResource($account))->setWithWireDetails(true));
    }


    /**
     * @OA\Put(
     *     path="/api/account/wire",
     *     summary="Update wire account",
     *     description="This API call is used to update the account.",
     *     tags={"004. Bank details"},
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
     *                     description="Wire account id",
     *                     property="u_account_id",
     *                     type="string",
     *                     example="s4a8s4a7-9948-5262-s45d-296b1b3588e1",
     *                 ),
     *                 @OA\Property(
     *                     description="Bank template name",
     *                     property="u_template_name",
     *                     type="string",
     *                     example="Good Invest USD",
     *                 ),
     *                 @OA\Property(
     *                     description="Account country code",
     *                     property="u_country",
     *                     type="string",
     *                     example="bg",
     *                 ),
     *                 @OA\Property(
     *                     description="Account currency",
     *                     property="u_currency",
     *                     type="string",
     *                     example="USD",
     *                     enum={"USD", "EUR", "GBP"}
     *                 ),
     *                 @OA\Property(
     *                     description="Account type. SEPA = 1 , SWIFT = 0",
     *                     property="u_type",
     *                     type="integer",
     *                     example=0,
     *                     enum={0, 1}
     *                 ),
     *                 @OA\Property(
     *                     description="iban (required when type is swift)",
     *                     property="u_iban",
     *                     type="string",
     *                     example="SD47969000000123280738595",
     *                 ),
     *                 @OA\Property(
     *                     description="Swift",
     *                     property="u_swift",
     *                     type="string",
     *                     example="FE34343R43985",
     *                 ),
     *                 @OA\Property(
     *                     description="Account holder",
     *                     property="u_account_holder",
     *                     type="string",
     *                     example="(your beneficiary account name)",
     *                 ),
     *                 @OA\Property(
     *                     description="Account number",
     *                     property="u_account_number",
     *                     type="string",
     *                     example="220148",
     *                 ),
     *                 @OA\Property(
     *                     description="Bank name",
     *                     property="u_bank_name",
     *                     type="string",
     *                     example="Centric Bank",
     *                 ),
     *                 @OA\Property(
     *                     description="Bank address",
     *                     property="u_bank_address",
     *                     type="string",
     *                     example="4320 Linglestown Road, Harrisburg, PA 17112",
     *                 ),
     *                 @OA\Property(
     *                     description="Correspondent bank. For SWIFT accounts",
     *                     property="u_correspondent_bank",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Correspondent bank SWIFT. For SWIFT accounts",
     *                     property="u_correspondent_bank_swift",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Intermediary bank. For SWIFT accounts",
     *                     property="u_intermediary_bank",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     description="Intermediary bank SWIFT. For SWIFT accounts",
     *                     property="u_intermediary_bank_swift",
     *                     type="string",
     *                 ),
     *                 required={"u_account_id", "u_template_name", "u_country", "u_currency", "u_type", "u_swift", "u_account_holder", "u_account_number", "u_bank_name", "u_bank_address"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of account",
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
     *             @OA\Examples(example="result", value={
     *                      "id": "s4a8s4a7-9948-5262-s45d-296b1b3588e1",
     *                      "accountId": 213,
     *                      "status": "Active",
     *                      "name": "Good Invest USD",
     *                      "currency": "USD",
     *                      "country": "bg",
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
     *                      "balance": 100,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *             }, summary="An result object."),
     *         )
     *     ),
     *
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
     *                              property="u_account_id",
     *                              type="string",
     *                              description="Account not found",
     *                         ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "u_account_id": "Account not found.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function updateWireAccount(BankDetailUpdateRequest $request, AccountService $accountService)
    {
        $account = $accountService->getAccountById($request->u_account_id);

        if ($account->cProfile->id != auth()->user()->cProfile->id) {
            return response()->json([
                'errors' => [
                    'u_account_id' => t('account_not_found')
                ]
            ], 422);
        }

        if ($account) {
            $accountData = [
                'name' => $request->u_template_name,
                'country' => $request->u_country,
                'currency' => $request->u_currency,
                'account_type' => $request->u_type,
            ];

            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_UPDATED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_UPDATED, null, (auth()->id()));

            $wireData = [
                'iban' => $request->u_iban,
                'swift' => $request->u_swift,
                'bank_name' => $request->u_bank_name,
                'bank_address' => $request->u_bank_address,
                'account_beneficiary' => $request->u_account_holder,
                'account_number' => $request->u_account_number,
                'correspondent_bank' => $request->u_correspondent_bank,
                'correspondent_bank_swift' => $request->u_correspondent_bank_swift,
                'intermediary_bank' => $request->u_intermediary_bank,
                'intermediary_bank_swift' => $request->u_intermediary_bank_swift
            ];
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_DETAILS_UPDATED, ['wire_account_detail_id' => $account->wire->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_WIRE_ACCOUNT_DETAIL_UPDATED, null, (auth()->id()));

            $account->update($accountData);
            $account->wire()->update($wireData);
            $account->refresh();
        }
        return response()->json((new AccountResource($account))->setWithWireDetails(true));
    }

    /**
     * @OA\Delete(
     *     path="/api/account/wire",
     *     summary="Delete wire account",
     *     description="This API call is used to delete a new wire account",
     *     tags={"004. Bank details"},
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
     *                     description="Wire account id",
     *                     property="account_id",
     *                     type="string",
     *                     example="s4a8s4a7-9948-5262-s45d-296b1b3588e1c",
     *                 ),
     *                 required={"account_id"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  description="If account was successfully deleted",
     *              ),
     *             @OA\Examples(example="result", value={
     *                  "success": true
     *             }, summary="An result object."),
     *         )
     *     ),
     *
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
     *                              property="account_id",
     *                              type="string",
     *                              description="Account not found",
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "account_id": "Account not found.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function deleteWireAccount(DeleteAccountRequest $request, AccountService $accountService)
    {
        $account = $accountService->getAccountById($request->account_id);

        if ($account->cProfile->id != auth()->user()->cProfile->id) {
            return response()->json([
                'errors' => [
                    'account_id' => t('account_not_found'),
                ]
            ], 422);
        }

        if ($account && $account->wire) {
            $account->status = AccountStatuses::STATUS_DISABLED;
            $account->save();
            ActivityLogFacade::saveLog(LogMessage::USER_BANK_ACCOUNT_DELETED, ['account_id' => $account->id],
                LogResult::RESULT_SUCCESS, LogType::TYPE_USER_BANK_ACCOUNT_DELETED, (auth()->id()));
        }
        return response()->json([
            'success' => true
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/account/crypto",
     *     summary="Create external crypto account",
     *     description="This API call is used to create a new external crypto account. ",
     *     tags={"003. Crypto wallets"},
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
     *                     description="Wallet address",
     *                     property="wallet_address",
     *                     type="string",
     *                     example ="NAaDxD4LU8Q9h6dMjaeiFJy68pz7Vm1UxM",
     *                 ),
     *                 @OA\Property(
     *                     description="Crypto Currency",
     *                     property="crypto_currency",
     *                     type="string",
     *                     example ="LTC",
     *                     enum={"BTC", "LTC", "BCH"}
     *                 ),
     *                 required={"wallet_address", "crypto_currency"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="accounts",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of account",
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
     *                      @OA\Property(
     *                          property="verifiedAt",
     *                          description="Crypto account verification date",
     *                          type="string"
     *                      ),
     *                     @OA\Property(
     *                          property="expiry",
     *                          description="Crypto account expiry days",
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
     *              "account": {
     *                      "id": "s4a8s4a7-4848-5262-s45d-296b1bd288e1",
     *                      "accountId": 315,
     *                      "status": "Active",
     *                      "name": "Good Invest BCH",
     *                      "currency": "BCH",
     *                      "country": "bg",
     *                      "cryptoDetails": {
     *                          "label": "Hoper Invest BCH",
     *                          "address": "4Aty6cyHl2spu5OifAHtV6cQTUWdeqhmg4",
     *                          "verifiedAt": "2021-09-14 18:38:04",
     *                          "expiry": "21 days"
     *                      },
     *                      "balance": 0,
     *                      "createdAt": "2021-09-14 18:38:04",
     *                      "updatedAt": "2022-02-07 15:29:07"
     *                  },
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="422",
     *         description="Account not created",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="wallet_address",
     *                              type="string",
     *                              description="Not supported wallet address",
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "wallet_address": "Not supported wallet address.",
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function addNewCryptoAccounts(CheckWalletAddressRequest $request, AccountService $accountService)
    {
        if (in_array($request->crypto_currency, Currency::getList())) {
            $account = $accountService->disabledAccount($request->crypto_currency, $request->wallet_address, auth()->user()->cProfile->id);
            if ($account) {
                $account->update(['status' => AccountStatuses::STATUS_ACTIVE]);
            } else {
                $account = $accountService->addWalletToClient($request->wallet_address, $request->crypto_currency, auth()->user()->cProfile, true);
            }
            if ($account && $account->status == AccountStatuses::STATUS_ACTIVE && $account->cryptoAccountDetail->isAllowedRisk()) {
                $account->refresh();
                return response()->json([
                    'account' => new AccountResource($account)
                ]);
            }
        }
        EmailFacade::sendUnsuccessfulAddingCryptoWallet(auth()->user(), $request->wallet_address);
        return response()->json([
            'errors' => [
                'wallet_address' => t('not_supported_wallet_address'),
            ]
        ], 422);
    }
}
