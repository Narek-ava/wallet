@extends('cabinet.layouts.cabinet')
@section('title', t('ui_cards'))

@section('content')
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('ui_card') }} *{{ substr($card->card_mask , -4) }}</h2>
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
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="row">
                        <div class="col-md-5 ">
                            <input type="hidden" value="{{ $card->id }}" name="id">
                            @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_VIRTUAL)
                                <button type="button" class="issue-plastic-card" data-url="{{ route('show.order', ['type' => 'plastic']) }}">{{ t('issue_plastic_card') }}</button>
                            @endif
                            @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && $card->status == \App\Enums\WallesterCardStatuses::STATUS_DISPATCHED)
                                <div class="wallester-activate-btn">
                                    <button data-toggle="modal" data-wallester-account-detail-id="{{ $card->id }}"
                                            type="button" data-target="#confirmDelivery"
                                            class="btn btn-sm btn-primary themeBtn themeBtnLight activateBtn"
                                            style="color: #000 !important;">Activate my card
                                    </button>
                                </div>
                            @endif

                            <div class="card-default p-0 credit-card"  >
                                <div class="d-flex justify-content-between align-items-center mr-3 ml-3 mt-4">
                                    <h4 style="margin: 10px 0px 0px 15px">{{ \App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[\App\Enums\Currency::CURRENCY_EUR] .' '. generalMoneyFormat($card->account->balance, \App\Enums\Currency::CURRENCY_EUR) }}</h4>
                                    <div class="card-logo d-flex align-content-end">
                                        <img src="{{ asset('/cratos.theme/images/connectee_card_logo.svg')}}" class="img-fluid" alt="">
                                    </div>
                                </div>
                                <div class="credit-card-number credit-card-number-details d-flex mr-3 ml-3 mt-6"
                                     style="justify-content: space-evenly"
                                     data-last-four-digits="{{ substr($card->card_mask , -4) }}">
                                </div>
                                <div class=" @if($card->status !== \App\Enums\WallesterCardStatuses::STATUS_BLOCKED) ordered-credit-card-data @else mt-4 @endif d-flex justify-content-between ordered-credit-card-padding">
                                    <div class="credit-card-date">**/**</div>
                                    <div class="credit-card-cvv">CVV</div>
                                    <form id="encryptDetails" method="POST" style="margin-top: 2px"
                                          action="{{ route('show.card.encrypted.details') }}">
                                        @csrf
                                        <button class="credit-card-icon" type="submit"
                                                @if($card->status === \App\Enums\WallesterCardStatuses::STATUS_BLOCKED) disabled
                                                @endif
                                                data-show="true"
                                                data-show-card-encrypted-details-url="{{ route('show.card.encrypted.details') }}">
                                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                        </button>

                                    </form>
                                    <div></div>
                                    <div class="credit-card-type"><img src="{{ asset('/cratos.theme/images/card-visa.png')}}" class="img-fluid" alt=""></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 ">
                            <div class="card-default credit-card-detail-info offset-1"  >
                                <div class="d-flex justify-content-between">
                                    <div class="mt-4 ml-4 d-flex flex-column">
                                        @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && !in_array($card->status, [\App\Enums\WallesterCardStatuses::STATUS_DISPATCHED, \App\Enums\WallesterCardStatuses::STATUS_CREATED]))
                                            <p class="activeLink m-0">{{ t('wallester_delivery') }}</p>
                                            <p>{{ t('in_order') }}</p>
                                        @else
                                            <p class="activeLink m-0">{{ t('wallester_card_type') }}</p>
                                            <p>{{ \App\Enums\WallesterCardTypes::getName($card->card_type) }}</p>
                                        @endif
                                    </div>
                                    <div class=" mt-4 d-flex flex-column">
                                        <p class="activeLink m-0">{{ t('status') }}</p>
                                        <p @if($card->status === \App\Enums\WallesterCardStatuses::STATUS_BLOCKED) class="text-danger" @endif>{{ \App\Enums\WallesterCardStatuses::getName($card->status) }}</p>
                                    </div>

                                    <div class=" mt-4 mr-4 d-flex flex-column">
                                        <p class="activeLink m-0">{{ t('creation_date') }}</p>
                                        <p>{{$card->created_at->timezone(auth()->user()->cProfile->timezone)->format('d.m.Y') }}</p>
                                    </div>
                                </div>
                                <div class="cardDetailBtnContainer">
                                    <button
                                        class="card-details-button"
                                        data-toggle="modal" data-target="#cardsDetails"
                                        type="button"> {{ t('card_details') }}
                                    </button>
                                    <button
                                        class="card-top-up-button"
{{--                                        style="    white-space: break-spaces;"--}}
                                        @if($card->status === \App\Enums\WallesterCardStatuses::STATUS_BLOCKED) disabled
                                        @endif
                                        data-toggle="modal" data-target="#cardTopUp"
                                        type="button">{{ t('card_top_up') }}
                                    </button>
                                    <button
                                        class="card-block-button"
                                        data-toggle="modal" data-target="#blockCard"
                                        @if(!in_array($card->status, [\App\Enums\WallesterCardStatuses::STATUS_ACTIVE, \App\Enums\WallesterCardStatuses::STATUS_BLOCKED])) disabled
                                        @endif
                                        type="button">
                                        @if($card->status !== \App\Enums\WallesterCardStatuses::STATUS_BLOCKED)
                                            {{ t('block') }}
                                        @else
                                            {{ t('unblock') }}
                                        @endif
                                    </button>

                                    <button
                                        class="card-limit-button"
                                        data-toggle="modal" data-target="#card-limits-wallester"
                                        type="button"> {{ t('ui_limits') }}
                                    </button>
                                    <button
                                        class="card-security-button"
                                        data-toggle="modal" data-target="#securityModal"
                                        type="button">{{ t('security') }}
                                    </button>
                                    <button
                                        class="card-cvv-button"
                                        data-toggle="modal" data-target="#remindPinOrCVV"
                                        type="button">{{ $card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC ? t('pin') : t('cvv2') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @include('cabinet._modals.2fa-operation-confirm')
            </div>
        </div>
    </div>

    @include('cabinet.cards._limits')
    @include('cabinet.cards._block-card')
    @include('cabinet.cards._remind-cvv')
    @include('cabinet.cards._security')
    @include('cabinet.cards._detail')
    @include('cabinet.cards._top-up')
    @include('cabinet.cards._payment-method')
    @include('cabinet.cards._plastic-confirm-delivery')


    <div class="row">
        <div class="card-transaction-history mt-5 p-2">{{ t('card_transaction_history') }}</div>
    </div>
    <div class="row">
        <div class="card-transaction-filter mt-4 p-2">{{ t('ui_filters') }}</div>
    </div>
    <div class="col-md-12 p-0 mt-3">
        <form>
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="form-group">
                        <label
                            class="font-weight-bold mb-0">{{ t('transaction_history_table_heading_date_time') }}</label>
                        <input class="date-inputs display-sell w-100" name="from_date" id="from_date"
                               value="{{ request()->from_date }}" placeholder="From date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input class="date-inputs display-sell w-100" name="to_date" id="to_date"
                               value="{{ request()->to_date }}" placeholder="To date">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="type" class="font-weight-bold">{{ t('transaction_type') }}</label>
                        <select class="w-100" name="type" id="type">
                            <option value="">All</option>
                            @foreach(\App\Enums\WallesterTansactionTypes::NAMES as $key => $name)
                                <option
                                    value="{{ $key }}" {{ request()->type == $key ? 'selected' : '' }}>
                                    {{ \App\Enums\WallesterTansactionTypes::getName($key) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 ">
                    <div class="form-group">
                        <label for="merchant_name"
                               class="font-weight-bold m-0">{{ t('transaction_history_merchant_name') }}</label>
                        <input class="date-inputs display-sell w-100" name="merchant_name" id="merchant_name"
                               value="{{ request()->merchant_name }}">
                    </div>
                </div>

                <div class="col-md-2 ">
                    <div class="form-group">
                        <button class="btn btn-lg btn-primary themeBtn btn-radiused"
                                type="submit">{{ t('ui_search') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <br><br>
    <div class="wallet-transactions p-2">
        <div class="row d-none d-md-flex">
            <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
            <div class="col-md-2 textBold">{{ t('transaction_history_merchant_name') }}</div>
            <div class="col-md-2 textBold">{{ t('transaction_history_amount') }}</div>
            <div class="col-md-2 textBold">{{ t('transaction_currency') }}</div>
            <div class="col-md-1 textBold">{{ t('transaction_account_amount') }}</div>
            <div class="col-md-1 textBold">{{ t('transaction_account_currency') }}</div>
            <div class="col-md-2 textBold">{{ t('type') }}</div>
        </div>
        @if($cardTransactions['transactions']->isNotEmpty())
                @foreach($cardTransactions['transactions'] as $transaction)
                <div class="row backofficeTransactionHistoryItem">
                    <div class="col-md-2 history-element-item"
                         data-label-sm="{{ t('transaction_history_table_heading_date_time') }}">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('Y-m-d, H:i:s') }}</div>
                    <div class="col-md-2 history-element-item"
                         data-label-sm="{{  t('transaction_history_merchant_name') }}">{{ $transaction['merchant_name'] }}</div>
                    <div class="col-md-2 history-element-item"
                         data-label-sm="{{ t('transaction_history_amount') }}">{{ $transaction['transaction_amount'] }}</div>
                    <div class="col-md-2 history-element-item"
                         data-label-sm="{{ t('transaction_currency') }}">{{ $transaction['transaction_currency_code'] }}</div>
                    <div class="col-md-1 history-element-item"
                         data-label-sm="{{ t('transaction_account_amount') }}">{{ $transaction['account_amount'] }}</div>
                    <div class="col-md-1 history-element-item"
                         data-label-sm="{{ t('transaction_account_currency') }}">{{ $transaction['account_currency_code'] }}</div>
                    <div class="col-md-2 history-element-item"
                         data-label-sm="{{  t('type') }}">{{ $transaction['group'] }}</div>
            </div>
                @endforeach
                {!! $cardTransactions['pagination'] !!}
        @endif

    </div>
@endsection

@section('scripts')
    <script src="/js/cabinet/2fa-wallester.js"></script>
    <script>
        let ask2fa = new AskTwoFAWallester();
        ask2fa.attachToFormSubmit('#encryptDetails',{{\C\c_user()->two_fa_type}});
    </script>
    <script src="/js/cabinet/wallester-order-card.js"></script>
    <script>
        @if($errors->any())
        @if($errors->has('show_limits_modal'))
        $('#card-limits-wallester').modal('show')
        @elseif($errors->has('show_security_modal'))
        $('#securityModal').modal('show')
        @endif
        @endif

        $('.issue-plastic-card').click(function (){
            window.location = $(this).data('url')
        })
    </script>

@endsection
