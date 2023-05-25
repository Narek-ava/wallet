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
        // Put the currency, which was selected in the first step
        $('#bank_currency').empty();
        let currency = $('.currency').val();
        $('#bank_currency').append('<option value="' + currency + '" class="">' + currency + '</option>');

        // AJax for getting the existing bank accounts
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-bank-templates',
            data: {
                "currency": $('.currency').val(),
                "accountType": $("input[type='radio'][name='type']:checked").val(),
                'c_profile_id': $('.c_profile_id').val(),
            },
            success: function (response) {
                $('#bank_template option').attr('hidden', 'hidden');
                $('#bank_template').append('<option hidden value="">' + 'Select...' + '</option>');
                for (let i = 0; i < response.accounts.length; i++) {
                    $('#bank_template').append('<option value="' + response.accounts[i].id + '" class="">' + response.accounts[i].name + '</option>');
                }
                $('#bank_template').append('<option value="0">' + 'New' + '</option>');

            }
        });


        $.ajax({
            type: "POST",
            url: API + 'get-limits',
            data: {
                c_profile_id: $('.c_profile_id').val(),
                toCurrency: $('.currency').val(),
                amount: $('.amount').val(),
                payment_provider_id: $('#providerContainer .provider_name').val(),
                provider_account_id: $('#providerContainer .provider_account_id').val(),
                operationType: $("input[type='radio'][name='type']:checked").val(),
                fromCurrency: $('.coin').val(),
                wireType: $('#operation_wire_type').val() ?? 8 // OperationType::WITHDRAW_WIRE ToDo

            },
            success: function (response) {
                let count = $('.blockchain-fee').data('count');
                $('.available-limit').text(response.availableAmountForMonth);
                $('.withdraw-fee').text(( ( response.commissions && response.commissions.percent_commission) ?  response.commissions.percent_commission : 0) + '%');

                let blockchainFee = response.liquidityProviderFee;
                if (response.blockChainFee) {
                    blockchainFee += (response.blockChainFee * count)
                }

                blockchainFee = blockchainFee.toFixed(8);

                $('.blockchain-fee').text( blockchainFee + ' ' + (response.blockChainFeeCurrency ?? ''));
                $('.transaction-limit').text(response.transactionLimit ?? '-');
                // $('.exchange-fee').text((response.exchangeCommissions.percent_commission ?? 0) + '%');


                if (response.message == 'failed') {
                    var string = window.location.href;
                    var result = string.split("/").pop();
                    window.location.href = '/cabinet/wallets/' + result + '?message=1';
                } else {
                    var message = [];
                    var amount = $('.amount').val();
                    var amountInFiat = $('.expected-amount').val();

                    if (parseFloat(amountInFiat) < parseFloat(response.limits.transaction_amount_min)
                        || (response.limits.transaction_count_daily_max && response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)
                        || (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1)) {
                        nextPrev(-2);

                        if (amountInFiat < parseFloat(response.limits.transaction_amount_min)) {
                            message.push("Minimum amount is " + response.limits.transaction_amount_min + ' ' + response.commissions.currency);
                        } else if (response.limits.transaction_count_daily_max && (response.transactionsPerDay >= response.limits.transaction_count_daily_max - 1)) {
                            message.push("Daily limit is over");
                        } else if (response.limits.transaction_count_monthly_max && response.transactionsPerMonth >= response.limits.transaction_count_monthly_max - 1) {
                            message.push("Monthly limit is over");
                        }
                    }

                    $('.limit-fail-text').text(message);

                    if (response.limits.monthly_amount_max < response.transactionsAmountPerMonth || response.limits.monthly_amount_max < amountInFiat) {
                        $('.complianceLevel').removeClass('d-none').addClass('d-block');
                    }
                    $('.available-limit').text(response.availableAmountForMonth);

                }
            }
        });
    }
    if (n == 3) {
        if ($('.country').val() == 0 || $('.currency').val() == 0) {
            $(".nextBtn").addClass('disabled');
        }

        //get providers by countries
        $('.reference-number').addClass('d-none').removeClass('d-block')

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'providers-by-country',
            data: {
                "country": $('.country').val(),
                "currency": $('.currency').val(),
                "accountType": $("input[type='radio'][name='type']:checked").val(),
                'fiatType': $('#fiatType').val(),
            },
            success: function (response) {
                $('.no-account-text').removeClass('d-none').addClass('d-block').text('');
                $('.no-providers-text').addClass('d-none').removeClass('d-block');

                if (response.accountExist == false) {
                    nextPrev(-1);
                    $('.no-providers-text').removeClass('d-none').addClass('d-block');
                } else {
                    for (let i = 0; i < response.providers.length; i++) {
                        let provider = response.providers[i];
                        if (provider.accounts.length != 0) {
                            for (let j = 0; j < provider.accounts.length; j++) {
                                let account = provider.accounts[j];
                                if (account.wire) {
                                    $('#providerContainer .provider_name').val(provider.id);
                                    $('#providerContainer .provider_account_id').val(account.id);
                                    $('#providerContainer .provider_name_text').html(`${provider.name} - ${account.name}`);
                                    $('#providerContainer .account_beneficiary').html(account.wire.account_beneficiary ?? '-');
                                    $('#providerContainer .beneficiary_address').html(account.wire.beneficiary_address ?? '-');

                                    $('.swift-details').attr('hidden', true)

                                    let accountType = $('.account_type').val();
                                    if(account.account_type == accountType) {
                                        $('.swift-details').removeAttr('hidden')
                                        $('#providerContainer .correspondent_bank').html(account.wire.correspondent_bank ?? '-');
                                        $('#providerContainer .correspondent_bank_swift').html(account.wire.correspondent_bank_swift ?? '-');
                                        $('#providerContainer .intermediary_bank').html(account.wire.intermediary_bank ?? '-');
                                        $('#providerContainer .intermediary_bank_swift').html(account.wire.intermediary_bank_swift ?? '-');
                                    }

                                    if(!response.isTypeSwift) {
                                        $('#providerContainer .iban_eur').html(account.wire.iban ?? '-');
                                        $('.iban_eur_text').removeClass('d-none')
                                        $('.iban_eur_text').addClass('d-block')
                                    }else {
                                        $('.iban_eur_text').removeClass('d-block')
                                        $('.iban_eur_text').addClass('d-none')
                                    }                                    $('#providerContainer .swift_bic').html(account.wire.swift ?? '-');
                                    $('#providerContainer .bank_name').html(account.wire.bank_name ?? '-');
                                    $('#providerContainer .bank_address').html(account.wire.bank_address ?? '-');
                                    $('#form_providers').addClass('providerContainer' + provider.name);
                                    $('#form_providers').append($('#providerContainer').html());
                                    $('.reference-number').addClass(account.id);
                                    $('.time-to-found').text(account.wire.time_to_found + ' days');
                                }
                            }
                        }
                    }
                    $('#providerContainer .provider_name').val('');
                    $('#providerContainer').empty();
                    $('#form_providers').find('.bank-details').first().click();
                }


            }
        });
    }
}

