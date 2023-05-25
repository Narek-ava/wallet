<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\OperationOperationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\API\v1\OperationHistoryRequest;
use App\Http\Resources\Cabinet\API\v1\BuyCryptoFromFiatOperationResource;
use App\Http\Resources\Cabinet\API\v1\BuyFiatFromCryptoOperationResource;
use App\Http\Resources\Cabinet\API\v1\CardOperationResource;
use App\Http\Resources\Cabinet\API\v1\CryptoToCryptoPFOperationResource;
use App\Http\Resources\Cabinet\API\v1\FiatTopUpWireOperationResource;
use App\Http\Resources\Cabinet\API\v1\FiatWithdrawWireOperationResource;
use App\Http\Resources\Cabinet\API\v1\TopUpCryptoPF;
use App\Http\Resources\Cabinet\API\v1\OperationResource;
use App\Http\Resources\Cabinet\API\v1\TopUpCryptoOperationResource;
use App\Http\Resources\Cabinet\API\v1\TopUpWireOperationResource;
use App\Http\Resources\Cabinet\API\v1\WithdrawCryptoOperationResource;
use App\Http\Resources\Cabinet\API\v1\WithdrawWireOperationResource;
use App\Models\Cabinet\CProfile;
use App\Models\Operation;
use App\Models\Transaction;
use App\Services\OperationService;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationHistoryController extends Controller
{
    /**
     * @OA\OpenApi(
     *    security={{"bearerAuth": {}}}
     * )
     *
     * @OA\Info(
     *   title="Cratos testing annotations from bugreports",
     *   version="2.0.0",
     *   description="AUTHORIZATION<br><br>
            We use the Bearer token for authentication.<br><br>
            Bearer tokens enable requests to authenticate using an access key, such as a JSON Web Token (JWT). The token is a text string, included in the request header. In the Token field, enter your API key value. For added security, store it in a variable and reference the variable by name.
            <br><br>Postman will append the token value to the text Bearer in the required format to the request Authorization header as follows:
            <br><br> Bearer < Your API key > <br><br>
            How to get a Bearer token:<br><br>
            1. Send request to /api/login;<br><br>
            2. Take token from response (e.g. 'accessToken': 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJ....');<br><br>
            3. Use header Bearer token in your requests.<br><br>
            Since the access token has a limited lifetime, we also give the refresh token, with the help of which you can get a new access token.  <br><br>"


     * )
     *
     * @OA\Components(
     *     @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         type="http",
     *         scheme="bearer",
     *     ),
     *     @OA\Attachable()
     * )
     *
     * @OA\Get(
     *     path="/api/operations",
     *     summary="Get operations",
     *     description="This API call is used to get all operations. Operation statuses can be PENDING, SUCCESSFULL AND DECLINED",
     *     tags={"008. Operations"},
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
     *         description="transaction_type is ALL = 1; TOP_UP_WIRE = 2;
               TOP_UP_CRYPTO = 3; TOP_UP_CARD = 4; EXCHANGE = 5; WITHDRAW_CRYPTO = 7;
               WITHDRAW_WIRE = 8; MERCHANT_PAYMENT = 9; TOP_UP_CARD_PF = 10; TOP_UP_CRYPTO_PF = 11; WITHDRAW_CRYPTO_PF = 12; TOP_UP_CRYPTO_EXTERNAL_PF = 13; TYPE_CRYPTO_TO_CRYPTO_PF = 14",
     *         in="query",
     *         name="transaction_type",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           default="1",
     *           enum={1, 2, 3, 4, 5, 7, 8, 9, 10, 11, 12, 13, 14, 15}
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Operation number",
     *         in="query",
     *         name="number",
     *         example ="415",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Start date for operation filtration",
     *         in="query",
     *         name="from",
     *         example ="2015-09-07",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *           format="date-time",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="End date for operation filtration",
     *         in="query",
     *         name="to",
     *         example ="2025-09-07",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *           format="date-time",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Collection of operations by pages. Maximum 10 operations on each page",
     *         in="query",
     *         name="page",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           default="1",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Client wallet id",
     *         in="query",
     *         name="wallet",
     *         example ="1491c68c-ccss-cs12-8060-4904de321eca",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operations were got successfully.",
     *         @OA\JsonContent(
     *         @OA\Property(
     *              property="operations",
     *              description="Operation id",
     *              type="object",
     *              @OA\Property(
     *                  property="operationId",
     *                  description="Operation id",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="operationNumber",
     *                  description="Operation number",
     *                  type="integer"
     *              ),
     *              @OA\Property(
     *                  property="operationType",
     *                  description="Operation type",
     *                  type="string"
     *               ),
     *              @OA\Property(
     *                  property="amount",
     *                  description="Amount",
     *                  type="number"
     *              ),
     *              @OA\Property(
     *                  property="amountInEuro",
     *                  description="Amount in euro",
     *                  type="number"
     *              ),
     *              @OA\Property(
     *                  property="fromCurrency",
     *                  description="From currency",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="toCurrency",
     *                  description="To currency",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                  property="toAccount",
     *                  description="To account",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="status",
     *                  description="Status of the operation.",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="date",
     *                  description="Operation creation date and time",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          ),
     *            @OA\Examples(example="result", value={
     *              "operations": {
     *                  {
     *                      "operationId": "492a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                      "operationNumber": 40,
     *                      "operationType": "Top up by SWIFT",
     *                      "amount": 7,
     *                      "amountInEuro": 5.99,
     *                      "fromCurrency": "USD",
     *                      "toCurrency": "LTC",
     *                      "fromAccount": "Swift Bulgaria payments",
     *                      "toAccount": "John Brown LTC",
     *                      "status": "Pending",
     *                      "date": "2022-01-01 21:00:00",
     *                      "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/15cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                  },
     *                  {
     *                      "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                      "operationNumber": 41,
     *                      "operationType": "Top up by SWIFT",
     *                      "amount": 7,
     *                      "amountInEuro": 5.99,
     *                      "fromCurrency": "USD",
     *                      "toCurrency": "LTC",
     *                      "fromAccount": "Swift Bulgaria payments",
     *                      "toAccount": "John Brown LTC",
     *                      "status": "Successful",
     *                      "date": "2022-01-01 21:00:00",
     *                      "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                   },
     *                  {
     *                      "operationId": "692a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                      "operationNumber": 42,
     *                      "operationType": "Top up by SWIFT",
     *                      "amount": 7,
     *                      "amountInEuro": 5.99,
     *                      "fromCurrency": "USD",
     *                      "toCurrency": "LTC",
     *                      "fromAccount": "Swift Bulgaria payments",
     *                      "toAccount": "John Brown LTC",
     *                      "status": "Declined",
     *                      "date": "2022-01-01 21:00:00",
     *                      "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/45cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                   },
     *                 },
     *                 "count": 10,
     *                 "currentPage": 2,
     *                 "perPage": 2,
     *                 "totalPages": 5
     *              }, summary="An result object."),
     *          )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Invalid parameters given.",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="transaction_type",
     *                              type="string",
     *                              description="Operations not found",
     *                     ),
     *                     ),
     *                     example={
     *                         "errors":{
     *                              "transaction_type": "The selected transaction type is invalid"
     *                          }
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function index(OperationHistoryRequest $request, OperationService $operationService)
    {
        $operations = $operationService->getClientOperationsPaginationWithFilter($request);

        return response()->json([
            'operations' => OperationResource::collection($operations),
            'count' => $operations->count(),
            'currentPage' => $operations->currentPage(),
            'perPage' => $operations->perPage(),
            'totalPages' => $operations->lastPage()
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/operation/pf/topup/crypto/{id}",
     *     summary="Get Top Up Crypto PF operation.",
     *     description="This API call is used to get Top Up Crypto PF operation.",
     *     tags={"014. Payment Form"},
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
     *         description="Top Up Crypto PF Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="credited",
     *                     description="Credited amount",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="cardPaymentDetails",
     *                     description="Card payment details",
     *                     type="object",
     *                     @OA\Property(
     *                          property="transactionReference",
     *                          description="Operation card transaction reference",
     *                          type="string"
     *                      ),
     *                    @OA\Property(
     *                         property="cardNumberMask",
     *                         description="Card number mask.",
     *                         type="string"
     *                      ),
     *                     @OA\Property(
     *                          property="blockchainFee",
     *                          description="Blockchain Fee",
     *                          type="number"
     *                      ),
     *                    @OA\Property(
     *                          property="topUpFee",
     *                          description="Top up fee",
     *                          type="number"
     *                      ),
     *                    @OA\Property(
     *                          property="exchangeRate",
     *                          description="Exchange rate",
     *                          type="number"
     *                      ),
     *                  @OA\Property(
     *                         property="payerName",
     *                         description="Payer Name",
     *                         type="string"
     *                      ),
     *                  @OA\Property(
     *                         property="payerPhoneNumber",
     *                         description="Payer Phone number",
     *                         type="string"
     *                      ),
     *                  @OA\Property(
     *                         property="payerEmail",
     *                         description="Payer email",
     *                         type="string"
     *                      ),
     *                 ),
     *             @OA\Examples(example="result", value={
     *              "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *              "operationNumber": "35",
     *              "operationType": "Top up by crypto (PF)",
     *              "status": "SUCCESSFUL",
     *              "fromCurrency":  "EUR",
     *              "amount": 10.00,
     *              "toCurrency":"BTC",
     *              "date": "2022-01-01 21:00:00",
     *              "transactionExplorerUrl": "https://live.blockcypher.com/btc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *              "cardPaymentDetails": {
     *                  "cardNumberMask": "400000######1000",
     *                  "blockchainFee": 0.0004,
     *                  "topUpFee": 0.001,
     *                  "transactionReference": "51-6-1346654",
     *                  "exchangeRate": 153.74,
     *                  "payerName": "John Doe",
     *                  "payerPhoneNumber": "19026682819",
     *                  "payerEmail": "jdoe@gmail.com"
     *               }
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getTopUpCryptoPFOperationData(string $id)
    {
        $merchant = auth()->user()->cProfile;
        /* @var CProfile $merchant */

        $operation = $merchant->getOperationById($id, [OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new TopUpCryptoPF($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/pf/crypto/crypto/{id}",
     *     summary="Get Top Up Crypto to Crypto PF operation.",
     *     description="This API call is used to get Top Up Crypto to Crypto PF operation.",
     *     tags={"014. Payment Form"},
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
     *         description="Top Up Crypto to Crypto PF Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="payerName",
     *                     description="Payer Name",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="payerPhone",
     *                     description="Payer Phone number",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="payerEmail",
     *                     description="Payer email",
     *                     type="string"
     *                 ),
     *             @OA\Examples(example="result", value={
     *              "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *              "operationNumber": 45,
     *              "operationType": "Crypto to crypto (PF)",
     *              "amount": 0.07,
     *              "amountInEuro": 7.09,
     *              "fromCurrency":  "LTC",
     *              "toCurrency":"LTC",
     *              "fromAccount":"External LTC Account",
     *              "toAccount":"Merchant account name",
     *              "status": "SUCCESSFUL",
     *              "date": "2022-01-01 21:00:00",
     *              "topUpFee": 0.001,
     *              "payerName": "John Doe",
     *              "payerPhoneNumber": "19026682819",
     *              "payerEmail":"jdoe@gmail.com"
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getCryptoToCryptoPFOperationData(string $id)
    {
        $merchant = auth()->user()->cProfile;
        /* @var CProfile $merchant */

        $operation = $merchant->getOperationById($id, [OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => [
                    'operation_error' => t('operation_not_found'),
                ]
            ], 404);
        }

        return response()->json(new CryptoToCryptoPFOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/topup/crypto/{id}",
     *     summary="Get Top Up Crypto operation",
     *     description="This API call is used to get Top Up Crypto operation",
     *     tags={"009. Top Up Crypto"},
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
     *         description="Top Up Crypto Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="fromWallet",
     *                     description="Operation from wallet",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *               "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Top up by crypto",
     *                "amount": 0.05397099,
     *                "amountInEuro": 8.25,
     *                "fromCurrency": "LTC",
     *                "toCurrency": "LTC",
     *                "fromAccount": "External LTC Account",
     *                "toAccount": "John Brown LTC",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "fromWallet": "M6svPWkKqVNbeALWq3oaAQ16thd2p6gNbm",
     *                "topUpFee": 0.001,
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getTopUpCryptoOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_TOP_UP_CRYPTO]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new TopUpCryptoOperationResource($operation));
    }

    /**
     * @OA\Get(
     *     path="/api/operation/topup/wire/{id}",
     *     summary="Get Top Up Wire operation.",
     *     description="This API call is used to get Top Up Wire operation.",
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
     *     @OA\Parameter(
     *         description="Top Up Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpMethod",
     *                     description="Top up method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="bankCountry",
     *                     description="Provider country",
     *                     type="string"
     *                 ),
     *           @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *           @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Top up by SWIFT",
     *                "amount": 7,
     *                "amountInEuro": 5.99,
     *                "fromCurrency": "USD",
     *                "toCurrency": "LTC",
     *                "fromAccount": "Swift Bulgaria payments",
     *                "toAccount": "John Brown LTC",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "topUpMethod": "SWIFT",
     *                "bankCountry": "Bulgaria",
     *                "topUpFee": 3,
     *                "exchangeRate": 192.95,
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getTopUpWireOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_TOP_UP_SEPA, OperationOperationType::TYPE_TOP_UP_SWIFT]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new TopUpWireOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/fiat-wallet/buy-crypto-from-fiat/{id}",
     *     summary="Get Buy Crypto From Fiat Wallet operation.",
     *     description="This API call is used to get Buy Crypto From Fiat Wallet operation.",
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
     *         description="Top Up Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpMethod",
     *                     description="Top up method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *           @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *           @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *                  "operationId": "357b6c86-66fc-482e-9b4c-a2ef36a7cee7",
     *                  "operationNumber": 10000,
     *                  "operationType": "Buy crypto from fiat wallet",
     *                  "amount": 28.7,
     *                  "amountInEuro": 26.96,
     *                  "credited": "0.00186075 BTC",
     *                  "fromCurrency": "GBP",
     *                  "toCurrency": "BTC",
     *                  "fromAccount": "Sky Man GBP",
     *                  "toAccount": "Sky Man BTC",
     *                  "status": "Successful",
     *                  "date": "2022-12-16 08:18:31",
     *                  "transactionExplorerUrl": "https://mempool.space/tx/58040d2856b68df1f9ebcfb1ebf69d78edba345d62f0ff586bcfe8070e0dc2d6",
     *                  "topUpFee": null,
     *                  "exchangeRate": 14344.5
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getBuyCryptoFromFiatOperationData(string $id)
    {
        $cProfile = getCProfile();
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_BUY_CRYPTO_FROM_FIAT]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new BuyCryptoFromFiatOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/fiat-wallet/buy-fiat-from-crypto/{id}",
     *     summary="Get Buy Fiat From Crypto Wallet operation.",
     *     description="This API call is used to get Buy Fiat From Crypto Wallet operation.",
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
     *         description="Top Up Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpMethod",
     *                     description="Top up method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="bankCountry",
     *                     description="Provider country",
     *                     type="string"
     *                 ),
     *           @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *           @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *                      "operationId": "d6fc3bb4-b169-4d3a-85a4-3dcc8f47eb46",
     *                      "operationNumber": 20084,
     *                      "operationType": "Buy fiat from crypto wallet",
     *                      "amount": 0.001,
     *                      "amountInEuro": 16.43,
     *                      "credited": "-",
     *                      "fromCurrency": "BTC",
     *                      "toCurrency": "GBP",
     *                      "fromAccount": "Sky Man LTC",
     *                      "toAccount": "Sky Man GBP",
     *                      "status": "Successfull",
     *                      "date": "2022-12-16 08:23:11",
     *                      "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                      "topUpFee": null,
     *                      "exchangeRate": 14344.7
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getBuyFiatFromCryptoOperationData(string $id)
    {
        $cProfile = getCProfile();
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_BUY_FIAT_FROM_CRYPTO]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new BuyFiatFromCryptoOperationResource($operation));
    }

    /**
     * @OA\Get(
     *     path="/api/operation/fiat-wallet/topup-wire/{id}",
     *     summary="Get Top Up Wire operation.",
     *     description="This API call is used to get Top Up Wire operation.",
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
     *         description="Top Up Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpMethod",
     *                     description="Top up method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="bankCountry",
     *                     description="Provider country",
     *                     type="string"
     *                 ),
     *           @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *           @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *                      "operationId": "ca182671-4e18-4b15-9b84-f35b29c8e502",
     *                      "operationNumber": 77,
     *                      "operationType": "Top up fiat wallet by wire",
     *                      "amount": 100,
     *                      "amountInEuro": 95.14,
     *                      "credited": "99 USD",
     *                      "fromCurrency": "USD",
     *                      "toCurrency": "USD",
     *                      "fromAccount": "RBC",
     *                      "toAccount": "Sky Man USD",
     *                      "status": "Successful",
     *                      "date": "2022-12-07 12:30:53",
     *                      "transactionExplorerUrl": "",
     *                      "topUpMethod": "SWIFT",
     *                      "bankCountry": "Andorra",
     *                      "topUpFee": 1,
     *                      "exchangeRate": 0
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getFiatTopUpWireOperationData(string $id)
    {
        $cProfile = getCProfile();
        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new FiatTopUpWireOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/withdraw/crypto/{id}",
     *     summary="Get Withdraw Crypto operation",
     *     description="This API call is used to get Withdraw Crypto operation",
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
     *     @OA\Parameter(
     *         description="Withdraw Crypto Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="walletVerified",
     *                     description="Is wallet verified.",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="blockchainFee",
     *                     description="Blockchain fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="withdrawalFee",
     *                     description="Withdrawal fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="walletServiceFee",
     *                     description="Wallet service fee",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Withdraw by crypto",
     *                "amount": 0.054,
     *                "amountInEuro": 8.26,
     *                "fromCurrency": "LTC",
     *                "toCurrency": "LTC",
     *                "fromAccount": "John Brown LTC",
     *                "toAccount": "External LTC Account",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "walletVerified": "Yes",
     *                "blockchainFee": 0.0002,
     *                "withdrawalFee": 0.01,
     *                "walletServiceFee": 0
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getWithdrawCryptoOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_WITHDRAW_CRYPTO, OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new WithdrawCryptoOperationResource($operation));
    }

    /**
     * @OA\Get(
     *     path="/api/operation/pf/withdraw/crypto/{id}",
     *     summary="Get Withdraw Crypto PF operation",
     *     description="This API call is used to get Withdraw Crypto PF operation",
     *     tags={"014. Payment Form"},
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
     *         description="Withdraw Crypto PF Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="walletVerified",
     *                     description="Is wallet verified.",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="blockchainFee",
     *                     description="Blockchain fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="withdrawalFee",
     *                     description="Withdrawal fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="walletServiceFee",
     *                     description="Wallet service fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="parentId",
     *                     description="Parent operation Id",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="parentOperationType",
     *                     description="Parent operation type",
     *                     type="string"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Withdraw by crypto PF",
     *                "amount": 0.054,
     *                "amountInEuro": 8.26,
     *                "fromCurrency": "LTC",
     *                "toCurrency": "LTC",
     *                "fromAccount": "John Brown LTC",
     *                "toAccount": "External LTC Account",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *               "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "walletVerified": "Yes",
     *                "blockchainFee": 0.0002,
     *                "withdrawalFee": 0.01,
     *                "walletServiceFee": 0,
     *                "parentId": "693a3ed4-f5b7-96c2-7ecd-4165730746c9",
     *                "parentOperationType": "Bank Card (PF)"
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getWithdrawCryptoPFOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new WithdrawCryptoOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/withdraw/wire/{id}",
     *     summary="Get Withdraw Wire operation.",
     *     description="This API call is used to get Withdraw by Wire operation.",
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
     *     @OA\Parameter(
     *         description="Withdraw Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="withdrawalMethod",
     *                     description="Withdrawal method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="withdrawalFee",
     *                     description="Withdrawal fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="blockchainFee",
     *                     description="Blockchain fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="country",
     *                     description="Provider country",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange Rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Withdraw by Wire swift",
     *                "amount": 0.12615505,
     *                "amountInEuro": 23.42,
     *                "fromCurrency": "LTC",
     *                "toCurrency": "USD",
     *                "fromAccount": "John Brown LTC",
     *                "toAccount": "Bulgaria Swift payments",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "withdrawalMethod": "SWIFT",
     *                "withdrawalFee": 1.5,
     *                "blockchainFee": 0.00100000,
     *                "country": "Bulgaria",
     *                "exchangeRate": 208.48
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getWithdrawWireOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new WithdrawWireOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/fiat-wallet/withdraw/wire/{id}",
     *     summary="Get Fiat Withdraw Wire operation.",
     *     description="This API call is used to get Withdraw by Wire operation.",
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
     *         description="Withdraw Wire Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="withdrawalMethod",
     *                     description="Withdrawal method (Sepa or Swift)",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="withdrawalFee",
     *                     description="Withdrawal fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="country",
     *                     description="Provider country",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange Rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *                      "operationId": "a7588539-f206-46cd-a800-d95057a3d8d9",
     *                      "operationNumber": 10000,
     *                      "operationType": "Withdraw from Fiat Wallet By Wire",
     *                      "amount": 100,
     *                      "amountInEuro": 93.92,
     *                      "credited": "100 GBP",
     *                      "fromCurrency": "GBP",
     *                      "toCurrency": "GBP",
     *                      "fromAccount": "Sky Man GBP",
     *                      "toAccount": "RBC",
     *                      "status": "Successful",
     *                      "date": "2022-12-16 08:13:33",
     *                      "transactionExplorerUrl": "",
     *                      "withdrawalMethod": "SEPA",
     *                      "withdrawalFee": 0,
     *                      "country": "Lithuania"
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getFiatWithdrawWireOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_WITHDRAW_FROM_FIAT_WALLET]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new FiatWithdrawWireOperationResource($operation));
    }

    /**
     * @OA\Get(
     *     path="/api/operation/card/{id}",
     *     summary="Get Card operation.",
     *     description="This API call is used to get Card operation.",
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
     *     @OA\Parameter(
     *         description="Bank Card Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="blockchainFee",
     *                     description="Blockchain fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="transactionID",
     *                     description="Card Transaction ID",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange Rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Bank Card",
     *                "amount": 100,
     *                "amountInEuro": 86.04,
     *                "fromCurrency": "USD",
     *                "toCurrency": "LTC",
     *                "fromAccount": "400000######1000",
     *                "toAccount": "John Brown LTC",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "transactionID": "56-9-1344643",
     *                "blockchainFee": 0.0002,
     *                "topUpFee": 1,
     *                "exchangeRate": 208.48
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getBankCardOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_CARD]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new CardOperationResource($operation));
    }


    /**
     * @OA\Get(
     *     path="/api/operation/pf/card/{id}",
     *     summary="Get Top Up Card PF operation.",
     *     description="This API call is used to get Top Up Card PF operation.",
     *     tags={"014. Payment Form"},
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
     *         description="Top Up Card PF Operation ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="operationId",
     *                     description="Operation id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="operationNumber",
     *                     description="Operation number",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="operationType",
     *                     description="Operation type",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="amount",
     *                     description="Amount",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="amountInEuro",
     *                     description="Amount in euro",
     *                     type="number"
     *                 ),
     *         @OA\Property(
     *                     property="fromCurrency",
     *                     description="From currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toCurrency",
     *                     description="To currency",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="fromAccount",
     *                     description="From account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="toAccount",
     *                     description="To account",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="status",
     *                     description="Operation status",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="date",
     *                     description="Operation creation date and time",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                  property="transactionExplorerUrl",
     *                  description="Blockchain explorer link for crypto transaction",
     *                  type="string"
     *              ),
     *          @OA\Property(
     *                     property="topUpFee",
     *                     description="Top up fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="blockchainFee",
     *                     description="Blockchain fee",
     *                     type="number"
     *                 ),
     *          @OA\Property(
     *                     property="transactionID",
     *                     description="Card Transaction ID",
     *                     type="string"
     *                 ),
     *          @OA\Property(
     *                     property="exchangeRate",
     *                     description="Exchange Rate",
     *                     type="number"
     *                 ),
     *             @OA\Examples(example="result", value={
     *             "operationId": "592a3ed3-f5a7-98c2-9ecc-4141730746c9",
     *                "operationNumber": 41,
     *                "operationType": "Bank Card (PF)",
     *                "amount": 100,
     *                "amountInEuro": 86.04,
     *                "fromCurrency": "USD",
     *                "toCurrency": "LTC",
     *                "fromAccount": "400000######1000",
     *                "toAccount": "John Brown LTC",
     *                "status": "Successful",
     *                "date": "2022-01-01 21:00:00",
     *                "transactionExplorerUrl": "https://live.blockcypher.com/ltc/tx/35cabe165b7f23ffc8a6f5d4251f24548efe796c7264925ba17lhc3c5d79199a/",
     *                "transactionID": "56-9-1344643",
     *                "blockchainFee": 0.0002,
     *                "topUpFee": 1,
     *                "exchangeRate": 208.48
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getTopUpCardPFOperationData(string $id)
    {
        $cProfile = auth()->user()->cProfile;
        /* @var CProfile $cProfile */

        $operation = $cProfile->getOperationById($id, [OperationOperationType::TYPE_CARD_PF]);

        /* @var Operation $operation */
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found'),]
            ], 404);
        }

        return response()->json(new CardOperationResource($operation));
    }


    /**
     * @OA\Post (
     *     path="/api/get/operation/report/pdf/{id}",
     *     summary="Get report for some operation types.",
     *     description="This API call is used to get report for Top Up Wire operations.",
     *     tags={"008. Operations"},
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
     *         description="Operation id.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     description="File Attachment",
     *                     type="string",
     *                     format="binary",
     *                 ),
     *             @OA\Examples(example="result", value={
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operation_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operation_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Not supported operation type for generating report.",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                              property="operationId",
     *                              type="string",
     *                              description="There is no generated pdf for this type of operation",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operationId": "Invalid operation type."}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function downloadReportPDF(string $id)
    {
        /* @var Operation $operation */
        $operation = getCProfile()->operations()->find($id);
        if (!$operation) {
            return response()->json([
                "errors" => ['operation_error' => t('operation_not_found')]
            ], 404);
        }
        if ($operation && in_array($operation->operation_type, OperationOperationType::TYPES_TOP_UP)) {
            $providerAccount = $operation->providerAccount->wire ? $operation->providerAccount :
                (isset($operation->providerAccount->parentAccount->wire) ? $operation->providerAccount->parentAccount : null);
            $pdf = PDF::loadView('cabinet.partials.deposit-pdf', ['operation' => $operation, 'providerAccount' => $providerAccount]);
        } else {
            return response()->json([
                "errors" => ['operationId' => t('invalid_operation_type')]
            ], 403);
        }
        return $pdf->download($id . '.pdf');
    }


    /**
     * @OA\Post (
     *     path="/api/get/transaction/report/pdf/{id}",
     *     summary="Get report for transaction of the operation.",
     *     description="This API call is used to get report for transaction of the operation. Supported operation types are TOP UP BY CARD, TOP UP BY CARD (PF), WITHDRAW WIRE. For WITHDRAW WIRE operations, operation status must be successful.",
     *     tags={"008. Operations"},
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
     *         description="Operation id.",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     description="File Attachment",
     *                     type="string",
     *                     format="binary",
     *                 ),
     *             @OA\Examples(example="result", value={
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Operation not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="operatio_error",
     *                              type="string",
     *                              description="Operation not found",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operatio_error": "Operation not found"}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Not supported operation type for generating report.",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         @OA\Property(
     *                              property="operationId",
     *                              type="string",
     *                              description="There is no generated pdf for this type of operation",
     *                     ),
     *                     ),
     *                     example={
     *                        "errors" : {"operationId": "Invalid operation type."}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function downloadReportForTransactionPDF(string $id)
    {
        /* @var Operation $operation */
        $operation = getCProfile()->operations()->find($id);

        if (!$operation) {
            return response()->json([
                "errors" => [
                    'operation_error' => t('operation_not_found')
                ]
            ], 404);
        }

        if (!in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::TYPE_CARD_PF, OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT, OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA])) {
            return response()->json([
                "errors" => [
                    'operationId' => t('invalid_operation_type')
                ]
            ], 403);
        }

        if ($operation->status === \App\Enums\OperationStatuses::SUCCESSFUL || in_array($operation->operation_type, [OperationOperationType::TYPE_CARD, OperationOperationType::TYPE_CARD_PF])) {
            $pdf = PDF::loadView('cabinet.partials.transaction-report-pdf', ['operation' => $operation]);
            return $pdf->download($id . '.pdf');
        }

        return response()->json([
            "errors" => [
                'operation_error' => t('invalid_operation_status')
            ]
        ], 403);
    }
}
