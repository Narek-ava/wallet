@extends('cabinet.layouts.cabinet')
@section('title', t('title_top_up_bts_page'))

@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title">Top Up - BTS Wallet</h3>
            <div class="row">
                <div class="col-md-12 d-flex justify-content-between">
                    <div class="balance">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form id="regForm" action="#">
                <div class="tab">
                    <h5>Step 1 - Select deposit type:</h5>
                    <div class="mt-5">
                        <label class="component ml-0">
                            <input type="radio" oninput="this.className = ''" checked="checked" name="radio">
                            <span class="checkmark-text">{{ t('transaction_history_detail_cryptocurrency') }}</span>
                            <span class="checkmark"></span>
                        </label>

                        <label class="component">
                            <input type="radio" oninput="this.className = ''" checked="checked"
                                   value="wire-transfer-deposit" name="radio">
                            <span class="checkmark-text">{{ t('ui_wire_transfer') }}</span>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block">{{ t('ui_step_2') }}</h5>
                    <div class="mt-5">
                        <label class="component ml-0">
                            <input type="radio" oninput="this.className = ''" checked="checked" name="radio">
                            <span class="checkmark-text">Sepa</span>
                            <span class="checkmark"></span>
                        </label>

                        <label class="component">
                            <input type="radio" oninput="this.className = ''" checked="checked" name="radio">
                            <span class="checkmark-text">Swift</span>
                            <span class="checkmark"></span>
                        </label>
                    </div>
                </div>

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('ui_step_3') }}</h5>
                    <div class="row mt-5">
                        <div class="col-md-6 col-lg-3">
                            <h6>{{ t('wire_transfer_country_of_your_bank') }}</h6>
                            <select class="w-100 mt-4" name="" style="border: 1px solid #bfb7b7;">
                                <option value="">{{config('cratos.company_details.country')}}</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <h6>{{ t('withdrawal_currency') }}</h6>
                            <select class="w-100 mt-4" name="" style="border: 1px solid #bfb7b7;">
                                <option value="">ETH</option>
                            </select>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <h6>{{ t('send_crypto_amount') }}</h6>
                            <input class="mt-3" value="" name="" placeholder="Amount">
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <h6>{{ t('ui_cabinet_deposit_exchange_to') }}</h6>
                            <select class="w-100 mt-4" name="" style="border: 1px solid #bfb7b7;">
                                <option value="">ETH</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('ui_step_4') }}</h5>
                    <div class="row mt-5" style="width: 136%">
                        <div class="col-md-6">
                            <label class="component ml-0">
                                <input type="radio" oninput="this.className = ''" checked="checked" name="radio">
                                <div class="row bank-details-checkmark-text">
                                    <div class="col-md-5">
                                        <h5 class="text-danger">{{ t('ui_wallter_sepa') }}</h5>
                                        <h6 class="m-0">{{ t('wire_transfer_account_beneficiary') }}</h6>
                                        <h6 class="m-0">{{ t('wire_transfer_beneficiary_address') }}</h6>
                                        <h6 class="m-0">IBAN EUR</h6>
                                        <h6 class="m-0">SWIFT/BIC</h6>
                                        <h6 class="m-0">{{ t('withdraw_wire_bank_name') }}</h6>
                                        <h6 class="m-0">{{ t('withdraw_wire_bank_address') }}</h6>
                                        <h6 class="mt-2">{{ t('wire_transfer_purpose_of_transfer') }}</h6>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 32px; line-height: 1.2">
                                        <p class="p-0 m-0">{{ t('wire_transfer_account_beneficiary') }}</p>
                                        <p class="p-0 m-0">{{ t('wire_transfer_beneficiary_address') }}</p>
                                        <p class="p-0 m-0">IBAN EUR</p>
                                        <p class="p-0 m-0">SWIFT/BIC</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_name') }}</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_address') }}</p>
                                        <p class="p-0 mt-2 font-weight-bold text-danger">{{ t('wire_transfer_purpose_of_transfer') }}</p>

                                        <p class="mt-2" style="color: #e88a85">
                                            {{ t('ui_unique_transfer') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="checkmark bank-details"></span>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="component ml-0">
                                <input type="radio" oninput="this.className = ''" checked="checked" name="radio">
                                <div class="row bank-details-checkmark-text">
                                    <div class="col-md-5">
                                        <h5 class="text-danger">Converta Sepa</h5>
                                        <p class="p-0 m-0">{{ t('wire_transfer_account_beneficiary') }}</p>
                                        <p class="p-0 m-0">{{ t('wire_transfer_beneficiary_address') }}</p>
                                        <p class="p-0 m-0">IBAN EUR</p>
                                        <p class="p-0 m-0">SWIFT/BIC</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_name') }}</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_address') }}</p>
                                        <p class="p-0 mt-2 font-weight-bold text-danger">{{ t('wire_transfer_purpose_of_transfer') }}</p>
                                    </div>
                                    <div class="col-md-6" style="margin-top: 32px;line-height: 1.2">
                                        <p class="p-0 m-0">{{ t('wire_transfer_account_beneficiary') }}</p>
                                        <p class="p-0 m-0">{{ t('wire_transfer_beneficiary_address') }}</p>
                                        <p class="p-0 m-0">IBAN EUR</p>
                                        <p class="p-0 m-0">SWIFT/BIC</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_name') }}</p>
                                        <p class="p-0 m-0">{{ t('withdraw_wire_bank_address') }}</p>
                                        <p class="p-0 mt-2 font-weight-bold text-danger">{{ t('wire_transfer_purpose_of_transfer') }}</p>
                                    </div>
                                </div>
                                <span class="checkmark bank-details"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="tab">
                    <a id="prevBtn" onclick="nextPrev(-1)">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    </a>
                    <h5 class="d-inline-block ml-4">{{ t('withdraw_wire_step_five') }}</h5>
                    <div class="row w-50 py-2 px-0 mt-5 wallet-border-pink">
                        <div class="col-md-5 text-center">
                            <h6 class="font-weight-bold">{{ t('wire_transfer_time') }}</h6>
                            <p>Instantly</p>
                            <h6 class="font-weight-bold">{{ t('wire_transfer_deposit_fee') }}</h6>
                            <p>0.5%</p>
                            <h6 class="font-weight-bold">{{ t('wire_transfer_exchange_fee') }}</h6>
                            <p>0.0005 BTC</p>
                        </div>
                        <div class="col-md-7 text-center">
                            <h6 class="font-weight-bold">{{ t('send_crypto_transaction_limit') }}</h6>
                            <p>eq. $400</p>
                            <h6 class="font-weight-bold">{{ t('send_crypto_available_limit') }}</h6>
                            <p>eq. $39999</p>
                        </div>
                    </div>
                </div>

                <div class="buttons" style="margin-top: 97px">
                    <button class="btn btn-primary themeBtn btnWhiteSpace mt-4" type="button" id="nextBtn"
                            onclick="nextPrev(1)">Next
                    </button>
                </div>
            </form>

            <!-- Circles which indicates the steps of the form: -->
            <div style="text-align:center;margin-top:40px;">
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
                <span class="step"></span>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
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
            if (n == 3) {
                document.getElementsByClassName("buttons")[0].style.marginTop = "290px"
            } else {
                document.getElementsByClassName("buttons")[0].style.marginTop = "97px"
            }
        }

        function nextPrev(n) {
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
                return false;
            }
            // Otherwise, display the correct tab:
            showTab(currentTab);
        }

        function validateForm() {
            // This function deals with validation of the form fields
            var x, y, i, valid = true;
            x = document.getElementsByClassName("tab");
            y = x[currentTab].getElementsByTagName("input");
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
    </script>
@endsection
