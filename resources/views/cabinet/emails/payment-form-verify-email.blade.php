@extends('cabinet.emails.layouts.layout')

@section('content')
    <h1>{!! $h1Text !!}</h1>

    <div style="border-radius: 5px;width: 200px;border: none;font-weight: bold;color: black">
        <h1>{!! $token !!}</h1>
    </div>
    <p class="breakWord">{!! t('payment_form_order_proceed') !!}</p>
    <p class="breakWord">{!! t('payment_form_enter_code') !!}</p>
@endsection
