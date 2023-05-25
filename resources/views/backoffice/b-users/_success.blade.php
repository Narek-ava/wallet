@extends('cabinet.layouts.cabinet-auth')

@section('content')
    <div class="login-form ml-auto mr-auto col-md-6">
        <h5 class="card-title text-center"><span>{{ t('b_user_set_password_header') }}</span></h5>
        <div class="common-form">
            <div class="row mx-auto justify-content-center">
                {{ t('ui_password_set_admin_text') }}
            </div>
            <div class="row mx-auto justify-content-center mt-4">
                <a href="{{ route('backoffice.profiles', ['type' => 1]) }}">
                    <button type="button" class="btn btn-lg btn-primary themeBtn mb-4 mb-md-0 mr-3">
                        {{ t('ui_password_set_admin_btn') }}
                    </button>
                </a>
            </div>
        </div>
    </div>
@endsection
