<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\TicketMessages;
use App\Enums\TicketStatuses;
use App\Http\Controllers\Controller;
use App\Enums\CProfileStatuses;
use App\Http\Requests\Cabinet\API\v1\TicketMessageRequest;
use App\Http\Requests\TicketApiRequest;
use App\Http\Requests\TicketMessageApiRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Resources\Cabinet\API\v1\CUserResource;
use App\Http\Resources\Cabinet\API\v1\TicketMessageResource;
use App\Http\Resources\Cabinet\API\v1\TicketResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\CUserService;
use App\Services\TicketMessageService;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class HelpDeskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/help/desk",
     *     summary="Get help desk tickets",
     *     description="This API call is used to get all tickets",
     *     tags={"015. Help desk"},
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
     *         description="Search tickets by subject text",
     *         in="query",
     *         name="search",
     *         required=false,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Ticket status is Opened = 1; Closed = 2. Must select one of these statuses.",
     *         in="query",
     *         name="ticket_status",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           default="1",
     *           enum={"1", "2"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="tickets",
     *                  type="object",
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="ticketId",
     *                      description="Number of ticket",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="subject",
     *                      description="Subject ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="question",
     *                      description="Question ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Ticket status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="toClient",
     *                      description="Client who create ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="createdByClient",
     *                      description="Ticket created by Client",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="messages",
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          description="Message ID",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="creatorName",
     *                          description="Ticket creator name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="viewed",
     *                          description="Is seen message",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          description="File path",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="message",
     *                          description="Message text",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ticketId",
     *                          description="Ticket Id",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="createdAt",
     *                          description="Message creation date",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="updatedAt",
     *                          description="Message update date",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Ticket creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Ticket update date",
     *                      type="string"
     *                  ),
     *              ),
     *              @OA\Property(
     *                      property="count",
     *                      description="Number of tickets for the current page.",
     *                      type="integet"
     *                  ),
     *              @OA\Property(
     *                      property="currentPage",
     *                      description="The current page number.",
     *                      type="integer"
     *                  ),
     *              @OA\Property(
     *                      property="perPage",
     *                      description="The number of tickets to be shown per page.",
     *                      type="integer"
     *                  ),
     *              @OA\Property(
     *                      property="totalPage",
     *                      description="Total pages",
     *                      type="integer"
     *                  ),
     *              @OA\Examples(example="result", value={
     *                  "tickets": {
     *                      {
     *                      "id": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                      "ticketId": 1,
     *                      "subject": "Subject",
     *                      "question": "message",
     *                      "status": "Open",
     *                      "toClient": "Client#1",
     *                      "createdByClient": true,
     *                      "messages": {
     *                          {
     *                          "id": "b172b2c0-3787-413e-9b00-bb51126706fb",
     *                          "creatorName": "John Johnson",
     *                          "viewed": 0,
     *                          "file": "b172b2c0-3787-413e-9b00-bb51126706fb.jpg",
     *                          "message": "message",
     *                          "ticketId": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                          "createdAt": "2022-02-08 11:05:46",
     *                          "updatedAt": "2022-02-08 11:05:47"
     *                          }
     *                  },
     *                      "createdAt": "2022-02-08 11:05:44",
     *                      "updatedAt": "2022-02-08 11:05:44"
     *                  }
     *                  },
     *                  "count": 1,
     *                  "currentPage": 1,
     *                  "perPage": 3,
     *                  "totalPages": 1
     *   }, summary="An result object."),
     * )
     *     ),
     * )
     */
    public function index(Request $request, TicketService $ticketService)
    {
        $validator = Validator::make($request->all(), ['ticket_status' => [Rule::in(array_keys(TicketStatuses::NAMES))]]);
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
            return response()->json(array_map(function ($error) {
                return $error[0] ?? $error;
            }, $errors), 403);
        }
        $tickets = $ticketService->getTicketsByClient($request->only(['ticket_status', 'search']));
        return response()->json([
            'tickets' => TicketResource::collection($tickets),
            'count' => $tickets->count(),
            'currentPage' => $tickets->currentPage(),
            'perPage' => $tickets->perPage(),
            'totalPages' => $tickets->lastPage()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/store/ticket",
     *     summary="Store ticket",
     *     description="This API call is used to create new ticket",
     *     tags={"015. Help desk"},
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="Subject of ticket",
     *                     property="subject",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="Question",
     *                     property="question",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="File to upload",
     *                     property="file",
     *                     type="file",
     *                     format="file",
     *                 ),
     *                 required={"subject", "question"},
     *                 example={"subject": "My subject", "question": "My question"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors" : { "fail_store_ticket": "Failed to add a new ticket."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message.",
     *                 type="object",
     *                 @OA\Property(
     *                      property="fail_store_ticket",
     *                      description="Error message.",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                           "id": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                           "ticketId": 1,
     *                           "subject": "Subject",
     *                           "question": "message",
     *                           "status": "New",
     *                           "toClient": "Client#1",
     *                           "createdByClient": true,
     *                           "messages": {
     *                               {
     *                               "id": "b172b2c0-3787-413e-9b00-bb51126706fb",
     *                               "creatorName": "John Johnson",
     *                               "viewed": 0,
     *                               "file": "b172b2c0-3787-413e-9b00-bb51126706fb.jpg",
     *                               "message": "message",
     *                               "ticketId": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                               "createdAt": "2022-02-08 11:05:46",
     *                               "updatedAt": "2022-02-08 11:05:47"
     *                               }
     *                           },
     *                           "createdAt": "2022-02-08 11:05:44",
     *                           "updatedAt": "2022-02-08 11:05:44"
     *               }, summary="An result object."),
     *                 @OA\Property(
     *                      property="id",
     *                      description="Id of ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="ticketId",
     *                      description="Number of ticket",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="subject",
     *                      description="Subject ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="question",
     *                      description="Question ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Ticket status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="toClient",
     *                      description="Client who create ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="createdByClient",
     *                      description="Ticket created by Client",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="messages",
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          description="Message ID",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="creatorName",
     *                          description="Ticket creator name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="viewed",
     *                          description="Is seen message",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          description="File path",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="message",
     *                          description="Message text",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ticketId",
     *                          description="Ticket Id",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="createdAt",
     *                          description="Message creation date",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="updatedAt",
     *                          description="Message update date",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Ticket creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Ticket update date",
     *                      type="string"
     *                  ),
     *              ),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function storeTicket(TicketApiRequest $request, TicketService $ticketService, TicketMessageService $ticketMessageService)
    {
        $user = auth()->user();

        if ($user->tickets()->where('created_at', '>=', Carbon::now()->subHour())->count() >= Ticket::MAX_TICKETS_COUNT_FOR_HOUR) {
            return response()->json(["errors" => ['fail_store_ticket' => t('ticket_limits_reached', ['count' => Ticket::MAX_TICKETS_COUNT_FOR_HOUR])]], 403);
        }

        $ticket = $ticketService->store($request->only(['subject', 'question']), auth()->user(), auth()->id());

        if (!$ticket) {
            return response()->json(["errors" => ['fail_store_ticket' => t('fail_store_ticket')]], 403);
        }

        $ticketMessage = $ticketMessageService->storeMessage(['message' => $ticket->question, 'ticket-id' => $ticket->id], auth()->user());
        if ($request->file()) {
            $ticketMessageService->storeTicketMessageFile($request, $ticketMessage, true);
        }

        return response()->json(new TicketResource($ticket));
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/{id}",
     *     summary="Get ticket",
     *     description="This API call is used to get ticket by id",
     *     tags={"015. Help desk"},
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
     *         description="ID of the ticket",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors" : {"ticket_error": "Ticket not found"}
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="ticket_error",
     *                      description="Ticket not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Examples( example="result",  value={
     *                      "id": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                      "ticketId": 1,
     *                      "subject": "Subject",
     *                      "question": "message",
     *                      "status": "New",
     *                      "toClient": "Client#1",
     *                      "createdByClient": true,
     *                      "messages": {
     *                          {
     *                          "id": "b172b2c0-3787-413e-9b00-bb51126706fb",
     *                          "creatorName": "John Johnson",
     *                          "viewed": 0,
     *                          "file": "b172b2c0-3787-413e-9b00-bb51126706fb.jpg",
     *                          "message": "message",
     *                          "ticketId": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                          "createdAt": "2022-02-08 11:05:46",
     *                          "updatedAt": "2022-02-08 11:05:47"
     *                          }
     *                      },
     *                      "createdAt": "2022-02-08 11:05:44",
     *                      "updatedAt": "2022-02-08 11:05:44"
     *              }, summary="An result object."),
     *                  @OA\Property(
     *                      property="id",
     *                      description="Id of ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="ticketId",
     *                      description="Number of ticket",
     *                      type="integer"
     *                  ),
     *                  @OA\Property(
     *                      property="subject",
     *                      description="Subject ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="question",
     *                      description="Question ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="Ticket status",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="toClient",
     *                      description="Client who create ticket",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="createdByClient",
     *                      description="Ticket created by Client",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="messages",
     *                      type="object",
     *                      @OA\Property(
     *                          property="id",
     *                          description="Message ID",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="creatorName",
     *                          description="Ticket creator name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="viewed",
     *                          description="Is seen message",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          description="File path",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="message",
     *                          description="Message text",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ticketId",
     *                          description="Ticket Id",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="createdAt",
     *                          description="Message creation date",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="updatedAt",
     *                          description="Message update date",
     *                          type="string"
     *                      ),
     *                  ),
     *                  @OA\Property(
     *                      property="createdAt",
     *                      description="Ticket creation date",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="updatedAt",
     *                      description="Ticket update date",
     *                      type="string"
     *                  ),
     *              ),
     *         )
     *     ),
     * )
     */
    public function getTicket($id, TicketService $ticketService)
    {
        $ticket = $ticketService->getTicketById($id, auth()->id());
        if (!$ticket) {
            return response()->json([
                "errors" => ['ticket_error' => t('ticket_not_found'),]
            ], 404);
        }
        return response()->json(new TicketResource($ticket));
    }

    /**
     * @OA\Post(
     *     path="/api/ticket/message",
     *     summary="Create ticket message",
     *     description="This API call is used to create ticket message",
     *     tags={"015. Help desk"},
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="New message for ticket, sent by client (max size 1000 characters)",
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="Ticket id to send message to",
     *                     property="ticket_id",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     description="File to upload",
     *                     property="m_file",
     *                     type="file",
     *                     format="file",
     *                 ),
     *                 required={"message", "ticket_id"},
     *             )
     *         )
     *     ),
     *    @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors" : {"ticket_error": "You have reached your message limits. For every ticket you can create max 30 messages in an hour"}
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="ticket_message_error",
     *                      description="Ticket not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *    @OA\Response(
     *         response=404,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors" : {"ticket_id": "Ticket not found."}
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="ticket_id",
     *                      description="Ticket not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="result",
     *                  value={
     *                      "id": "87c36cd0-5aeb-4911-a03e-34bea0dcb4d5",
     *                      "creatorName": "John Johnson",
     *                      "viewed": 0,
     *                      "file": "b172b2c0-3787-413e-9b00-bb51126706fb.jpg",
     *                      "message": "test5",
     *                      "ticketId": "a6e5ad33-0c44-4171-9395-93a0ba7279e5",
     *                      "createdAt": "2022-02-08 15:15:18",
     *                      "updatedAt": "2022-02-08 15:15:18"
     *                  },
     *                  summary="An result object."),
     *                     @OA\Property(
     *                          property="id",
     *                          description="Message ID",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="creatorName",
     *                          description="Ticket creator name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="viewed",
     *                          description="Is seen message",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="file",
     *                          description="File path",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="message",
     *                          description="Message text",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="ticketId",
     *                          description="Ticket Id",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="createdAt",
     *                          description="Message creation date",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="updatedAt",
     *                          description="Message update date",
     *                          type="string"
     *                      ),
     *              ),
     *             @OA\Examples(example="bool", value=false, summary="A boolean value."),
     *         )
     *     )
     * )
     */
    public function sendTicketMessage(TicketMessageApiRequest $request, TicketMessageService $ticketMessageService, TicketService $ticketService)
    {
        $user = auth()->user();

        $ticket = $user->tickets()->find($request['ticket_id']);
        if (!$ticket) {
            return response()->json([
                "errors" => ['ticket_id' => t('ticket_not_found')]
            ], 404);
        }

        if ($ticket->messages()->where('created_at', '>=', Carbon::now()->subHour())->count() >= TicketMessage::MAX_TICKETS_MESSAGES_COUNT_FOR_HOUR) {
            return response()->json([
                "errors" => ['ticket_message_error' => t('ticket_message_limits_reached', ['count' => TicketMessage::MAX_TICKETS_MESSAGES_COUNT_FOR_HOUR]),]
            ], 403);
        }

        $ticketMessage = $ticketMessageService->storeMessage($request->only(['message', 'ticket_id']), auth()->user());
        $ticketService->changeStatus($ticketMessage->ticket_id, TicketStatuses::STATUS_OPEN);
        if ($request->m_file) {
            $ticketMessageService->storeTicketMessageFile($request, $ticketMessage);
        }
        return response()->json(new TicketMessageResource($ticketMessage));
    }

    /**
     * @OA\Get(
     *     path="/api/close/ticket/{id}",
     *     summary="Close ticket",
     *     description="This API call is used to close the ticket",
     *     tags={"015. Help desk"},
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
     *         description="ID of ticket",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors":  {"fail_closed_ticket": "Fail to close ticked"}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="fail_closed_ticket",
     *                      description="Fail to close ticked",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully closed",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "message": "Ticket was successfully closed."
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="message",
     *                 description="Ticket was successfully closed.",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     */
    public function closeTicket($id, TicketService $ticketService)
    {
        $ticket = $ticketService->closeTicket($id);
        if (!$ticket) {
            return response()->json(["errors" => ['fail_closed_ticket' => t('fail_closed_ticket')]], 403);
        }
        return response()->json(['message' => t('ticket_successful_closed')]);
    }

    /**
     * @OA\Get(
     *     path="/api/ticket/message/file/{fileName}",
     *     summary="Get ticket file",
     *     description="This API call is used to get the ticket message file",
     *     tags={"015. Help desk"},
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
     *         description="Name of ticket file",
     *         in="path",
     *         name="fileName",
     *         example="emB4HO-VT6s70HC-mp3Nsk-xgmxv.jpg",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors":  {"none_file": "File not found"}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="none_file",
     *                      description="File not found",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Download file",
     *            @OA\Property(
     *                 property="message",
     *                 description="Download file.",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     */
    public function downloadTicketMessageFile($file)
    {
        if (file_exists(storage_path('app/images/ticket-messages/') . $file)){
            return response()->download(storage_path('app/images/ticket-messages/') . $file, $file);
        }
        return response()->json(["errors" => ['none_file' => t('file_not_found')]], 404);

    }

    /**
     * @OA\Get(
     *     path="/api/view/message/{id}",
     *     summary="Ticket message mark as seen",
     *     description="This API call is used to mark ticket message as seen",
     *     tags={"015. Help desk"},
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
     *         description="ID of ticket",
     *         in="path",
     *         name="ticketId",
     *         required=true,
     *         @OA\Schema(
     *           type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Error",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors" : {"message_not_found": "There are no new messages for this ticket."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="message_not_found",
     *                      description="There are no new messages for this ticket.",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "message": "Message seen"
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="message",
     *                 description="Message seen",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     */
    public function viewMessage($id, TicketMessageService $ticketMessageService)
    {
        $ticketMessage = $ticketMessageService->viewTicketMessages($id, TicketMessages::VIEW_CUSER);
        if (!$ticketMessage) {
            return response()->json(["errors" => ['message_not_found' => t('message_not_found')]], 404);
        }
        return response()->json(['message' => t('message_view_successful')]);
    }

}
