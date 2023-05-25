@extends('backoffice.layouts.backoffice')
@section('title', t('dashboard'))

@section('content')
    <div class="row mb-5 pb-2">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('dashboard') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex justify-content-between">
                    <div class="balance mr-2">
                        <p>{{ t('dashboard_title') }}</p>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <div class="row ml-1">

        <div class="col-md-12 pl-0 row">
            <h2>{{ t('ui_providers') }}</h2>
            <div class="ml-3">
                <a class="btn btn-primary themeBtnDark btn-sm" href="{{ route('get.kraken.balance') }}">Kraken balance</a>
            </div>
        </div>
        <form id="filterForm" method="get" class="row col-md-12 mb-4">
            <h2 class="pr-4">{{ t('dashboard_provider') }}</h2>
            <select name="status" class="mr-4" id="status" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\PaymentProvider::NAMES as $key => $status)
                    <option value="{{ $key }}"
                            @if(request()->get('status') == $key)
                                selected
                            @elseif(!request()->has('status') && App\Enums\PaymentProvider::STATUS_ACTIVE == $key)
                                selected
                            @endif>{{ t($status) }}</option>
                @endforeach
            </select>

            <select name="providerType" id="providerType" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\Providers::ONLY_PROVIDER_NAMES as $key => $provider)
                    <option value="{{ $key }}" @if(request()->get('providerType') == $key) selected @endif>{{ $provider }}</option>
                @endforeach
            </select>
        </form>

        <div class="col-md-12">
            <div class="row" id="providersSection">
                @foreach($providers as $provider)
                    <div class="col-md-3 providers-section" data-provider-id="{{$provider->id}}" style="cursor:pointer;"
                     data-provider-type="{{ $provider->getProviderGroup() }}" data-is-dashboard="true">
                        <p class="activeLink provider-name">{{ $provider->name }}</p>
                        <p class="providers-section-dates">Created: {{ $provider->created_at }}</p>
                        <p class="providers-section-dates">Last change: {{ $provider->updated_at }}</p>
                        <div class="providers-section-status">{{ \App\Enums\PaymentProvider::getName($provider->status) }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-md-12 mt-5">
            <div class="row">
            <div class="row ml-1" id="card_provider_accounts" hidden style="width: 100%">
                <div class="col-md-12 pl-0 mb-5" >
                    <h3 style="display: inline;margin-right: 25px;">{{ t('ui_accounts') }}</h3>
                </div>
                <div class="col-md-1 activeLink mr-3 text-center">{{ t('ui_no') }}</div>
                {{--            <div class="col-md-2 activeLink">{{ t('ui_name') }}</div>--}}
                <div class="col-md-1 activeLink text-center">{{ t('ui_system') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('type') }}</div>
                <div class="col-md-1 activeLink">{{ t('ui_secure') }}</div>
                <div class="col-md-1 activeLink mr-3">{{ t('ui_currencies') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('ui_cprofile_region') }}</div>
                <div class="col-md-2 activeLink text-center">{{ t('ui_cprofile_created_at') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('balance') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('ui_status') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('details') }}</div>
                </div>
            </div>
        <div class="row" id="other_provider_accounts" hidden style="width: 100%">
            <div class="col-md-12 pl-0 mb-5" >
                <h3 style="display: inline;margin-right: 25px;">{{ t('ui_accounts') }}</h3>
            </div>
            <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('ui_name') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('type') }}</div>
            <div class="col-md-1 activeLink mr-3">{{ t('ui_currencies') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('ui_cprofile_country') }}</div>
            <div class="col-md-2 activeLink text-center">IBAN/Account</div>
            <div class="col-md activeLink text-center">{{ t('ui_cprofile_created_at') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('balance') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('status') }}</div>
            <div class="col-md-1 activeLink text-center">{{ t('details') }}</div>
        </div>
            <div class="row" id="wallet_provider_account" hidden style="width: 100%">
                <div class="col-md-12 pl-0 mb-5">
                    <h3 style="display: inline;margin-right: 25px;">Accounts</h3>
                </div>
                <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('ui_name') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('type') }}</div>
                <div class="col-md-1 activeLink mr-3 text-center">{{ t('ui_currencies') }}</div>
                <div class="col-md-2 activeLink text-center">{{ t('wallet_address') }}</div>
                <div class="col-md activeLink text-center">{{ t('created_on') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('balance') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('ui_status') }}</div>
                <div class="col-md-1 activeLink text-center">{{ t('ui_details') }}</div>
            </div>

            <div id="providersAccounts"style="width: 100%"></div>

        </div>

    </div>
@endsection
@section('scripts')
    <script>
        $('#status').on('change', function () {
            $('#filterForm').submit();
        })
        $('#providerType').on('change', function () {
            $('#filterForm').submit();
        })
    </script>
@endsection
