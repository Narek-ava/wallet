<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\CardAccountDetails\GetByCUserIdRequest;
use App\Http\Resources\Cabinet\API\v1\CardResource;
use App\Services\CardService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CardAccountDetailsController extends Controller
{
    /**
     *
     * @OA\Get(
     *     path="/api/users/cards",
     *     summary="Users Cards",
     *     description="This API call is used to get user card",
     *     tags={"User Cards"},
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
     *         description="User Cards",
     *         @OA\JsonContent(
     *            @OA\Property(
     *                     property="cards",
     *                     description="User cards",
     *                     type="array",
     *              @OA\Items(
     *             @OA\Property(
     *                     property="id",
     *                     description="Card id",
     *                     type="string"
     *                 ),
     *             @OA\Property(
     *                     property="name",
     *                     description="Name",
     *                     type="string"
     *                 ),
     *             @OA\Property(
     *                     property="last4",
     *                     description="Last 4 numbers of card",
     *                     type="number"
     *                 ),
     *                  @OA\Property(
     *                     property="balance",
     *                     description="Card balance",
     *                     type="number"
     *                 ),
     *                   @OA\Property(
     *                     property="verified",
     *                     description="Card is verify or no",
     *                     type="boolean"
     *                 ),
     *                  @OA\Property(
     *                     property="status",
     *                     description="status",
     *                     type="string"
     *                 ),
     *                   @OA\Property(
     *                     property="cardType",
     *                     description="Card type",
     *                     type="string"
     *                 ),
     *                   @OA\Property(
     *                     property="paymentSystemName",
     *                     description="PaymentSystemName Visa or MasterCard",
     *                     type="number"
     *                 ),
     *                  @OA\Property(
     *                     property="creationDate",
     *                     description="Creation Date",
     *                     type="string"
     *                 ),
     *                 ),
     *             ),
     *             @OA\Examples(example="result", value={
     *                    "cards": {{
     *                        "id": "6f2cb252-2d72-4b43-b8b9-51b56f7a226b",
     *                        "name": "Marat g",
     *                        "last4": "####",
     *                        "balance": 0,
     *                        "verified": 0,
     *                        "status": "Pending Payment",
     *                        "cardType": "virtual",
     *                        "paymentSystemName": "VISA",
     *                        "creationDate": "2022-11-07 07:31:16"
     *                       }}
     *                   }, summary="An result object."),
     *         ),
     *     ),
     *
     *             )
     *         }
     *     ),
     * )
     * @param CardService $cardService
     * @return JsonResponse
     */
    public function getUserCards(CardService $cardService): JsonResponse
    {

        $cards = $cardService->getCardsByCUser(auth()->id());

        return response()->json([
            'cards' => CardResource::collection($cards)
        ]);
    }
}


