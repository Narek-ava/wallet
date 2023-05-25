@extends('cabinet.layouts.cabinet')
@section('title', t('notification_page_heading'))

@section('content')
    <div class="row mb-2">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('notification_page_heading') }}</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">{{ t('ui_request_48_hours') }}</div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2 w-100 m-0">
        @foreach($notifications as $notification)
            <div class="col-md-12 mt-3 clientNotificationItem card-default">
                <div class="row">
                    <div class="col-md-10">
                        <div class="notificationStatusButton statusButton{{ $notification->id }} {{ $notification->viewed_at ? 'notificationStatusButtonInactive' : 'notificationStatusButtonActive' }}"></div>
                        <p class="textBold ml30">{{ $notification->title }}</p>
                        <p class="ml30 shortBodyMessage{{ $notification->id }}">{!! \Illuminate\Support\Str::limit($notification->shortBody, 50, $end='...') !!}</p>
                        <p class="ml30 bodyMessage{{ $notification->id }} display-none">{!! $notification->body !!}</p>
                    </div>
                    <div class="col-md-2 notificationCabinetMoreMessageDown">
                        <p>{{ $notification->created_at->timezone($timezone) }}</p>
                        <p>
                            <span class="link-default seeMore cursor-pointer text-nowrap" href="#" data-notification-id="{{ $notification->id }}">
                                See Details <i class="fa fa-angle-toggle" aria-hidden="true"></i>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-12 mt-3 pl-0">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('body').on('click', '.seeMore', function () {
            let notificationId = $(this).data('notification-id');
            let notificationBlock = $(this).parent().parent();
            if(notificationBlock.hasClass('notificationCabinetMoreMessageUp')) {
                notificationBlock.removeClass('notificationCabinetMoreMessageUp').addClass('notificationCabinetMoreMessageDown');
                $('.shortBodyMessage'+notificationId).removeClass('display-none');
                $('.bodyMessage'+notificationId).addClass('display-none');
            } else if(notificationBlock.hasClass('notificationCabinetMoreMessageDown')) {
                notificationBlock.removeClass('notificationCabinetMoreMessageDown').addClass('notificationCabinetMoreMessageUp');
                $('.shortBodyMessage'+notificationId).addClass('display-none');
                $('.bodyMessage'+notificationId).removeClass('display-none');

                let clientNotificationItemBlock = notificationBlock.closest(".clientNotificationItem");

                if (clientNotificationItemBlock.find(".notificationStatusButtonInactive").length > 0) {
                    return;
                }

                $.ajax({
                    url: '{{ route('verify.notification') }}',
                    data: {id: notificationId},
                    success: function () {
                        $('.notifications-count').text('{{ --$notifications_count_client }}');
                        $('.statusButton'+notificationId).removeClass('notificationStatusButtonActive')
                            .addClass('notificationStatusButtonInactive');
                        let notificationBlockId = $('#notificationSection').data('notification-id');
                        if (notificationId === notificationBlockId) {
                            $.ajax({
                                url: '/cabinet/get-notification',
                                success: function success(data) {
                                    $('#notificationSection').replaceWith(data);
                                }
                            });
                        }
                    }
                });
            }
        })
    });
</script>
@endsection

