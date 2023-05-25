<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\ProjectStatuses;
use App\Enums\TicketMessages;
use App\Enums\TicketStatuses;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\BackofficeTicketRequest;
use App\Http\Requests\BackofficeTicketSearchRequest;
use App\Http\Requests\TicketMessageRequest;
use App\Http\Requests\TicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\ProjectService;
use App\Services\TicketMessageService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketsController extends Controller
{
    public function index(BackofficeTicketSearchRequest $request, TicketService $ticketService , ProjectService $projectService)
    {
        $openedTickets = $ticketService->ticketsPaginate($request, TicketStatuses::STATUS_OPEN);
        $closedTickets = $ticketService->ticketsPaginate($request, TicketStatuses::STATUS_CLOSE);
        $newTickets = $ticketService->ticketsPaginate($request, TicketStatuses::STATUS_NEW);
        $activeProjects =  $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        $projectId = $request->input('project_id');
        $status = $request->status ?? null;
        return view('backoffice.tickets.index', compact('openedTickets',
            'closedTickets',
            'newTickets',
            'status',
            'activeProjects',
            'projectId'
        ));
    }

    public function storeTicket(BackofficeTicketRequest $request, TicketService $ticketService, TicketMessageService $ticketMessageService)
    {
        $ticket = $ticketService->store($request->only(['subject', 'question']), auth()->user(), $request->to_client);
        if ($ticket) {
            $ticketMessage = $ticketMessageService->storeMessage(['message' => $ticket->question, 'ticket-id' => $ticket->id, 'to' => $request->to_client], auth()->user());
            if ($request->file()){
                $ticketMessageService->storeTicketMessageFile($request, $ticketMessage, true);
            }
        }

        return redirect()->to(url()->previous() . '#tickets')->with('status', t('ticket_successfully_added'));
    }

    public function getTicket($id, $toClient, TicketService $ticketService)
    {
        return response()->json(new TicketResource($ticketService->getTicketById($id, $toClient)));
    }

    public function sendTicketMessage(TicketMessageRequest $request, TicketMessageService $ticketMessageService, TicketService $ticketService)
    {
        $ticketMessage = $ticketMessageService->storeMessage($request->only(['message', 'ticket-id', 'to']), auth()->user());
        $ticketService->changeStatus($ticketMessage->ticket_id, TicketStatuses::STATUS_OPEN);
        if ($request->m_file) {
            $ticketMessageService->storeTicketMessageFile($request, $ticketMessage);
        }
        EmailFacade::replyTicketFromCratosManager($ticketMessage->ticket);
        return redirect()->to(url()->previous().'#tickets')->with('status', t('ticket_message_successfully_added'))->withInput();
    }

    public function closeTicket($id, TicketService $ticketService)
    {
        $ticket = $ticketService->closeTicket($id);
        if ($ticket) {
            $url = back()->with('status', t('ticket_successful_closed'))->getTargetUrl() . '?status=' . t('ticket_closed') . '#tickets';
            EmailFacade::ticketClosureNotification($ticket->user, $ticket->ticket_id);
            return redirect($url);
        }
        return redirect()->back();
    }

    public function viewMessage($ticketId, $type, TicketMessageService $ticketMessageService)
    {
        $ticketMessageService->viewMessage($ticketId, TicketMessages::NAMES[$type]);
    }

    public function downloadTicketMessagePdfFile($file)
    {
        if (file_exists(storage_path('app/images/ticket-messages/') . $file)){
            return response()->download(storage_path('app/images/ticket-messages/') . $file, $file);
        }
        abort(404);
    }
}
