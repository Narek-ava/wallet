@extends('backoffice.layouts.backoffice')
@section('title', t('notification_page_heading'))

@section('content')
    <div class="container-fluid">
        @include('backoffice.partials.notification-header')
        <div class="row">
            @if ($notificationsWithPaginate->count())
                <div class="col-md-12"><h2 class="themeColorRed">{{ t('title_new_notifications_page') }}</h2></div>
                <div class="col-md-12 pl-5 pr-5">
                    <div class="row">
                        <div class="col-md-2 activeLink">{{ t('ui_bo_c_profile_page_log_date') }}</div>
                        <div class="col-md-3 activeLink">{{ t('ui_user_name') }}</div>
                        <div class="col-md-2 activeLink">{{ t('ui_tag') }}</div>
                        <div class="col-md-3 activeLink">{{ t('ui_message') }}</div>
                        <div class="col-md-2 activeLink"></div>
                    </div>
                </div>
                @foreach($notificationsWithPaginate as $notification)
                    <div class="col-md-12">
                        <div class="row createdNotifications">
                            <div class="col-md-2 createdNotificationsItems activeLink">{{ $notification->updated_at }}</div>
                            <div class="col-md-3 createdNotificationsItems activeLink">{{ $notification->bUser ? $notification->bUser->email : 'System' }}</div>
                            <div class="col-md-2 createdNotificationsItems activeLink">{{ \Illuminate\Support\Facades\Lang::has('cratos.'.$notification->title_message) ? t($notification->title_message, json_decode($notification->title_params, true)) : $notification->title_message }}</div>
                            <div class="col-md-3 createdNotificationsItems">
                            <span id="shortMessage{{ $notification->id }}">{!! \Illuminate\Support\Str::limit($notification->shortBody, 50, $end='...') !!}</span>
                            <span id="message{{ $notification->id }}" class="display-none">{!! $notification->body !!}</span>
                            </div>
                            <div class="col-md-2 createdNotificationsItems notificationCabinetMoreMessageDown">
                                <span class="link-default seeMore cursor-pointer text-nowrap" href="#" data-notification-id="{{ $notification->id }}">
                                    See Details <i class="fa fa-angle-toggle" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
                {{ $notificationsWithPaginate->appends(request()->query())->links() }}
                <br><br><br><br><br>
            @endif
            <div class="col-md-12 mt-5" id="historyOfNotifications"><h2>History of notifications</h2></div>
            <div class="pl-3" style="width: 100%">
                <form action="{{ route('backoffice.notifications.history').'#historyOfNotifications' }}">
                    <div class="width15" style="display: inline-block">
                        <input name="from" id="from" value="{{ app('request')->input('from') }}" placeholder="From">
                    </div>
                    <div class="width15" style="display: inline-block">
                        <input name="to" id="to" value="{{ app('request')->input('to') }}" placeholder="To">
                        @error('to')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="width15" style="display: inline-block">
                        <input type="text" name="incoming_from" id="incomingFrom" placeholder="Incoming From" value="{{ app('request')->input('incoming_from') }}">
                    </div>
                    <div class="width15" style="display: inline-block">
                        <input type="text" name="search" id="search" placeholder="Search" value="{{ app('request')->input('search') }}">
                    </div>
                    <button class="btn themeBtn">Search</button>
                </form>
            </div>
            @if ($notificationsSearchWithPaginate->count())
                <div class="col-md-12 mt-5 pl-5 pr-5">
                    <div class="row">
                        <div class="col-md-2 activeLink">{{ t('ui_bo_c_profile_page_log_date') }}</div>
                        <div class="col-md-3 activeLink">{{ t('ui_user_name') }}</div>
                        <div class="col-md-2 activeLink">{{ t('ui_tag') }}</div>
                        <div class="col-md-3 activeLink">{{ t('ui_message') }}</div>
                        <div class="col-md-2 activeLink"></div>
                    </div>
                </div>
                @foreach($notificationsSearchWithPaginate as $notification)
                    <div class="col-md-12">
                        <div class="row createdNotifications">
                            <div class="col-md-2 createdNotificationsItems activeLink">{{ $notification->updated_at }}</div>
                            <div class="col-md-3 createdNotificationsItems activeLink">{{ $notification->bUser ? $notification->bUser->email : 'System' }}</div>
                            <div class="col-md-2 createdNotificationsItems activeLink">{{ \Illuminate\Support\Facades\Lang::has('cratos.'.$notification->title_message) ? t($notification->title_message, json_decode($notification->title_params, true)) : $notification->title_message }}</div>
                            <div class="col-md-3 createdNotificationsItems">
                            <span id="shortMessage{{ $notification->id }}">{!! \Illuminate\Support\Str::limit($notification->shortBody, 20, $end='...') !!}</span>
                            <span id="message{{ $notification->id }}" class="display-none">{{ $notification->body }}</span>
                            </div>
                            <div class="col-md-2 createdNotificationsItems notificationCabinetMoreMessageDown">
                                <span class="link-default seeMore cursor-pointer text-nowrap" href="#" data-notification-id="{{ $notification->id }}">
                                    See Details <i class="fa fa-angle-toggle" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
                {{ $notificationsSearchWithPaginate->appends(request()->query())->links() }}
            @endif
        </div>
    </div>

@endsection


@section('scripts')
    <script>
        $(document).ready(function () {
            $('body').on('click', '.seeMore', function () {
                let notificationId = $(this).data('notification-id');
                let notificationBlock = $(this).parent();
                let notificationItemContainer = $(this).closest(".createdNotifications");
                if(notificationBlock.hasClass('notificationCabinetMoreMessageUp')) {
                    notificationBlock.removeClass('notificationCabinetMoreMessageUp').addClass('notificationCabinetMoreMessageDown');
                    notificationItemContainer.find('#shortMessage'+notificationId).removeClass('display-none');
                    notificationItemContainer.find('#message'+notificationId).addClass('display-none');
                } else if(notificationBlock.hasClass('notificationCabinetMoreMessageDown')) {
                    notificationBlock.removeClass('notificationCabinetMoreMessageDown').addClass('notificationCabinetMoreMessageUp');
                    notificationItemContainer.find('#shortMessage'+notificationId).addClass('display-none');
                    notificationItemContainer.find('#message'+notificationId).removeClass('display-none');
                }
            })
        });
    </script>
@endsection
