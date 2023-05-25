@section('styles')
    <style>
        .invalid {
            background: #ffdddd;
        }
        .bank-details-label {
            height: 345px;
        }
        .bank-details-label .checkmark.bank-details {
            height: 100%;
        }
        .bank-details-rows {
            font-size: 14px;
        }
    </style>
@endsection
    <div class="row mt-5">
        <div class="col-md-12">
            <form id="regForm" method="post"
                  action="{{ route('wallester.confirm.wire.payment') }}">
                @csrf
                <input type="hidden" name="id" value="{{ $account->id }}">

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-1">{{ t('wire_transfer_step_two') }}</h5>

                    <div class="mt-5 text-left">
                        @error('wire_type')
                        <div class="error text-danger m-2">{{ $message }}</div>
                        @enderror

                        @foreach(\App\Enums\AccountType::ACCOUNT_WIRE_TYPES as $key => $wireType)
                            @if($key == \App\Enums\AccountType::TYPE_WIRE_SWIFT) @continue @endif
                            <label class="component ml-0 mb-5 mt-3 mt-sm-0">
                                <input class="wire-type account-type" type="radio" name="wire_type" @if ($loop->first) checked="checked" @endif value="{{ \App\Enums\WireType::OPERATION_WIRE_TYPES[$key] ?? ''}}">
                                <span class="checkmark">
                                    <i class="fa fa-info-circle position-absolute payment-info payment-info-sepa"
                                       data-toggle="tooltip"
                                       title="{{ $wireType == 'SEPA' ? t('sepa_popup_info') : t('swift_popup_info') }}"
                                       data-content="Some content inside the popover"></i>
                                    <img class="position-absolute h-50" style="left: 68px; top: 30px"
                                         src="{{ asset('/cratos.theme/images/' . (\App\Enums\WireType::IMAGES[$wireType] ?? '')) }}"
                                    >
                                </span>
                            </label>
                        @endforeach

                        <p class="select-wire-type d-none text-danger"> {{ t('wire_transfer_select_wire_type') }}</p>
                    </div>
                </div>

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('wire_transfer_step_three') }}</h5>
                    <div class="row mt-5">
                        <div class="col-md-6 col-lg-3 mb-4">
                            <h6>{{ t('wire_transfer_country_of_your_bank') }}</h6>
                            <select class="w-100 country" name="country" style="border: 1px solid #bfb7b7;width: 100%!important;"
                                    onclick="removeError('country')">
                                <option value="">{{ t('wire_transfer_select_country') }}</option>
                                @foreach(\App\Models\Country::getCountries(false) as $key => $country)
                                    <option value="{{ $key }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            @error('country')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 col-lg-3 mb-4">
                            <h6>Currency</h6>
                            <select class="w-100 currency" name="currency" style="border: 1px solid #bfb7b7;"
                                    onclick="removeError('currency')">
                                <option value="">{{ t('wire_transfer_select_currency') }}</option>
                                @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency)
                                    <option value="{{ $currency }}">{{ $currency }}</option>
                                @endforeach
                            </select>
                            @error('currency')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <input type="text" hidden name="compliance_level" value="{{ auth()->user()->cProfile->compliance_level }}">
                        <span class="d-none no-providers-text text-danger ml-3 mt-3">There is no provider with selected features. </span>
                        <span class="d-none invalid-amount-text text-danger ml-3 mt-3"></span>
                        <span class="limit-fail-text text-danger ml-3 mt-3"></span>
                    </div>
                </div>

                <div class="tab container ml-0">
                    <div class="mb-3 row">
                        <a id="prevBtn" onclick="nextPrev(-1)">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        </a>
                        <h5 class="d-inline-block ml-4">{{ t('wire_transfer_step_four') }}</h5>
                        <span class="select-bank text-danger ml-5"></span>
                    </div>
                    <div id="form_providers" class="row">
                        <p class="no-account-text text-danger d-none"></p>
                    </div>
                </div>

                <div class="tab" id="createTab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('wire_transfer_step_five') }}</h5>
                    <div class="row pl-3 pr-3">
                        <div class="col-md-7" style="max-width: 500px;">
                            <div class="row p-3 pt-4 mt-5 wallet-border-pink">
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_time') }}</h6>
                                    <p class="time-to-fund mb-2">-</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_transaction_limit') }}</h6>
                                    <p class="mb-2 transactionLimit">-</p>
                                    <h6 class="font-weight-bold mb-0">{{ t('wire_transfer_available_limit') }}</h6>
                                    <p class="available-limit mb-2">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 complianceLevel mt-5 pl-3 pl-md-5 row align-content-center">
                            <p>{{ t('wire_transfer_transaction_exceeds_verification_level') }}</p>
                            <p>{{ t('wire_transfer_promote_before_committing_transaction') }}
                                <a href="{{ route('cabinet.compliance') }}"
                                   class="text-dark text-decoration-none"><strong>Compliance page</strong></a>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="buttons mt-1">
                    <a class="btn btn-primary themeBtn btnWhiteSpace mt-5 loader" type="submit" id="nextBtn"
                       onclick="nextPrev(1)">{{ t('wire_transfer_next') }}
                    </a>
                </div>
            </form>

            <!-- Circles which indicates the steps of the form: -->
            <div style="text-align:center;margin-top:40px;" hidden>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </div>
    </div>

    <div id="providerContainer" class="col-md-12 m-5" style="display: none">
        <div class="row">
            <div class="col-md-12 mt-4 pt-2">
                <label class="component ml-0 p-0 bank-details-label">
                    <input type="radio" name="provider_account_id" class="provider_name d-none"
                           value="" onclick="showRefNum(this.parentElement.classList.add(this.value), this.value)">
                    @error('provider_account_id')
                    <div class="error text-danger">{{ $message }}</div>
                    @enderror
                    <div class="checkmark bank-details">
                        <div class="row bank-details-checkmark-text">
                            <div class="col-md-12">
                                <h5 class="themeColorRed provider_name_text"></h5>

                                <input hidden class="account_type" value="{{ \App\Enums\AccountType::TYPE_WIRE_SWIFT }}"/>
                                <div class="row">
                                    <div class="col-md-5">
                                        <h6 class="m-0 bank-details-rows">{{ t('wire_transfer_account_beneficiary') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block account_beneficiary"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0">
                                    <div class="col-md-5">
                                        <h6 class="m-0 bank-details-rows">{{ t('wire_transfer_beneficiary_address') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block beneficiary_address"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0 ">
                                    <div class="col-md-5 iban_eur_text"><h6 class="m-0">IBAN EUR</h6></div>
                                    <div class="col-md-7"><small class="d-block iban_eur"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0">
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">SWIFT/BIC</h6></div>
                                    <div class="col-md-7"><small class="d-block swift_bic"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0">
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('wire_transfer_bank_name') }}</h6></div>
                                    <div class="col-md-7"><small class="d-block bank_name"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0">
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('wire_transfer_bank_address') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block bank_address"></small></div>
                                </div>


                                <div class="row mt-2 mt-md-0 swift-details" hidden>
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_correspondent_bank') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block correspondent_bank"></small></div>
                                </div> <br>
                                <div class="row mt-2 mt-md-0 swift-details" hidden>
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_correspondent_bank_swift') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block correspondent_bank_swift"></small></div>
                                </div>
                                <div class="row mt-2 mt-md-0 swift-details" hidden>
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block intermediary_bank"></small></div>
                                </div> <br>
                                <div class="row mt-2 mt-md-0 swift-details" hidden>
                                    <div class="col-md-5"><h6 class="m-0 bank-details-rows">{{ t('ui_cabinet_intermediary_bank_swift') }}</h6>
                                    </div>
                                    <div class="col-md-7"><small class="d-block intermediary_bank_swift"></small></div>
                                </div>

                                <div class="purpose-transfer-row row d-none reference-number mt-2 mt-md-0">
                                    <div class="col-md-5"><h6
                                            class="m-0 d-none reference-number bank-details-rows">{{ t('wire_transfer_purpose_of_transfer') }}</h6></div>
                                    <div class="col-md-7">
                                        <div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>
    </div>
    <div class="overlay"></div>



@section('scripts')
    <script>
        $(document).ready(function () {
            if ($('.payment-type-swift').hasClass('disabled')) {
                $('.payment-type-swift').parent().attr("title", "Переводы в SWIFT в данный момент не доступны.");
            }
            if ($('.payment-type-sepa').hasClass('disabled')) {
                $('.payment-type-sepa').parent().attr("title", "Переводы в SEPA в данный момент не доступны.");
            }

            $('[data-toggle="tooltip"]').tooltip();
        });

        var currentTab = 0; // Current tab is set to be the first tab (0)
        showTab(currentTab); // Display the current tab

        function showTab(n) {
            // This function will display the specified tab of the form...
            var x = document.getElementsByClassName("tab");
            x[n].style.display = "block";
            //... and fix the Previous/Next buttons:
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "inline";
            }
            if (n == (x.length - 1)) {
                document.getElementById("nextBtn").innerHTML = "Create";
            } else {
                document.getElementById("nextBtn").innerHTML = "Next";

            }
            //... and run a function that will display the correct step indicator:
            fixStepIndicator(n)

            if (n == 2) {
                //get providers by countries
                $('.reference-number').addClass('d-none').removeClass('d-block')
                $.ajax({
                    type: "POST",
                    url: API + 'providers-by-country',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "country": $('.country').val(),
                        "currency": $('.currency').val(),
                        "amount": $('.amount').val(),
                        // "accountType": $("input[type='radio'][name='wire_type']:checked").val(), //for sepa and swift
                        "accountType": {{ \App\Enums\OperationOperationType::TYPE_TOP_UP_SEPA }},
                        "validateMinAmount": true,
                    },
                    success: function (response) {
                        $('.no-account-text').removeClass('d-none').addClass('d-block').text('');
                        $('.no-providers-text').addClass('d-none').removeClass('d-block');
                        $('.invalid-amount-text').addClass('d-none').removeClass('d-block');
                        if (response.accountExist == false) {
                            nextPrev(-1);
                            if (response.accountExist == false) {
                                $('.no-providers-text').removeClass('d-none').addClass('d-block');
                            }
                        } else {
                            for (let i = 0; i < response.providers.length; i++) {
                                let provider = response.providers[i];
                                if (provider.accounts.length != 0) {
                                    for (let j = 0; j < provider.accounts.length; j++) {
                                        let account = provider.accounts[j];
                                        $('#providerContainer .provider_name').val(account.id);
                                        $('#providerContainer .provider_name_text').html(`${provider.name} - ${account.name}`);
                                        $('#providerContainer .account_beneficiary').html(account.wire.account_beneficiary ?? '-');
                                        $('#providerContainer .beneficiary_address').html(account.wire.beneficiary_address ?? '-');
                                        if(!response.isTypeSwift) {
                                            $('#providerContainer .iban_eur').html(account.wire.iban ?? '-');
                                            $('.iban_eur_text').removeClass('d-none')
                                            $('.iban_eur_text').addClass('d-block')
                                        }else {
                                            $('.iban_eur_text').removeClass('d-block')
                                            $('.iban_eur_text').addClass('d-none')
                                        }
                                        $('#providerContainer .swift_bic').html(account.wire.swift ?? '-');
                                        $('#providerContainer .bank_name').html(account.wire.bank_name ?? '-');
                                        $('#providerContainer .bank_address').html(account.wire.bank_address ?? '-');

                                        $('.swift-details').attr('hidden', true)

                                        let accountType = $('.account_type').val();
                                        if(account.account_type == accountType) {
                                            $('.swift-details').removeAttr('hidden')
                                            $('#providerContainer .correspondent_bank').html(account.wire.correspondent_bank ?? '-');
                                            $('#providerContainer .correspondent_bank_swift').html(account.wire.correspondent_bank_swift ?? '-');
                                            $('#providerContainer .intermediary_bank').html(account.wire.intermediary_bank ?? '-');
                                            $('#providerContainer .intermediary_bank_swift').html(account.wire.intermediary_bank_swift ?? '-');
                                        }

                                        $('#form_providers').addClass('providerContainer' + provider.name);
                                        $('#form_providers').append($('#providerContainer').html());
                                        $('.reference-number').addClass(account.id);
                                    }
                                }
                            }
                            $('#providerContainer .provider_name').val('');
                            $('#providerContainer').empty();
                            $('#form_providers').find('.bank-details').first().click();
                        }


                    }
                });

                //check limits of transaction amount
                $.ajax({
                    type: "POST",
                    url: API + 'get-limits',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "c_profile_id": "{{ auth()->user()->cProfile->id }}",
                        "amount" : $('.amount').val(),
                        "fromCurrency" : $('.currency').val(),
                        "operationType": $("input[type='radio'][name='wire_type']:checked").val(),
                        toCurrency: $('#exchange_to').val(),
                        wireType: "{{ \App\Enums\OperationType::TOP_UP_WIRE }}"
                    },
                    success: function (response) {

                        if (response.message == 'failed') {
                            var string = window.location.href;
                            var result = string.split("/").pop();
                            window.location.href = '/cabinet/wallets/' + result + '?message=1';
                        } else {
                            var message = [];
                            var amount = $('.amount').val();
                            var currency = $('.currency').val();

                            if (amount < parseFloat(response.limits.transaction_amount_min) || amount <= 0
                                || (response.limits.transaction_count_daily_max && response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)
                                || (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1)) {

                                if (parseFloat(amount) < parseFloat(response.limits.transaction_amount_min)) {
                                    message.push(" {{ t('wire_transfer_minimum_amount') }} " + response.limits.transaction_amount_min + ' ' + currency);
                                } else if (response.limits.transaction_count_daily_max && (response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)) {
                                    message.push(" {{ t('wire_transfer_daily_limit_is_over') }} ");
                                } else if (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1) {
                                    message.push(" {{ t('wire_transfer_monthly_limit_is_over') }} ");
                                } else if (amount <= 0) {
                                    message.push(" {{ t('wire_transfer_more_than_zero') }} ");
                                }
                                nextPrev(-1);
                            }

                            $('.limit-fail-text').text(message);

                            if (response.limits.monthly_amount_max < response.transactionsAmountPerMonth || response.limits.monthly_amount_max < amount) {
                                $('.complianceLevel').removeClass('d-none').addClass('d-block');
                            }
                            let availableLimit = response.availableAmountForMonth;
                            $('.available-limit').text(availableLimit);
                            $('.transactionLimit').text(response.transactionLimit)
                            let blockChainCount = parseInt("{{ \App\Enums\OperationOperationType::BLOCKCHAIN_FEE_COUNT_TOP_UP_WIRE }}");
                        }
                    }
                });
            } else if (n == 3) {
                if(!$("input[type='radio'][name='provider_account_id']:checked").val()){
                    $('.select-bank').text('Select bank account');
                    nextPrev(-1)
                }
                $.ajax({
                    type: "POST",
                    url: API + 'get-provider-account',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "provider_account_id": $("input[type='radio'][name='provider_account_id']:checked").val(),
                    },
                    success: function (response) {
                        $('.time-to-fund').html(response.account.wire.time_to_found + ' days' ?? '-');
                    }
                });
            } else {
                document.getElementsByClassName("buttons")[0].style.marginTop = "97px"
            }
        }

        let createButton = false;

        function nextPrev(n) {
            $('#nextBtn').removeClass('disabled');
            // This function will figure out which tab to display
            var x = document.getElementsByClassName("tab");
            // Exit the function if any field in the current tab is invalid:
            if (n == 1 && !validateForm()) return false;
            // Hide the current tab:
            x[currentTab].style.display = "none";
            // Increase or decrease the current tab by 1:
            currentTab = currentTab + n;
            // if you have reached the end of the form...
            if (currentTab >= x.length) {
                // ... the form gets submitted:
                document.getElementById("regForm").submit();
                createButton = true;
                return false;
            }
            // Otherwise, display the correct tab:
            showTab(currentTab);
            if (currentTab == 3) {
                $('#nextBtn').addClass('createButton');
            }
        }

        function validateForm() {
            // This function deals with validation of the form fields
            var x, y, i, valid = true;
            x = document.getElementsByClassName("tab");
            y = x[currentTab].getElementsByTagName("input");
            r = x[currentTab].getElementsByTagName("select");
            // A loop that checks every input field in the current tab:
            for (i = 0; i < y.length; i++) {
                // If a field is empty...
                if (y[i].value == "") {
                    // add an "invalid" class to the field:
                    y[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                }
            }

            for (i = 0; i < r.length; i++) {
                // If a field is empty...
                if (r[i].value == "") {
                    // add an "invalid" class to the field:
                    r[i].className += " invalid";
                    // and set the current valid status to false
                    valid = false;
                } else {
                    r[i].classList.remove("invalid");
                }
            }
            // If the valid status is true, mark the step as finished and valid:
            if (valid) {
                document.getElementsByClassName("step")[currentTab].className += " finish";
            }
            return valid; // return the valid status
        }

        function fixStepIndicator(n) {
            // This function removes the "active" class of all steps...
            var i, x = document.getElementsByClassName("step");
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(" active", "");
            }
            //... and adds the "active" class on the current step:
            x[n].className += " active";
        }

        function removeError($class) {
            $('.' + $class).removeClass('invalid')
            $('.no-providers-text').addClass('d-none');
        }

        function showRefNum($f, $value) {
            $('.' + $value + ' .reference-number').removeClass('d-none').addClass('d-block');
        }

        $(document).ready(function () {
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
            }
            setInterval(function(){
                if (getCookie("fileLoading")) {
                    document.cookie = "fileLoading=";
                    {{--location.href = '/cabinet/wallets/' + '{{ $cryptoAccountDetail->id }}';--}}
                } else {
                    console.log(5);
                }
            },1000);
        });
    </script>
@endsection
