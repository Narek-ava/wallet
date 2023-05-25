@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1>{{ t('payment_form_login_new_device') }}</h1>


    <p class="breakWord">{!! t('payment_form_noticed_new_device') !!}</p>
    <p class="breakWord"><b>{{ t('payment_form_device_details') }}</b></p>
    <p class="breakWord">{{ t('payment_form_location') }} <br> {!! $geo !!} </p>
    <p class="breakWord">{{ t('payment_form_browser') }} <br> {!! $browser !!} </p>
    <p class="breakWord">{{ t('payment_form_ip') }} <br> {!! $ip !!}</p>
@endsection
