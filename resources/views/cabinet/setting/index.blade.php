@extends('cabinet.layouts.cabinet')
@section('title', t('title_settings_page'))

@section('content')

    @include('cabinet._modals._success')
    @include('cabinet._modals.result-fail')
    @include('cabinet._modals.2fa-google-register')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
        <h1 class="h2">{{__('Settings')}}</h1>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-lg-5"></div>
            @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
        </div>
    </div>
    <div class="container p-0 ml-0">
        <div class="row">
            <div class="col-md-12">
                @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                    @include('cabinet.setting._personal_corporate_form')
                @else
                    @include('cabinet.setting._personal_form')
                @endif

                @include('cabinet.setting._password_form')
                @include('cabinet.setting._email_form')

                <div class="row">
                    <div class="col-md-6">
                        <h2 class="mt-5">{{ t('ui_2fa_settings_google_header') }}</h2>
                        <p>{{ t('ui_2fa_settings_google_recommended') }}</p>
                    </div>

                    <div class="col-md-4">
                        <h2 class="mt-5">{{ t('ui_cprofile_status') }}</h2>
                        <div id="2fa-google-status" class="d-block font36bold"></div>

                        <button id="2fa-google-button" class="btn btn-lg btn-primary themeBtn register-buttons mr-3 mt-3"
                                type="button">{{ t('ui_cprofile_enable') }}
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h2 class="mt-5">{{ t('ui_2fa_settings_email_header') }}</h2>
                        <p>{{ t('ui_2fa_settings_email_recommended') }}</p>
                    </div>

                    <div class="col-md-4">
                        <h2 class="mt-5">{{ t('ui_cprofile_status') }}</h2>
                        <div id="2fa-email-status" class="d-block font36bold"></div>

                        <button id="2fa-email-button" class="btn btn-lg btn-primary themeBtn register-buttons mr-3 mt-3"
                                type="button">{{ t('ui_cprofile_enable') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/cabinet/setting.js"></script>
    <script src="/js/ceo-and-beneficial-owners.js"></script>
    <script src="/js/cabinet/2fa.js?v={{ time() }}"></script>
@stop

@include('cabinet._modals.2fa-confirm-cabinet')
@include('cabinet._modals.2fa-message')
