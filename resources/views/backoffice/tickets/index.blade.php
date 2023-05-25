@extends('backoffice.layouts.backoffice')
@section('title', t('ui_tickets'))

@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
            <h4>{{ session()->get('success') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container-fluid">
        @include('backoffice.partials.tickets-header')
        <div class="row flex-column">
            <div class="col-md-12">
                <div class="row">
                    <div data-status="{{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN) }}" class="col-md-2 ticket-status-buttons pointerClass {{ !request()->status || request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN) ? 'ticket-active' : 'ticket-inactive' }}" style="max-width: 125px">
                        {{ t('ticket_opened') }}
                        @if($backoffice_open_tickets_count)
                            <span class="tickets-count">{{ $backoffice_open_tickets_count }}</span>
                        @endif
                    </div>
                    <div data-status="{{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) }}" class="col-md-2 ticket-status-buttons pointerClass {{ request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) ? 'ticket-active' : 'ticket-inactive' }}" style="max-width: 125px">
                        {{ t('ticket_closed') }}
                        @if($backoffice_closed_tickets_count)
                            <span class="tickets-count">{{ $backoffice_closed_tickets_count }}</span>
                        @endif
                    </div>
                    <div data-status="{{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_NEW) }}" class="col-md-2 ticket-status-buttons pointerClass {{ request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_NEW) ? 'ticket-active' : 'ticket-inactive' }}" style="max-width: 125px">
                        {{ t('ticket_new') }}
                        @if($backoffice_new_tickets_count)
                            <span class="tickets-count">{{ $backoffice_new_tickets_count }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @if(session()->has('error'))
                <div class="alert alert-error alert-dismissible fade show mt-5" role="alert" id="errorMessageAlert">
                    <h4>{{ session()->get('error') }}</h4>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <div class="col-md-12 mt-5">
                <form action="">
                    <div class="row align-items-end display: flex;">
                        <input type="hidden" name="status" id="status" value="{{ request()->status ?? \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN) }}">
                        <div class="col-md-2">
                            <div class="textBold fs20 mb-4">{{ t('client') }}</div>
                            <input type="text" name="client" value="{{ request()->client }}">
                            @error('client')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class=" col-md-2 ">
                            <div class="textBold fs20 mb-4">{{ 'Project' }}</div>
                            <select style="" data-permission="{{ \App\Enums\BUserPermissions::ADD_ANSWER_TICKETS }}" class="w-100 form-control projectSelect" name="project_id">
                                <option style="appearance: none" value="">All</option>
                                @foreach($activeProjects as $project)
                                    <option @if($projectId == $project->id) selected
                                            @endif value="{{ $project->id  }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="textBold fs20 mb-4">{{ t('ui_number') }}</div>
                            <input type="text" name="number" value="{{ request()->number }}">
                            @error('number')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-2">
                            <div class="textBold fs20 mb-4">{{ t('date') }}</div>
                            <input type="text" name="dateFrom" id="dateFrom" value="{{ request()->dateFrom }}">
                            @error('dateFrom')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="dateTo" id="dateTo" value="{{ request()->dateTo }}">
                            @error('dateTo')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-md-2">
                            <button class="btn themeBtn btn-radiused" type="submit">{{ t('ui_show') }}</button>
                        </div>
                    </div>

                </form>
                <div class="error text-danger projectSelectError"></div>

            </div>
            @if($openedTickets->count() || $closedTickets->count() || $newTickets->count())
                <div class="row mt-5 fs20 textBold themeColorRed pl-3">
                    <div class="col-md-12" id="status">
                        {{ !request()->status ? t('ticket_opened') : request()->status }}
                    </div>
                </div>
                <div class="col-md-12 mt-5">
                    <div class="row">
                        <div class="col-md-2 textBold">{{ t('ticket_table_client_id') }}</div>
                        <div class="col-md-2 textBold">{{ t('ticket_table_date') }}</div>
                        <div class="col-md-2 textBold">{{ t('ticket_table_ticket_id') }}</div>
                        <div class="col-md-2 textBold">{{ t('ticket_table_ticket_title') }}</div>
                        <div class="col-md-2 textBold">{{ t('ticket_table_status') }}</div>
                        <div class="col-md-2 textBold">{{ t('ticket_table_details') }}</div>
                    </div>
                </div>
            @endif
            @if($openedTickets->count())
                <div class="row searchResult {{ !request()->status || request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN) ? '' : 'd-none' }}">
                    @foreach($openedTickets as $ticket)
                        <div class="col-md-12">
                            <div class="row backofficeTransactionHistoryItem">
                                <div class="col-md-2">{{ $ticket->user->cProfile->profile_id }}</div>
                                <div class="col-md-2">{{ $ticket->created_at }}</div>
                                <div class="col-md-2">{{ $ticket->ticket_id }}</div>
                                <div class="col-md-2">{{ $ticket->subject }}</div>
                                <div class="col-md-2">{{ \App\Enums\TicketStatuses::getName($ticket->status) }}</div>
                                <div class="col-md-2">
                                    <a href="{{ route('backoffice.profile', ['profileId' => $ticket->user->cProfile->id]) . '?status='.\App\Enums\TicketStatuses::getName($ticket->status).'#tickets' }}">
                                        {{ t('see_details_link') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{ $openedTickets->appends(array_merge(request()->all(), ['status' => \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN)]))->links() }}
                </div>
            @else
                <div class="mt-5 col-md-12 searchResult {{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_OPEN) }}">
                    <h6 class="textBold">{{ t('ui_no_results') }}</h6>
                </div>
            @endif
            @if($closedTickets->count())
                <div class="row searchResult {{ request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) ? '' : 'd-none' }}">
                    @foreach($closedTickets as $ticket)
                        <div class="col-md-12">
                            <div class="row backofficeTransactionHistoryItem">
                                <div class="col-md-2">{{ $ticket->user->cProfile->profile_id }}</div>
                                <div class="col-md-2">{{ $ticket->created_at }}</div>
                                <div class="col-md-2">{{ $ticket->ticket_id }}</div>
                                <div class="col-md-2">{{ $ticket->subject }}</div>
                                <div class="col-md-2">{{ \App\Enums\TicketStatuses::getName($ticket->status) }}</div>
                                <div class="col-md-2">
                                    <a href="{{ route('backoffice.profile', ['profileId' => $ticket->user->cProfile->id]) . '?status='.\App\Enums\TicketStatuses::getName($ticket->status).'#tickets' }}">
                                        {{ t('see_details_link') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{ $closedTickets->appends(array_merge(request()->all(), ['status' => \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE)]))->links() }}
                </div>
            @else
                <div class="mt-5 col-md-12 searchResult d-none {{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) }}">
                    <h6 class="textBold">{{ t('ui_no_results') }}</h6>
                </div>
            @endif
            @if($newTickets->count())
                <div class="row searchResult {{ request()->status == \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_NEW) ? '' : 'd-none' }}">
                    @foreach($newTickets as $ticket)
                        <div class="col-md-12">
                            <div class="row backofficeTransactionHistoryItem">
                                <div class="col-md-2">{{ $ticket->user->cProfile->profile_id }}</div>
                                <div class="col-md-2">{{ $ticket->created_at }}</div>
                                <div class="col-md-2">{{ $ticket->ticket_id }}</div>
                                <div class="col-md-2">{{ $ticket->subject }}</div>
                                <div class="col-md-2">{{ \App\Enums\TicketStatuses::getName($ticket->status) }}</div>
                                <div class="col-md-2">
                                    <a href="{{ route('backoffice.profile', ['profileId' => $ticket->user->cProfile->id]) . '?status='.\App\Enums\TicketStatuses::getName($ticket->status).'#tickets' }}">
                                        {{ t('see_details_link') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    {{ $newTickets->appends(array_merge(request()->all(), ['status' => \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_NEW)]))->links() }}
                </div>
            @else
                <div class="mt-5 col-md-12 searchResult d-none {{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_NEW) }}">
                    <h6 class="textBold">{{ t('ui_no_results') }}</h6>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#dateFrom').datepicker({ format: 'yyyy-mm-dd' });
            $('#dateTo').datepicker({ format: 'yyyy-mm-dd' });

            $('body').on('click', '.ticket-status-buttons', function () {
                let status = $(this).data('status');
                location.href = '{{ route('backoffice.tickets') }}'+'?status='+status;
            });
        });
    </script>
@endsection

