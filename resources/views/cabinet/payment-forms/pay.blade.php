@extends('cabinet.layouts.payment-form')

@section('content')

    <div class="container">
    <a href="/" class="navbar-brand d-block pb-3 text-center">
        <img src="{{ $currentProject->logoPng }}" class="projectLogo" alt="">
    </a>
    <div class="login-form login-form-outer-merchant-payment ml-auto mr-auto">
        <div class="common-form">
            <h3 class="text-center"><span>{{ t('pending_operation') }}</span></h3>
            <p class="text-center pay-step d-block"><span>{{ t('continue_description') }}</span></p>
            <p class="text-center end-operation d-none"><span>{{ t('pending_operation_description') }}</span></p>

            <form method="get" target="_blank"
                  action="{{route('redirect.trust.payments', ['operationId' => $operation->id])}}"
                  class="form-signin">
                <div>
                    <div class="form-label-group">
                        <div class="row col-md-12" style="margin: 0">
                            <div class="col-sm-12 payment-form-loader-container">
                                <img src="{{ config('cratos.urls.theme') }}images/checkmark.jpg" width="100%" alt="">
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-lg btn-primary themeBtn col-md-12" id="finishOperation" type="sumbit"
                            style="text-decoration: none"> {{ t('pay') }} </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#finishOperation').on('click', function () {
            $(this).attr('hidden', true);
            $('.end-operation').addClass('d-block').removeClass('d-none')
            $('.pay-step').addClass('d-none').removeClass('d-block')
        })

    })
</script>
@endsection
