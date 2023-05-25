@extends('cabinet.layouts.cabinet')
@section('title', t('title_request_page'))

@section('content')
    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 mt-2 large-heading-section page-title">Request - Wire #48</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex">
                    <p>{{ t('ui_lorem') }}</p>
                </div>
                <div class="col-lg-7">
                    <div class="compliance common-shadow-theme">
                        <div class="info-label">
                            <i class="fa fa-exclamation" aria-hidden="true"></i>
                        </div>
                        <div class="col"><h2 class="mb-3">{{ t('ui_menu_compliance') }}</h2></div>
                        <div class="row m-0">
                            <div class="col-lg-9">
                                <p class="font-weight-bold">{{ t('ui_use_deposit') }}</p>
                            </div>
                            <div class="col-lg-3">
                                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4" type="submit">
                                    {{ t('ui_compliance_0_level_info_box_button_text') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3">
                    <h3>{{ t('ui_wire_transfer_detail') }}</h3>
                    @php
                        $currency = \App\Enums\Currency::FIAT_CURRENCY_NAMES[$exchangeRequest->trans_currency];
                        $toCurrency = \App\Enums\Currency::getName($exchangeRequest->account->currency);
                        $by = \App\Enums\WireType::NAMES[$bankAccountTemplate->type];
                        $percent = number_format((float)$ratesValueRateValue, 1, '.', '');
                        $transactionLimit = number_format((float)$ratesValueRateLimitValue, 1, '.', '');
                        $availableLimit = number_format((float)$ratesValueRateMonthLimitValue, 1, '.', '');
                        $fee = \C\fee($exchangeRequest->trans_amount, $percent);
                        $receive = $exchangeRequest->trans_amount - $fee;
                    @endphp
                    <div class="mt-5">
                        <p><span class="bold">Date:</span> {{ date('d-m-Y', strtotime($exchangeRequest->creation_date)) }}</p>
                        <p><span class="bold">Currency:</span> {{ $currency }}</p>
                        <p><span class="bold">Exchange to:</span> {{ $toCurrency }}</p>
                        <p><span class="bold">Amount:</span> {{ (int)$exchangeRequest->trans_amount }}</p>
                        <p><span class="bold">By:</span> {{ $by }}</p>
                        <p><span class="bold">Time to fund:</span> {{t('ui_cabinet_deposit_time_to_fund_text')}}</p>
                        <p>
                            <span class="bold">Fee:</span>
                            {{ $fee . " " . $currency }}
                            ({{ $percent }}%, minimum 35 {{ $currency }})
                        </p>
                        <p><span class="bold">You will receive:</span> {{ $receive . ' ' . $currency  }}</p>
                        <p><span class="bold">Transaction limit:</span> {{ (int)$transactionLimit . ' ' . $currency }}</p>
                        <p><span class="bold">Available limit:</span> {{ (int)$availableLimit . ' ' . $currency }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <h3>Bank details</h3>
                    <p class="mt-4">Choose from template</p>
                    <div class="form-group col-md-12 mt-4">
                        <input class="mt-3 form-control" type="text" name="template" id="template" value="{{ $bankAccountTemplate->name }}">
                    </div>
                    <button class="btn btn-danger mt-5" data-toggle="modal" data-target="#bankDetails">View details</button>
                    <p class="mt-4"><span class="bold">Credited:</span> {{ $receive }} {{ $currency }}</p>
                </div>
                <div class="col-md-6">
                    <div>
                        <h3>Status - <span class="status-class">{{ t(\App\Enums\ExchangeRequestStatuses::NAMES[$exchangeRequest->status]) }}</span></h3>
                        <div>
                            <textarea name="" id="" rows="4" cols="60" class="status-textarea">Your deposit was credited on amount of {{ $receive }} {{ $currency }}</textarea>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-md-8">
                            <h3 class="font-weight-bold">{{ $by }} bank details</h3>
                        </div>
                        <div class="col-md-4 clickable-class">
                            <a href="{{ route('cabinet.download.pdf', ['filename' => strtolower($currency) . '_' . strtolower($by) . '.pdf']) }}">
                                <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="50" class="pdf-icon">
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <span class="bold w150">Account Beneficiary</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">Beneficiary address</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">IBAN EUR</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">SWIFT/BIC</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">Bank Name</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">Bank Address</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                        <div class="col-md-4">
                            <span class="bold w150">Reference</span>
                        </div>
                        <div class="col-md-8">
                        </div>
                    </div>
                </div>
            </div>
                <div class="row mt-5">
                    <div class="col-md-3">
                        <h3>Upload proof</h3>
                        <p>Here you need upload a scan copy or photo of the document confirming relpenishment</p>
                        <form action="{{ route('deposit.upload.proof', ['id' => $exchangeRequest->id]) }}" method="post" enctype="multipart/form-data" id="proofForm">
                            @csrf
                            <label class="btn btn-danger">Upload
                                <input type="file" id="proof" class="btn btn-danger" style="display: none;" name="proof" accept="image/x-png,image/jpeg,application/pdf">
                            </label>
                        </form>
                    </div>
                    @if($ratesValueRateMonthLimitValue < (int)$exchangeRequest->trans_amount)
                        <div class="col-md-3">
                            <h3>Compilance</h3>
                            <p>no additional requests <br> This operation goes beyond the limits of your verification level,
                                provide source of funds to approve</p>
                            <button class="btn btn-primary">Upload</button>
                        </div>
                    @endif
                    <div class="col-md-6 p-5">
                        <a href="{{ route('deposit.set.status', ['id' => $exchangeRequest->id, 'status' => \App\Enums\ExchangeRequestStatuses::STATUS_WAITING_FOR_DEPOSIT]) }}" class="btn btn-danger">Approve</a>
                        <a href="{{ route('deposit.set.status', ['id' => $exchangeRequest->id, 'status' => \App\Enums\ExchangeRequestStatuses::STATUS_DELETED]) }}" class="btn btn-dark">Delete</a>
                    </div>
                </div>
            <div class="row mt-5">
                <div class="col-md-12">
                    <h3>Upload documents</h3>
                </div>
            </div>
        </div>
    </div>
    @include('cabinet._modals.bank_details')

@endsection

@section('scripts')
    <script>
        $('#proofForm').on('change', function() {
            let formData = new FormData();
            formData.append('proof', $('#proof')[0].files[0]);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            $.ajax({
                url : '{{ route("deposit.upload.proof", ["id" => $exchangeRequest->id]) }}',
                type : 'POST',
                data : formData,
                processData: false,
                contentType: false,
                enctype: 'multipart/form-data',
                success : function() {
                    $('#proofForm').append('<p>{{ t('ui_compliance_documents_requested_success') }}</p>')
                }
            });
        });
    </script>
@endsection
