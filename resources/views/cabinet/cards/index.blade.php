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

    @include('cabinet.cards._success')
    <input hidden id="newCardOperationSuccess" value="{{ session()->get('newCardOperationSuccess')}}"/>


    <div class="d-flex flex-column mt-5 mb-5">
        <div class="col-md-12">
            <div class="d-flex flex-row" style="line-height: 20px">
                <div style="line-height: inherit">
                    <h3 class="d-inline-block mb-0">{{ t('ui_cards') }}</h3>
                </div>

                @if($cards->isEmpty())
                    <div class="ml-3" style="line-height: inherit">
                        <button type="button" class="btn btn-sm btn-dark badge-pill" data-toggle="modal"
                                data-target="#cardsConditions"
                                style="width: 180px;border-radius: 20px">{{ t('cards_conditions') }}
                        </button>
                        @include('cabinet.cards._cards-conditions')
                    </div>
                @else
                    <div class="ml-3" style="line-height: inherit">
                        <a type="button" class="btn btn-sm btn-dark badge-pill"
                           href="{{ route('wallester-cards.create') }}">
                            {{ t('order_new_card') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @include('cabinet.cards.session_messages')

        @if($cards->isEmpty())
            <div class="mt-5 mb-4 pr-0">
                <div class="card-default p-0 credit-card">
                    <div class="d-flex justify-content-between align-items-center mr-2 ml-4 mt-4">
                        <a class="credit-card-button text-decoration-none" style="color: #1c09d7;" href="{{ route('wallester-cards.create') }}">
                            {{ t('order_new_card') }}
                        </a>
                        <div class="card-logo d-flex align-content-end">
                            <img src="{{ asset('/cratos.theme/images/connectee_card_logo.svg') }}" class="img-fluid" alt="">
                        </div>
                    </div>
                    <div class="credit-card-no-number d-flex justify-content-between pl-4 pr-2">
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number-hide-section">****</div>
                    </div>
                    <div style="vertical-align: bottom!important;" class="ordered-credit-card-no-data ordered-credit-card-no-card-padding d-flex justify-content-between center">
                        <div style="vertical-align: bottom!important;" class="credit-card-cardholder-name">{{ t('cardholder') }}</div>
                        <div class="credit-card-date">**/**</div>
                        <div class="credit-card-cvv">CVV</div>
                        <div class="credit-card-type"><img src="{{ asset('/cratos.theme/images/card-visa.png')}}" class="img-fluid" alt=""></div>
                    </div>
                </div>
            </div>
        @else
            <br><br>
            <div class="row justify-content-start wallesterShowCards" style="flex-wrap: wrap">
                @foreach($cards as $card)
                    <div class="m-3">
                        @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && $card->status == \App\Enums\WallesterCardStatuses::STATUS_DISPATCHED)
                            <div class="wallester-activate-btn">
                                <button data-toggle="modal" data-wallester-account-detail-id="{{ $card->id }}"
                                        type="button" data-target="#confirmDelivery"
                                        class="btn btn-sm btn-primary themeBtn themeBtnLight activateBtn"
                                        style="color: #000 !important;">Activate my card
                                </button>
                            </div>
                        @endif

                        <div class="card-default p-0 credit-card">
                            <div class="d-flex justify-content-between align-items-center mr-2 ml-4 mt-4">
                                <h4 class="credit-card-balance">{{ \App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[\App\Enums\Currency::CURRENCY_EUR] . ' '. $card->account->balance }}</h4>
                                <div class="card-logo d-flex align-content-end">
                                    <img src="{{ asset('/cratos.theme/images/connectee_card_logo.svg') }}" class="img-fluid" alt="">
                                </div>
                            </div>
                            <div class="credit-card-number d-flex justify-content-between pl-4 pr-2">
                                <div class="credit-card-number-hide-section">****</div>
                                <div class="credit-card-number-hide-section">****</div>
                                <div class="credit-card-number-hide-section">****</div>
                                <div class="credit-card-number{{ $card->card_mask ? '' : '-hide' }}-section">{{ $card->card_mask ? substr($card->card_mask , -4) : '****' }}</div>
                            </div>
                            <div style="vertical-align: bottom!important;" class="ordered-credit-card-data ordered-credit-card-padding d-flex justify-content-between center">
                                <div style="vertical-align: bottom!important;" class="credit-card-cardholder-name">{{ $card->name }}</div>
                                <div class="credit-card-date">**/**</div>
                                <div class="credit-card-cvv">CVV</div>
                                <div class="credit-card-type"><img src="{{ asset('/cratos.theme/images/card-visa.png')}}" class="img-fluid" alt=""></div>
                            </div>
                        </div>
                        <div class="d-flex mt-3 justify-content-around flex-row card-data-details">
                            <div>
                                <p class="activeLink">{{ t('status') }}</p>
                                @if(!$card->is_paid)
                                    @if(!$card->hasPendingOperationWithTransactions())
                                        <p>Waiting payment</p>
                                    @else
                                        <p>In process</p>
                                    @endif
                                @else
                                    <p>{{ \App\Enums\WallesterCardStatuses::getName($card->status) }}</p>
                                @endif
                            </div>
                            <div>
                                @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && !in_array($card->card_type, [\App\Enums\WallesterCardStatuses::STATUS_DISPATCHED, \App\Enums\WallesterCardStatuses::STATUS_CREATED]))
                                    <p class="activeLink">{{ t('wallester_delivery') }}</p>
                                    <p>{{ t('in_order') }}</p>
                                @else
                                    <p class="activeLink">{{ t('wallester_card_type') }}</p>
                                    <p>{{ \App\Enums\WallesterCardTypes::getName($card->card_type) }}</p>
                                @endif
                            </div>
                            <div>
                                <p class="activeLink">{{ t('creation_date') }}</p>
                                <p>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $card->created_at)->format('d.m.Y') }}</p>
                            </div>
                            <div>
                                @if(!$card->is_paid && !$card->hasPendingOperationWithTransactions())
                                    <button class="btn btn-sm btn-primary themeBtn themeBtnLight choosePaymentMethod" data-toggle="modal"
                                            data-card-type="{{ $card->card_type }}" data-wallester-account-detail-id="{{ $card->id }}"
                                            type="button" data-target="#paymentMethod">Pay</button>
                                @else
                                <button class="btn btn-sm btn-primary themeBtn themeBtnLight wallester-details-btn"
                                        @if(!$card->is_paid) disabled @endif
                                        data-wallester-account-detail-id="{{ $card->id }}"
                                        data-details-url="{{ route('wallester.card.details') }}"
                                        type="button">Details
                                </button>
                                @endif

                            </div>
                        </div>

                    </div>

                @endforeach
            </div>
    </div>
    @include('cabinet.cards._payment-method')

    @include('cabinet.cards._plastic-confirm-delivery')

    @endif


@endsection

@section('scripts')
    <script src="/js/cabinet/wallester-order-card.js"></script>

    <script>
        let successMessage = $('#newCardOperationSuccess').val();
        if (successMessage) {
            $('#successText').text(successMessage)
            $('#success').modal('show');
            @if( session()->get('newOperationId') )
                window.location = '{{ route('client.download.pdf.operation', ['operationId' => session()->get('newOperationId') ]) }}';
            @endif
        } else {
            $('#success').modal('hide');
        }
    </script>
@endsection
