$(document).ready(function () {
    $('body').delegate('.regenerateCaptchaButton', 'click', function () {
        $(this).attr('disabled', true);
        $.ajax({
            url: '/get-captcha',
            type: 'get'
        }).done((jqXHR) => {
            $(this).attr('disabled', false);
            if (jqXHR.captcha_image) {
                $('.captcha-image').find('img').attr('src', jqXHR.captcha_image)
            }
        });
    })
});
