<div class="row pt-4 pl-3 wallet-instruction-items pr-3 pr-md-0 mt-3">
    <div class="col-md pl-0 pr-0">
        <div class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_0'] }}">
            <div class="col-12">
                <div class="textBold activeLevel"></div>
                <div class="textBold inactiveLevel"></div>
                <h2 class="mb-3">{{ t('ui_step_one_withdraw_wire') }}</h2>
            </div>
            <div class="col-12">
                <p>{{ t('ui_withdraw_wire_trx_step_one') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
        <div class="dashedBlock"></div>
    </div>
    <div class="col-md pl-0 pr-0">
        <div class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_1'] }}">
            <div class="col-12">
                <div class="textBold activeLevel"></div>
                <div class="textBold inactiveLevel"></div>
                <h2 class="mb-3">{{ t('ui_step_two_withdraw_wire') }}</h2>
            </div>
            <div class="col-12">
                <p>{{ t('ui_withdraw_wire_trx_step_two') }}</p>
            </div>
        </div>
    </div>

    @if(!isset($isCardOrderOperation) || !$isCardOrderOperation)
    <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
        <div class="dashedBlock"></div>
    </div>
    <div class="col-md pl-0 pr-0 pr-md-3">
        <div class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_2'] }}">
            <div class="col-12">
                <div class="textBold activeLevel"></div>
                <div class="textBold inactiveLevel"></div>
                <h2 class="mb-3">{{ t('ui_step_three_withdraw_wire') }}</h2>
            </div>
            <div class="col-12">
                <p>{{ t('ui_withdraw_wire_trx_step_three') }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-1 p-1 mx-auto mt-0 mb-3" style="max-width: 2%">
        <div class="dashedBlock"></div>
    </div>
    <div class="col-md pl-0 pr-0 pr-md-3">
        <div class="common-shadow-theme wallet-instruction-item row h-100 m-0 p-3 pt-4 pb-4 {{ $steps['stepState_3'] }}">
            <div class="col-12">
                <div class="textBold activeLevel"></div>
                <div class="textBold inactiveLevel"></div>
                <h2 class="mb-3">{{ t('ui_step_four_withdraw_wire') }}</h2>
            </div>
            <div class="col-12">
                <p>{{ t('ui_withdraw_wire_trx_step_four') }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
