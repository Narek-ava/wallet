<div class="col-md-12 display-none details{{ $operation->id }}">
    <hr class="hr-style">
    <div class="col-md-12 mt-3 fs14">
        <div class="row">
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('comment') }}</span>
                <div class="operation-comment">
                    @if($operation->comment)
                        {{ $operation->comment }}
                    @else
                        <span>{{ t('top_up_wire_no_comment') }}</span>
                    @endif
                </div>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_type') }}</span><br>{{ $operation->getOperationType() ?? '-'}}<br><br>
                <span class="activeLink">{{ t('withdrawal_method') }}</span><br>{{ $operation->getOperationMethodName() }}<br><br>
                <span class="activeLink">{{ t('withdrawal_currency') }}</span><br>{{ $operation->from_currency ?? '-' }}<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_exchange_to') }}</span><br>{{ strtoupper($operation->to_currency)  ?? '-' }}<br><br>
                <span class="activeLink">{{ t('withdrawal_bank_country') }}</span><br>{{ $operation->toAccount->country ? \App\Models\Country::getCountryNameByCode($operation->toAccount->country) : '-' }}<br><br>
                <span class="activeLink">{{ t('withdrawal_amount_euro') }}</span><br> {{ eur_format($operation->amount_in_euro ?? null) }}<br><br>

            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_fee') }}</span><br>{{ $operation->withdrawalFee ?? 0 }}<br><br>
                <span class="activeLink">Report</span><br>@if($operation->status == \App\Enums\OperationStatuses::SUCCESSFUL)
                    <a href="{{ route('cabinet.download.transaction.report.pdf', ['operation' => $operation->id]) }}"><img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="20" class="pdf-icon pb-1">{{  t('withdrawal_report') }}</a>
                @else - @endif<br><br>
            </div>
            <div class="col-md-2 mt-4">
                <span class="activeLink">{{ t('withdrawal_amount_to_receive') }}</span><br>{{ $operation->credited }}<br><br>
            </div>
        </div>
    </div>
</div>
