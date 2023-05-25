@extends('backoffice.layouts.backoffice')
@section('title', t('title_new_notifications_page'))

@section('content')
    <div class="container-fluid">
        <div class="row pb-3">
            <div class="col-md-12">
                <h2 class="mb-3 mt-2 large-heading-section">{{ t('notification_heading') }} : {{ $notification->id }}</h2>
                <div class="row">
                    <div class="col-lg-5 d-block d-md-flex subheader-title">
                        <p>{{ t('backoffice_profile_page_header_body') }}</p>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>
        <div class="col-5">
            <div class="form-group">
                <h5>{{ t('ui_number_notification') }}</h5>
                <p>{{ $notification->id }}</p>
              </div>
            <div class="form-group mt-4">
                <h5>{{ t('ui_date_time') }}</h5>
                <p>{{ $notification->updated_at }}</p>
            </div>
            <div class="form-group mt-4">
                <h5>{{ t('notification_recepient') }}</h5>
                <p>{{ $notification->bUser ? $notification->bUser->email : 'System' }}</p>
            </div>
            <div class="form-group mt-4">
                <h5>{{ t('ui_status') }}</h5>
                <p>{{ $notification->allNotificationUsersCount ? 'Delivered' : 'Not Send' }}</p>
             </div>
            <div class="form-group mt-4">
                <h5>{{ t('ticket_subject') }}</h5>
                <p>{{ \Illuminate\Support\Facades\Lang::has('cratos.'.$notification->title_message) ? t($notification->title_message, json_decode($notification->title_params, true)) : $notification->title_message }}</p>
              </div>
            <div class="form-group mt-4">
                <h5>{{ t('ui_message_lowercase') }}</h5>
                <p>{!! $notification->body !!}</p>
              </div>
        </div>

     </div>
@endsection
