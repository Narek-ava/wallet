<div class="row d-flex align-items-start">
    @foreach ($steps['virtual'] as $step => $class)
        <div class="d-flex align-items-start">
            <div class="wallesterOrderStepBlock {{ $class }}">
                <div class="textBold activeLevel"></div>
                <h5>{{ t('order_virtual_card_' . $step) }}</h5>
            </div>
            @if(!$loop->last)
                <div class="dashedBlock"></div>
            @endif</div>
    @endforeach
</div>
