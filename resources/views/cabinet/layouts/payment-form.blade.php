<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            --main-color: {{ $project->colors->mainColor ?? \C\getProjectColors()->mainColor ?? '#fe3d2b' }};
            --button-color: {{ $project->colors->buttonColor ?? \C\getProjectColors()->buttonColor ?? '#fe3d2b' }};
            --border-color: {{ $project->colors->borderColor ?? \C\getProjectColors()->borderColor ?? '#fe3d2b' }};
            --notify-from: {{ $project->colors->notifyFromColor ?? \C\getProjectColors()->notifyFromColor ?? '#f96283' }};
            --notify-to: {{ $project->colors->notifyToColor ?? \C\getProjectColors()->notifyToColor ?? '#ffc052' }};
        }
    </style>
    <link href="{{ config('cratos.urls.theme') }}css/select2.css" rel="stylesheet"/>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css"
          integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/duotone.css"
          integrity="sha384-R3QzTxyukP03CMqKFe0ssp5wUvBPEyy9ZspCB+Y01fEjhMwcXixTyeot+S40+AjZ" crossorigin="anonymous"/>

    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/fontawesome.css"
          integrity="sha384-eHoocPgXsiuZh+Yy6+7DsKAerLXyJmu2Hadh4QYyt+8v86geixVYwFqUvMU8X90l" crossorigin="anonymous"/>
    <title>:: Welcome to {{ config('app.name') }} ::</title>
</head>

<body>
<section class="middil-container payment-form-no-background">
    @yield('content')
</section>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>
<script src="/js/common/bootstrap.min.js"></script>
@yield('scripts')
</body>
</html>
