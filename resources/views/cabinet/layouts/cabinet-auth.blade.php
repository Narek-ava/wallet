<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <style>

        :root {
            --main-color: {{ \C\getProjectColors()->mainColor ?? '#fe3d2b' }};
            --button-color: {{ \C\getProjectColors()->buttonColor ?? '#fe3d2b' }};
            --border-color: {{ \C\getProjectColors()->borderColor ?? '#fe3d2b' }};
            --notify-from: {{ \C\getProjectColors()->notifyFromColor ?? '#f96283' }};
            --notify-to: {{ \C\getProjectColors()->notifyToColor ?? '#ffc052' }};
        }
    </style>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}">
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/cratos.css?v={{ time() }}">
    @auth
        <script src="{{ asset('js/main.js') }}"></script>
    @endauth
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        html {
            zoom: 1;
        }
    </style>
    <title>:: Welcome to {{ config('app.name') }} ::</title>
    @yield('fbpixel', '')
    @yield('metrika', '')
    @yield('styles')
</head>
<body>
<!-- <div class="container">
    <div class="row">
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent w-100">
            <a href="{{ config('cratos.urls.landing') }}" class="navbar-brand col-md-2 col-4 pt-0"><img
                    src="{{ config('cratos.urls.theme') }}images/logo.png" alt=""></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText"
                    aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse col-lg-10" id="navbarText">

                <ul class="navbar-nav mr-auto ml-auto text-center mt-4 mt-sm-0">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ config('cratos.urls.landing') }}currency">Trade <span
                                class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ config('cratos.urls.landing') }}about">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ config('cratos.urls.landing') }}faq">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ config('cratos.urls.landing') }}news">News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ config('cratos.urls.landing') }}contact">Contacts</a>
                    </li>
                </ul>

                <div class="mr-0 col-12 col-lg-5 col-xl-4 text-center pr-0 pt-4 pt-sm-0">
                    <a href="{{ route('cabinet.login.get') }}" class="btn btn-default themeBtn mb-2 mb-sm-0" type="button">Sign in</a>
                    <a href="{{ route('cabinet.register.get') }}" class="btn btn-default themeBtn ml-0 ml-sm-4 mb-2 mb-sm-0" type="button">Sign up</a>
                </div>

            </div>
        </nav>
    </div>
</div> -->
<section class="middil-container">
    <div class="container">
        <a href="/" class="navbar-brand d-block pb-3 text-center">
            @if($currentProject)
                <img src="{{ $currentProject->logoPng }}" class="projectLogo" alt="">
            @endif
        </a>
        @yield('content')
    </div>

</section>
<!-- <footer>
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-3 mb-3">
                <a href="{{ config('cratos.urls.landing') }}" class="mb-4 d-block"><img src="{{ config('cratos.urls.theme') }}images/logo.png" alt=""></a>
                <a class="d-block" href="mailto:support@cratos.net">support@cratos.net</a>
            </div>

            <div class="col-md-12 col-lg-8">
                <div class="row">
                    <div class="col-md-2">
                        <div class="footer-links">
                            <ul>
                                <li><a href="{{ config('cratos.urls.landing') }}exchange">Exchange</a></li>
                                <li><a href="{{ config('cratos.urls.landing') }}currency">Currency</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="footer-links">
                            <ul>
                                <li><a href="{{ config('cratos.urls.landing') }}about">About us</a></li>
                                <li><a href="{{ config('cratos.urls.landing') }}faq">FAQ</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="footer-links">
                            <ul>
                                <li><a href="{{ config('cratos.urls.landing') }}news">News</a></li>
                                <li><a href="{{ config('cratos.urls.landing') }}contact">Contact us</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="footer-links-red">
                            <ul>
                                <li><a href="{{ config('cratos.urls.landing') }}terms-and-conditions">User Agreement</a></li>
                                <li><a href="{{ config('cratos.urls.landing') }}privacy-policy-2">Privacy Policy</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="footer-links-red">
                            <ul>
                                <li><a href="{{ config('cratos.urls.landing') }}trade">Trade</a></li>
                                <li><a href="{{ config('cratos.urls.landing') }}aml-kyc-policy">AML KYC Policy</a></li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="copyright">

                    Website is operated by Vulture OÜ Registry code 14848194, registered at Peterburi tee 47, Lasnamäe
                    linnaosa, Tallinn,   114. Vulture OÜ provides: services of exchanging a virtual
                    currency against a fiat currency License Number FVR001244 Start of validity 04.12.2019 Issuer of
                    licence: Politsei- ja Piirivalveamet Places of business. A virtual currency wallet service Number
                    FRK001128 Start of validity 04.12.2019 Issuer of license: Politsei- ja Piirivalveamet Places of
                    business. WARNING: Virtual currencies are highly volatile and novel instruments traded on
                    unregulated exchanges with minimal or no regulatory supervision. Investing in virtual currencies is
                    highly risky, and you can rapidly lose your entire invested capital. Before buying virtual
                    currencies, you should consider whether you understand how the market in virtual currencies works
                    and whether you can afford to take the high risk of losing your money. RESTRICTED JURISDICTIONS: We
                    do not establish accounts to residents of certain jurisdictions. For further details please see our
                    Client Agreement. For privacy and data protection related complaints please contact us at
                    support@cratos.net Please read our Privacy Policy for more information on the handling of your
                    personal data.

                </div>
            </div>
        </div>
    </div>
</footer> -->
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="/js/common/bootstrap.min.js"></script>
<link href="{{ config('cratos.urls.theme') }}css/select2.css?v={{ time() }}" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
<script>
    window.geetestProtocol = '{{ config('geetest.protocol')}}';
    var API = '{{ config('cratos.urls.cabinet.api-v1') }}';
    let baseFlagUrl = "{{ config('cratos.urls.theme') }}images/flag/";
    var smsRegisterToShow = "{{ $smsRegisterToShow ?? false}}";
    var emailRegisterToShow = "{{ $emailRegisterToShow ?? false}}";
    var twoFAToShow = "{{ $twoFAToShow  ?? false}}";
    let countries = {!! getNotBannedCountriesForRegister() !!};

    setTimeout(function () {
        $('.alert-success').remove();
    }, 2000);
</script>
<script src="/js/cabinet/app.js?v={{ time() }}"></script>
<script src="/js/cabinet/email-verify.js"></script>
<script src="/js/cabinet/captcha.js"></script>

@yield('scripts')
</body>
</html>
