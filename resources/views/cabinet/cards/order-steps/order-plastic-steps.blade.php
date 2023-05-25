<div class="d-flex row align-items-start">
    @foreach ($steps['plastic'] as $step => $class)
        <div class="d-flex align-items-start">
            <div class="wallesterOrderStepBlock {{ $class }}">
                <div class="textBold activeLevel"></div>
                <h5>{{ t('order_plastic_card_' . $step) }}</h5>
            </div>
            @if(!$loop->last)
                <div class="dashedBlock"></div>
            @endif</div>
    @endforeach
</div>
