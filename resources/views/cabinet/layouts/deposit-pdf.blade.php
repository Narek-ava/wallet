<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"  />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.min.css"   />
    <style type="text/css">

        body {
            font-family: DejaVu Sans;
        }

    </style>
</head>
@php
    $currentProject = $operation->CProfile->cUser->project ?? null;
@endphp
<body class="">
<div class="container">
    <div class="row">
        <div class="col-md-12 mt-5" style="margin-top: 50px">
            <img src="{{ $currentProject->logoPng ?? '' }}" style="max-width: 180px;max-height: 180px" class="img-fluid" alt="">
        </div>
        @yield('content')
    </div>
</div>

<script>
    let API = '{{ config('cratos.urls.cabinet.api-v1') }}';
</script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" ></script>
<script src="/js/common/bootstrap.min.js"></script>
</body>
</html>
