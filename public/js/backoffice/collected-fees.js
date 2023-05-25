$(document).ready(function () {
    $('.openWithdrawModalBtn').on('click', function () {
        $('.transactionForm').hide()
        let currency = $(this).data('currency');

        $('.transaction-checkbox').prop('checked', true);
        let form = $('#collectedFeeWithdraw' + currency);
        form.find('.currency').val(currency)

        form.show();
        let amount = $(this).data('withdrawal-amount')
        $('input[name="amount"]').val(amount)
    })

    $('.transaction-checkbox').on('change', function () {
        let amount = 0;
        let form = $(this).closest('form');
        form.find('.transaction-checkbox:checked').each(function () {
            amount += parseFloat($(this).data('amount'));
        });

        let amountInput = form.find('input[name="amount"]');
        let currency = form.find('input[name="currency"]').val();
        amountInput.val(amount);

        let url = amountInput.data('get-fee');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: url,
            type:'post',
            data: {
                'amount': amount,
                'currency': currency,
            },
            success: (response) => {
                $('.providerFeeAmount').text(response.feeAmount)
            },
            error: () => {

            }
        })

    })


    $('#projectId').on('change', function () {
        let projectId = $(this).val();
        let url = $(this).data('url')

        window.location.href = url + '?project_id=' + projectId

    })
})
