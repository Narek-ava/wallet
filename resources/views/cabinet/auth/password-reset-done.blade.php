@extends('cabinet.layouts.cabinet-auth')

@section('content')

    <div class="login-form ml-auto mr-auto col-md-6">
        <h5 class="card-title text-center"><span>{{ __('Reset Password') }}</span></h5>
        <div class="common-form">

            <div class="row mx-auto justify-content-center">
                {{ t('ui_reset_link_text') }}
            </div>

        </div>
    </div>

@endsection
