@extends('cabinet.layouts.cabinet-auth')
@section('fbpixel')
    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '458981425073843');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
                   src="https://www.facebook.com/tr?id=458981425073843&ev=PageView&noscript=1"
        /></noscript>
    <!-- End Facebook Pixel Code -->
@endsection
@section('metrika')
    <!-- Yandex.Metrika counter -->
    <script type="text/javascript" >
        (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
        (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

        ym(66737068, "init", {
            clickmap:true,
            trackLinks:true,
            accurateTrackBounce:true,
            webvisor:true
        });
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/66737068" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
@endsection
@section('content')

    <div class="login-form login-form-outer ml-auto mr-auto">
        <h5 class="card-title text-center"><span>{{ t('ui_cprofile_registration') }}</span></h5>

        <form class="form-signin" method="POST" action="{{ route('cabinet.register.post') }}">
            @csrf
            <div class="common-form">
                @if(is_null($accountType))
                <div class="custom-checkbox-container checkbox-as-radio-container">
                    <label class="custom-checkbox">{{ t('ui_individual_account') }}
                        <input name="account_type" type="radio" value="1" {{ old('account_type') == 1 ? 'checked' : ''}}>
                        <span class="checkmark"></span>
                    </label>
                    <label class="custom-checkbox">{{ t('ui_corporate_account') }}
                        <input name="account_type" type="radio" value="2" {{ old('account_type') == 2 ? 'checked' : ''}}>
                        <span class="checkmark"></span>
                    </label>
                </div>
                @else
                    <input name="account_type" type="hidden" value="{{$accountType}}">
                @endif
                <div class="form-label-group">
                    <label for="inputEmail">{{ t('profile_wallets_email') }}</label>
                    <input name="email" type="email" id="inputEmail" class="form-control" required autofocus value="{{ old('email') }}">

                </div>

                <div class="form-label-group">
                    <label for="inputPassword">{{ t('profile_wallets_password') }}</label>
                    <input name="password" type="password" id="inputPassword" class="form-control" required>
                </div>

                <div class="form-label-group">
                    <label for="input-phone-no-part">{{ t('profile_phone_number') }}</label>
                    <div class="row pl-3 pr-3">
                        <div class="form-group col-12 col-sm-5 pl-0 pr-0 pr-sm-2">
                            <select name="phone_cc_part" class="select-phone-cc form-control" style="width: 100%;">
                                <option></option>
                            </select>
                        </div>
                        <input name="phone_no_part" type="text" id="input-phone-no-part" class="form-control col-12 col-sm-7"
                               placeholder="" required value="{{ old('phone_no_part') }}">
                    </div>
                </div>


                <div class="custom-checkbox-container">
                    <label class="custom-checkbox">By checking this box i accepted the
                        <a href="{{ config('cratos.urls.terms_and_conditions') }}" target="_blank">
                            {{ t('terms_conditions_privacy_policy') }}</a>
                        <input name="confirm_agreement" type="checkbox">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="custom-checkbox-container">
                    <label class="custom-checkbox">I’m not from <a href="{{ config('cratos.urls.terms_and_conditions') }}#!/tab/249472834-4" target="_blank">Blocked countries</a>
                        <input name="confirm_country" type="checkbox">
                        <span class="checkmark"></span>
                    </label>
                </div>

                @if(config('cratos.age_confirmation') && (is_null($accountType) || $accountType === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL))
                    <div id="age_confirmation" class="custom-checkbox-container">
                        <label class="custom-checkbox">{{ t('registration_confirm_years_old') }}
                            <input name="age_confirmation_required" type="checkbox">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                @endif

                <div class="form-label-group">
                    <input type="hidden" name="geetest_challenge">
                    <input type="hidden" name="geetest_validate">
                    <input type="hidden" name="geetest_seccode">
                    <br>
                    <p class="captcha-fail error-text" style="display: none">{{ t('error_bad_captcha') }}</p>
                    {!! Geetest::render('popup') !!}
                    <br><br>
                </div>

                @if ($errors->any())
                    <div class="form-label-group">
                        <label class="for-error"></label>
                        @foreach($errors->all() as $message)
                            <p class="error-text">{{ $message }}</p>
                        @endforeach
                    </div>
                @endif

                <a href="javascript:history.back()" style="text-decoration: none;">
                <button class="btn btn-lg btn-primary themeBtnDark register-buttons mb-1" type="button">{{ t('ui_back') }}
                </button>
                </a>
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-1"
                        type="submit">{{ t('ui_next_step') }}</button>
            </div>
        </form>


        @include('cabinet.auth._additional_links', ['login' => true])
        <div style="font-size: 10px;text-align: center;margin-top: 18px;">
            {{ t('ui_sign_ui') }}
        </div>
    </div>

    @include('cabinet._modals.register-email')
    @include('cabinet._modals.register-sms')
@endsection

@section('scripts')
    <script>
        $('input[name="account_type"]').change(function () {
            let isChecked = $(this).prop('checked', true);
            let accountType = $(this).val();
            let ageConfirm = $('#age_confirmation');
            if (isChecked && accountType == '{{ \App\Models\Cabinet\CProfile::TYPE_CORPORATE }}') {
                ageConfirm.hide();
            } else {
                ageConfirm.show();
            }
        })
    </script>
    @parent
@endsection
