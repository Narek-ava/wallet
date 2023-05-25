@extends('cabinet.layouts.cabinet-auth')

@section('content')

    <div class="login-form ml-auto mr-auto col-md-8">
        <h5 class="card-title text-center"><span>{{ __('Reset Password') }}</span></h5>
        <div class="common-form">
            <form method="POST" action="{{ route('cabinet.password-reset-request') }}">
                @csrf

                <div class="row">
                    <div class="col-lg-6">

                        <div class="form-label-group">
                            <label for="inputEmail">Email</label>

                            <input name="email" type="email" id="inputEmail" class="form-control" placeholder=""
                                   required
                                   autofocus>
                            @error('email')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="additional-label">
                            {{ t('ui_lost_password') }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        @include('_phone')
                    </div>
                    <div class="col-lg-6">
                        <div class="additional-label">
                            {{ t('ui_lost_password_long') }}
                        </div>
                    </div>
                </div>


                <div class="form-label-group mb-4">
                    <div class="form-label-group mb-4">
                        <p class="captcha-fail error-text" style="display: none">{{ t('error_bad_captcha') }}</p>
                        {!! Geetest::render('popup') !!}
                    </div>
                </div>

                <div class="row md-0">
                <div class="form-label-group mb-4 col-md-4 mx-auto">
                    <button class="btn btn-lg btn-primary themeBtn btn-block" type="submit">{{ t('ui_send') }}</button>
                </div>
                </div>
            </form>

        </div>

        @include('cabinet.auth._additional_links', ['login' => false])

    </div>

@endsection
