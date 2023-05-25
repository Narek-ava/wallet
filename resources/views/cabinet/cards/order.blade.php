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

    <div class="row mb-5">
        <div class="col-md-12">
            <div class="row">
                <div class="col-10 m-2 pl-2 pt-1 pt-sm-0" style="max-width: 230px;">
                    <h4 class="d-inline-block mb-0">{{ t('new_card_order') }}</h4>
                </div>

                <div class="col-8 mt-1 pl-0 pl-sm-2 orderVirtualSteps orderSteps">
                    @include('cabinet.cards.order-steps.order-virtual-steps')
                </div>
                <div class="col-8 mt-1 pl-0 pl-sm-2 orderPlasticSteps orderSteps" hidden>
                    @include('cabinet.cards.order-steps.order-plastic-steps')
                </div>
            </div>

        <div class="row mt-5 pl-3">
            <div class="col-md-12 p-0 wallet-tablist">
                <h5 class="mb-3">{{ t('select_card_type') }}</h5>
                <br>
                <div class="text-left">
                    <a id="virtual" class="select-card-type btn text-dark ml-0 mb-0 active" data-amount="{{ $amountInEuroVirtual }}" data-order-card-url="{{ $orderVirtualUrl }}">
                        <span>{{ t('card_type_virtual') }}</span>
                    </a>
                    <a id="plastic" class="select-card-type btn text-dark ml-0 mb-0" data-amount="{{ $amountInEuroPlastic }}" data-order-card-url="{{ $orderPlasticUrl }}">
                        <span>{{ t('card_type_plastic') }}</span>
                    </a>
                </div>

                <div class="mt-5">
                    <button class="btn btn-primary themeBtn btnWhiteSpace mt-5" @if(!$amountInEuroVirtual) disabled @endif data-new-card-order-url="{{ route('show.order', ['type' => 'virtual']) }}" type="button" id="orderBtn" style="border-radius: 25px">Next</button>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="/js/cabinet/wallester-order-card.js"></script>
@endsection
