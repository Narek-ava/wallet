@extends('backoffice.layouts.backoffice',['showClients' => $profile->account_type, 'profileId' => $profile->profile_id])
@section('title', t('title_client_page') . $profile->profile_id)

@section('content')
    <div class="container-fluid p-0 ml-0 balance-outer crm-users-outer">
        <div class="row mb-3 pb-2">
            <div class="col-md-12">
                <h2 class="mb-3 large-heading-section">
                    {{ t('backoffice_profile_page_header_title', ['profileId' => $profile->profile_id]) }}</h2>
                <div class="row">
                    <div class="col-md-4 d-flex justify-content-between">
                        <div class="balance mb-4">
                            {{ t('backoffice_profile_page_header_body') }}
                        </div>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-md-12">
                @include('backoffice.partials.session-message')
                <div class="col-md-12 mb-12 pr-0">
                    <a href="{{ route('backoffice.profile', $profile->id) }}" style="font-size: 22px; color: black">Client {{ $profile->profile_id }}
                        /</a>
                    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                        <a href="{{ route('backoffice.profile', $profile->id . '#cards') }}"
                           style="font-size: 22px; color: black">{{ t('ui_bo_c_profile_page_bank_card') }} </a>
                    @endif
                    <br><br>


                </div>
                <div class="row mt-5 mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="row">
                                <div class="col-md-12 d-flex justify-content-start">
                                    <div class="col-md-6 mb-4 pr-0 d-flex flex-column mr-3">
                                        <input type="hidden" value="{{ $card->id }}" name="id">
                                        @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && $card->card_type == \App\Enums\WallesterCardStatuses::STATUS_DISPATCHED)
                                            <div class="wallester-activate-btn">
                                                <button data-toggle="modal" data-wallester-account-detail-id="{{ $card->id }}"
                                                        type="button" data-target="#confirmDelivery"
                                                        class="btn btn-sm btn-primary themeBtn themeBtnLight activateBtn"
                                                        style="color: #000 !important;">Activate my card
                                                </button>
                                            </div>
                                        @endif

                                        <div class="card-default p-0 credit-card">
                                            <div class="d-flex justify-content-between align-items-center mr-3 ml-3 mt-4">
                                                <h4 style="margin-top: 10px">{{\App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[\App\Enums\Currency::CURRENCY_EUR] . ' ' . generalMoneyFormat($card->account->balance, \App\Enums\Currency::CURRENCY_EUR ) }}</h4>                                                <div class="card-logo d-flex align-content-end">
                                                    <img src="{{ asset('/cratos.theme/images/connectee_card_logo.svg')}}" class="img-fluid" alt="">
                                                </div>
                                            </div>
                                            <div class="credit-card-number credit-card-number-details d-flex mr-3 ml-3 mt-6" style="justify-content: space-evenly" data-last-four-digits="{{ substr($card->card_mask , -4) }}">
                                            </div>
                                            <div class="ordered-credit-card-data ordered-credit-card-padding d-flex justify-content-between ">
                                                <div class="credit-card-date">**/**</div>
                                                <div class="credit-card-cvv">CVV</div>
                                                <form id="encryptDetails" method="POST" action="{{ route('show.card.encrypted.details') }}" style="margin-top: 2px">
                                                    @csrf
                                                    <button class="credit-card-icon" type="submit"
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
                                    <div class="col-md-6 mb-4 pr-0 d-flex flex-column mr-3">

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
                                                    <p>{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $card->created_at)->format('d.m.Y') }}</p>
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

                                </div>
                                @includeWhen( $cUser->two_fa_type, 'cabinet._modals.2fa-operation-confirm')
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wallet-transactions p-5">
                    <div class="container pl-0 ml-0">
                        <div class="row">
                            <div class="col-md-12">
                                <h1>{{ t('card_transaction_history') }}</h1>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-4 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
                            <div class="col-md-2 textBold">{{ t('pdf_transaction_type') }}</div>
                            <div class="col-md-2 textBold">{{ t('transaction_history_merchant_name') }}</div>
                            <div class="col-md-2 textBold"></div>
                        </div>
                        <form action="" class="search-form" name="transactionTable">
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <input class="date-inputs display-sell" name="from_date" id="from"
                                           required
                                           value="{{ request()->from_date }}"
                                           placeholder="From date">
                                    <input class="date-inputs display-sell" name="to_date" id="to"
                                           required
                                           value="{{ request()->to_date }}"
                                           placeholder="To date">
                                </div>
                                <div class="col-md-2">
                                    <select class="w-100" name="type" id="">
                                        <option value="">All</option>
                                    @foreach(\App\Enums\WallesterTansactionTypes::NAMES as $key => $name)
                                            <option
                                                value="{{ $key }}" {{ request()->type == $key ? 'selected' : '' }}>
                                                {{ \App\Enums\WallesterTansactionTypes::getName($key) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="merchant_name" value="{{ request()->merchant_name }}">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-lg btn-primary themeBtn mb-4 btn-radiused"
                                            type="submit">{{ t('ui_search') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <div class="col-md-2 textBold">{{ t('transaction_history_table_heading_date_time') }}</div>
                        <div class="col-md-2 textBold">{{ t('transaction_history_merchant_name') }}</div>
                        <div class="col-md-2 textBold">{{ t('transaction_history_amount') }}</div>
                        <div class="col-md-2 textBold">{{ t('transaction_currency') }}</div>
                        <div class="col-md-1 textBold">{{ t('transaction_account_amount') }}</div>
                        <div class="col-md-1 textBold">{{ t('transaction_account_currency') }}</div>
                        <div class="col-md-2 textBold">{{ t('type') }}</div>
                        <div class="col-md-12">
                            @if(!empty($cardTransactions['transactions']))
                                @foreach($cardTransactions['transactions'] as $transaction)
                                    <div class="row backofficeTransactionHistoryItem">
                                        <div
                                            class="col-md-2">{{ \Carbon\Carbon::parse($transaction['created_at'])->format('Y-m-d, H:i:s') }}</div>
                                        <div class="col-md-2">{{ $transaction['merchant_name'] }}</div>
                                        <div class="col-md-2">{{ $transaction['transaction_amount'] }}</div>
                                        <div class="col-md-2">{{ $transaction['transaction_currency_code'] }}</div>
                                        <div class="col-md-1">{{ $transaction['account_amount'] }}</div>
                                        <div class="col-md-1">{{ $transaction['account_currency_code'] }}</div>
                                        <div class="col-md-2">{{ $transaction['group'] }}</div>
                                    </div>
                                @endforeach
                                {!! $cardTransactions['pagination'] !!}
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('cabinet._modals.2fa-operation-confirm')
    @include('cabinet.cards._limits')
    @include('cabinet.cards._block-card')
    @include('cabinet.cards._remind-cvv')
    @include('cabinet.cards._security')
    @include('cabinet.cards._detail')
    @include('cabinet.cards._top-up')

@endsection

@section('scripts')
    <script>
        //copy text of input by clicking the icon
        function copyText(btn) {
            var id = btn.id;
            var copyText = document.getElementById('text' + id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            setBtnSuccessfullyCopied(btn);
            copyText.blur();
        }

        function setBtnSuccessfullyCopied(btnEl) {
            $(btnEl).addClass("btn-successfully-copied");

            var $icon = $(btnEl).find("i.fa");
            $icon.removeClass("fa-copy").addClass("fa-check");

            setTimeout(function() {
                $(btnEl).removeClass("btn-successfully-copied");
                $icon.removeClass("fa-check").addClass("fa-copy");
            }, 2000);
        }

        let isBO = true;
        var API = '';
    </script>
    <script src="/js/cabinet/2fa-wallester.js"></script>
    <script>
        let ask2fa = new AskTwoFAWallester();
        ask2fa.attachToFormSubmit('#encryptDetails');
    </script>
    <script src="/js/cabinet/wallester-order-card.js"></script>
@endsection
