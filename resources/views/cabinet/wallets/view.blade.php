@extends('cabinet.layouts.cabinet')
@section('title', strtoupper($cryptoAccountDetail->coin) . t('title_wallet_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ strtoupper($cryptoAccountDetail->coin) }} Wallet</h2>
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
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h4>
                {{ t('ui_warning') }}
                <br>
                {{ $errors->first()}}
            </h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @include('cabinet.partials.session-message')

    <div class="row">
        <div class="col-md-6" style="max-width: 550px;">
            <a href="{{ route('cabinet.wallets.index') }}" class="text-dark"><i class="fa fa-arrow-left"
                                                                                aria-hidden="true"></i> Back</a>
            <div class="common-shadow-theme wallet-btc btc mt-5 mb-4 ml-0"
                data-cryptocurrency="{{strtolower($cryptoAccountDetail->coin)}}" style="max-width: 100%">
                <div class="label">
                    <img
                        src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$cryptoAccountDetail->coin] ?? '')) }}"
                        width="35px" alt="">
                </div>
                <div class="d-block">
                    <h3>{{ strtoupper($cryptoAccountDetail->coin) }} {{ $cryptoAccountDetail->account->displayAvailableBalance() }}</h3>
                    <p class="h3 themeColorRed font-weight-bold">&euro; {{ generalMoneyFormat($rateForEUR, \App\Enums\Currency::CURRENCY_EUR) }}</p>
                    <h5>$ {{ generalMoneyFormat($rateForUSD, \App\Enums\Currency::CURRENCY_USD) }}</h5>
                    <div class="mb-3 w-100 d-flex">
                        <input id="{{ 'text' . $cryptoAccountDetail->coin }}"
                               type="text"
                               class="wallet-address-input"
                               value="{{ $cryptoAccountDetail->address }}">
                        <button id="{{ $cryptoAccountDetail->coin }}" class="btn btn-light"
                                onclick="copyText(this)">
                            <i class="fa fa-copy" aria-hidden="true"></i>
                        </button>
                    </div>
                    <a class="btn btn-primary themeBtn btnWhiteSpace mb-2"
                       style="border-radius: 30px"
                       href="{{ route('cabinet.wallets.top_up_crypto', $cryptoAccountDetail->id) }}" type="submit">Top
                        up</a>
                    <a class="btn btn-primary themeBtn btnWhiteSpace mb-2 {{ $cryptoAccountDetail->account->displayAvailableBalance() <= 0 ? 'disabled' : '' }}"
                       style="border-radius: 30px"
                       href="{{ route('cabinet.wallets.send.crypto', $cryptoAccountDetail->id) }}"
                       type="submit">{{ t('wallet_detail_withdraw') }}</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <h4>{{ t('ui_transactions_history') }}</h4>
            <form action="{{ route('cabinet.wallets.show', $cryptoAccountDetail->id) }}" method="get">
                @csrf
                <div class="row align-items-end mt-5 mb-5">
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <label for="number" class="font-weight-bold">{{ t('ui_number') }}</label>
                            <input type="text" name="number" class="operation-number" placeholder="Number" value="{{ request()->number }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <label class="font-weight-bold">{{ t('date') }}</label>
                            <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="date w-100" name="from" placeholder="From date" autocomplete="off" value="{{ request()->from }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <input data-provide="datepicker" data-date-format="yyyy-mm-dd" class="date w-100" name="to" placeholder="To date" autocomplete="off" value="{{ request()->to }}">
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <div class="form-group">
                            <label for="number" class="font-weight-bold">{{ t('transaction_type') }}</label>
                            <select class="w-100 transaction-type" name="transaction_type" id="transaction_type">
                                @foreach(\App\Enums\OperationType::NAMES as $key => $name)
                                    @continue(!config('cratos.enable_fiat_wallets') && in_array($key, \App\Enums\OperationType::FIAT_PAYMENT_TYPES))
                                    <option value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <div class="form-group">
                            <button class="btn btn-lg btn-primary themeBtn" type="submit">Search</button>
                        </div>
                    </div>
                </div>

            </form>

            <div class="row">
                @if($operations->count())
                    <div class="col-md-12 mt-3">
                        <div class="row d-none d-md-flex">
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_number') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_date_time') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_amount') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_type') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_received') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_status') }}</div>
                            <div class="col-md activeLink">{{ t('transaction_history_table_heading_details') }}</div>
                        </div>
                    </div>
                    @foreach($operations as $operation)
                        <div class="col-md-12 mt-4 history-element">
                            <div class="row">
                                <div class="col-md history-element-item activeLink" data-label-sm="NUMBER">{{ $operation->operation_id ?? '-'}}</div>
                                <div class="col-md history-element-item activeLink" data-label-sm="DATE & TIME">{{ $operation->created_at->timezone($operation->cProfile->timezone)}}</div>
                                <div class="col-md history-element-item activeLink" data-label-sm="AMOUNT">
                                    {{ formatMoney($operation->amount, $operation->from_currency) }}
                                </div>
                                <div class="col-md history-element-item activeLink" data-label-sm="TYPE">
                                    {{ $operation->getOperationType() }}
                                </div>
                                <div class="col-md history-element-item activeLink" data-label-sm="RECEIVED">{{ $operation->credited }}</div>
                                <div class="col-md history-element-item activeLink" data-label-sm="STATUS">{{ \App\Enums\OperationStatuses::NAMES[$operation->status] ?? '-' }}</div>
                                <div class="col-md history-element-item activeLink" data-label-sm="DETAILS">
                                    <a class="details link-default text-nowrap" href="#" data-operation-id="{{ $operation->id }}">
                                        {{ t('see_details') }} <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                            @if (!$operation->isLimitsVerified())
                                <a href="{{ route('cabinet.compliance') }}"
                                    class="btn btn-lg btn-primary themeBtn approval-operation-btn">
                                    {{ t('approval_request') }}
                                </a>
                            @endif
                            @if($operation->operationDetailView)
                                @include($operation->operationDetailView)
                                @include('cabinet._modals.decline-request')
                            @endif
                        </div>
                    @endforeach
                    {!!$operations->appends(request()->query())->links() !!}
                @else
                    <div class="col-md-12">
                        <h6 class="mt-2 empty-data-text">{{ t('transactions_empty') }}</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('cabinet.wallets._withdraw-wire-popup')
