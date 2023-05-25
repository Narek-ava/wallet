function showTrxBankDetails($id) {
    $.ajax({
        type: "get",
        url: "/backoffice/transactions/get-transaction-details",
        data: {
            'transaction_id': $id,
        },
        success: function (response) {
            let trx = response.transaction;
            let exchangeFee = null;

            if(trx.operation.operation_type == 1 || trx.operation.operation_type == 2 || trx.operation.operation_type == 8 || trx.operation.operation_type == 9){
                exchangeFee = response.exchangeFee ?? exchangeFee;
            }

            //Exchange trx
            if (response.transaction.type == 3) {
                $('#transactionDetail .exchange-rate').attr('hidden', false).val(trx.exchange_rate ?? '');
                $('#transactionDetail .to-currency').attr('hidden', false).val(trx.to_account.currency ?? '');
                $('#transactionDetail .to-fee-percent').attr('hidden', 'hidden');
                $('#transactionDetail .to-fee').attr('hidden', 'hidden');
                $('#transactionDetail .to-fee-min').attr('hidden', 'hidden');
                $('#transactionDetail .exchange-fee-percent').text('Exchange fee %');
                $('#transactionDetail .exchange-fee').text('Exchange fee');
                $('#transactionDetail .from-fee-min').text('Exchange fee minimum');
                $('#transactionDetail .exchange-fee').val(exchangeFee ?? '');
                $('#transactionDetail .from-fee-minimum').addClass('exchangeCaseStyle');
                $('#transactionDetail .cryptocurrency-amount').attr('hidden', false);
                $('.to-amount').val(trx.recipient_amount)
            }
            //Crypto trx
            else if(response.transaction.type == 2){
                $('#transactionDetail .to-fee-percent').attr('hidden', 'hidden');
                $('#transactionDetail .to-fee').attr('hidden', 'hidden');
                $('#transactionDetail .to-fee-min').attr('hidden', 'hidden');
                $('#transactionDetail .exchange-fee').val(trx.from_commission ? trx.from_commission.fixed_commission : '');
                $('#transactionDetail .from-fee-min').text('Blockchain fee');
                $('#transactionDetail .exchange-fee-min').val(trx.from_commission ? trx.from_commission.blockchain_fee : '');
                $('#transactionDetail .exchange-fee-percent').text('From fee %');
                $('#transactionDetail .exchange-fee').text('From fee');
                $('#transactionDetail .from-fee-minimum').addClass('exchangeCaseStyle');
                $('#transactionDetail .crypto-address').attr('hidden', false);
                if(response.toCryptoAccountDetail) {
                    $('.crypto-address').val(response.toCryptoAccountDetail.address ?? '');
                }
            }
            else{
                $('#transactionDetail .exchange-rate').attr('hidden', 'hidden');
                $('#transactionDetail .to-currency').attr('hidden', 'hidden');
                $('#transactionDetail .exchange-api').attr('hidden', 'hidden');
                $('#transactionDetail .to-fee-percent').attr('hidden', false);
                $('#transactionDetail .to-fee').attr('hidden', false);
                $('#transactionDetail .to-fee-min').attr('hidden', false);
                $('#transactionDetail .exchange-fee-min').val(trx.from_commission ? trx.from_commission.min_commission : '');
                $('#transactionDetail .from-fee-min').text('From fee  minimum');
                $('#transactionDetail .exchange-fee-percent').text('From fee %');
                $('#transactionDetail .exchange-fee').text('From fee');
                $('#transactionDetail .from-fee-minimum').removeClass('exchangeCaseStyle');
                $('#transactionDetail .exchange-fee').val(trx.from_commission ? trx.from_commission.fixed_commission : '');
            }

            $('#transactionDetail .datepicker').val(trx.commit_date ?? '');
            $('#transactionDetail .transaction-type').val(response.trxType ?? '');
            $('#transactionDetail .from-type').val(response.fromType ?? '');
            $('#transactionDetail .from-account').val(trx.from_account.name ?? '');
            $('#transactionDetail .to-type').val(response.toType ?? '');
            $('#transactionDetail .to-account').val(trx.to_account.name ?? '');
            $('#transactionDetail .from-currency').val(trx.from_account.currency ?? '');
            $('#transactionDetail .from-amount').val(trx.trans_amount ?? '');
            if (response.cryptoToCryptoDetails) {
                $('#transactionDetail .exchange-fee-percent').val(response.cryptoToCryptoDetails.incomingFee ?? '');
            } else {
                $('#transactionDetail .exchange-fee-percent').val(trx.from_commission ? trx.from_commission.percent_commission : '');
            }
            $('#transactionDetail .to-fee-percent').val(trx.to_commission ? trx.to_commission.percent_commission : '');
            $('#transactionDetail .to-fee').val(trx.to_commission ? trx.to_commission.fixed_commission : '');
            $('#transactionDetail .to-fee-min').val(trx.to_commission ? trx.to_commission.min_commission : '');
        }
    })
}

