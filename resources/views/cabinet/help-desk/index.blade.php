@extends('cabinet.layouts.cabinet')
@section('title', t('help_desk'))

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="row mb-0 mb-sm-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('help_desk') }}</h2>
                    <button data-toggle="modal" data-target="#addTicket" class="btn themeBtn new-ticket-btn" style="border-radius: 20px" type="submit">{{ t('new_ticket') }}</button>
                    <div class="row" style="margin-top: -30px;">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('ui_request_48_hours') }}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session()->has('status'))
        <div class="alert alert-success alert-dismissible" role="alert" id="alertMessage">
            <h4 class="alert-heading">{{ session()->get('status') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible" role="alert" id="alertMessage">
            <h4 class="alert-heading">{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($openTickets->count() + $closedTickets->count())
        <div class="row pl-2 mt-5">
            <div class="ticket-status-buttons {{ ((!request()->status || request()->status ==  t('ticket_opened')) || request()->status !=  t('ticket_closed')) ? 'ticket-active': 'ticket-inactive'}} pointerClass">
                <span class="{{ (!request()->status || request()->status ==  t('ticket_opened')) ? 'requestStatus': ''}}">{{ t('ticket_opened') }}</span>
                @if($active_tickets)
                    <span class="tickets-count cabinetTicketsCount">{{ $active_tickets }}</span>
                @endif
            </div>
            <div class="ticket-status-buttons {{ request()->status ==  t('ticket_closed') ? 'ticket-active': 'ticket-inactive'}} pointerClass">
                <span class="{{ request()->status ==  t('ticket_closed') ? 'requestStatus': ''}}">{{ t('ticket_closed') }}</span>
                @if($closed_tickets)
                    <span class="tickets-count cabinetTicketsCount">{{ $closed_tickets }}</span>
                @endif
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-4 tickets-block pr-3 pr-md-4">
                <div class="row pl-2">
                    <div class="col-md-12 pl-1">
                        <form action="">
                            <h5 class="mb-0">{{ t('ui_search') }}</h5>
                            <input type="text" name="search" id="search" placeholder="Type search text here..." value="{{ request()->sInput }}">
                        </form>
                    </div>
                    @if($openTickets->count())
                        <div id="activeTickets" class="row {{ ((!request()->status || request()->status ==  t('ticket_opened')) || request()->status !=  t('ticket_closed')) ? '': 'd-none'}} tickets w-100 mt-4 pl-4 pl-sm-3">
                            @foreach($openTickets as $ticket)
                                <div class="col-md-12 mt-3 ticket-style {{ old('ticket-id') == $ticket->id ? 'ticket-active' : 'ticket-inactive'}}" data-ticket-id="{{ $ticket->id }}">
                                    <div class="statusIcon {{ $ticket->allMessagesViewed ? 'statusIconActive' : 'statusIconInactive' }}"></div>
                                    <p class="textBold fs20">ID: {{ $ticket->ticket_id }}</p>
                                    <p class="textBold fs20">{{ $ticket->subject }}</p>
                                    <p>{{ $ticket->shortQuestion }}</p>
                                    <p class="text-custom-light">{{ $ticket->created_at->timezone(auth()->user()->cProfile->timezone) }}</p>
                                </div>
                            @endforeach
                            @if($openTickets->count())
                                <div class="col-md-12 mt-3">
                                    {{ $openTickets->appends(request()->all())->links() }}
                                </div>
                            @endif
                        </div>
                    @endif
                    <div id="inactiveTickets" class="row {{ request()->status ==  t('ticket_closed') ? '': 'd-none'}} tickets w-100 mt-4 pl-4 pl-sm-3">
                        @foreach($closedTickets as $ticket)
                            <div class="col-md-12 mt-3 ticket-style {{ old('ticket-id') == $ticket->id ? 'ticket-active' : 'ticket-inactive'}}" data-ticket-id="{{ $ticket->id }}">
                                <div class="statusIcon {{ $ticket->allMessagesViewed ? 'statusIconActive' : 'statusIconInactive' }}"></div>
                                <p class="textBold fs20">ID: {{ $ticket->ticket_id }}</p>
                                <p class="textBold fs20">{{ $ticket->subject }}</p>
                                <p>{{ $ticket->shortQuestion }}</p>
                                <p class="text-custom-light">{{ $ticket->created_at->timezone(auth()->user()->cProfile->timezone) }}</p>
                            </div>
                        @endforeach
                        @if($closedTickets->count())
                            <div class="col-md-12 mt-3">
                                {{ $closedTickets->appends(request()->all())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row d-none" id="messageBox">
                    <div class="col-md-8 m-4 m-md-0 pl-0 pl-md-5 text-center text-md-left">
                        <h5 class="pl-1"><span id="subject"></span><br>
                        ID: <span id="id"></span> <span id="status"></span></h5>
                    </div>
                    <div class="col-md-4 mb-4 text-center text-md-right" id="closeTicketButton"></div>
                    <div id="messagingBlock" class="row ml-0 ml-md-2"></div>
                    <div class="col-md-12 mt-5 pl-3 pl-md-5 ml-1">
                        <h5 class="mb-4">{{ t('ui_reply') }}</h5>
                        <form enctype="multipart/form-data" action="{{ route('cabinet.send.ticket.message') }}" method="post">
                            @csrf
                            <input type="hidden" name="ticket-id" id="ticketIdMessageForm">
                            <input type="hidden" name="to" value="{{ auth()->user()->cProfile->getManager()->id }}">
                            <textarea placeholder="Type text here..." name="message" id="replyMessage" cols="30" rows="7">{{ session()->has('status') ? '' : old('message') }}</textarea>
                            @error('message')<p class="text-danger">{{ $message }}</p>@enderror
                            <input name="m_file" type="file" class="border-none" style="display: none" id="createTicketFile">
                            <p class="underlineText pointerClass mt-3" id="createTicketIcon"><i class="fa fa-file-text-o"></i> {{ t('ui_browse_file') }}
                                <br> <span class="textBold" id="uploadFileName"></span></p>
                            @error('m_file')<p class="text-danger">{{ $message }}</p>@enderror
                            <button type="submit" class="btn themeBtn mt-3" style="border-radius: 20px;">{{ t('ui_send') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <h6>{{ t('ui_empty_tickets') }}</h6>
    @endif

    <div class="modal fade modal-center" id="addTicket" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="max-width: 500px">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('new_ticket_header') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form enctype="multipart/form-data" name="ticketForm" id="ticketForm" action="{{ route('cabinet.store.ticket') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>{{ t('ticket_modal_text') }}</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">{{ t('ticket_subject') }}</label>
                                    <input class="form-control" type="text" name="subject" class="ticketSubject" value="{{ old('subject') }}">
                                    @error('subject')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="font-weight-bold">{{ t('ticket_question') }}</label><br>
                                    <textarea name="question" id="" cols="30" rows="5" class="ticketQuestion">{{ old('question') }}</textarea>
                                    @error('question')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input name="file" type="file" class="border-none" style="display: none" id="updateTicketFile">
                                    <p class="underlineText pointerClass" id="updateTicketIcon"><i class="fa fa-file-text-o"></i> {{ t('ui_browse_file') }}
                                        <br> <span class="textBold" id="uploadFileNameTicket"></span></p>
                                    @error('file')<p class="text-danger">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="ticketCreate" class="btn btn-lg btn-primary themeBtn">{{ t('ui_send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('body').on('click', '#createTicketIcon', function () {$('#createTicketFile').click();});
            $('body').on('click', '#updateTicketIcon', function () {$('#updateTicketFile').click();});
            $('#createTicketFile').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileName').html(filename);
            });
            $('#updateTicketFile').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileNameTicket').html(filename);
            });
            $('#addTicket').on('hidden.bs.modal', function () {
                $('.text-danger').each(function () {
                    $(this).text('');
                })
            });
            setTimeout(function () {
                $('#alertMessage').remove();
            }, 2000);

            $(document).ready(function () {
                if ('{{ old('ticket-id') }}') {
                    let id = "{{ old('ticket-id') }}";
                    $('div[data-ticket-id="' + id + '"]').click();
                }
            });

            function getWithZero(val) {
                if (val < 10){
                    return '0' + val;
                }
                return val;
            }

            function getCorrectDate(date) {
                return getWithZero(date.getFullYear()) + '-' +
                    getWithZero(date.getMonth() + 1) + '-' +
                    getWithZero(date.getDate()) +  ' ' +
                    getWithZero(date.getHours()) +  ':' +
                    getWithZero(date.getMinutes()) +  ':' +
                    getWithZero(date.getSeconds());
            }

            function viewMessage(id, type) {
                $.ajax({
                    url: 'view-message/' + id + '/{{ \App\Enums\TicketMessages::VIEW_CUSER }}'
                })
            }

            $('body').on('click', '.ticket-style', function () {
                $('#messageBox').removeClass('d-none');
                let ticket = $(this);
                ticket.removeClass('ticket-inactive').addClass('ticket-active');
                let ticketId = ticket.data('ticket-id');
                $('#ticketIdMessageForm').val(ticketId);
                $('.ticket-style').each(function () {
                    if ($(this).data('ticket-id') !== ticketId) {
                        $(this).removeClass('ticket-active').addClass('ticket-inactive');
                    }
                });
                $.ajax({
                    url: 'ticket/' + ticketId,
                    success: function (data) {
                        if (data) {
                            $('#closeTicketButton').html('');
                            if (data.createdByClient && (data.status !== '{{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) }}')) {
                                $('#closeTicketButton').prepend('<a href="' + '{{ route('cabinet.close.ticket', ['id' => false]) }}' + '/' + data.id + '" class="btn themeBtn close-ticket-btn" style="border-radius: 20px" type="submit">' + '{{ t('close_ticket') }}' + '</a>');
                            }
                            $('#id').text(data.ticket_id);
                            $('#subject').text(data.subject);
                            $('#status').text(data.status);
                            $('#messagingBlock').html('');
                            if ($.isEmptyObject(data.messages)) {
                                $('#messagingBlock').prepend('<div class="col-md-12 message-block">\n<h1>' + '{{ t('empty_messages')}}' + '</h1></div>');
                            }
                            let allMessagesViewed = false;
                            for (let ticketMessage of data.messages) {
                                let statusIcon = ticketMessage.viewed ? 'statusIconInactive' : 'statusIconActive';
                                if (!ticketMessage.viewed) {
                                    allMessagesViewed = true;
                                }
                                let messageBlock = '<div class="col-md-12 message-block">\n' +
                                    '<div class="statusIconMessage ' + statusIcon + '"></div>\n' +
                                    '<p><span class="textBold">' + ticketMessage.creatorName + '</span> <span class="pl-2 text-custom-light">' + getCorrectDate(new Date(ticketMessage.created_at)) + '</span>';
                                if (ticketMessage.file) {
                                    messageBlock += ' <i class="fa fa-file-text-o"></i><span class="pointerClass underlineText"><a href="/cabinet/cabinet-download-ticket-message-pdf-file/' + ticketMessage.file + '">' + ticketMessage.file + '</a></span></p>\n';
                                }
                                messageBlock += '<p>' + ticketMessage.message + '</p>\n' +
                                    '</div>';
                                $('#messagingBlock').prepend(messageBlock);
                            }
                            if (!allMessagesViewed) {
                                ticket.find(">:first-child").removeClass('statusIconActive').addClass('statusIconInactive');
                                $('.cabinetTicketsCount').each(function () {
                                    let count = parseInt($(this).text());
                                    if (count > 1) {
                                        $(this).html(--count);
                                    } else {
                                        $(this).addClass('d-none');
                                    }
                                });
                            }
                            viewMessage(data.id, 5);

                            $([document.documentElement, document.body]).animate({
                                scrollTop: $("#messageBox").offset().top - 100
                            }, 500);
                        }
                    }
                });
            });

            function searchTicket(sInput)
            {
                $.ajax({
                   url: '{{ route('cabinet.help.desk') }}',
                    data: {sInput}
                });
            }

            $('body').on('change', '#search', function () {
                let search = $(this).val();
                let status = $('.requestStatus').text();
                if($.isNumeric(search)) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?sInput=' + search + '&id=1&status=' + status;
                } else if (search.length > 2) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?sInput=' + search + '&id=0&status=' + status;
                } else if (search == '' && !search) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?status=' + status;
                }
            });
        });

        $('body').on('click', '.ticket-status-buttons', function () {
            if ($(this).hasClass('ticket-inactive')) {
                $('.ticket-status-buttons').each(function () {
                    $(this).removeClass('ticket-active').addClass('ticket-inactive');
                });
                $('.requestStatus').removeClass('requestStatus');
                $('#messageBox').addClass('d-none');
                $(this).removeClass('ticket-inactive').addClass('ticket-active');
                $(this).find(">:first-child").addClass('requestStatus');
                $('.tickets').each(function () {
                    if ($(this).hasClass('d-none')) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });

                $(".ticket-style").removeClass("ticket-active");
            }
        });

        if ('{{ $errors->any() }}' && '{{ !$errors->has('message') }}' && '{{ !$errors->has('m_file') }}') {
            $('#addTicket').modal('show');
        }
    </script>
@endsection
