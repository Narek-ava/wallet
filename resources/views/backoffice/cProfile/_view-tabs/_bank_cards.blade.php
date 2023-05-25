<div class="row mt-5 mb-5">
    <div class="col-md-12">
        @if($profile->wallester_person_id)
            <div class="row">
            <div class="col m-2 pl-2 pt-1 pt-sm-0">
                <h4 class="d-inline-block mb-0">
                    <a class="text-decoration-none" href="{{ config('cratos.wallester.appSite') }}persons/{{ $profile->wallester_person_id }}" target="_blank">{{ t('ui_wallester_id') }}</a>
                </h4>
                <small>{{ t('ui_wallester_id_description') }}</small>

            </div>
        </div>
        @endif
        <div class="row mt-3">
            <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 80px;">
                <h4 class="d-inline-block mb-0">{{ t('ui_cards') }}</h4>
            </div>
            <div class="col mt-1 pl-0 pl-sm-2">
                <button type="button" class="btn btn-sm btn-dark badge-pill" data-toggle="modal"
                        data-target="#cardsConditions"
                        style="width: 180px;border-radius: 20px">{{ t('cards_conditions') }}
                </button>
                @if($cards->isNotEmpty() && $currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_BANK_CARD_FOR_CLIENT], $profile->cUser->project_id))
                    <a id="credit-card" class="backoffice-credit-card-button text-decoration-none" href="" data-toggle="modal" data-target="#createCard">
                        {{ t('order_new_card') }}
                    </a>
                @endif

                @include('cabinet.cards._cards-conditions')
            </div>
        </div>
    </div>
</div>




