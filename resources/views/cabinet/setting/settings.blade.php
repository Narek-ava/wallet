@extends('cabinet.layouts.cabinet')
@section('title', t('title_settings_page'))

@section('content')
    @include('cabinet._modals._success')
    @include('cabinet._modals.result-fail')
    @include('cabinet._modals.2fa-google-register')
    @include('cabinet._modals.2fa-confirm-cabinet')
    @include('cabinet._modals.2fa-message')

    <div class="col-md-12 p-0">
        <h2 class="mb-3 large-heading-section page-title">{{__('Settings')}}</h2>
        <div class="row">
            <div class="col-lg-5">
                <div class="balance">
                    {{ t('notification_page_body') }}
                </div>
            </div>
            @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
        </div>
    </div>
    <div class="col-md-12 mt-5 pt-4 pl-0">
        <div class="row">
            <div class="col-md-5 d-flex mb-4 pr-0 pr-lg-3">
                <div class="card-default w-100 p-4">
                    <h2 class="mb-4" style="color: var(--main-color)">
                        @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                            {{ $profile->company_name . ' - ID ' . $profile->profile_id }}
                        @else
                            {{ $profile->first_name . ' ' . $profile->last_name . ' - ID ' . $profile->profile_id }}
                        @endif
                    </h2>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="activeLink">{{ t('profile_wallets_account_type') }}</span><br>
                            <span>
                                @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                                    {{ t('enum_type_corporate') }}
                                @else
                                    {{ t('notification_recepient_all_individual') }}
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <span class="activeLink">{{ t('created_on') }}</span><br>
                            <span>{{$profile->created_at->timezone($profile->timezone)->format('d.m.Y')}}</span>
                        </div>
                        <div class="col-md-12 mt-3">
                            <span class="activeLink">
                                {{ t('profile_wallets_email') }}
                                <span class="cursor-pointer" data-toggle="modal" data-target="#emailModal">
                                    <i class="fa fa-pencil"></i>
                                </span>
                            </span><br>
                            <span>{{$profile->cUser->email}}</span>
                        </div>
                        <div class="col-md-12 mt-3">
                            <span class="activeLink">
                                {{ t('profile_wallets_password') }}
                                <span class="cursor-pointer" data-toggle="modal" data-target="#passwordModal">
                                    <i class="fa fa-pencil"></i>
                                </span>
                            </span><br>
                            <span>********</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md mb-4 pr-0 pr-lg-3">
                <div class="card-default p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p style="font-weight: bold;font-size:20px;">{{ t('ui_2fa') }}</p>
                            {!! t('ui_2fa_text', ['connectedStatus' => auth()->user()->getTwoFaConnectedStatusAttribute()]) !!}
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card-default p-3">
                                        <p style="font-weight: bold;font-size:15px;">{{ t('ui_2fa_confirm_google') }}</p>
                                        <label class="switch">
                                            <input type="checkbox" id="2fa-google-button" {{ $profile->cUser->two_fa_type == 1 ? 'checked' : '' }}>
                                            <span class="slider round" id="2fa-google-switch"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="card-default p-3 mt-4">
                                        <p style="font-weight: bold;font-size:15px;">{{ t('ui_email_message') }}</p>
                                        <label class="switch">
                                            <input type="checkbox" id="2fa-email-button" {{ $profile->cUser->two_fa_type == 2 ? 'checked' : '' }}>
                                            <span class="slider round" id="2fa-email-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mt-3">
        {{ t('ui_personal_information') }}
        <span class="cursor-pointer" data-toggle="modal" data-target="#personalInformationModal">
            <i class="fa fa-pencil"></i>
        </span>
    </h4>
    <div class="col-md-12 mt-5">
        <div class="row">
            <div class="col-md-12 card-default p-4">
                <div class="row">
                    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                        @include('cabinet.setting._corporate_info')
                    @else
                        @include('cabinet.setting._personal_info')
                    @endif
                </div>
            </div>
        </div>
    </div>

    <br><br>
    <h4 class="mt-3">
        {{ t('ui_timezone_information') }}
        <span class="cursor-pointer" data-toggle="modal" data-target="#timezoneInformationModal">
            <i class="fa fa-pencil"></i>
        </span>
    </h4>
    <p>{{ $profile->timezone }} ({{\Illuminate\Support\Carbon::now($profile->timezone)}})</p>

    <br><br><br><br>

    @if($profile->is_merchant)
        <h4 class="mt-5">
            {{ t('dev_integration') }}
        </h4>
        <div class="col-md-12 mt-5">
            <div class="row">
                <div class="col-md-12 card-default p-4">
                    <div class="row">
                        @include('cabinet.setting.dev_integration')
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Email change modal -->
    @include('cabinet.setting._email_form')

    <!-- Password change modal -->
    @include('cabinet.setting._password_form')

    <!-- Personal information change modal -->
    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
        @include('cabinet.setting._personal_corporate_form')
    @else
        @include('cabinet.setting._personal_form')
    @endif

    @include('cabinet.setting._timezone_info')
    <div class="overlay"></div>

@endsection

@section('scripts')
    <script src="/js/cabinet/setting.js?v={{ time() }}"></script>
    <script src="/js/ceo-and-beneficial-owners.js"></script>
    <script src="/js/cabinet/2fa.js?v={{ time() }}"></script>
    <script>
        $(document).ready(function () {
            if ('{{ $errors->has('old_email') }}' || '{{ $errors->has('email') }}' || '{{ $errors->has('email_confirmation') }}') {
                $("#emailModal").modal('show');
            }
            if ('{{ $errors->has('old_password') }}' || '{{ $errors->has('password') }}' || '{{ $errors->has('password_confirmation') }}') {
                $("#passwordModal").modal('show');
            }

            if ($.urlParam('open') === "personal_info") {
                $("#personalInformationModal").modal('show');
                $("#personalInformationModal").find(".change_btn").click();
            }
        });
    </script>
@stop

