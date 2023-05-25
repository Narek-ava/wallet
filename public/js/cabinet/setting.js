function showErrors(errors) {
    $.each(errors, function (key, value) {
        if (Array.isArray(value)) {
            if (key.indexOf('beneficial_owners') !== -1) {
                $('p[data-error-target="beneficial_owners"]').text(value[0]).show()
            } else {
                $('p[data-error-target="ceos"]').text(value[0]).show()
            }
        } else {
            $('p[data-error-target="' + key + '"]').text(value).show()
        }
    });
}

function changeSettingAjax(_this, formUrl) {
    $('.error-text').hide()
    $.ajax({
        url: API + formUrl,
        type: 'patch',
        data: _this.serialize()
    }).done(function (data) {
        _this.find('.errors-alert').hide();
        $('#successText').html(data.success)
        $('#success').modal() // show success modal
        _this.find('.disabled_el').attr('disabled', true);
        _this.closest(".modal").modal("hide");
        let  refreshTimeout;
        if (data.redirect) {
            refreshTimeout = setTimeout(function(){
              window.location.replace(data.redirect)
          }, 5000);
        }
        window.onclick = function () {
                if (data.redirect) {
                    clearTimeout(refreshTimeout);
                    window.location.replace(data.redirect);
                }
        };

    }).fail(function (data) {
        showErrors(data.responseJSON.errors);
    });
}

$(function () {
    $('.error-text').hide()
    $('.change_btn').click(function (e) {
        e.preventDefault();
        $(this).closest('form').find('.disabled_el').removeAttr('disabled');
    });


    $('#personal-form').on('submit', function (e) {
        e.preventDefault();
        changeSettingAjax($(this), 'settings-update');
    });

    $('#personal-corporate-form').on('submit', function (e) {
        e.preventDefault();
        changeSettingAjax($(this), 'settings-corporate-update');
    });

    $('#updateWebhookUrl').on('submit', function (e) {
        e.preventDefault()

        let token = $(this).data('token')
        $.ajax({
            url: $('#updateWebhookUrl').attr('action'),
            type: 'post',
            data: {
                _token: token,
                webhook_url: $('input[name="webhook_url"]').val()
            },
            success: (data) => {
                $(this).find('.error-text').attr('hidden', true)

                let secretKey = data.secretKey
                if (secretKey) {
                    $('#textSecretKey').val(secretKey)
                    $('.secret-key-btn').text(secretKey)
                }
                $('.secretKeyContainer').attr('hidden', false)


                $('#successText').text(data.success)
                $('#success').modal() // show success modal
            },
            error: () => {
                $(this).find('.error-text').attr('hidden', false)
            }
        })
    })

    $('.webhook-params-details').on('click', function () {
        let detailsContainer = $('.webhookParamsDetailsContainer');
        if (detailsContainer.css('display') === 'none') {
            detailsContainer.show()
        } else {
            detailsContainer.hide()
        }
    })
});

function copyText(id) {
    var copyText = document.getElementById('text' + id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
}

