$(document).ready(function () {
    let paymentFormUrl = 'payment-form/get-data/';
    let accountHeader = $('#accountHeader');
    let cryptoAccountHeader = $('#cryptoAccountHeader');
    let formId = 0;

    let paymentTypeSelectTemplate = '<select name="paymentFormType" class="col-12">';
    let paymentTypeInputTemplate = '<div class="row col-12"><input readonly value="##paymentTypeName##"/><input hidden name="paymentFormType" value="##paymentType##"/></div>';

    let projectSelectTemplate = '<select name="paymentFormProject" id="paymentFormProject" style="width:200px;padding-right: 50px; margin-left: 10px">';
    let projectInputTemplate = '<div><input hidden name="paymentFormProject" value="##paymentFormProjectId##"/><input readonly value="##projectName##"/></div>';

    let cardProviderSelectTemplate = '<select name="paymentFormCardProvider" style="min-width: 170px; padding-right: 20px;">';
    let liquidityProviderSelectTemplate = '<select name="paymentFormLiquidityProvider" style="min-width: 170px; padding-right: 20px;">';
    let walletProviderSelectTemplate = '<select name="paymentFormWalletProvider" style="min-width: 170px; padding-right: 20px;">';
    let merchantSelectTemplate = '<select name="paymentFormMerchant"  class="paymentFormMerchantSelect col-12">';
    let rateSelectTemplate = '<select name="paymentFormRate"  class="paymentFormRateSelect col-12">';


    $('.editMerchantForm').click(function () {
        accountHeader.text(accountHeader.data('update-text'))
        formId = $(this).data('form-id');
        resetForm();
        $.ajax({
            url: paymentFormUrl + formId,
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                fillPaymentFormData(data)
            },
        });
    })

    $('.editCryptoMerchantForm').click(function () {
        cryptoAccountHeader.text(cryptoAccountHeader.data('update-text'))
        formId = $(this).data('form-id');
        resetForm();
        $.ajax({
            url: paymentFormUrl + formId,
            method: 'GET',
            dataType: 'json',
            success: (data) => {
                fillCryptoPaymentFormData(data)
            },
        });
    })


    $('#createPaymentFormButton, #createCryptoPaymentFormButton').click(function () {
        accountHeader.text(accountHeader.data('create-text'))
        cryptoAccountHeader.text(cryptoAccountHeader.data('create-text'))
        resetForm();
        renderCreateModal();
    })

    $('form[name="paymentForm"]').submit(function (e) {
        e.preventDefault();
        let submitButton = $(this).children().find('button[type="submit"]');
        submitButton.attr('disabled', true);
        let action = submitButton.attr('action');
        let url = $(this).data(action + '-action');
        if (action === 'update') {
            url = url.replace('##formId##', submitButton.attr('data-form-id'))
        }
        let sendData = formSendData();

        cleanErrors();
        $.ajax({
            url: url,
            method: 'POST',
            data: sendData,
            dataType: 'json',
            success: (data) => {
                window.location.reload();
            },
            error: (error) => {
                displayErrors(error.responseJSON.errors);
                submitButton.attr('disabled', false);
            }
        });
    })


    $('form[name="paymentCryptoForm"]').submit(function (e) {
        e.preventDefault();
        let submitButton = $(this).children().find('button[type="submit"]');
        submitButton.attr('disabled', true);
        let action = submitButton.attr('action');
        let url = $(this).data(action + '-action');
        if (action === 'update') {
            url = url.replace('##formId##', submitButton.attr('data-form-id'))
        }
        let sendData = formCryptoSendData();

        cleanErrors();
        $.ajax({
            url: url,
            method: 'POST',
            data: sendData,
            cache: false,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            success: (data) => {
                window.location.reload();
            },
            error: (error) => {
                displayErrors(error.responseJSON.errors);
                submitButton.attr('disabled', false);
            }
        });
    })

    function fillPaymentFormData(formData) {
        let targetModal = $('#paymentForm');

        if (formData.hasOperations) {
            let html = paymentTypeInputTemplate.replace('##paymentTypeName##', formData.paymentTypeName).replace('##paymentType##', formData.paymentFormType);
            targetModal.children().find('.merchantTypeContainer').html(html)
            containerControl(formData.paymentFormType)
            let htmlProject = projectInputTemplate.replace('##projectName##', formData.projectName).replace('##paymentFormProjectId##', formData.projectId);
            targetModal.children().find('.projectForPaymentForm').html(htmlProject)
        } else {
            targetModal.children().find('.merchantTypeContainer').html(getPaymentTypeSelectBox())
            bindEvents()
            targetModal.children().find('select[name="paymentFormType"]').val(formData.paymentFormType)
            containerControl()
            targetModal.children().find('.projectForPaymentForm').html(getSelectBox(projectSelectTemplate, AvailableProjects, 'Select Project',formData.projectId ?? null))
        }
        targetModal.children().find('input[name="paymentFormName"]').val(formData.name)
        targetModal.children().find('select[name="paymentFormStatus"]').val(formData.status)
        // targetModal.children().find('select[name="paymentFormMerchant"]').val(formData.paymentFormMerchant)
        // targetModal.children().find('select[name="paymentFormRate"]').val(formData.paymentFormRate)
        let clientInsideForm = $('.merchantTypeContainer').data('client-inside-form');
        if(formData.paymentFormType != clientInsideForm) {
            targetModal.children().find('select[name="paymentFormKYC"]').val(formData.paymentFormKYC)
        }
        targetModal.children().find('.cardProviders').html(getSelectBox(cardProviderSelectTemplate, formData.cardProviders, 'Select Provider',formData.paymentFormCardProvider ?? null))
        targetModal.children().find('.walletProviders').html(getSelectBox(walletProviderSelectTemplate, formData.walletProviders, 'Select Provider',formData.paymentFormWalletProvider ?? null))
        targetModal.children().find('.liquidityProviders').html(getSelectBox(liquidityProviderSelectTemplate, formData.liquidityProviders, 'Select Provider',formData.paymentFormLiquidityProvider ?? null))
        targetModal.children().find('.merchantContainer').html(getSelectBox(merchantSelectTemplate, formData.merchants, 'Select Merchant',formData.paymentFormMerchant ?? null))
        targetModal.children().find('.rateContainer').html(getSelectBox(rateSelectTemplate, formData.rates, 'Select Rate',formData.paymentFormRate ?? null))
        // targetModal.children().find('select[name="paymentFormCardProvider"]').val(formData.paymentFormCardProvider)
        // targetModal.children().find('select[name="paymentFormWalletProvider"]').val(formData.paymentFormWalletProvider)
        // targetModal.children().find('select[name="paymentFormLiquidityProvider"]').val(formData.paymentFormLiquidityProvider)
        targetModal.children().find('button[type="submit"]').attr('action', 'update')
        targetModal.children().find('button[type="submit"]').attr('data-form-id', formData.id)

        formData.currencyAddresses.forEach((item) => {
            targetModal.children().find('input[name="' + item.name + '"]').val(item.value)
        })
    }

    function fillCryptoPaymentFormData(formData) {
        let targetModal = $('#paymentCryptoForm');
        if (formData.hasOperations) {
            let html = paymentTypeInputTemplate.replace('##paymentTypeName##', formData.paymentTypeName).replace('##paymentType##', formData.paymentFormType);
            targetModal.children().find('.merchantTypeContainer').html(html)
            let htmlProject = projectInputTemplate.replace('##projectName##', formData.projectName).replace('##paymentFormProjectId##', formData.projectId);
            targetModal.children().find('.projectForPaymentForm').html(htmlProject)
         } else {
            targetModal.children().find('.merchantTypeContainer').html(getPaymentTypeSelectBox())
            targetModal.children().find('.projectForPaymentForm').html(getSelectBox(projectSelectTemplate, AvailableProjects, 'Select Project',formData.projectId ?? null))
        }



        targetModal.children().find('input[name="paymentFormName"]').val(formData.name)
        targetModal.children().find('input[name="paymentFormWebSiteUrl"]').val(formData.paymentFormWebSiteUrl)
        targetModal.children().find('input[name="paymentFormDescription"]').val(formData.paymentFormDescription)
        targetModal.children().find('input[name="paymentFormIncomingFee"]').val(formData.paymentFormIncomingFee)
        targetModal.children().find('select[name="paymentFormStatus"]').val(formData.status)
        // targetModal.children().find('select[name="paymentFormMerchant"]').val(formData.paymentFormMerchant)
        // targetModal.children().find('select[name="paymentFormWalletProvider"]').val(formData.paymentFormWalletProvider)
        targetModal.children().find('.walletProviders').html(getSelectBox(walletProviderSelectTemplate, formData.walletProviders, 'Select Provider',formData.paymentFormWalletProvider ?? null))
        targetModal.children().find('.liquidityProviders').html(getSelectBox(liquidityProviderSelectTemplate, formData.liquidityProviders, 'Select Provider',formData.paymentFormLiquidityProvider ?? null))
        targetModal.children().find('.cardProviders').html(getSelectBox(cardProviderSelectTemplate, formData.cardProviders, 'Select Provider',formData.paymentFormCardProvider ?? null))
        targetModal.children().find('.merchantContainer').html(getSelectBox(merchantSelectTemplate, formData.merchants, 'Select Merchant',formData.paymentFormMerchant ?? null))


        targetModal.children().find('button[type="submit"]').attr('action', 'update')
        targetModal.children().find('button[type="submit"]').attr('data-form-id', formData.id)
        targetModal.children().find('#updateMerchantLogo').attr('src', '/cratos.theme/images/' + formData.paymentFormMerchantLogo).show()

    }

    function renderCreateModal() {
        let targetModal = $('#paymentForm');
        let cryptoModal = $('#paymentCryptoForm');
        targetModal.children().find('button[type="submit"]').attr('action', 'create')
        cryptoModal.children().find('button[type="submit"]').attr('action', 'create')
        targetModal.children().find('.merchantTypeContainer').html(getPaymentTypeSelectBox())


        targetModal.children().find('.projectForPaymentForm').html(getSelectBox(projectSelectTemplate, AvailableProjects, 'Select Project'))
        cryptoModal.children().find('.projectForPaymentForm').html(getSelectBox(projectSelectTemplate, AvailableProjects, 'Select Project'))

        targetModal.children().find('.walletProviders').html(getSelectBox(walletProviderSelectTemplate, [], 'Select Provider'))
        cryptoModal.children().find('.walletProviders').html(getSelectBox(walletProviderSelectTemplate, [], 'Select Provider'))
        targetModal.children().find('.liquidityProviders').html(getSelectBox(liquidityProviderSelectTemplate, [], 'Select Provider'))
        cryptoModal.children().find('.liquidityProviders').html(getSelectBox(liquidityProviderSelectTemplate, [], 'Select Provider'))
        targetModal.children().find('.cardProviders').html(getSelectBox(cardProviderSelectTemplate, [], 'Select Provider'))
        cryptoModal.children().find('.cardProviders').html(getSelectBox(cardProviderSelectTemplate, [], 'Select Provider'))
        targetModal.children().find('.merchantContainer').html(getSelectBox(merchantSelectTemplate, [], 'Select Merchant'))
        cryptoModal.children().find('.merchantContainer').html(getSelectBox(merchantSelectTemplate, [], 'Select Merchant'))
        targetModal.children().find('.rateContainer').html(getSelectBox(rateSelectTemplate, [], 'Select Rate'))

        containerControl()
        bindEvents()
    }

    function formSendData() {
        let formData = $('form[name="paymentForm"]').serializeArray();
        let sendData = {};
        formData.forEach((item) => {
            sendData[item.name] = item.value;
        })

        return sendData;
    }

    function formCryptoSendData() {
        let serializeArray = $('form[name="paymentCryptoForm"]').serializeArray();
        let formData = new FormData();
        serializeArray.forEach((item) => {
            formData.append(item.name, item.value);
        })

        let merchantLogo = $('#merchant_logo');
        if(merchantLogo.get(0).files.length > 0) {
            formData.append('paymentFormMerchantLogo', merchantLogo[0].files[0]);
        }

        return formData;
    }


    function resetForm() {
        $('form[name="paymentForm"]')[0].reset();
        $('form[name="paymentCryptoForm"]')[0].reset();
        $('#paymentFormMerchantLogoStatus').text('');
        $('#updateMerchantLogo').attr('src', '').hide();
        cleanErrors();
    }

    function cleanErrors() {
        $('#paymentForm, #paymentCryptoForm').children().find('.text-danger').remove();
    }

    function displayErrors(errors) {
        for (const [key, value] of Object.entries(errors)) {
            $('[name="' + key + '"]').after('<p class="text-danger">' + value + '</p>')
        }
    }

    function getPaymentTypeSelectBox() {
        let paymentTypeSelect = paymentTypeSelectTemplate;
        for (const [key, value] of Object.entries(AvailablePaymentTypes)) {
            paymentTypeSelect += '<option value="' + key + '">' + value + '</option>';
        }
        paymentTypeSelect += '</select>';

        return paymentTypeSelect;
    }

    function getSelectBox(template, array , placeholder, isSelected = null) {
        let select = template + '<option value="">' + placeholder + '</option>';
        for (let [key, value] of Object.entries(array)) {
            if(rateSelectTemplate == template) {
                key = value.id;
                value = value.name;
            }
            select += '<option value="' + key + '" ' + ((isSelected !== null && isSelected === key) ? ' selected ' : '')  + '>' + value + '</option>';
        }
        select += '</select>';

        return select;
    }

    function showOrHideWalletAddressContainer(currentType = null) {
        let currentFormType = currentType ?? $('select[name="paymentFormType"]').val();
        let merchantOutsideForm = $('.merchantTypeContainer').data('merchant-outside-form');
        if (currentFormType == merchantOutsideForm) {
            $('#walletAddressContainer').show()
        } else {
            $('#walletAddressContainer').hide()
        }
    }

    function showOrHideCustomersRatesContainer(currentType = null) {
        let currentFormType = currentType ?? $('select[name="paymentFormType"]').val();
        let typeContainer = $('.merchantTypeContainer');
        let clientOutsideForm = typeContainer.data('client-outside-form');
        let clientInsideForm = typeContainer.data('client-inside-form');
        if (currentFormType == clientOutsideForm || currentFormType == clientInsideForm) {
            $('.merchantRateContainerTitle, .rateContainer, .clientRateShowHide').show()
            $('.merchantRateContainerTitle, .merchantContainer').hide()
        } else {
            $('.merchantRateContainerTitle, .rateContainer, .clientRateShowHide').hide()
            $('.merchantRateContainerTitle, .merchantContainer').show()
        }
    }

    function getKYCSelectBox(currentFormType) {
        let clientInsideForm = $('.merchantTypeContainer').data('client-inside-form');
        let KYCSelectBox = $('#paymentFormKYC');
        let type = $('select[name="paymentFormType"]').val();
        $('.kycContainer').show();

        if (currentFormType == clientInsideForm || type == clientInsideForm) {
            $('.kycContainer').hide();
            let KYCContainer = $('#paymentFormKYCContainer');
            let KYC = KYCContainer.data('kyc');
            let KYCText = KYCContainer.data('kyc-text');
            KYCSelectBox.css('pointer-events', 'none');

            KYCSelectBox.html('<option value="' + KYC + '" selected="selected">' + KYCText + '</option>');

            return;
        }

        let kycOptionsHtml = '';
        for (const [key, value] of Object.entries(AvailableKYC)) {
            kycOptionsHtml += '<option value="' + key + '">' + value + '</option>';
        }
        KYCSelectBox.html(kycOptionsHtml);
    }

    function bindEvents() {
        $('body').delegate('select[name="paymentFormType"]', 'change', function () {
            containerControl()
        });
    }

    $('body').delegate('#paymentFormProject', 'change', function () {
        let permUrl = $('#managerPermissions').data('permissions')
        let self = this;
        let url = $('.projectForPaymentForm').data('get-url');
        let form = $(this).closest('form')
        let merchantOptions = '';
        let rateOptions = '';
        let cardProviderOptions = '';
        let liquidityProviderOptions = '';
        let walletProviderOptions = '';
        $.when($.ajax(permUrl)).done(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: url,
                type: 'post',
                data: {
                    project: $(self).val()
                },

                success: function (data) {
                    data.merchants.forEach(function (value, index) {
                        merchantOptions += '<option value="' + value.id + '">' + value.company_name +  ' (Id:' + value.profile_id + ')</option>'
                    })
                    form.find('select[name="paymentFormMerchant"]').html(merchantOptions);

                    data.rates.forEach(function (value, index) {
                        rateOptions += '<option value="' + value.id + '">' + value.name + '</option>'
                    })
                    form.find('select[name="paymentFormRate"]').html(rateOptions);

                    data.cardProviders.forEach(function (value, index) {
                        cardProviderOptions += '<option value="' + value.id + '">' + value.name + '</option>'
                    })
                    form.find('select[name="paymentFormCardProvider"]').html(cardProviderOptions);

                    data.liquidityProviders.forEach(function (value, index) {
                        liquidityProviderOptions += '<option value="' + value.id + '">' + value.name + '</option>'
                    })
                    form.find('select[name="paymentFormLiquidityProvider"]').html(liquidityProviderOptions);

                    data.walletProviders.forEach(function (value, index) {
                        walletProviderOptions += '<option value="' + value.id + '">' + value.name + '</option>'
                    })
                    form.find('select[name="paymentFormWalletProvider"]').html(walletProviderOptions);
                },
                error: function (data) {

                }
            })
        })

    })

    function containerControl(paymentFormType = null) {
        showOrHideWalletAddressContainer(paymentFormType)
        showOrHideCustomersRatesContainer(paymentFormType)
        getKYCSelectBox(paymentFormType)
    }

    $(document).on('click', '#labelFile', function () {
        $('#merchant_logo').click();
    })

    $(document).on('change', '#merchant_logo', function () {
        var file = $('#merchant_logo')[0].files[0];
        var fileName = file.name;
        $('#paymentFormMerchantLogoStatus').text(fileName + ' successful selected.');
        $('#updateMerchantLogo').attr('src', URL.createObjectURL(file)).show()
    })
})
