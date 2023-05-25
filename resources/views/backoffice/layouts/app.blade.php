<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width">
    <meta name="theme-color" content="#41be3b">
    <title>CRATOS Backoffice</title>

    <style>
        :root {
            --main-color: #fe3d2b;
            --button-color: #fe3d2b;
            --border-color: #fe3d2b;
            --notify-from: #f96283;
            --notify-to: #ffc052;
        }
    </style>
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/bootstrap.min.css">
    <link href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"></script>
    <style>
        html {
            zoom: 1;
        }
    </style>
    @yield('styles')


</head>
<body>
    <!-- header -->
    <main class="main ">
        <div class="shell">
            <div class="wrap_content">
                <div class="content">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

@yield('scripts')
    <!-- Optional JavaScript -->
    <!-- jQuery Popper.js, then Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
            integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
            crossorigin="anonymous"></script>
    <script src="/js/common/bootstrap.min.js"></script>
    <link href="{{ config('cratos.urls.theme') }}css/select2.css?v={{ time() }}" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
</body>
</html>
