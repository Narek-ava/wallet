@extends('cabinet.layouts.cabinet')
@section('title', t('title_history_page'))
@section('content')
    <div class="col-md-12 p-0">
        <h2 class="mb-3 large-heading-section page-title">{{__('History')}}</h2>
        <div class="row">
            <div class="col-lg-5">
                <div class="balance">
                    {{ t('ui_request_48_hours') }}
                </div>
            </div>
            @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
        </div>
    </div>

   <div class="mt-5">
       @include('backoffice.partials.session-message')
   </div>

    @include('cabinet._modals._success')
    <input hidden id="newCardOperationSuccess" value="{{ session()->get('newCardOperationSuccess')}}"/>

    <div class="col-md-12 mt-5 p-0 pt-4">
        <form>
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="number" class="font-weight-bold mb-0">{{ t('ui_number') }}</label>
                        <input type="number" name="number" class="w-100" value="{{ request()->number }}" placeholder="Number">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="font-weight-bold mb-0">{{ t('date') }}</label>
                        <input class="date-inputs display-sell w-100" name="from" id="from" value="{{ request()->from }}" placeholder="From date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input class="date-inputs display-sell w-100" name="to" id="to" value="{{ request()->to }}" placeholder="To date">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="number" class="font-weight-bold">{{ t('transaction_type') }}</label>
                        <select class="w-100" name="transaction_type" id="transaction_type">
                            @foreach(\App\Enums\OperationType::NAMES as $key => $name)
                                @continue(!config('cratos.enable_fiat_wallets') && in_array($key, \App\Enums\OperationType::FIAT_PAYMENT_TYPES))
                                @if((!in_array($key, \App\Enums\OperationType::MERCHANT_PAYMENT_TYPES)) || ($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE && $key !== \App\Enums\OperationType::MERCHANT_PAYMENT))
                                    <option
                                        value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 ">
                    <div class="form-group">
                        <label for="number" class="font-weight-bold">{{ t('title_wallet_page') }}</label>
                        <select class="w-100" name="wallet" id="wallet">
                            <option value=""></option>
                            @foreach($cryptoAccountDetails as $wallet)
                                <option value="{{ $wallet->account_id }}" {{ $wallet->account_id == request()->wallet ? 'selected' : '' }}>{{ $wallet->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 ">
                    <div class="form-group">
                        <button class="btn btn-lg btn-primary themeBtn" type="submit">{{ t('ui_search') }}</button>
                    </div>
                </div>
                <a href="javascript: void(0)" class="history-list-report">
                    <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">{{ t('ui_wire_operation_report') }}</a>
                    <img src="{{ config('cratos.urls.theme') }}images/loader.gif" class="reportLoading">
            </div>
        </form>
    </div>
    @if($operations->count())
        <div class="col-md-12 mt-3">
            <div class="row d-none d-md-flex">
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_number') }}</div>
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_date_time') }}</div>
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_amount') }}</div>
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_type') }}</div>
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_status') }}</div>
                <div class="col-md-2 activeLink">{{ t('transaction_history_table_heading_details') }}</div>
            </div>
        </div>
        @foreach($operations as $operation)
            @if($operation->operation_type != \App\Enums\OperationOperationType::TYPE_CARD_PF)
                @include('cabinet.history.partials.transaction-details.card-template',['operation' => $operation])
            @endif
            @if($operation->parent && $operation->operation_type == \App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF && $operation->parent->operation_type == \App\Enums\OperationOperationType::TYPE_CARD_PF)
                @include('cabinet.history.partials.transaction-details.card-template',['operation' => $operation->parent])
            @endif
        @endforeach
        {{ $operations->appends(request()->all())->links() }}
    @else
        <div class="col-md-12 mt-5 pl-0">
            <h6 class="mt-3 empty-data-text">{{ t('transactions_empty') }}</h6>
        </div>
    @endif


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
            })

            $('.history-list-report').click(function (e) {
                e.preventDefault();
                $('.reportLoading').show();
                var data = {};

                var form = $(this).closest('form');

                form.find('[name="number"]').val() ? data.number = form.find('[name="number"]').val() : false;
                form.find('[name="from"]').val() ? data.from = form.find('[name="from"]').val() : false;
                form.find('[name="to"]').val() ? data.to = form.find('[name="to"]').val() : false;
                form.find('[name="transaction_type"]').val() ? data.transaction_type = form.find('[name="transaction_type"]').val() : false;
                form.find('[name="wallet"]').val() ? data.wallet = form.find('[name="wallet"]').val() : false;
                var params = new window.URLSearchParams(window.location.search);
                data.page = params.get('page') ?? 1;
                var param = '?';

                for (const property in data) {
                    param += `${property}=${data[property]}&`;
                }

                window.location = '{{ route('cabinet.download.history.report.pdf') }}' + param;
                setTimeout(function () {
                    $('.reportLoading').hide();
                }, 3000)
            });

        });
        $('#from').datepicker({ format: 'yyyy-mm-dd' });
        $('#to').datepicker({ format: 'yyyy-mm-dd' });

    </script>

    <script>
        let successMessage = $('#newCardOperationSuccess').val();
        if (successMessage) {
            $('#successText').text(successMessage)
            $('#success').modal('show');
        } else {
            $('#success').modal('hide');
        }
    </script>
@endsection
