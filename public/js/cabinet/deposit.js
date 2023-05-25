let rateMin = 0;
let minSendingAmount = 35;

$(function () {

    $('.feePercent').text(feePercent+'%');

    $('#chooseTemplate').on('change', function (e) {
        let templateId = $(this).val();
        getTemplateDataAjax(templateId)

    });

    $('.pdf-icon').on('click', function (e) {
        let from_currency = $('#currency_from').val();
        let to_type = $('#by').val();
        let file = from_currency+'_'+to_type;
        if (pdfFiles[file]) {
            let pdf = pdfFiles[file];
        }

       // getPdf(from_currency, to_type)
    });

    $('#currency_from').on('change', function (e) {
        let currencySymbol = $('option:selected', this).attr('data-symbol');
        $(".currency-symbol").text(currencySymbol);
    });

    $('#amount').on('keyup', function (e) {
        $('.invalid-amount').hide();
        let amount = $(this).val();
        if (!checkAmountFormat(amount)) {
            $('.invalid-amount').show();
            $(".totalAmount").text(0);
            $(".totalFee").text(0);
            $(".totalReceive").text(0);
        }else {
            calculateFee(amount, feePercent);
        }
    });
});

function getTemplateDataAjax(templateId) {
    $.ajax({
        url: API + "deposit-template/" + templateId,
        type: 'get',
    }).done(function (data) {
        $('#templateName').val(data.name),
            $('#accountHolder').val(data.account_holder),
            $('#accountNumber').val(data.account_number),
            $('#bank_name').val(data.bank_name),
            $('#bank_address').val(data.bank_address),
            $('#iban').val(data.iban),
            $('#swift').val(data.swift)
    }).fail(function (data, textStatus) {

    });
}

function getPdf(from, to) {
    console.log(from),
    console.log(to)
  /*  $.ajax({
        url: API + "deposit-template/" + templateId,
        type: 'get',
    }).done(function (data) {

    }).fail(function (data, textStatus) {

    });*/
}

function checkAmountFormat(amount) {
    if (amount < rateMin) {
        return false;
    }
    let reg = /^(\d+(,\d{0,2})*)(\.\d+)?$/;
    return reg.test(amount)
}

function calculateFee(amount, feePercent)
{
    amount = amount.replace(",", ".");
    $(".totalAmount").text(amount);
    let feeAmount = (amount*feePercent)/100;
    feeAmount = feeAmount < rateMin ? minSendingAmount : feeAmount;
    $(".totalFee").text(feeAmount);
    $(".totalReceive").text(amount-feeAmount);
}
