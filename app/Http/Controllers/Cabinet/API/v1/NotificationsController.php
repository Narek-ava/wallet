<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cabinet\API\v1\NotificationUserResource;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Find notifications",
     *     description="This API call is used to get a paginated collection of notifications.",
     *     tags={"016. Notifications"},
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
     *         description="Limiting the count of returned notifications",
     *         in="query",
     *         name="limit",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           default="10",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Collection of notifications by pages.",
     *         in="query",
     *         name="page",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           default="1",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *         @OA\Property(
     *                     property="id",
     *                     description="Notification id",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="status",
     *                     description="Notification status",
     *                     type="integer"
     *                 ),
     *         @OA\Property(
     *                     property="viewedAt",
     *                     description="Notification seen time",
     *                     type="string"
     *                 ),
     *         @OA\Property(
     *                     property="notification",
     *                     description="Notification message",
     *                     type="object",
     *                     @OA\Property(
     *                                  property="id",
     *                                  description="Notification ID",
     *                                  type="integer",
     *                      ),
     *                      @OA\Property(
     *                                  property="titleMessage",
     *                                  description="Message title",
     *                                  type="string",
     *                      ),
     *                      @OA\Property(
     *                                  property="shortBody",
     *                                  description="Notification text",
     *                                  type="string",
     *                      ),
     *                 ),
     *         @OA\Property(
     *                     property="created_at",
     *                     description="Time to created notification",
     *                     type="integer"
     *                 ),
     *         @OA\Examples(
     *                     example="result",
     *                     value={
     *                          "notifications": {
     *                              {
     *                              "id": 1,
     *                              "status": "Seen",
     *                              "viewedAt": "2022-02-11 15:06:38",
     *                              "titleMessage": "Successful Cratos operation ###",
     *                              "shortBody": "Dear client, Your operation ### for the amount of 1 has been successfully exchanged to 0.00000001 LTC and sent to your Cratos crypto wallet. In the attachment to the letter, you will find a report on this operation, which is also available in your personal account. If you have any questions please contact our support team.",
     *                              "created_at": "2021-11-02 13:07:11"
     *                              }
     *                          },
     *                          "count": 1,
     *                          "currentPage": 10,
     *                          "perPage": "1",
     *                          "totalPages": 58},
     *     summary="An result object."),
     * )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $notifications = auth()->user()->notifications()->orderByDesc('created_at')->paginate($request->limit ?? config('cratos.pagination.notifications'));

        return response()->json([
            'notifications' => NotificationUserResource::collection($notifications),
            'count' => $notifications->count(),
            'currentPage' => $notifications->currentPage(),
            'perPage' => $notifications->perPage(),
            'totalPages' => $notifications->lastPage()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notification/{id}",
     *     summary="Get information on notification(s)",
     *     description="This API call is used to get notification information by Id",
     *     tags={"016. Notifications"},
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
     *         description="Notification ID",
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
     *              @OA\Property(
     *                     property="id",
     *                     description="Notification id",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                     property="status",
     *                     description="Notification status",
     *                     type="integer"
     *                 ),
     *              @OA\Property(
     *                     property="viewedAt",
     *                     description="Notification seen time",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                     property="notification",
     *                     description="Notification message",
     *                     type="integer"
     *                 ),
     *              @OA\Property(
     *                     property="created_at",
     *                     description="Time to created notification",
     *                     type="integer"
     *                 ),
     *              @OA\Examples(example="result",
     *                     value={
     *                          "id": 1,
     *                          "status": "Seen",
     *                          "viewedAt": "2022-02-11 15:06:38",
     *                          "titleMessage": "Successful Cratos operation ###",
     *                          "shortBody": "Dear client, Your operation ### for the amount of 1 has been successfully exchanged to 0.0000001 LTC and sent to your Cratos crypto wallet. In the attachment to the letter, you will find a report on this operation, which is also available in your personal account. If you have any questions please contact our support team.",
     *             }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors": {"notification_error": "Notification not found"}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="notification_error",
     *                      description="Notification not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     )
     * )
     */
    public function getNotificationUserById(string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        /** Notification $notification */
        if (!$notification) {
            return response()->json([
                "errors" => ['notification_error' => t('notification_not_found'),]
            ], 404);
        }

        return response()->json(new NotificationUserResource($notification));
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/{id}/seen",
     *     summary="Make notification as seen",
     *     description="This API call is used to make notification as seen.",
     *     tags={"016. Notifications"},
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
     *         description="Notification ID",
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
     *              @OA\Property(
     *                     property="success",
     *                     description="Notification status change successfully",
     *                     type="boolean"
     *                 ),
     *              @OA\Examples(example="result",
     *                     value={
     *                          "success": true,
     *                          }
     *             , summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors": {"notification_error": "Notification not found"}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="notification_error",
     *                      description="Notification not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     )
     * )
     */
    public function verifyNotificationApi(string $id)
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        /** Notification $notification */
        if (!$notification) {
            return response()->json([
                "errors" => ['notification_error' => t('notification_not_found'),]
            ], 404);
        }
        verifyNotification($id);

        return response()->json(['success' => true]);
    }

}
