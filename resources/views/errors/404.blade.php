@extends('cabinet.layouts.cabinet-auth')

@section('content')
    <div class="container text-center pt-5">
        <h1 class="pt-5">{{ t('error_unknown') }}</h1>
        <h3 class="pt-2">{{ t('error_code', ['code' => 404]) }}</h3>
        <h2 class="pt-5">{!! t('error_page_message') !!}</h2>
        <h3 class="pt-2">{{ t('contact_support_team', ['email' => \C\SUPPORT_EMAIL]) }}</h3>
    </div>
@endsection
