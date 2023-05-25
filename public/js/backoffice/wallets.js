function copyText(id) {
    var copyText = document.getElementById('text' + id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}
//
// function getWithdrawFee(cryptoAccountId) {
//     $('.withdraw-amount-text').text($('.withdraw-amount').val());
//
//     $.ajax({
//         type: "POST",
//         url: 'withdraw-crypto/get-withdraw-fee',
//         data: {
//             "_token": $('meta[name="csrf-token"]').attr('content'),
//             "cryptoAccountId": cryptoAccountId,
//             "cProfileId" : "{{ $profile->id }}",
//             "amount": $('.withdraw-amount').val(),
//         },
//         success: function (response) {
//             $('.withdraw-fee').text(response.result);
//         }
//     });
// }