let createButton = false;

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


        $('#withdraw_wire_form').trigger('submit');
        //document.getElementById("withdraw_wire_form").submit();
        createButton = true;
        return false;
    }
    // Otherwise, display the correct tab:
    showTab(currentTab);
    if (currentTab == 3) {
        $('#nextBtn').addClass('createButton');
    }

    if ($('.validationError').hasClass('d-block')) {
        $('.validationError').removeClass('d-block');
        $('.validationError').addClass('d-none')
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
    console.log(x[n].className)
    x[n].className += " active";
}

function removeError($class) {
    $('.' + $class).removeClass('invalid');
    $('.no-providers-text').addClass('d-none');
}

function showRefNum() {
    $('.reference-number').removeClass('d-none').addClass('d-block')
}

function getExpectedAmount() {
    let amountTo = $('.expected-amount');
    let amount = $('.amount').val();
    let from = $('.coin').val();
    let to = $('.currency').val();
    let expectedRate = $('.expected-rate');

    if (from && to && amount) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: API + 'get-rate-crypto-fiat',
            type: 'post',
            data: { from, to, amount },
            success: function (data) {
                amountTo.val(data);
                expectedRate.text(Math.round(data/amount) + ' ' + to);
                amountTo.removeClass("invalid");
            }
        })
    }

}


function displayBankTemplate() {
    $('.country').empty();
    $('#bank_currency').empty();

    if ($('#bank_template').val() == 0) {
        $('.country').val('');
        $('#bank_currency').val('');
        $('.iban').val('');
        $('.swift').val('');
        $('.account-holder').val('');
        $('.account-number').val('');
        $('.bank-name').val('');
        $('.bank-address').val('');
        $('.template-name').val('');
        $('.bank-template-name').removeClass('d-none');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-available-countries',
            data: {
                "currency": $('.currency').val(),
                "accountType": $("input[type='radio'][name='type']:checked").val(),
            },
            success: function (response) {
                let object = response.availableCountries;
                for (const property in object) {
                    $('.country').append('<option value="' + property + '" class="">' + object[property] + '</option>');
                }

                for (let i = 0; i < response.currencies.length; i++) {
                    $('#bank_currency').append('<option value="' + response.currencies[i] + '" class="">' + response.currencies[i] + '</option>');
                }
            }
        });
    } else {
        $('#bank_currency').empty();
        $('.template-name').val('1');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
            url: API + 'get-bank-template',
            data: {
                "account_id": $('#bank_template').val(),
            },
            success: function (response) {
                $('.country').append('<option selected value="' + response.account.country + '">' + response.country + '</option>');
                $('#bank_currency').append('<option selected value="' + response.account.currency + '">' + response.account.currency + '</option>');
                $('.iban').val(response.wireAccountDetail.iban ?? '-');
                $('.swift').val(response.wireAccountDetail.swift ?? '-');
                $('.account-holder').val(response.wireAccountDetail.account_beneficiary ?? '-');
                $('.account-number').val(response.wireAccountDetail.account_number ?? '-');
                $('.bank-name').val(response.wireAccountDetail.bank_name ?? '-');
                $('.bank-address').val(response.wireAccountDetail.bank_address ?? '-');
                $('.bank-template-name').addClass('d-none');
            }
        });

    }
}
