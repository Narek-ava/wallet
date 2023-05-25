@extends('cabinet.layouts.cabinet')
@section('title', t('title_top_up_page') . strtoupper($fiatAccount->currency) . t('title_wallet_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title">Top Up - {{ strtoupper($fiatAccount->currency) }} Wallet</h3>
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
                <a id="wire_transfer" class="select-crypto-type btn text-dark ml-0 mb-0"
                   onclick="setHref('{{route('cabinet.fiat.top_up.wire', ['id' => $fiatAccount->id])}}', this.id)">
                    <span>{{ t('ui_wire_transfer') }}</span>
                </a>
            </div>
        </div>
    </div>


    <a class="btn btn-primary themeBtn btnWhiteSpace d-none" type="submit" id="nextBtn" style="border-radius: 25px">{{ t('wire_transfer_next') }}</a>
@endsection

@section('scripts')
    <script>
        function setHref(href, id) {

            if (!$('#currency').data('disable-other-operations')) {
                $('#crypto_section').addClass('d-none');
                $('#nextBtn').removeClass('d-none');
            }

            $('#nextBtn').attr('href', href);
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
