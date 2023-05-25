@extends('cabinet.layouts.cabinet-auth')

@section('content')

    <div class="login-form ml-auto mr-auto col-md-6">
        <h5 class="card-title text-center"><span>{{ !empty($payment_form) ? t('payment_form_password_set_header') : t('ui_password_reset_header') }}</span></h5>
        <div class="common-form">

            <div class="row mx-auto justify-content-center">
                {{ t('ui_password_reset_finish_text') }}
            </div>

            <div class="row mx-auto justify-content-center mt-4">
                <a href="{{ route('cabinet.wallets.index') }}">
                    <button type="button" class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3">
                        {{ t('ui_password_reset_finish_button') }}
                    </button>
                </a>
            </div>

        </div>
    </div>

@endsection
