@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1>{{ t('payment_form_unsuccessful_payment') }}</h1>


    <p class="breakWord">{{ t('payment_form_unable_process') }}</p>
    <p class="breakWord"><b>{{ t('reason') }}</b></p>
    <p class="breakWord">{!! $error !!}</p>
    <br>
    <p class="breakWord"><a href="" style="background-color: var(--main-color);
    border-color: var(--border-color);color: #fff !important;font-size: 18px;font-weight: bold;
    padding: 14px 50px 15px;border-radius: 15px;text-decoration: none;"> {{ t('payment_form_dashboard') }}</a></p>
    <br>
    <p class="breakWord">{!! t('support_team_link', [ 'link' => t('contact_to_support_link')]) !!}</p>
@endsection
