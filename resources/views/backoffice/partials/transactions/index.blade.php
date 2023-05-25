<div class="container pl-0 ml-0">
    <div class="row">
        <div class="col-md-3 pt-2" style="max-width: 200px;">
            <h5>{{ t('operations') }}</h5>
        </div>
        <div class="col-md-9">
            <ul class="nav" role="tablist">
                <li class="nav-item transaction-history-tab tab-active">
                    {{--                        <div class="history-tab-count">111</div>--}}
                    <a class="tab-link-color-black nav-link" id="pendingLink" data-toggle="tab" href="#pending" role="tab" aria-controls="home" aria-selected="true" data-id="{{ \App\Enums\OperationStatuses::PENDING }}">Pending</a>
                </li>
                <li class="nav-item transaction-history-tab tab-inactive">
                    <a class="tab-link-color-black nav-link" id="successfulLink" data-toggle="tab" href="#successful" role="tab" aria-controls="home" aria-selected="true" data-id="{{ \App\Enums\OperationStatuses::SUCCESSFUL }}">Successful</a>
                </li>
                <li class="nav-item transaction-history-tab tab-inactive">
                    <a class="tab-link-color-black nav-link" id="declinedLink" data-toggle="tab" href="#declined" role="tab" aria-controls="home" aria-selected="true" data-id="{{ \App\Enums\OperationStatuses::DECLINED }}">Declined</a>
                </li>
                <li class="nav-item transaction-history-tab tab-inactive">
                    <a class="tab-link-color-black nav-link" id="returnedLink" data-toggle="tab" href="#returned" role="tab" aria-controls="home" aria-selected="true" data-id="{{ \App\Enums\OperationStatuses::RETURNED }}">Returned</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="row mt-5">
        @if($client)
            <div class="col-md-1 textBold">{{ t('client') }}</div>
        @endif
        <div class="col-md-1 textBold">{{ t('ui_number') }}</div>
        <div class="col-md-2 textBold">{{ t('transaction_type') }}</div>
        <div class="col-md-2 textBold">{{ t('substatus') }}</div>
        <div class="col-md-2 textBold">{{ t('ui_project') }}</div>
        <div class="col-md-4 textBold">{{ t('ticket_table_date') }}</div>

    </div>
    <form action="">
        <div class="row mt-3">
            @if($client)
                <div class="col-md-1 pt-2">
                    <input type="number" name="profile_id" value="{{ request()->profile_id }}">
                </div>
            @endif
            <div class="col-md-1 pt-2">
                <input type="number" name="number" value="{{ request()->number }}">
            </div>
            <div class="col-md-2">
                <select class="w-100" name="transaction_type" id="country">
                    <option value=""></option>
                    @foreach( isset($showProviderTypes) ? \App\Enums\OperationType::NAMES :  \App\Enums\OperationType::NAMES as $key => $name)
                        @if($key !== \App\Enums\OperationType::MERCHANT_PAYMENT)
                            <option value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
                <div class="col-md-2">
                    <select class="w-100" name="substatus" id="substatus">
                        <option value=""></option>
                        @foreach(\App\Enums\OperationSubStatuses::NAMES as $key => $name)
                            <option
                                value="{{ $key }}" {{ request()->substatus == $key ? 'selected' : '' }}>{{ t($name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="w-100 projectSelect" name="project" id="project" data-permission="{{ \App\Enums\BUserPermissions::VIEW_OPERATION }}">
                        <option value=""></option>
                        @foreach($projectNames as $key => $name)
                            <option
                                value="{{ $key }}" {{ request()->project == $key ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="error text-danger projectSelectError"></div>

                </div>
                <div class="col-md-4 pt-2">
                <input class="date-inputs display-sell" name="from" id="from" value="{{ request()->from }}" placeholder="From date" autocomplete="off">
                <input class="date-inputs display-sell" name="to" id="to" value="{{ request()->to }}" placeholder="To date" autocomplete="off">
            </div>
            <div class="col-md-2">
                <button class="btn btn-lg btn-primary themeBtn mb-4 btn-radiused" type="submit">Find</button>
            </div>
            @if(isset($showReport))
                <a href="javascript: void(0)" class="history-list-report" data-payment-form-id="{{ $paymentFormId ?? '' }}">
                    <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">{{  t('ui_wire_operation_report') }}</a>
                    <img src="{{ config('cratos.urls.theme') }}images/loader.gif" class="reportLoading">
              @endif
        </div>
    </form>
</div>

<div class="tab-content mt-4 complianceRatesFontSize">
    <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pendingLink">
        @include('backoffice.partials.transactions.transaction-table', [
            'heading' => t('transaction_status_pending'),
            'status' => \App\Enums\OperationStatuses::PENDING,
            'operations' => $operationsPending,
            'tab' => 'pendingLink'
        ])
    </div>
    <div class="tab-pane fade" id="successful" role="tabpanel" aria-labelledby="successfulLink">
        @include('backoffice.partials.transactions.transaction-table', [
            'heading' => t('transaction_status_successful'),
            'status' => \App\Enums\OperationStatuses::SUCCESSFUL,
            'operations' => $operationsSuccessful,
            'tab' => 'successfulLink'
        ])
    </div>
    <div class="tab-pane fade" id="declined" role="tabpanel" aria-labelledby="declinedLink">
        @include('backoffice.partials.transactions.transaction-table', [
            'heading' => t('transaction_status_declined'),
            'status' => \App\Enums\OperationStatuses::DECLINED,
            'operations' => $operationsDeclined,
            'tab' => 'declinedLink'
        ])
    </div>
    <div class="tab-pane fade" id="returned" role="tabpanel" aria-labelledby="returnedLink">
        @include('backoffice.partials.transactions.transaction-table', [
            'heading' => t('transaction_status_returned'),
            'status' => \App\Enums\OperationStatuses::RETURNED,
            'operations' => $operationsReturned,
            'tab' => 'returnedLink'
        ])
    </div>
</div>

<script>
    $(document).ready(function() {
        let url = window.location.href.split("#");
        if (url.length > 1) {
            let tab = url[1];
            if (['successfulLink', 'pendingLink', 'returnedLink', 'declinedLink'].includes(tab)) {
                $('#operationsTabBtn').click();
                let activeTab = $('#' + tab)
                activeTab.trigger('click');
                $('.transaction-history-tab').removeClass('tab-active').addClass('tab-inactive')
                activeTab.parent('li').removeClass('tab-inactive').addClass('tab-active')
            }
        }


        $('body').delegate('.tab-inactive', 'click', function () {
            window.location.href = window.location.pathname + '#' + $(this).children().first().attr('id')
        })
    })
</script>
