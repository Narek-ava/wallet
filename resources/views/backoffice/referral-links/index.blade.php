<?php
    /** @var \App\Models\ReferralPartner[] $referralPartners*/
?>

@extends('backoffice.layouts.backoffice')

@section('title', t('settings'))

@section('content')
    <div class="row">

        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('settings') }}</h2>
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
        <h2>Referral Links</h2>
        <div class="col-md-2">
            <a type="button" class="btn themeBtnWithoutHover" href="{{ route('referral-links.create') }}">
                {{ t('create_new') }}
            </a>
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

        <div class="col-md-12">
            <div class="row" id="merchantFormsSection">
                @foreach($referralPartners as $referralPartner)
                    <div class="col-md-3 api-clients-forms-section" data-merchant-id="{{$referralPartner->id}}" style="cursor:pointer;">
                        <p class="activeLink provider-name">{{ $referralPartner->name ?? '' }}</p>
                        <p class="providers-section-dates">Created: {{ $referralPartner->created_at }}
                        </p>
                        <div class="providers-section-status">{{ t(\App\Models\ReferralPartner::STATUS_NAMES[$referralPartner->status]) }}</div>
                        <a class="border-none" href="{{ route('referral-links.edit', $referralPartner) }}">
                            <img src="{{ config('cratos.urls.theme') }}images/edit_pencil.png" width="20" height="20" alt="">
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/backoffice/referral-links.js') }}"></script>
@endsection
