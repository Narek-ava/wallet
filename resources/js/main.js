setInterval(function () {
    $.ajax({
        url: '/cabinet/get-notification',
        success:function (data) {
            $('#notificationSection').replaceWith(data.replaceAll("<br>", ""));
        }
    })
}, 10000);
