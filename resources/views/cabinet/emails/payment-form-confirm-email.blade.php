@extends('cabinet.emails.layouts.layout')

@section('content')
    <br>
    <h1>{{ t('successful_payment') }}</h1>
    <p class="breakWord">{!! t('payment_details') !!}</p>
    <p class="breakWord">{!! $payment_details_info !!}</p>
    <p class="breakWord">{!! $walletAddress !!}</p>
    <br>
    <a href="{{ $dashboardUrl }}" style="background-color: var(--main-color);
    border-color: var(--border-color);color: #fff !important;font-size: 18px;font-weight: bold;
    padding: 7px 60px 8px;border-radius: 30px;text-decoration: none;">{{ t('go_dashboard') }}</a>
    <br>
    <p class="breakWord">{!! $support_team_link !!}</p>
    <br>
@endsection
