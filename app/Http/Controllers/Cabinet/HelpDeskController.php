<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TicketMessages;
use App\Enums\TicketStatuses;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\TicketMessageRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\TicketMessageService;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelpDeskController extends Controller
{
    public function index(Request $request)
    {
        $paginate = config('cratos.pagination.tickets');

        $queryOpenTickets = auth()->user()->openTickets();
        $queryClosedTickets = auth()->user()->closedTickets();

        if ($request->sInput) {
            if ($request->id) {
                $queryOpenTickets->where('ticket_id', $request->sInput);
                $queryClosedTickets->where('ticket_id', $request->sInput);
            } else {
                $queryOpenTickets->where('subject', 'like', '%'.$request->sInput.'%');
                $queryClosedTickets->where('subject', 'like', '%'.$request->sInput.'%');
            }
        }
        $openTickets = $queryOpenTickets->paginate($paginate);
        $closedTickets = $queryClosedTickets->paginate($paginate);
        return view('cabinet.help-desk.index', compact('openTickets', 'closedTickets'));
    }

    public function storeTicket(TicketRequest $request, TicketService $ticketService, TicketMessageService $ticketMessageService)
    {
        $user = auth()->user();

        if ($user->tickets()->where('created_at', '>=', Carbon::now()->subHour())->count() >= Ticket::MAX_TICKETS_COUNT_FOR_HOUR) {
            session()->flash('error', t('ticket_limits_reached', ['count' => Ticket::MAX_TICKETS_COUNT_FOR_HOUR]));
            return redirect()->back();
        }

        $ticket = $ticketService->store($request->only(['subject', 'question']), $user, auth()->id());
        if ($ticket) {
            $ticketMessage = $ticketMessageService->storeMessage(['message' => $ticket->question, 'ticket-id' => $ticket->id], auth()->user());
            if ($request->file()){
                $ticketMessageService->storeTicketMessageFile($request, $ticketMessage, true);
            }
        }
        return redirect()->back()->with('status', t('ticket_successfully_added'));
    }

    public function getTicket($id, TicketService $ticketService)
    {
        return response()->json(new TicketResource($ticketService->getTicketById($id, auth()->id())));
    }

    public function sendTicketMessage(TicketMessageRequest $request,
                                      TicketMessageService $ticketMessageService,
                                      TicketService $ticketService)
    {
        $user = auth()->user();
        $ticket = $user->tickets()->find($request['ticket-id']);
        if (!$ticket) {
            session()->flash('error', t('ticket_not_found'));
            return redirect()->back();
        }
        if ($ticket->messages()->where('created_at', '>=', Carbon::now()->subHour())->count() >= TicketMessage::MAX_TICKETS_MESSAGES_COUNT_FOR_HOUR) {
            session()->flash('error', t('ticket_message_limits_reached', ['count' => TicketMessage::MAX_TICKETS_MESSAGES_COUNT_FOR_HOUR]));
            return redirect()->back();
        }

        $ticketMessage = $ticketMessageService->storeMessage($request->only(['message', 'ticket-id']), $user);
        $ticketService->changeStatus($ticketMessage->ticket_id, TicketStatuses::STATUS_OPEN);
        if ($request->m_file) {
            $ticketMessageService->storeTicketMessageFile($request, $ticketMessage);
        }
        $ticket = Ticket::find($ticketMessage->ticket_id);
//        ActivityLogFacade::saveLog(LogMessage::USER_TICKET_ANSWER_BACKOFFICE, ['id' => $ticket->ticket_id],
//            LogResult::RESULT_SUCCESS, LogType::TYPE_TICKET_ANSWER_BACKOFFICE, null, $ticket->to_client);
        return redirect()->back()->with('status', t('ticket_message_successfully_added'))->withInput();
    }

    public function closeTicket($id, TicketService $ticketService)
    {
        $ticketService->closeTicket($id);
        $url = back()->with('status', t('ticket_successful_closed'))->getTargetUrl() . '?status=' . t('ticket_closed');
        return redirect($url);
    }

    public function viewMessage($ticketId, $type, TicketMessageService $ticketMessageService)
    {
        $ticketMessageService->viewMessage($ticketId, TicketMessages::NAMES[$type]);
    }

    public function downloadTicketMessagePdfFile($file, TicketMessageService $ticketMessageService)
    {
        if (file_exists(storage_path('app/images/ticket-messages/') . $file) && $ticketMessageService->fileBelongsUser($file)) {
            return response()->download(storage_path('app/images/ticket-messages/') . $file, $file);
        }
        abort(404);
    }


}
