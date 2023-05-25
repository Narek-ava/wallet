<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <link rel="icon" href="/favicon.ico">

    <title> @yield('title') </title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/dashboard/">

    <style>
        :root {
            --main-color: {{ \C\getProjectColors()->mainColor ?? '#fe3d2b' }};
            --button-color: {{ \C\getProjectColors()->buttonColor ?? '#fe3d2b' }};
            --border-color: {{ \C\getProjectColors()->borderColor ?? '#fe3d2b' }};
            --notify-from: {{ \C\getProjectColors()->notifyFromColor ?? '#f96283' }};
            --notify-to: {{ \C\getProjectColors()->notifyToColor ?? '#ffc052' }};
        }
    </style>
    <link rel="stylesheet" type="text/css"
          href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap core CSS -->
    <link href="{{ config('cratos.urls.theme') }}css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"  />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.min.css"   />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <!-- Animate css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <!-- Custom styles for this template -->

    <link href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ config('cratos.urls.theme') }}css/datatables.min.css"/>
    <link href="{{ config('cratos.urls.theme') }}css/cratos.css?v={{ time() }}" rel="stylesheet">
    <link href="{{ config('cratos.urls.theme') }}css/wallets.css?v={{ time() }}" rel="stylesheet">

    @yield('styles')

    <style>
        .textColorRed {
            color: #fe3d2b
        }
        .textColorLavander {
            color: #6349e0
        }
    </style>

    <script src="{{ asset('js/main.js') }}?v={{ time() }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    @yield('tiktokpixel', '')
</head>

<body class="">
<div class="page-wrapper toggled">
    <div class="quation"></div>

    <div class="quationform">
        <div id="feedback-form" style="display: none;" class="col-xs-4 col-md-4 panel panel-default">
            {{-- what is this? @todo --}}
        </div>
    </div>


    <div class="container-fluid">
        <div class="row row-offcanvas row-offcanvas-left">
            <div class="col-md-3 col-lg-2 sidebar-offcanvas pl-0" id="sidebar" role="navigation">
                <nav class="sidebar">
                    <button type="button" class="navbar-toggler mr-2  d-lg-none" d-data-toggle="offcanvas"
                            title="Toggle responsive left sidebar">
                        <i class="fa fa-bars" aria-hidden="true"></i>
                    </button>
                    <div class="users-info pt-3">
                        <a class="text-center text-lg-left d-block p-3" href="{{ route('cabinet.wallets.index') }}"><img src="{{ $currentProject->logoPng }}"
                                                                       class="img-fluid sidebar-logo projectLogo" alt=""></a>
                        <div class="user-name pl-2 pl-lg-3 mt-0 mt-lg-3">{!! auth()->guard('cUser')->user()->cProfile->getFullNameInCabinet() !!}</div>
                        <a class="logout pl-0 pl-lg-3" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Log out</a>
                        <form id="logout-form" action="{{ route('cabinet.logout') }}" method="POST" style="display: none;">
                        </form>
                    </div>


                    <div class="sidebar-sticky">

                        <ul class="nav flex-column mt-5">
                            @foreach(cabinet_menu() as $menuName => $menuData)
                                @continue(empty($menuData))
                                <li class="nav-item">
                                    <a class="nav-link d-dropdown-toggle {{activeMenu($menuData['url'])}}  {{!$menuData['active'] ? 'disabled' : ''}}"
                                       d-data-toggle="collapse" aria-expanded="false"
                                       href="{{$menuData['active'] && $menuData['url'] ? route($menuData['url']) : ''}}">
                                        {{t($menuName)}}
                                        @if($menuName === 'ui_cabinet_menu_notifications' && $notifications_count_client)
                                            <span class="notifications-count">{{ $notifications_count_client }}</span>
                                        @endif
                                    </a>
                                </li>

                            @endforeach
                        </ul>

                        <div class="help-button mt-4 ml-3">
                            <a href="{{ route('cabinet.help.desk') }}" class="btn btn-lg btn-primary themeBtn w-100" style="max-width: 127px;">
                                {{ t('ui_help') }}
                            </a>
                            @if($active_tickets)
                                <span class="help-count cabinetTicketsCount">{{ $active_tickets }}</span>
                            @endif
                        </div>
                    </div>
                </nav>

            </div>

            <main role="main" class="right-panel col main  px-4">
                <button type="button" class="navbar-toggler mr-2  d-lg-none" d-data-toggle="offcanvas"
                        title="Toggle responsive left sidebar">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>

                <div class="notifications-bell">
                    <a href="{{ route('cabinet.notifications.index') }}">
                        <i class="fa fa-bell-o" aria-hidden="true"></i>
                        @if($notifications_count_client)
                            <span class="notifications-count">{{ $notifications_count_client }}</span>
                        @endif
                    </a>
                </div>

                <div class="container-fluid p-0 ml-0 balance-outer">

                    @yield('content')

                </div>

            </main>
        </div>
        <footer>
            <div class="row">
                <div class="col-md-3 col-lg-2 d-none d-lg-block"></div>
                <div class="col-md balance-outer pl-4 ml-2">
                    <div class="row footer-links-red-inner">
                        <div class="col-sm-4 col-xl" style="display: flex;justify-content: center;">
                            <a href="/" class="mb-4 d-block">
                                <img src="{{ $currentProject->logoPng }}"
                                                                    class="img-fluid projectLogo" alt=""></a>
                        </div>

                        <div class="col-sm-4 col-xl-2">
                            {{ config('cratos.company_details.name') }}
                            <br>
                            Registry code {{config('cratos.company_details.registry')}}
                        </div>

                        <div class="col-sm-4 col-xl-3">
                            {!! t('cratos_layout_address') !!}
                        </div>

                        <div class="col-sm-4 col-xl-3">
                            {{ config('cratos.company_details.name') }} provides
                            <br>
                            a virtual currency services
                        </div>

                        <div class="col-sm-4 col-xl">
                            <a href="https://cratos.net/faq" target="_blank">FAQ</a>
                            <br>
                            <a href="https://cratos.net/terms-and-conditions" target="_blank">User agreement</a>
                        </div>

                        <div class="col-sm-4 col-xl">
                            <a href="https://cratos.net/privacy-policy" target="_blank">Privacy policy</a>
                            <br>
                            <a href="https://cratos.net/aml-policy" target="_blank">AML policy</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script>
    let API = '{{ config('cratos.urls.cabinet.api-v1') }}';
    /** current 2FA type */
    var two_fa_type = '{{ $cUser = auth()->guard('cUser')->user()->two_fa_type }}';

    setTimeout(function () {
        $('.alert-success').remove();
    }, 2000);
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" ></script>
<script src="/js/common/bootstrap.min.js"></script>
<link href="{{ config('cratos.urls.theme') }}css/select2.css?v={{ time() }}" rel="stylesheet"/>
<script src="/js/common/select2.full.min.js"></script>
<script src="/js/cabinet/common.js?v={{ time() }}"></script>
<script src="/js/loader.js"></script>
<script>
    $("#from").datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        todayBtn: "linked",
        endDate: new Date(),
        autoclose: true,
        todayHighlight: true
    });
    $( "#to" ).datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        todayBtn: "linked",
        endDate: new Date(),
        autoclose: true,
        todayHighlight: true
    });
</script>
@yield('scripts')
</body>
</html>
