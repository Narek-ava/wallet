@extends('cabinet.layouts.cabinet-auth')

@section('content')

    <div class="login-form login-form-outer ml-auto mr-auto">
        <h5 class="card-title text-center"><span>{{ t('ui_cprofile_login') }}</span></h5>
        <form method="POST" action="{{ route('cabinet.login.post') }}" class="form-signin">
            @csrf
            <div class="common-form">

                <div class="form-label-group">
                    <label for="inputEmail">{{ t('profile_wallets_email') }}</label>

                    @error('email')
                    <p class="error-text">{{ $message }}</p>
                    @enderror

                    <input name="email" value="{{ ${\C\REMEMBER_MY_USERNAME_COOKIE} ?? null }}" type="email"
                           id="inputEmail"
                           class="form-control" placeholder="" required autofocus>
                </div>

                <div class="form-label-group">
                    <label for="inputPassword">{{ t('profile_wallets_password') }}</label>
                    <input name="password" type="password" id="inputPassword" class="form-control" placeholder=""
                           required>
                </div>

                <div class="form-label-group mb-4">
                    <input type="hidden" name="geetest_challenge">
                    <input type="hidden" name="geetest_validate">
                    <input type="hidden" name="geetest_seccode">
                    <p class="captcha-fail error-text" style="display: none">{{ t('error_bad_captcha') }}</p>
                    {!! Geetest::render('popup') !!}
                </div>

                <div class="custom-checkbox-container">
                    <label class="custom-checkbox">{{ t('ui_remember_my_username_label') }}
                        <input name="remember_my_username" type="checkbox">
                        <span class="checkmark"></span>
                    </label>
                </div>




                <div class="form-label-group mb-4">
                    <button class="btn btn-lg btn-primary themeBtn btn-block"
                            type="submit">{{ t('profile_login_cratos') }}</button>
                </div>
            </div>

        </form>


        @include('cabinet.auth._additional_links', ['login' => false])

    </div>
    @include('cabinet._modals.2fa-confirm')
@endsection

@section('scripts')
    <script src="/js/cabinet/2fa-login.js"></script>
    <script>
        var API = '';
        $(document).ready(function () {
            var showModal = '{{ $twoFAToShow ?? false }}';
            if(showModal){
                let ask2fa = new AskTwoFALogin();
                ask2fa.attachToFormSubmit('#backofficeLogin');
            }
        });
    </script>
@endsection
