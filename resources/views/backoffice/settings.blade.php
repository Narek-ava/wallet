@extends('backoffice.layouts.backoffice')
@section('title', t('settings'))

@section('content')
    <div class="row mb-3 pb-3">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">Settings</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        Platform is operated by {{ config('cratos.company_details.name') }} Registry code {{config('cratos.company_details.registry')}}, registered at
                        {{config('cratos.company_details.address')}}, {{config('cratos.company_details.city')}},  {{ config('cratos.company_details.zip_code') }}.
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <h2 class="mb-3 large-heading-section" style="margin-bottom: 0 !important;margin-top: 14px !important;">
                General
            </h2>
        </div>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_API_CLIENTS])
            || $currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_CLIENT_RATES])
            || $currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_COUNTRIES]))
            <div class="col-md-3">
                <h2 class="mb-3 large-heading-section" style="margin-bottom: 0 !important;margin-top: 14px !important;">
                    Clients
                </h2>
            </div>
        @endif
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_PROVIDERS]))
            <div class="col-md-3">
                <h2 class="mb-3 large-heading-section" style="margin-bottom: 0 !important;margin-top: 14px !important;">
                    Providers
                </h2>
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-3 pl-0">
            <div class="d-block clienInformationTabs">
                <ul class="nav d-flex flex-column" role="tablist">
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_COLLECTED_CRYPTO_FEES]))
                        <li class="nav-item">
                            <a class="nav-link" style="color: black;text-decoration: underline;"
                               href="{{ route('collected.fee') }}">Collected Crypto Fee</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" style="color: black;text-decoration: underline;" href="{{ route('b-users.twoFactor') }}">Two factor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" style="color: black;text-decoration: underline;" href="{{ route('referral-links.index') }}">Referral Partner</a>
                    </li>
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_PROJECTS]))
                        <li class="nav-item">
                            <a class="nav-link" style="color: black;text-decoration: underline;"
                               href="{{ route('projects.index') }}">Projects</a>
                        </li>
                    @endif
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_ROLES]))
                        <li class="nav-item">
                            <a class="nav-link" style="color: black;text-decoration: underline;"
                               href="{{ route('roles.index') }}">Roles</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-3 pl-0">
            <div class="d-block clienInformationTabs">
                <ul class="nav d-flex flex-column" role="tablist">
                    @if(auth()->guard('bUser')->user()->is_super_admin)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('b-users.index') }}"
                               style="color: black;text-decoration: underline;">B-Users</a>
                        </li>
                    @endif
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_API_CLIENTS]))
                        <li class="nav-item">
                            <a class="nav-link"  href="{{ route('api-clients.index') }}" style="color: black;text-decoration: underline;">Api clients</a>
                        </li>
                    @endif
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_CLIENT_RATES]))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('rate.templates.index') }}"
                               style="color: black;text-decoration: underline;">Rates</a>
                        </li>
                    @endif
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_COUNTRIES]))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('countries.index') }}"
                               style="color: black;text-decoration: underline;">Countries</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="col-md-3 pl-0">
            <div class="d-block clienInformationTabs">
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::VIEW_PROVIDERS]))
                    <ul class="nav d-flex flex-column" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.payment.providers') }}"
                               style="color: black;text-decoration: underline;">Payment providers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.liquidity.providers') }}"
                               style="color: black;text-decoration: underline;">Liquidity providers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.credit.card.providers') }}"
                               style="color: black;text-decoration: underline;">Card providers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.wallet.providers') }}"
                               style="color: black;text-decoration: underline;">Wallet providers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.card.issuing.providers') }}"
                               style="color: black;text-decoration: underline;">Card Issuers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.compliance.providers') }}"
                               style="color: black;text-decoration: underline;">Compliance providers</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('backoffice.kyt.providers') }}"
                               style="color: black;text-decoration: underline;">KYT providers</a>
                        </li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection

