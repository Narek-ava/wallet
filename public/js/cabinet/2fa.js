var TwoFAService = {
    // value is current 2FA type
    setSingleStatus: function (type, value, any, fn) {
        $button = $('#2fa-' + type + '-button');
        var disFn;
        if (type == 'google') {
            disFn = TwoFAService.showModalGoogleDisable;
        } else {
            disFn = TwoFAService.sendEmailDisable;
        }
        if (value) {
            $button
                .html('Disable')
                .off('click')
                .on('click', disFn)
            ;
        } else {
            $button
                .html('Enable')
                .off('click')
                .on('click', fn)
            ;
        }

        $status = $('#2fa-' + type + '-status');
        if (value) {
            $status
                .html('Enabled')
                .addClass('font36boldblack');
        } else {
            $status.html('Disabled');
            if (any) {
                $status.addClass('font36boldblack');
            } else {
                $status.removeClass('font36boldblack');
            }
        }
    },

    getCode: function () {
        return {code: $('[name="2fa-confirm-code"]').val()}
    },

    showErrors: function (errors) {
        // @todo CodeDup showErrors
        var $list = $('.error-text-list');
        if (Array.isArray(errors)) {
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<p class="error-text">' + value + '</p>';
            });
            $list.html(errorsHtml);
        } else {
            $list.html(null);
        }
    },

    setStatuses: function () {
        TwoFAService.setSingleStatus(
            'google', two_fa_type == 1, two_fa_type != 0,
            TwoFAService.sendGoogleRegister
        );
        TwoFAService.setSingleStatus(
            'email',  two_fa_type == 2, two_fa_type != 0,
            TwoFAService.sendEmailEnable
        );
    },

    nonSubmit: function (event) {
        event.preventDefault();
        $('#2fa-confirm-button').trigger('click');
    },

    /**
     * @param fn | false or empty to unbind
     */
    bindConfirmButton: function (fn) {
        var btn = $('#2fa-confirm-button');
        btn.closest('form').off('submit').on('submit', TwoFAService.nonSubmit)
        btn.prop('type', 'button');
        if (!fn) {
            btn.off('click');
            return;
        }
        btn.on('click', fn);
    },

    show2FAConfirmModal: function (strings) {
        TwoFAService.showErrors();
        var $modal = $('#modal-2fa-operation-confirm');
        if (strings) {
            $modal.find('#2fa-confirm-header').text(strings.header || '');
            $modal.find('label[for="2fa-confirm-code"]').text(strings.label || '');
        }
        $modal.find('[name="2fa-confirm-code"]').val(null);
        $modal.modal('show');
    },

    hide2FAConfirmModal: function () {
        $('#modal-2fa-operation-confirm').modal('hide')
    },

    handleChangeStatus: function (new_two_fa_type) {
        TwoFAService.hide2FAConfirmModal();
        TwoFAService.bindConfirmButton(false);
        two_fa_type = new_two_fa_type;
        TwoFAService.setStatuses();
    },

    resultMessage: function (text) {
        var $modal = $('#modal-2fa-message');
        $modal.find('#2fa-message-text').text(text);
        $modal.modal('show');
    },

    /**
     * General error
     * @param text
     */
    resultFail: function (text) {
        var $modal = $('#modal-result-fail');
        if (!text) {
            text = 'Fail';
        }
        $modal.find('#modal-result-text').text(text);
        $modal.modal('show');
    },

    /**
     * Предполалось на случай необходимости выводить сообщение об успехе операции
     * @deprecated
     * @param text
     */
    resultSuccess: function (text) {
        alert('resultSuccess'); // если это сработало, искать неправомерное (@deprecated) использование resultSuccess
        return;

        // предыдущая версия
        $('#success').modal();
        var $modal = $('#modal-result-fail');
        if (!text) {
            text = 'Success';
        }
        $modal.find('#modal-result-text').text(text);
        $modal.modal('show');
    },



    sendEmailEnable: function () {
        $.ajax({
            url: API + '2fa-email-enable',
            type: 'post',
        }).done(function (jqXHR) {
            $('#2fa-email-switch').trigger('click');
            if (jqXHR.success !== true) {
                if (jqXHR.errors) {
                    TwoFAService.resultMessage(jqXHR.errors.enable_2fa_error);
                    return;
                }

                TwoFAService.resultFail(jqXHR.error);
                return;
            }

            TwoFAService.bindConfirmButton(TwoFAService.sendEmailEnableConfirm);
            TwoFAService.show2FAConfirmModal(jqXHR);
        }).fail(function (jqXHR, textStatus) {
            TwoFAService.resultFail(jqXHR.error);
        });
    },

    sendEmailEnableConfirm: function () {
        TwoFAService.showErrors();

        $.ajax({
            url: API + '2fa-email-enable-confirm',
            type: 'post',
            dataType: 'json',
            data: TwoFAService.getCode()
        }).done(function (jqXHR) {
            let status = $('#twoFaStatus');
            if (status) {
                status.html('Enabled');
            }
            if (jqXHR.success !== true) {
                TwoFAService.showErrors([jqXHR.errors.error_2fa_wrong_code]);
                return;
            }

            $('#2fa-email-switch').trigger('click');
            TwoFAService.handleChangeStatus(jqXHR.two_fa_type);
        }).fail(function (jqXHR, textStatus) {
            TwoFAService.showErrors([jqXHR.responseJSON.message]);
        });
    },

    sendEmailDisable: function () {
        $.ajax({
            url: API + '2fa-email-disable',
            type: 'post',
        }).done(function (jqXHR) {
            if (jqXHR.success !== true) {
                TwoFAService.resultFail(jqXHR.error);
                return;
            }
            TwoFAService.bindConfirmButton(TwoFAService.sendEmailDisableConfirm);
            TwoFAService.show2FAConfirmModal(jqXHR);
        }).fail(function (jqXHR, textStatus) {
            TwoFAService.resultFail(jqXHR.error);
        });
    },

    sendEmailDisableConfirm: function () {
        TwoFAService.showErrors();

        $.ajax({
            url: API + '2fa-email-disable-confirm',
            type: 'post',
            dataType: 'json',
            data: TwoFAService.getCode()
        }).done(function (jqXHR) {
            let status = $('#twoFaStatus');
            if (status) {
                status.html('Disabled');
            }
            if (!jqXHR.success) {
                TwoFAService.showErrors([jqXHR.errors.error_2fa_wrong_code]);
                return;
            }

            TwoFAService.handleChangeStatus(jqXHR.two_fa_type);
        }).fail(function (jqXHR, textStatus) {
            TwoFAService.showErrors([jqXHR.responseJSON.message]);
        });
    },



    showModalGoogleDisable: function () {
        TwoFAService.show2FAConfirmModal();
        TwoFAService.bindConfirmButton(TwoFAService.sendGoogleDisable);
    },

    sendGoogleRegister: function () {
        TwoFAService.showErrors();

        $.ajax({
            url: API + '2fa-google-register',
            type: 'post',
        }).done(function (jqXHR) {
            $('#2fa-google-switch').trigger('click');
            if (jqXHR.success !== true) {
                if (jqXHR.errors) {
                    TwoFAService.resultMessage(jqXHR.errors.enable_2fa_error);
                    return;
                }

                TwoFAService.resultFail(jqXHR.error);
                return;
            }

            $('#2fa-google-secret').html(jqXHR.secret);
            $('#2fa-google-qr-image').html(jqXHR.qrImage);
            $('#2fa-google-enable-confirm-code').val(null);
            $('#modal-2fa-google-register').modal('show');

        }).fail(function (jqXHR, textStatus) {
            TwoFAService.showErrors([jqXHR.responseJSON.message]);
        });
    },

    sendGoogleEnableConfirm: function () {
        //* @note здесь потенциальная засада - сделано, исхлдя из того, что разные 2FA-попапы никогда не показаны одновременно
        TwoFAService.showErrors();

        $.ajax({
            url: API + '2fa-google-confirm',
            type: 'post',
            dataType: 'json',
            data: {code: $('#2fa-google-enable-confirm-code').val()},
        }).done(function (jqXHR) {
            let status = $('#twoFaStatus');
            if (status) {
                status.html('Enabled');
            }
            if (!jqXHR.success) {
                TwoFAService.showErrors([jqXHR.errors.error_2fa_wrong_code]);
                return;
            }
            $('#modal-2fa-google-register').modal('hide');
            $('#2fa-google-switch').trigger('click');
            TwoFAService.handleChangeStatus(jqXHR.two_fa_type);
        }).fail(function (jqXHR, textStatus) {
            if(jqXHR.responseJSON && jqXHR.responseJSON.message) {
                TwoFAService.showErrors([jqXHR.responseJSON.message]);
            } else {
                $('#modal-2fa-google-register').modal('hide');
                TwoFAService.resultFail(jqXHR.error);
            }
        });
    },

    sendGoogleDisable: function () {
        TwoFAService.showErrors();

        $.ajax({
            url: API + '2fa-google-disable',
            type: 'post',
            dataType: 'json',
            data: TwoFAService.getCode()
        }).done(function (jqXHR) {
            let status = $('#twoFaStatus');
            if (status) {
                status.html('Disabled');
            }
            if (!jqXHR.success) {
                TwoFAService.showErrors([jqXHR.errors.error_2fa_wrong_code]);
                return;
            }

            TwoFAService.handleChangeStatus(jqXHR.two_fa_type);
        }).fail(function (jqXHR, textStatus) {
            TwoFAService.showErrors([jqXHR.responseJSON.message]);
        });
    },

};

$(function () {
    TwoFAService.setStatuses();
    $('#2fa-google-enable-confirm-button').on('click', TwoFAService.sendGoogleEnableConfirm);
    $('#2fa-google-regenerate-button').on('click', TwoFAService.sendGoogleRegister);
});
