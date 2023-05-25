<div id="tickets" class="container-fluid tab-pane fade pl-0 mt-5"><br>
    <div class="row">
        <div class="col-md-4 mt-3">
            <h2 class="textBold">{{ t('backoffice_client_view_tickets_header', ['id' => 5]) }}</h2>
            <button data-toggle="modal" data-target="#addTicket" class="btn themeBtn" style="border-radius: 20px" type="submit">{{ t('ui_new_ticket') }}</button>
        </div>
    </div>

    @if($openTickets->count() + $closedTickets->count())
        <div class="row mt-4">
            <div class="col-md-4 tickets-block pl-4 pr-5">
                <div class="row pl-2">
                    <div class="col-md-12 mt-3 row pr-0">
                        <div class="col ticket-status-buttons ml-0 {{ ((!request()->status || request()->status ==  t('ticket_opened')) || request()->status !=  t('ticket_closed')) ? 'ticket-active': 'ticket-inactive'}} pointerClass" style="max-width: 120px">
                            <span class="{{ (!request()->status || request()->status ==  t('ticket_opened')) ? 'requestStatus': ''}}">{{ t('ticket_opened') }}</span>
                        </div>
                        <div class="col ticket-status-buttons {{ request()->status ==  t('ticket_closed') ? 'ticket-active': 'ticket-inactive'}} pointerClass" style="max-width: 120px">
                            <span class="{{ request()->status ==  t('ticket_closed') ? 'requestStatus': ''}}">{{ t('ticket_closed') }}</span>
                        </div>
                        <form class="mt-4 pt-3 mb-4 w-100" action="">
                            <p class="textBold fs20 mb-0">{{ t('ui_search') }}</p>
                            <input type="text" class="w-100 p-2" name="search" id="search" value="{{ request()->sInput }}" placeholder="Type search text here...">
                        </form>
                    </div>
                    @if($openTickets->count())
                        <div id="activeTickets" class="row w-100 {{ ((!request()->status || request()->status ==  t('ticket_opened')) || request()->status !=  t('ticket_closed')) ? '': 'd-none'}} tickets ml-0">
                            @foreach($openTickets as $ticket)
                                <div class="col-md-12 mt-3 ticket-style {{ old('ticket-id') == $ticket->id ? 'ticket-active' : 'ticket-inactive'}}" data-ticket-id="{{ $ticket->id }}">
                                    <div class="statusIcon {{ $ticket->allMessagesViewed ? 'statusIconActive' : 'statusIconInactive' }}"></div>
                                    <p class="textBold fs20">ID: {{ $ticket->ticket_id }}</p>
                                    <p class="textBold fs20">{{ $ticket->subject }}</p>
                                    <p>{{ $ticket->shortQuestion }}</p>
                                    <p>{{ $ticket->created_at }}</p>
                                </div>
                            @endforeach
                            @if($openTickets->count())
                                <div class="col-md-12 mt-3">
                                    {{ $openTickets->appends(request()->all())->fragment('tickets')->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                    <div id="inactiveTickets" class="row w-100 {{ request()->status ==  t('ticket_closed') ? '': 'd-none'}} tickets ml-0">
                        @foreach($closedTickets as $ticket)
                            <div class="col-md-12 mt-3 ticket-style {{ old('ticket-id') == $ticket->id ? 'ticket-active' : 'ticket-inactive'}}" data-ticket-id="{{ $ticket->id }}">
                                <div class="statusIcon {{ $ticket->allMessagesViewed ? 'statusIconActive' : 'statusIconInactive' }}"></div>
                                <p class="textBold fs20">ID: {{ $ticket->ticket_id }}</p>
                                <p class="textBold fs20">{{ $ticket->subject }}</p>
                                <p>{{ $ticket->shortQuestion }}</p>
                                <p>{{ $ticket->created_at }}</p>
                            </div>
                        @endforeach
                        @if($closedTickets->count())
                            <div class="col-md-12 mt-3">
                                {{ $closedTickets->appends(request()->all())->fragment('tickets')->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row d-none" id="messageBox">
                    <div class="col-md-8 pl-5">
                        <p class="textBold fs16 pl-2"><span id="subject"></span><br>
                            ID: <span id="id"></span> <span id="status"></span></p>
                    </div>
                    <div class="col-md-4" id="closeTicketButton"></div>
                    <div id="messagingBlock" class="row ml-2"></div>
                    <div class="col-md-12 mt-5 pl-5">
                        <p class="textBold fs20">{{ t('ui_reply') }}</p>
                        <form enctype="multipart/form-data" action="{{ route('backoffice.send.ticket.message') }}" method="post">
                            @csrf
                            <input type="hidden" name="ticket-id" id="ticketIdMessageForm">
                            <input type="hidden" name="to" value="{{ $profile->cUser->id }}">
                            <textarea placeholder="Type text here..." name="message" id="replyMessage" cols="30" rows="7">{{ session()->has('status') ? '' : old('message') }}</textarea>
                            @error('message')<p class="text-danger">{{ $message }}</p>@enderror
                            <input name="m_file" type="file" class="border-none" style="display: none" id="createTicketFile">
                            <p class="underlineText pointerClass" id="createTicketIcon"><i class="fa fa-file"></i> {{ t('ui_browse_file') }}
                                <br> <span class="textBold" id="uploadFileName"></span></p>
                            @error('m_file')<p class="text-danger">{{ $message }}</p>@enderror
                            @error('to')<p class="text-danger">{{ $message }}</p>@enderror
                            <button type="submit" class="btn themeBtn mt-3" style="border-radius: 20px;">{{ t('ui_send') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h1 class="mt-4" align="center">{{ t('ui_empty_backoffice_tickets') }}</h1>
    @endif

    <div class="modal fade modal-center" id="addTicket" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;border-radius: 5px;padding: 25px;width: 500px">
                <div class="modal-body">
                    <button type="button" class="close" data-dismiss="modal" style="position: absolute; top: -10px;right: 0">×</button>
                    <form enctype="multipart/form-data" name="ticketForm" id="ticketForm" action="{{ route('backoffice.store.ticket') }}" method="post">
                        <h1>{{ t('new_ticket_header') }}</h1>
                        <p>{{ t('ticket_modal_text') }}</p>
                        @csrf
                        <input type="hidden" name="to_client" value="{{ $profile->cUser->id }}">
                        <label for="" class="textBold fs20">{{ t('ticket_subject') }}</label><br>
                        <input type="text" name="subject" style="width: 100%" class="ticketSubject" value="{{ old('subject') }}"><br>
                        @error('subject')<p class="text-danger">{{ $message }}</p>@enderror
                        <label for="" class="textBold fs20 mt-4">{{ t('ticket_question') }}</label><br>
                        <textarea name="question" id="" cols="30" rows="5" class="ticketQuestion">{{ old('question') }}</textarea>
                        @error('question')<p class="text-danger">{{ $message }}</p>@enderror
                        <input name="file" type="file" class="border-none" style="display: none" id="updateTicketFile">
                        <p class="underlineText pointerClass" id="updateTicketIcon"><i class="fa fa-file"></i> {{ t('ui_browse_file') }}
                            <br> <span class="textBold" id="uploadFileNameTicket"></span></p>
                        @error('file')<p class="text-danger">{{ $message }}</p>@enderror
                        <button type="submit" id="ticketCreate" class="btn themeBtn btn-centered" style="border-radius: 25px">{{ t('ui_send') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


