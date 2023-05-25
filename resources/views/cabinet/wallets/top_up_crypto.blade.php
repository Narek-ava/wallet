@extends('cabinet.layouts.cabinet')
@section('title', t('title_top_up_page') . strtoupper($cryptoAccountDetail->coin) . t('title_wallet_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title">Top Up - {{ strtoupper($cryptoAccountDetail->coin) }} Wallet</h3>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>
        </div>
    </div>

    <div class="row mb-5 pl-3">
        <div class="col-md-12 p-0 wallet-tablist">
            <h5 class="mb-3">{{ t('deposit_step_1') }}</h5>
            <br>
            <div class="text-left">
                <a id="crypto" class="select-crypto-type btn text-dark ml-0 mb-0" onclick="setHref('/cabinet/top-up-crypto/', this.id)">
                    <span>{{ t('ui_cryptocurrency_transfer') }}</span></a>
                @if($paymentProviderExists)
                    <a id="wire_transfer"
                       @if(!config('app.allow_wire') &&
                           @auth()->user()->cProfile->account_type === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                       hidden
                       @endif
                       class="select-crypto-type btn text-dark ml-0 mb-0"
                       @if(!$restrictOperations)
                       onclick="setHref('/cabinet/wire-transfer/', this.id)"
                       @else
                       style="opacity: 0.5" title="{{ t('ui_user_top_up_blocking_message_wire_transfer') }}"
                        @endif>
                        <span>{{ t('ui_wire_transfer') }}</span>
                    </a>
                    @if(config('cratos.enable_fiat_wallets'))
                        <a id="fiat" class="select-crypto-type btn text-dark ml-0 mb-0"
                           @if(!$restrictOperations)
                               onclick="setHref('/cabinet/buy-crypto-from-fiat/', this.id)"
                           @else
                               style="opacity: 0.5" title="{{ t('ui_user_top_up_blocking_message_bank_card') }}"
                            @endif>
                            <span>{{ t('ui_bank_fiat_transfer') }}</span>
                        </a>
                    @endif
                @endif
                @if(auth()->user()->cProfile->account_type === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL && $cardProviderExists)
                    <a id="bank_card" class="select-crypto-type btn text-dark ml-0 mb-0"
                       @if(!$restrictOperations)
                           onclick="setHref('/cabinet/card-transfer/', this.id)"
                       @else
                            style="opacity: 0.5" title="{{ t('ui_user_top_up_blocking_message_bank_card') }}"
                       @endif>
                        <span>{{ t('ui_bank_card_transfer') }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div id="crypto_section" class="row pl-3 d-none">
        <div class="col-md-12 pl-0" style="max-width: 600px;">
            <h5>{{ t('ui_wallet_address') }}</h5>
            <div class="mb-3 w-100 d-block d-sm-flex">
                <input id="{{ 'text' . $cryptoAccountDetail->coin }}"
                       type="text"
                       value="{{ $cryptoAccountDetail->address }}">
                <button id="{{ $cryptoAccountDetail->coin }}" class="btn btn-light"
                        onclick="copyText(this)">
                    <i class="fa fa-copy" aria-hidden="true"></i> {{ t('ui_copy_address') }}
                </button>
            </div>
            <br>
            <img height="150px" class="mt-3"
                 src="{{ 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $cryptoAccountDetail->address}}" alt="">
        </div>
    </div>

    <a class="btn btn-primary themeBtn btnWhiteSpace d-none" type="submit" id="nextBtn" style="border-radius: 25px">{{ t('wire_transfer_next') }}</a>
@endsection

@section('scripts')
    <script>
        function setHref(href, id) {
            $('#bank_card').removeClass('active');
            $('#crypto').removeClass('active');
            $('#wire_transfer').removeClass('active');
            $('#fiat').removeClass('active');

            if(id == 'crypto'){
                $('#crypto_section').removeClass('d-none');
                $('#nextBtn').addClass('d-none');
            }else{
                if ($('#currency').data('disable-other-operations')) {
                    console.log($('#currency').data('disable-other-operations'))
                } else {
                    $('#crypto_section').addClass('d-none');
                    $('#nextBtn').removeClass('d-none');
                }

            }

            $('#nextBtn').attr('href', href + "{{ $cryptoAccountDetail->id }}");
            $('#' + id).addClass('active');
        }
    </script>

    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
    <script>
        $('#wire_transfer, #bank_card, #fiat').tooltip({
            content: $(this).attr('title'),
            track: false,
        })
    </script>
@endsection
