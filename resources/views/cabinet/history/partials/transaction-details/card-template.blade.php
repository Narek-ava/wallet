<div class="col-md-12 mt-4 history-element">
    <div class="row">
        <div class="col-md-2 history-element-item activeLink" data-label-sm="NUMBER">{{ $operation->operation_id }}</div>
        <div class="col-md-2 history-element-item activeLink" data-label-sm="DATE & TIME">{{ $operation->created_at->timezone($profile->timezone) }}</div>
        <div class="col-md-2 history-element-item activeLink" data-label-sm="AMOUNT">
            {{generalMoneyFormat($operation->amount,$operation->from_currency)}}
        </div>
        <div class="col-md-2 history-element-item activeLink" data-label-sm="TYPE">
            {{ $operation->getOperationType() }}
        </div>
        <div class="col-md-2 history-element-item activeLink" data-label-sm="STATUS">{{ \App\Enums\OperationStatuses::NAMES[$operation->status] ?? null }}</div>
        <div class="col-md-2 history-element-item activeLink" data-label-sm="DETAILS">
            <a class="details link-default text-left" href="" data-operation-id="{{ $operation->id }}">See Details <i class="fa fa-angle-down" aria-hidden="true"></i></a>
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
</div>
