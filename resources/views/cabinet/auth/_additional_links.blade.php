<div class="register-link text-center">
    @if ($login)
        <p>{{ t('ui_cprofile_have_account') }} <a href="{{ route('cabinet.login.get') }}">{{ t('ui_cprofile_login') }}</a></p>
    @else
        <p>{{ t('ui_cprofile_dont_have_account') }} <a href="{{ route('cabinet.register.get') }}">{{ t('ui_cprofile_registration') }}</a></p>
    @endif
    <p><a href="{{ route('cabinet.password-reset-request.get') }}">{{ t('ui_cprofile_forgot_password') }}</a></p>
</div>
