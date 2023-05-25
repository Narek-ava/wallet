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

    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="d-flex flex-row justify-content-start">
                <div class="m-2 pl-2 pt-1 pt-sm-0 cursor-pointer" id="prevPageBtnDelivery" data-save-delivery-form-data-url="{{ route('wallester.card.save.delivery.data') }}" data-prev-page-url="{{ $prevPageUrl }}">
                    <a style="color: black">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 300px;">
                    <h4 class="d-inline-block mb-0">{{ t('card_delivery_address') }}</h4>
                </div>
                <div class="col mt-1 pl-0 pl-sm-2 orderPlasticSteps orderSteps">
                    @include('cabinet.cards.order-steps.order-plastic-steps')
                </div>
            </div>
        </div>
    </div>

    <div class="row d-flex flex-row">
        <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 300px;">
            <h4 class="d-inline-block mb-0">{{ t('card_delivery_address') }}</h4>
            <p class="mt-1">{{ t('delivery_address_condition') }}</p>
        </div>
        <div class="col mt-2 pl-0 pt-1 pl-sm-2">
            <button id="changeDefaultDeliveryAddress" type="button" class="btn btn-sm themeBtn badge-pill"  style="min-width: 200px">{{ t('change_address') }}
            </button>
        </div>
    </div>
    <div class="row d-flex">
        <form method="post" action="{{ route('confirm.order.plastic.card.delivery') }}" id="wallesterCardOrderDelivery" class="p-3">
            @csrf
            <input type="hidden" name="type" value="{{ \App\Enums\WallesterCardTypes::TYPE_PLASTIC }}">
            <div class="col-lg-12 m-2 pl-2 pt-1 pt-sm-0 d-flex">
                <div class="row d-flex justify-content-between w-100">
                    <div class="col-6 p-0 d-flex">
                        <div>
                            <p class="activeLink">
                                {{ t('wallester_card_order_first_name') }}
                                <input readonly class="wallesterInputs font-weight-light" name="first_name" value="{{ $currentOrderData['delivery']['first_name'] ?? getCProfile()->first_name }}">
                            </p>
                            @error('first_name')
                            <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <p class="activeLink p-0">
                                {{ t('wallester_card_order_last_name') }}
                                <input readonly class="wallesterInputs font-weight-light" name="last_name" value="{{ $currentOrderData['delivery']['last_name'] ?? getCProfile()->last_name }}">
                            </p>
                            @error('last_name')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>

                    </div>
                    <div class="col-6 p-0 d-flex w-100">
                        <div>
                            <p class="activeLink p-0 w-100">
                                {{ t('wallester_card_order_address1') }}
                                <input readonly class="wallesterInputs font-weight-light w-95" name="address1" value="{{ isset($currentOrderData['delivery']['address1']) ? substr($currentOrderData['delivery']['address1'], 0, 45) : substr(getCProfile()->address, 0, 45) }}">
                            </p>
                            @error('address1')<p class="text-danger">{{ $message }}</p>@enderror

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 m-2 pl-2 pt-1 pt-sm-0 d-flex">
                <div class="row d-flex justify-content-between w-100">
                    <div class="col-lg-6 p-0 d-flex ">
                        <div class="w-100">
                            <p class="activeLink p-0 col-lg-12 col-md-8 w-100">
                                {{ t('wallester_card_order_address2') }}
                                <input readonly class="wallesterInputs font-weight-light w-95" name="address2" value="{{ $currentOrderData['delivery']['address2'] ?? null }}">
                            </p>
                            @error('address2')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="col-lg-6 p-0 d-flex">
                        <div>
                            <p class="activeLink p-0 col-6">
                                {{ t('wallester_card_postal_code') }}
                                <input readonly class="wallesterInputs font-weight-light" name="postal_code" value="{{ $currentOrderData['delivery']['postal_code'] ?? getCProfile()->zip_code }}">
                            </p>
                            @error('postal_code')<p class="text-danger">{{ $message }}</p>@enderror

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 m-2 pl-2 pt-1 pt-sm-0 d-flex">
                <div class="row d-flex justify-content-between w-100">
                    <div class="col-lg-6 p-0 d-flex w-100">
                        <div>
                            <p class="activeLink p-0">
                                {{ t('ui_cprofile_city') }}
                                <input readonly class="wallesterInputs font-weight-light" name="city" value="{{ $currentOrderData['delivery']['city'] ?? getCProfile()->city }}">
                            </p>
                            @error('city')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <p class="activeLink p-0 d-flex flex-column">
                                {{ t('ui_cprofile_country') }}
                                <span style="font-weight: normal">
                                <select readonly class="w-100 font-weight-normal" name="country_code">
                                    @foreach($countries as $countryKey => $country)
                                        <option
                                            @if(isset($currentOrderData['delivery']['country']) && $currentOrderData['delivery']['country'] == $countryKey)
                                                selected
                                            @elseif(getCProfile()->country == $countryKey)
                                                selected
                                            @endif
                                            value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                            </span>
                            </p>
                            @error('country_code')<p class="text-danger">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5 ml-1">
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                        type="submit"> {{ t('wallester_card_btn_next') }} </button>
            </div>
        </form>
    </div>

@endsection
@section('scripts')
    <script src="/js/cabinet/wallester-order-card.js"></script>

@endsection