@if($cards->isEmpty())
    <div class="col-md-4 mb-4 pr-0">
        <div class="card-default p-0 credit-card">
            <div class="d-flex justify-content-between align-items-center mr-4 ml-4 mt-4">
                @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_BANK_CARD_FOR_CLIENT], $profile->cUser->project_id))
                    <a id="credit-card" class="credit-card-button text-decoration-none" href="" data-toggle="modal"
                       data-target="#createCard">
                        {{ t('order_new_card') }}
                    </a>
                @endif
                <div class="card-logo">
                    <img src="{{ asset('/cratos.theme/images/logo.png')}}" class="img-fluid" alt="">
                </div>
            </div>
            <div class="credit-card-number d-flex justify-content-between mr-4 ml-4 mt-6">
                <div class="credit-card-number-hide-section">****</div>
                <div class="credit-card-number-hide-section">****</div>
                <div class="credit-card-number-hide-section">****</div>
                <div class="credit-card-number-hide-section">****</div>
            </div>

            <div class="credit-card-data d-flex justify-content-between p-4">
                <div class="credit-card-cardholder-name">{{ t('cardholder') }}</div>
                <div class="credit-card-date">**/**</div>
                <div class="credit-card-cvv">CVV</div>
                <div class="credit-card-type">VISA</div>
            </div>
        </div>
    </div>
@else
    <br><br>
    <div class="d-flex justify-content-start wallesterShowCards" style="flex-wrap: wrap">
        @foreach($cards as $card)
            <div class="m-3">
{{--                @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC && $card->status == \App\Enums\WallesterCardStatuses::STATUS_DISPATCHED)--}}
{{--                    <div class="wallester-activate-btn">--}}
{{--                        <button data-toggle="modal" data-wallester-account-detail-id="{{ $card->id }}"--}}
{{--                                type="button" data-target="#confirmDelivery"--}}
{{--                                class="btn btn-sm btn-primary themeBtn themeBtnLight activateBtn"--}}
{{--                                style="color: #000 !important;">Activate my card--}}
{{--                        </button>--}}
{{--                    </div>--}}
{{--                @endif--}}

                <div class="card-default p-0 credit-card">
                    <div class="d-flex justify-content-between align-items-center mr-2 ml-4 mt-4 card-balance">
                        <h4>{{  \App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[\App\Enums\Currency::CURRENCY_EUR]  . ' '. $card->account->balance  }}</h4>
                        <div class="card-logo d-flex align-content-end">
                            <img src="{{ asset('/cratos.theme/images/connectee_card_logo.svg')}}" class="img-fluid" alt="">
                        </div>
                    </div>
                    <div class="credit-card-number d-flex justify-content-between pl-4 pr-2">
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number-hide-section">****</div>
                        <div class="credit-card-number{{ $card->card_mask ? '' : '-hide'  }}-section">{{ $card->card_mask ? substr($card->card_mask , -4) : '****' }}</div>
                    </div>
                    <div class="ordered-credit-card-data d-flex justify-content-between ordered-credit-card-padding ">
                        <div class="credit-card-cardholder-name">{{ $card->name }}</div>
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
                        <button class="btn btn-sm btn-primary themeBtn themeBtnLight wallester-details-btn"
                                @if(!$card->is_paid) disabled @endif
                                data-wallester-account-detail-id="{{ $card->id }}"
                                data-details-url="{{ route('backoffice.wallester.card.details') }}"
                                type="button">Details
                        </button>
                    </div>
                </div>

            </div>

        @endforeach
    </div>
{{--    @include('cabinet.cards._plastic-confirm-delivery')--}}

@endif
@if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_BANK_CARD_FOR_CLIENT], $profile->cUser->project_id))
    <div class="modal fade login-popup rounded-0" id="createCard" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content common-shadow-theme">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <img src="{{ config('cratos.urls.theme') }}images/close.png" alt="">
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('backoffice.wallester.cards.store') }}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" value="{{ $profile->id }}" name="cProfileId">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mb-4">{{ t('order_new_card')}}</h5>
                            </div>

                            <div class="col-md-5 mb-2">
                                <div class="form-label-group">
                                    <div class="row ">
                                        <div class="">
                                            <div class="row">
                                                <div class="form-group col-md-6">
                                                    <label for="inputEmail"
                                                           class="activeLink">{{ t('select_card_type') }}</label>
                                                    <select id="type" class="w-100 " name="type" required>
                                                        <option value="" disabled selected
                                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                                        @foreach(\App\Enums\WallesterCardTypes::NAMES as $type => $card)
                                                            <option
                                                                value="{{$type}}">{{\App\Enums\WallesterCardTypes::getName($type)}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('type')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="inputEmail"
                                                           class="activeLink">{{ t('choose_payment_method') }}</label>
                                                    <select name="paymentMethod" class="mr-4" id="paymentMethod"
                                                            style="padding-right: 50px;" required>
                                                        <option value="" disabled selected
                                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                                        @foreach(\App\Enums\WallesterCardOrderPaymentMethods::getList() as $value => $name)
                                                            <option value="{{ $value }}">{{ $name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('cardType')
                                                    <div class="error text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div id="security" class="mt-5">
                                    <h2 class="wallesterOrderBlocks">{{ t('security') }}</h2>
                                    <div class="row mt-4 col-12 d-flex align-items-start">
                                        <p class=" activeLink">
                                            {{ t('cards_conditions_contactless_purchases') }}
                                            <select class="mt-3 col-10" name="contactless_purchases"
                                                    style="min-width: 100px; padding-right: 20px;">
                                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                    <option value="{{ $value }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @error('contactless_purchases')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                        </p>
                                        <p class="activeLink">
                                            {{ t('cards_conditions_atm_withdrawals') }}
                                            <select class="mt-3 col-10" name="atm_withdrawals"
                                                    style="min-width: 100px; padding-right: 20px;">
                                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                    <option value="{{ $value }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @error('atm_withdrawals')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                        </p>

                                        <p class="activeLink">
                                            {{ t('cards_conditions_purchases') }}
                                            <select class="mt-3 col-10" name="internet_purchases"
                                                    style="min-width: 100px; padding-right: 20px;">
                                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                    <option value="{{ $value }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @error('internet_purchases')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                        </p>
                                        <p class="activeLink">
                                            {{ t('cards_conditions_overall_limits_enabled') }}
                                            <select class="mt-3 col-10" name="overall_limits_enabled"
                                                    style="min-width: 100px; padding-right: 20px;">
                                                @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                    <option value="{{ $value }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        @error('overall_limits_enabled')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
                                        </p>
                                    </div>
                                </div>
                                <div id="password" class="mt-5">
                                    <h2 class="wallesterOrderBlocks">{{ t('wallester_card_3ds_password') }}</h2>
                                    {!! t('wallester_card_3ds_password_description') !!}

                                    <div class="row mt-4 col-12 d-flex align-items-start">
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_3ds_password_input') }}</label><br>
                                            <input autocomplete="off" class="wallesterInputs " type="password"
                                                   name="password"
                                                   value="{{ old('password')}}">
                                            @error('password')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_3ds_confirm_password_input') }}</label><br>
                                            <input autocomplete="off" class="wallesterInputs " type="password"
                                                   name="password_confirmation"
                                                   value="{{ old('password_confirmation')}}">
                                            @error('password_confirmation')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div id="cardLimits" class="mt-5">
                                    <h2 class="wallesterOrderBlocks">{{ t('wallester_card_limits') }}</h2>
                                    {!! t('wallester_card_limits_description') !!}

                                    <div class="row mt-4 col-12 d-flex align-items-start">
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_daily_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[daily_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_daily_purchase_limit'] ?? '' }}">
                                            @error('limits.daily_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_withdrawal_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[daily_withdrawal_limit]"
                                                   value="{{ $wallesterLimits['default_account_daily_withdrawal_limit'] ?? '' }}">
                                            @error('limits.daily_withdrawal_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_internet_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[daily_internet_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_daily_internet_purchase_limit'] ?? '' }}">
                                            @error('limits.daily_internet_purchase')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_contacless_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[daily_contactless_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_daily_contactless_purchase_limit'] ?? '' }}">
                                            @error('limits.daily_contactless_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div id="weeklyLimits" class="mt-5">
                                    <h2 class="wallesterOrderBlocks">{{ t('wallester_weekly_limits') }}</h2>
                                    <div class="row mt-4 col-12 d-flex align-items-start">
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_weekly_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[weekly_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_weekly_purchase_limit'] ?? '' }}">
                                            @error('limits.weekly_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_weekly_withdrawal_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[weekly_withdrawal_limit]"
                                                   value="{{$wallesterLimits['default_account_weekly_withdrawal_limit'] ?? '' }}">
                                            @error('limits.weekly_withdrawal_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_weekly_internet_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[weekly_internet_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_weekly_internet_purchase_limit'] ?? '' }}">
                                            @error('limits.weekly_internet_purchase')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_weekly_contacless_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[weekly_contactless_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_weekly_contactless_purchase_limit'] ?? '' }}">
                                            @error('limits.weekly_contactless_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div id="monthlyLimits" class="mt-5">
                                    <h2 class="wallesterOrderBlocks">{{ t('wallester_monthly_limits') }}</h2>
                                    <div class="row mt-4 col-12 d-flex align-items-start">
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_monthly_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[monthly_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_monthly_purchase_limit'] ?? '' }}">
                                            @error('limits.monthly_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_monthly_withdrawal_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[monthly_withdrawal_limit]"
                                                   value="{{ $wallesterLimits['default_account_monthly_withdrawal_limit'] ?? '' }}">
                                            @error('limits.monthly_withdrawal_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_monthly_internet_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[monthly_internet_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_monthly_internet_purchase_limit'] ?? '' }}">
                                            @error('limits.monthly_internet_purchase')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="inputEmail"
                                                   class="activeLink">{{ t('wallester_card_order_monthly_contacless_limits') }}</label>
                                            <input autocomplete="off" class="wallesterInputs " type="text"
                                                   name="limits[monthly_contactless_purchase_limit]"
                                                   value="{{ $wallesterLimits['default_account_monthly_contactless_purchase_limit'] ?? '' }}">
                                            @error('limits.monthly_contactless_purchase_limit')
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="cardDeliveryAddress">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col m-2 pl-2 pt-1 pt-sm-0" style="max-width: 300px;">
                                            <h2 class="d-inline-block mb-0">{{ t('card_delivery_address') }}</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="inputEmail"
                                           class="activeLink">{{ t('wallester_card_order_first_name') }}</label>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="first_name"
                                           value="{{ old('first_name')}}">
                                    @error('first_name')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail"
                                           class="activeLink">{{ t('wallester_card_order_last_name') }}</label>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="last_name"
                                           value="{{ old('last_name')}}">
                                    @error('last_name')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail"
                                           class="activeLink">{{ t('wallester_card_order_address1') }}</label><br>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="address1"
                                           value="{{ old('address1')}}">
                                    @error('address1')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail"
                                           class="activeLink">{{ t('wallester_card_order_address2') }}</label><br>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="address2"
                                           value="{{ old('address2')}}">
                                    @error('address2')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail" class="activeLink">{{ t('ui_cprofile_zip_code') }}</label>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="postal_code"
                                           value="{{ old('postal_code')}}">
                                    @error('postal_code')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail" class="activeLink">{{ t('ui_cprofile_city') }}</label><br>
                                    <input autocomplete="off" class="wallesterInputs " type="text"
                                           name="city"
                                           value="{{ old('city')}}">
                                    @error('city')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="inputEmail" class="activeLink">{{ t('ui_country_residence') }}</label>
                                    <select class="w-100 " name="country_code">
                                        <option value="" disabled selected
                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                        @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                            <option value="{{$countryKey}}">{{$country}}</option>
                                        @endforeach
                                    </select>
                                    @error('country_code')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                        <div class="mt-5 ml-1">
                            <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
                                    type="submit"> {{ t('save') }} </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
