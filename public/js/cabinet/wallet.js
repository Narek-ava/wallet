$(document).ready(function () {
    if ($('.payment-type-swift').hasClass('disabled')) {
        $('.payment-type-swift').parent().attr("title", "Переводы в SWIFT в данный момент не доступны.");
    }
    if ($('.payment-type-sepa').hasClass('disabled')) {
        $('.payment-type-sepa').parent().attr("title", "Переводы в SWIFT в данный момент не доступны.");
    }

    $('[data-toggle="tooltip"]').tooltip();
})

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
        document.getElementsByClassName("buttons")[0].style.marginTop = "290px"

        $.ajax({
            type: "POST",
            url: API + 'providers-by-country',
            data: {
                "_token": "{{ csrf_token() }}",
                "country": $('.country').val(),
                "currency": $('.currency').val(),
                'fiatType': $('#fiatType').val(),
            },
            success: function (response) {
                $('#providerContainer .provider_name').val('');
                let provider;
                for (let i = 0; i < response.providers.length; i++) {
                    $('#providerContainer .provider_name').val('');
                    provider = response.providers[i];
                    console.log(provider)
                    $('#providerContainer .provider_name').val(provider.name);
                    $('#providerContainer .provider_name_text').html(provider.name);
                    $('#providerContainer .account_beneficiary').html(provider.account.wire.account_beneficiary);
                    $('#providerContainer .beneficiary_address').html(provider.account.wire.beneficiary_address);
                    $('#providerContainer .iban_eur').html();
                    $('#providerContainer .swift_bic').html();
                    $('#providerContainer .bank_name').html(provider.name);
                    $('#providerContainer .bank_address').html(provider.name);
                    $('#providerContainer .purpose_transfer').html(provider.name);

                    $('#form_providers').append($('#providerContainer').html());
                }
                $('#providerContainer .provider_name').val('');
            }
        });
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
