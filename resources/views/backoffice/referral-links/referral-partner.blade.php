@extends('backoffice.layouts.backoffice')
@section('title', t('title_referral_partner_page'))

@section('content')
    @if (isset($errors) && count($errors) > 0)
        <div id="containErrors"></div>
    @endif
    <div class="row mb-4 pb-4">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('settings') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <h2 style="display: inline;margin-right: 25px;">{{ t('title_referral_partner_page') }}</h2>
        <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal" id="addPartnerrBtn" data-target="#partner">{{ t('add') }}</button>
        <p>
{{--            <input type="checkbox" id="providerAll" data-link-create-url="{{ route('referral_links.link.show', ['partner_id' => null]) }}"><label for="providerAll" style="margin-left: 15px">{{ t('ui_view_all') }}</label>--}}
        </p>
        @if($message = \Illuminate\Support\Facades\Session::get('success'))
            <div class="alert alert-success alert-dismissible">
                <h4>
                    {{ $message }}
                </h4>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>
    <div class="col-md-12">
        <div class="row" id="providersSection">
            @foreach($referralPartners as $referralPartner)
                <div class="@if(\Illuminate\Support\Facades\Session::has('success') &&
                   \Illuminate\Support\Facades\Session::get('referral_partner_id') ==  $referralPartner->id )
                    red-border
                @elseif(! \Illuminate\Support\Facades\Session::has('success')) {{ $referralPartner->id === $partnerId ? 'red-border' : '' }}
                @endif col-md-3 partners-section" data-provider-id="{{$referralPartner->id}}" data-link-create-url="{{ route('referral_links.link.show', ['partner_id' => $referralPartner->id]) }}" style="cursor:pointer;">
                    <p class="activeLink provider-name">{{ $referralPartner->name }}</p>
                    <p class="providers-section-dates">Created: {{ $referralPartner->created_at }}</p>
                    <p class="providers-section-dates">Last change: {{ $referralPartner->updated_at }}</p>
                    <div class="editPartner" data-partner-id="{{ $referralPartner->id }}">{{ t('ui_edit') }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12 mt-5">
        <div class="row">
            <div class="col-md-12 mb-3" id="accountsHeaderSection">
                <h3 style="display: inline;margin-right: 25px;">{{ t('referral_links') }}</h3>
                @if($partnerId)
                    <button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addReferralLinkBtn" data-redirect-url="{{ route('referral_links.link.show', ['partner_id' => $partnerId]) }}">Add</button>
                @endif
            </div>
            <div class="col-md-1 activeLink">{{ t('ui_no') }}</div>
            <div class="col-md-1 activeLink">{{ t('ui_name') }}</div>
            <div class="col-md-2 activeLink">{{ t('individual_rate_name') }}</div>
            <div class="col-md-2 activeLink">{{ t('corporate_rate_name') }}</div>
            <div class="col-md-2 activeLink">{{ t('activated_date') }}</div>
            <div class="col-md-2 activeLink">{{ t('deactivated_date') }}</div>
            <div class="col-md-1 activeLink">{{ t('details') }}</div>
            <div id="providersAccounts" style="width: 100%">
                @foreach($referralLinks as $key => $referralLink)
                    <div class="row providersAccounts-item">
                        <div class="col-md-1">{{ ++$key }}</div>
                        <div class="col-md-1">{{ $referralLink->name }}</div>
                        <div class="col-md-2">{{ $referralLink->individualRate->name  }}</div>
                        <div class="col-md-2">{{ $referralLink->corporateRate->name  }}</div>
                        <div class="col-md-2">{{ $referralLink->activation_date  }}</div>
                        <div class="col-md-2">{{ $referralLink->deactivation_date  }}</div>
                        <div style="cursor:pointer;" class="col-md-1" data-link-view-url="{{ route('referral-links.edit', $referralLink) }}" id="linkView">{{ t('view') }}/{{ t('ui_edit') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="modal fade modal-center" id="partner" role="dialog">
        <div class="modal-dialog modal-dialog-center">
            <!-- Modal content-->
            <div class="modal-content" style="border:none;border-radius: 5px;padding: 25px;width: 500px">
                <div class="modal-body">
                    <form name="providerForm" id="providerForm" action="{{ route('referral_links.add_partner') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" id="providerToken">
                        <h3>{{ t('add_referral_partner') }}</h3>
                        <button type="button" class="close" data-dismiss="modal" style="position: absolute; top: -10px;right: 0">&times;</button>
                        <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                        <input style="width: 350px;" type="text" id="name" name="name" required><br>
                        <span class="text-danger" id="partnerName"></span><br>
                        <button type="submit" class="btn themeBtn" style="border-radius: 25px">{{ t('save') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
