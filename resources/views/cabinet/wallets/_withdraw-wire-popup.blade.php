<div id="operationCreatedPopUp" class="modal w-100 fade" tabindex="-1" role="dialog"
     aria-labelledby="operationCreatedPopUp"
     aria-hidden="true">
    <div class="modal-dialog modal-sm" style="max-width: 600px;">

        <div class="row m-auto">
            <div class="col-md-12">

                <div class="modal-content text-center pb-4">
                    <div class="modal-header">
                        {{--                        <h4 class="modal-title" id="operationCreatedPopUp">{{ t('status_successful') }}</h4>--}}
                        <h4 class="modal-title" id="operationCreatedPopUp"> {!! session('showModalInfo') !!} </h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                    @if(session()->has('walletAddress'))
                        <p class="m-4">{{ t('your_crypto_address') }}</p>
                        <h6 class="mb-5 pl-2 pr-2" style="word-break: break-all;"> {!! session('walletAddress') !!}</h6>
                    @endif

                    @if(session()->has('operationCreated'))
                        <p class="m-4">{!! session('operationCreated') !!}</p>
                    @endif

                    @if(session()->has('showModalInfo') && session('showModalInfo') != t('withdrawal_crypto_fail'))
                    <a href="{{ route('cabinet.history') }}" class="btn btn-lg btn-primary themeBtn w-100 m-auto"
                       style="max-width: 165px;">
                        {{ t('go_to_history') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
