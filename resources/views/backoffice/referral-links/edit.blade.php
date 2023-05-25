<?php
/** @var \App\Models\ReferralLink $referralLink*/
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
        <div class="col-md-6">
            <form action="{{ route('referral-links.update', $referralLink) }}" method="POST">
                @method('PUT')
                @csrf
                <input type="hidden" name="partner_id" value="{{ $referralLink->partner_id }}">
                <h3> {{ t('edit_referral_partner_information') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputName">{{ t('referral_partner_name') }}</label> </h5>
                        <input id="inputName" name="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"  value="{{ $referralLink->name }}" required>
                    </div>
                    @error('name')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('individual_rate_name') }}</label></h5>
                        <select name="individual_rate_templates_id" id="inputStatus" required>
                            @foreach($rates as $key => $rate)
                                <option @if($referralLink->individual_rate_templates_id == $rate->id) selected @endif value="{{ $rate->id }}">{{ $rate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('corporate_rate_name') }}</label></h5>
                        <select name="corporate_rate_templates_id" required>
                            @foreach($rates as $key => $rate)
                                <option @if($referralLink->corporate_rate_templates_id == $rate->id) selected @endif value="{{ $rate->id }}">{{ $rate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('activated_date') }}</label></h5>
                        <input class="date-inputs display-sell w-50" name="activation_date" id="activated_date" value="{{ $referralLink->activation_date }}" autocomplete="off" placeholder="Activate date" required>
                    </div>
                    @error('activation_date')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus"> {{ t('deactivated_date') }}</label></h5>
                        <input class="date-inputs display-sell w-50" name="deactivation_date" id="deactivated_date" value="{{ $referralLink->deactivation_date }}" autocomplete="off" placeholder="Deactivate date" required>
                    </div>
                    @error('deactivation_date')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputToken"> {{ t('registration_link') }} </label> </h5>
                        <div class="d-flex justify-center">
                            <input id="inputToken" readonly type="text" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}"  value="{{ route('cabinet.register.get') .'?ref='. $referralLink->id }}" >
                            <button id="CopyToken" type="button" class="btn btn-light token-copy"
                                    onclick="copyText(this)">
                                <i class="fa fa-copy" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    @error('token')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class=" col-md-3 form-group mt-5 pl-0">
                    <div class="form-label-group">
                        <button class="btn btn-lg btn-primary themeBtn btn-block"
                                type="submit">{{ t('save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let registrationLink = '{{ route('cabinet.register.get') }}';
    </script>
    <script type="text/javascript" src="{{ asset('js/backoffice/referral-links.js') }}"></script>
@endsection