@endsection

@section('scripts')
    <script>
        function declineRequest(id) {
            $('#declineRequest').modal('show');

            $.ajax({
                url: '{{ route('cabinet.decline.operation.data') }}',
                type:'get',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'operation_id': id
                },
                success:function (data) {
                    $('.operation-info').text(data.type + ' ' + data.operation.operation_id);
                    var url = '{{ route("cabinet.operation.decline", ":id") }}';
                    url = url.replace(':id', id);
                    $('#decline-operation-form').attr('action', url);
                }
            })
        }
            $(document).ready(function () {
            var string = window.location.href;
            var result = string.split("=").pop();

            if (result == 1) {
                $('.alert-warning').removeClass('d-none');
            }

            $('body').on('click', '.details', function (e) {
                e.preventDefault();
                let operationId = $(this).data('operation-id');
                if ($('.details'+operationId).css('display') === 'block'){
                    $('.details'+operationId).css('display', 'none');
                    $(this).children('i').removeClass('fa-angle-up').addClass('fa-angle-down')
                } else if ($('.details'+operationId).css('display') === 'none'){
                    $('.details'+operationId).css('display', 'block');
                    $(this).children('i').addClass('fa-angle-up').removeClass('fa-angle-down')
                }
            });

            @if(session()->has('showModalInfo'))
            $("#operationCreatedPopUp").modal("toggle");
            @endif
        });
    </script>
@endsection
