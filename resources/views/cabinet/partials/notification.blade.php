<div class="col-lg-7" id="notificationSection" data-notification-id="{{ $notify->id ?? '' }}">
    @if($notify)
        @php $notification = $notify->notification @endphp
        <div class="compliance common-shadow-theme">
        <div class="info-label d-none">
                <i class="fa fa-exclamation" aria-hidden="true"></i>
            </div>
        <div class="col">
            <h2 class="mb-3 pt-3">{{ \Illuminate\Support\Facades\Lang::has('cratos.'.$notification->title_message) ? t($notification->title_message, json_decode($notification->title_params, true)) : $notification->title_message }}</h2></div>
            <div class="row m-0">
                <div class="col-lg-9">
                    <p class="breakWord">{!! htmlCut($notification->shortBody, 120) !!}</p>
                </div>
                <div class="col-lg-3">
                    @if ($admin)
                        <a href="{{ route('verify.notification.admin', ['id' => $notify->id]) }}" class="btn btn-lg btn-primary themeBtn register-buttons mb-4" type="submit">{{ t('ui_show') }}</a>
                    @else
                        <a href="{{ route('cabinet.notifications.index') }}" class="btn btn-lg btn-primary themeBtn register-buttons mb-4" type="submit">{{ t('ui_show') }}</a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
