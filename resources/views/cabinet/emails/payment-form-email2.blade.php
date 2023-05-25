@extends('cabinet.emails.layouts.layout')

@section('content')
    <br><br>
    <h1 style="color: black">{!! t('payment_form_welcome_aboard') !!}</h1>

{{--    <p class="breakWord">--}}
{{--        <img src="<?= config('app.url') . '/cratos.theme/images/icon_red_check.png' ?>"--}}
{{--             style="width: 100px; height: 100px"--}}
{{--        />--}}
{{--    </p>--}}

    <p class="breakWord">{!! t('payment_form_creating_account') !!}</p>
    <p class="breakWord">{!! t('payment_form_access_dashboard') !!}</p>
    <br>
    <p class="breakWord"><a href="{!! $url !!}" style="background-color: var(--main-color);border-color: var(--border-color);color: #fff !important;font-size: 18px;
    padding: 14px 50px 15px;border-radius: 15px;text-decoration: none;">{{ t('go_dashboard') }}</a></p>
    <br>

{{--    <p class="breakWord">--}}
{{--        <img src="<?= config('app.url') . '/cratos.theme/images/icon_arrow_down.png' ?>"--}}
{{--             style="width: 100px; height: 100px"/>--}}
{{--    </p>--}}

{{--    <h1 style="color: black">{!! t('payment_form_earn_crypto') !!}</h1>--}}
{{--    <br>--}}
{{--    <div style="border-radius: 5px;width: 200px; padding: 10px;border: 1px solid rgba(168,159,159,0.2);font-weight: bold;color: black">--}}
{{--        <b>pay.cratos.net/7854</b>--}}
{{--    </div>--}}
{{--    <br>--}}
{{--    <p class="breakWord">{!! t('payment_form_share_refferal') !!}</p>--}}
{{--    <h2>{!! t('payment_form_forever') !!}</h2>--}}
    <p class="breakWord">{!! t('payment_form_transaction_limit', ['monthlyAmountMaxLimit' => $monthlyAmountMaxLimit, 'complianceUrl' => $complianceUrl]) !!}</p>

@endsection
