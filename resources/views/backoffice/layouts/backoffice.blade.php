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
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <style>
        :root {
            --main-color: #fe3d2b;
            --button-color: #fe3d2b;
            --border-color: #fe3d2b;
            --notify-from: #f96283;
            --notify-to: #ffc052;
        }
    </style>
    @yield('styles')

    <link rel="canonical" href="https://getbootstrap.com/docs/4.0/examples/dashboard/">
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Bootstrap core CSS -->
    <link href="{{ config('cratos.urls.theme') }}css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"  />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.min.css"   />
    <!-- Custom styles for this template -->
    <link href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ config('cratos.urls.theme') }}css/datatables.min.css"/>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/cratos.css?v={{ time() }}">
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/backoffice.css?v={{ time() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
    <script async src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
</head>
<body class="">
<div class="page-wrapper toggled">
    <div class="quation"></div>
    <div class="quationform">
        <div id="feedback-form" style="display: none;" class="col-xs-4 col-md-4 panel panel-default">
            sddsfsff
        </div>
    </div>
    <div class="container-fluid">
        <div class="row row-offcanvas row-offcanvas-left">
            <div class="col-md-3 col-lg-2 sidebar-offcanvas pl-0" id="sidebar" role="navigation">
                @include('backoffice.layouts._menu')
            </div>
            <main role="main" class="right-panel col main  px-4">
                {{--
                <button type="button" class="navbar-toggler mr-2 " data-toggle="offcanvas" title="Toggle responsive left sidebar">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3">
                </div>
                --}}
                <div class="container-fluid p-0 ml-0 balance-outer crm-users-outer">
                @yield('content')
                </div>
                <input type="hidden" id="managerPermissions" data-permissions="{{ route('backoffice.check.manager.permission') }}">
            </main>
        </div>
        <footer>
            <div class="row">
                <div class="col-md-3 col-lg-2 d-none d-lg-block"></div>
                <div class="col-md balance-outer pl-4 ml-2">
                    <div class="row footer-links-red-inner">
                        <div class="col-sm-4 col-xl">
                            <a href="/" class="mb-4 d-block">
                                <img src="{{ config('cratos.urls.theme') }}images/logo.svg"
                                     class="img-fluid" alt=""></a>
                        </div>

                        <div class="col-sm-4 col-xl-2">
                            {{ config('cratos.company_details.name') }}
                            <br>
                            Registry code {{config('cratos.company_details.registry')}}
                        </div>

                        <div class="col-sm-4 col-xl-3">
                            Registered at {{config('cratos.company_details.address')}},
                            <br>
                            {{config('cratos.company_details.city')}}, {{config('cratos.company_details.zip_code')}}, {{ config('cratos.company_details.country') }}.
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
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="/js/common/bootstrap.min.js"></script>
<script src="/js/common/input-number-format.jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" ></script>
<script src="/js/backoffice/custom.js?v={{ time() }}" ></script>
<script type="text/javascript">
    $(document).ready(function() {
     //   $('#example').DataTable();
    });
    $(function () {

        {{-- @see https://24eme.github.io/jquery-input-number-format/example.html --}}
        $('.rates-input').inputNumberFormat().trigger('change');



        var deactivateCategory = function (cId) {
            if (!confirm('Sure?')) {
                return;
            }

            $.post({
                url: "/backoffice/rates/deactivate",
                data: {RatesCategoryId: cId, _token: "{{ csrf_token() }}",},
            }).always(function () {
                location.reload();
            });
        };

        $(".rates-category-list-item .close").hide();
        $(".rates-category-list-item .close").on('click', function () {
            deactivateCategory($(this).data().categoryId);
        });

        $(".rates-category-list-item").hover(function (e) {
            $(this).find('.close').show();
        }, function () {
            $(this).find('.close').hide();
        });

        setTimeout(function () {
            $('.alert-success').remove();
        }, 2000);


    })
</script>

@yield('scripts')
<script src="/js/backoffice/permissions.js" ></script>

</body>
</html>
