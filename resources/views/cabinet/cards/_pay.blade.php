@extends('cabinet.layouts.cabinet')
@section('title', t('ui_cards'))

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('total_balance') }}</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('ui_webatach_request') }}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('cabinet.cards.session_messages')

    <div class="d-flex flex-column justify-content-start mt-5">
        <h2 class="d-inline-block mb-0">{{ t('wallester_card_payment') }}</h2>
        <p class="mt-1">{{ t('wallester_card_payment_description') }}</p>
    </div>

    @if($paymentMethod == \App\Enums\WallesterCardOrderPaymentMethods::CRYPTOCURRENCY)
        @include('cabinet.cards.payments.crypto-payment')
    @elseif($paymentMethod == \App\Enums\WallesterCardOrderPaymentMethods::SEPA)
        @include('cabinet.cards.payments.wire_payment')
    @endif
@endsection
@section('scripts')
    <script src="/js/cabinet/wallester-order-card.js"></script>
@endsection
