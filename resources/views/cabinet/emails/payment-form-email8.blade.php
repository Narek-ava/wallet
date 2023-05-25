@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1>{{ t('payment_form_upgrade_verification_level') }}.</h1>


    <p class="breakWord">{!! t('payment_form_increase_limits') !!}</p>
    <div class="breakWord">{{ t('payment_form_current_transaction') }} {!! '500 EUR' !!} </div>
    <div class="breakWord"> {{ t('payment_form_current_monthly') }} {!! '15. 000 EUR' !!} </div>
    <br>
    <p class="breakWord"><a href="{{ route('cabinet.compliance') }}" style="background-color: var(--main-color);
    border-color: var(--border-color);color: #fff !important;font-size: 18px;font-weight: bold;
    padding: 14px 50px 15px;border-radius: 15px;text-decoration: none;">{{ t('payment_form_upgrade_verification_level') }}</a></p>
    <br>
    <p class="breakWord">{!! t('support_team_link', [ 'link' => t('contact_to_support_link')]) !!}</p>

@endsection
