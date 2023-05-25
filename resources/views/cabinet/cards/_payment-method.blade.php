<!-- Modal -->
<div class="modal fade" id="paymentMethod" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">

        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <div class="d-flex flex-column">
                    <h5 class="modal-title">{{ t('choose_payment_method') }}</h5>
                    <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <span>{{ t('wallester_payment_description') }}</span>
                </div>
            </div>
            <form action="{{ route('confirm.order.plastic.card') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="{{ $cardType ?? null }}">
                <input type="hidden" name="id" value="{{ $wallesterAccountDetailId ?? null }}">
                <div class="modal-body">
                    <select name="paymentMethod" class="mr-4" id="paymentMethodForCardOrder"
                            style="padding-right: 50px;">
                        @foreach(\App\Enums\WallesterCardOrderPaymentMethods::getList() as $value => $name)
                            @if($value == \App\Enums\WallesterCardOrderPaymentMethods::BANK_CARD) @continue @endif
                            <option value="{{ $value }}" selected="">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-lg btn-primary themeBtn">{{ t('checkout') }}</button>
                </div>
            </form>
        </div>

    </div>
</div>
