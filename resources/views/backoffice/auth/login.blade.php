@extends('backoffice.layouts.app')

@section('content')

<div class="middil-container">
    <div class="container">
        <a href="//cratos.net/" class="navbar-brand d-block pb-3 text-center">
            <img src="/cratos.theme/images/logo.svg" width="140" height="32" alt="">
        </a>

        <div class="login-form login-form-outer ml-auto mr-auto">
            <h5 class="card-title text-center"><span>{{ t('ui_backoffice') }} {{ __('Login') }}</span></h5>
            <div class="common-form">
                <form id="backofficeLogin" method="POST" action="{{ route('backoffice/login') }}">
                    @csrf

                    <div class="form-label-group">
                        <label for="inputEmail">{{ t('profile_wallets_email') }}</label>
                        @if ($errors->has('email'))
                            <p class="error-text">{{ $errors->first('email') }}</p>
                        @endif
                        <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="form-label-group">
                        <label for="inputPassword">{{ t('profile_wallets_password') }}</label>
                        @if ($errors->has('password'))
                            <p class="error-text">{{ $errors->first('password') }}</p>
                        @endif
                        <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                    </div>

                    <div class="form-label-group mb-4">
                        <input type="hidden" name="geetest_challenge">
                        <input type="hidden" name="geetest_validate">
                        <input type="hidden" name="geetest_seccode">
                        <p class="captcha-fail error-text" style="display: none">{{ t('error_bad_captcha') }}</p>
                        {!! Geetest::render() !!}
                    </div>

                    <div class="custom-checkbox-container">
                        <label class="custom-checkbox">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ __('Remember Me') }}
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <div class="form-label-group mb-4 pt-2 mt-1">
                        <button class="btn btn-lg btn-primary themeBtn btn-block" type="submit">{{ t('ui_login_backoffice') }}</button>
                    </div>
                </form>

            </div>

        </div>

    </div>
</div>
@include('backoffice.auth.2fa-confirm')
@endsection

@section('scripts')
    <script>
        window.geetestProtocol = '{{ config('geetest.protocol')}}';
        let API = '';
    </script>
    <script src="/js/backoffice/2fa-login.js"></script>
    <script>
        $(document).ready(function () {
            var showModal = '{{ $twoFAToShow ?? false }}';
            if(showModal){
                let ask2fa = new AskTwoFABackofficeLogin();
                ask2fa.attachToFormSubmit('#backofficeLogin');
            }
        });
    </script>
@endsection

