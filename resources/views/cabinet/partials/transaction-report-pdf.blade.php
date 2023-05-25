@extends('cabinet.layouts.deposit-pdf')

@section('content')
    <div style="margin-top: 50px;">
        <h1>{{ t('transaction_report') }}</h1>
    </div>
    <div style="clear: both;height:30px;"></div>
    <div>
        <div style="width:300px;float: left">{{ t('pdf_client_type') }}</div>
        <div style="width:300px;float: left">{{ t(\App\Models\Cabinet\CProfile::TYPES_LIST[$operation->CProfile->account_type]) }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_client_name') }}</div>
        <div style="width:300px;float: left">
            @if($operation->CProfile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                {{ $operation->CProfile->company_name }}
            @else
                {{ $operation->CProfile->first_name . ' ' . $operation->CProfile->last_name . ' - ID ' . $operation->CProfile->profile_id }}
            @endif
        </div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_transaction_id') }}</div>
        <div style="width:300px;float: left">{{ $operation->operation_id }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_date') }}</div>
        <div style="width:300px;float: left">{{ $operation->created_at->timezone($operation->cProfile->timezone) }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_transaction_type') }}</div>
        <div style="width:300px;float: left">{{ \App\Enums\OperationOperationType::getName($operation->operation_type) }}</div>
    </div>
    <div style="clear: both;height:30px;"></div>
    <div style="clear: both">
        <div style="width:300px;float: left">{{ t('pdf_from_currency') }}</div>
        <div style="width:300px;float: left">{{ $operation->from_currency }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_amount') }}</div>
        <div style="width:300px;float: left">{{ $operation->amount }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_to_currency') }}</div>
        <div style="width:300px;float: left">{{ $operation->to_currency }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_to_amount') }}</div>
        <div style="width:300px;float: left">{{ $operation->credited }}</div>
    </div>
    <div style="clear: both;">
        <div style="width:300px;float: left">{{ t('pdf_exchange_rate') }}</div>
        <div style="width:300px;float: left">{{ $operation->getExchangeTransaction()->exchange_rate ?? '-' }}</div>
    </div>
    <div style="clear: both;height:30px;"></div>
    <div style="clear: both">
        <div style="width:300px;float: left;font-weight: bold;">{{ t('pdf_status') }}</div>
        <div style="width:300px;float: left;font-weight: bold;">{{ \App\Enums\OperationStatuses::NAMES[$operation->status] }}</div>
    </div>

    <div style="clear: both;height:50px;"></div>
    <div style="clear:both;">
        {!! (new \App\Services\SettingService)->getProjectAddress($operation->cProfile->cUser->project_id ?? null) ?? '' !!}
    </div>
@endsection
