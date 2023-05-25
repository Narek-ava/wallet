@if(count($cryptoAccountDetail->operations()))
    <div class="wallet-transactions" data-wallet-id="{{ $cryptoAccountDetail->id }}">
        <div class="container pl-0 ml-0">
            <div class="row">
                <div class="col-md-3">
                    <h1>{{ t('operations') }}</h1>
                </div>
                <div class="col-md-9">
                    <ul class="nav" role="tablist">
                        <li class="nav-item transaction-history-tab {{ request()->hash ? request()->hash == 'pendingLinkTo' ? 'tab-active' : '' : 'tab-active'}}">
                            <a class="tab-link-color-black nav-link textBold" id="pendingLinkTo" data-toggle="tab"
                               href="#pendingTo" role="tab" aria-controls="home" aria-selected="true">{{ t('Pending') }}</a>
                        </li>
                        <li class="nav-item transaction-history-tab {{ request()->hash == 'successfulLinkTo' ? 'tab-active' : 'tab-inactive'}}">
                            <a class="tab-link-color-black nav-link textBold" id="successfulLinkTo" data-toggle="tab"
                               href="#successfulTo" role="tab" aria-controls="home" aria-selected="true">{{ t('Successful') }}</a>
                        </li>
                        <li class="nav-item transaction-history-tab {{ request()->hash == 'declinedLinkTo' ? 'tab-active' : 'tab-inactive'}}">
                            <a class="tab-link-color-black nav-link textBold" id="declinedLinkTo" data-toggle="tab"
                               href="#declinedTo" role="tab" aria-controls="home" aria-selected="true">{{ t('Declined') }}</a>
                        </li>
                        <li class="nav-item transaction-history-tab {{ request()->hash == 'returnedLinkTo' ? 'tab-active' : 'tab-inactive'}}">
                            <a class="tab-link-color-black nav-link textBold" id="returnedLinkTo" data-toggle="tab"
                               href="#returnedTo" role="tab" aria-controls="home" aria-selected="true">{{ t('Returned') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row mt-5">
                @if($client)
                    <div class="col-md-2 textBold">{{ t('client') }}</div>
                @endif
                <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_number') }}</div>
                <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_type') }}</div>
                <div class="col-md-4 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
                <div class="col-md-2 textBold"></div>
            </div>
            <form action="" class="search-form" name="transactionTable">
                <input type="hidden" name="hash" id="hash" value="{{ request()->hash ? request()->hash : 'pendingLinkTo' }}">
                <div class="row mt-3">
                    @if($client)
                        <div class="col-md-2">
                            <input type="number" name="profile_id" value="{{ request()->profile_id }}">
                        </div>
                    @endif
                    <div class="col-md-2">
                        <input type="text" hidden class="wallet-hidden-id">
                        <input type="number" name="number" value="{{ request()->number }}">
                    </div>
                    <div class="col-md-2">
                        <select class="w-100" name="transaction_type" id="">
                            @foreach(\App\Enums\OperationType::NAMES as $key => $name)
                                @continue(!config('cratos.enable_fiat_wallets') && in_array($key, \App\Enums\OperationType::FIAT_PAYMENT_TYPES))
                                <option value="{{ $key }}" {{ request()->transaction_type == $key ? 'selected' : '' }}>
                                    {{ t($name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input class="date-inputs display-sell" name="from" id="from" value="{{ request()->from }}"
                               placeholder="From date">
                        <input class="date-inputs display-sell" name="to" id="to" value="{{ request()->to }}"
                               placeholder="To date">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-lg btn-primary themeBtn mb-4 btn-radiused" type="submit">{{ t('find') }}</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="tab-content mt-4 complianceRatesFontSize">
            <div class="tab-pane fade {{ request()->hash ? request()->hash == 'pendingLinkTo' ? 'show active' : '' : 'show active'}}" id="pendingTo" role="tabpanel" aria-labelledby="pendingLinkTo">
                @include('backoffice.partials.transactions.account-transaction', ['heading' => t('transaction_status_pending'), 'status' => \App\Enums\OperationStatuses::PENDING,
                'filteredOperations' => $filteredOperationsPending, 'operations' => $operations])
            </div>
            <div class="tab-pane fade {{ request()->hash == 'successfulLinkTo' ? 'show active' : ''}}" id="successfulTo" role="tabpanel" aria-labelledby="successfulLinkTo">
                @include('backoffice.partials.transactions.account-transaction', ['heading' => t('transaction_status_successful'), 'status' => \App\Enums\OperationStatuses::SUCCESSFUL,
                'filteredOperations' => $filteredOperationsSuccessful, 'operations' => $operations])
            </div>
            <div class="tab-pane fade {{ request()->hash == 'declinedLinkTo' ? 'show active' : ''}}" id="declinedTo" role="tabpanel" aria-labelledby="declinedLinkTo">
                @include('backoffice.partials.transactions.account-transaction', ['heading' => t('transaction_status_declined'), 'status' => \App\Enums\OperationStatuses::DECLINED,
                'filteredOperations' => $filteredOperationsDeclined, 'operations' => $operations])
            </div>
            <div class="tab-pane fade {{ request()->hash == 'returnedLinkTo' ? 'show active' : ''}}" id="returnedTo" role="tabpanel" aria-labelledby="returnedLinkTo">
                @include('backoffice.partials.transactions.account-transaction', ['heading' => t('transaction_status_returned'), 'status' => \App\Enums\OperationStatuses::RETURNED,
                'filteredOperations' => $filteredOperationsReturned, 'operations' => $operations])
            </div>
        </div>
    </div>
@endif
