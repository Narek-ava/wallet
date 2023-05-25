@extends('cabinet.emails.layouts.layout')

@section('content')
    @if(isset($account) && isset($wireAccountDetail))
        @php
            $requisites = '<span style="font-weight: bold;">'.t('template_name').'</span><br>
                            <span>'. $account->name .'</span><br>
                            <span style="font-weight: bold;">'.t('country_of_clients_bank').'</span><br>
                            <span>'. \App\Models\Country::getCountryNameByCode($account->country) .'</span><br>
                            <span style="font-weight: bold;">'.t('currency').'</span><br>
                            <span>'. $account->currency .'</span><br>
                            <span style="font-weight: bold;">'.t('method').'</span><br>
                            <span>'. t(App\Enums\AccountType::getName($account->account_type)) .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_applicable_iban').'</span><br>
                            <span>'. $wireAccountDetail->iban .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_swift_bic').'</span><br>
                            <span>'. $wireAccountDetail->swift .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_account_holder_placeholder').'</span><br>
                            <span>'. $wireAccountDetail->account_beneficiary .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_account_number_placeholder').'</span><br>
                            <span>'. $wireAccountDetail->account_number .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_bank_name_placeholder').'</span><br>
                            <span>'. $wireAccountDetail->bank_name .'</span><br>
                            <span style="font-weight: bold;">'.t('ui_cabinet_bank_details_bank_address_placeholder').'</span><br>
                            <span>'. $wireAccountDetail->bank_address .'</span>';
            $replacements = ['requisites' => $requisites];
            session('replacements', $replacements);
            $body = t($body, $replacements);
        @endphp
    @endif
    <h1>{{ $h1Text }}</h1>
    <p class="breakWord">{!! $body !!}</p>
@endsection
