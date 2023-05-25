@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1>{!! t('payment_form_authentication_code') !!}</h1>

    <div style="border-radius: 5px;width: 200px;border: solid 1px rgba(100,94,94,0.14);font-weight: bold;color: black">
        <h1>{!! $token !!}</h1>
    </div>

    <p class="breakWord">{!! t('support_team_link', [ 'link' => t('contact_to_support_link')]) !!}</p>

    <p class="breakWord">{{ t('payment_form_security_purpose') }}</p>
@endsection
