<?php
/** @var \App\Models\Country[] $countries */
?>

@extends('backoffice.layouts.backoffice')

@section('title', t('title_countries_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_countries_page') }}</h2>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>



    <div class="row mt-5">
        <h2>{{ t('ui_countries') }}</h2>
        <div class="col-md-2">
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_AND_EDIT_COUNTRIES]))
                <a type="button" class="btn themeBtnWithoutHover" href="{{ route('countries.create') }}">
                    {{ t('create_new') }}
                </a>
            @endif
        </div>

        <div class="col-md-12">
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
                    <h4>{{ session()->get('success') }}</h4>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
        @include('backoffice.settings.countries._filter')


        <div class="col-md-12 mt-5">
            <div class="row pb-5">
                <div class="col-md-12">
                    <div class="users-list d-block mb-5 p-2">
                        <div class="d-block col-md-12">
                            <div class="col-md-12 pt-4">
                                <div class="row d-none d-md-flex">
                                    <div class="col-md-2 activeLink text-center">
                                        <span>{!! t('ui_country_name') !!}</span>
                                    </div>
                                    <div class="col-md-2 activeLink text-center">
                                        <span>{!! t('ui_country_code') !!}</span>
                                    </div>
                                    <div class="col-md-2 activeLink text-center">
                                        <span>{!! t('ui_phone_code') !!}</span>
                                    </div>
                                    <div class="col-md-2 activeLink text-center">
                                        <span>{!! t('ui_country_banned') !!} </span>
                                    </div>
                                    <div class="col-md-2 activeLink text-center">
                                        <span>{!! t('ui_country_alphanumeric_sender') !!} </span>
                                    </div>
                                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_AND_EDIT_COUNTRIES]))
                                        <div class="col-md-2 activeLink text-center">
                                            <span>{!! t('ui_country_edit') !!} </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @foreach($countries as $country)
                                <div class="col-md-12 mt-4 history-element">
                                    <div class="row">
                                        <div class="col-md-2 text-center"
                                             title="{{ $country->name }}">{{ $country->name }}</div>
                                        <div class="col-md-2 text-center"
                                             title="{{ $country->code }}">{{ $country->code ?? '-' }}</div>
                                        <div class="col-md-2 text-center"
                                        >{{ implode(', ', $country->phone_code ?? []) }}</div>
                                        <div class="col-md-2 text-center"
                                             title="{{ $country->is_banned }}">{{ \App\Models\Country::BANNED_NAMES[$country->is_banned] }}</div>
                                        <div class="col-md-2 text-center"
                                             title="{{ $country->is_alphanumeric_sender }}">{{ \App\Models\Country::ALPHANUMERIC_SENDER_NAMES[$country->is_alphanumeric_sender] }}</div>
                                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_AND_EDIT_COUNTRIES]))
                                            <div class="col-md-2 text-center orange">
                                                <a href="{{ route('countries.edit', $country) }}"
                                                   class="btn btn-lg btn-primary themeBtn register-buttons round-border mb-0 mb-md-0">
                                                    {{ t('ui_country_edit') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {!! $countries->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
