@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1>{{ t('payment_form_wallet_verification_failed') }}</h1>

    <p class="breakWord">{!! t('payment_form_unfortunately') !!}</p>
    <p class="breakWord"><b>{{ $address }}</b></p>
    <p class="breakWord">{{ t('payment_form_different_wallet_address') }} <br> <a href="{{ route('cabinet.wallets.index') }}" style="color: red"><b>{{ t('payment_form_wallet_cratos') }}</b></a></p>
    <br>
{{--    <p class="breakWord"><a href="{{ route('cabinet.wallets.index') }}" style="background-color: #fe3d2b;--}}
{{--    border-color: #fe3d2b;color: #fff !important;font-size: 18px;font-weight: bold;--}}
{{--    padding: 14px 50px 15px;border-radius: 15px;text-decoration: none;"> {{ t('payment_form_create_cratos_wallet') }}</a></p>--}}
    <br>
    <p class="breakWord">{!! t('support_team_link', [ 'link' => t('contact_to_support_link')]) !!}</p>

@endsection
