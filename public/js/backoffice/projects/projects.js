$(document).ready(function () {
    let bUserSelect = $('#bUsers');
    let liqProvidersSelect = $('#liquidityProviders');
    let roles = $('.roles');


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('body').delegate('.projectCard', 'click', function () {
        window.location.href = $(this).data('edit-url')
    });

    // targetModal.children().find('#updateMerchantLogo').attr('src', '/cratos.theme/images/logo.png').show()


    $('#projectLogo').on('change', function () {
        let file = $('#projectLogo')[0].files[0];
        var fileName = file.name;
        $('#updateProjectLogoStatus').text(fileName + ' successful selected.');
        $('.error-text').hide();
        $('#updateProjectLogo').attr('src', URL.createObjectURL(file)).show()
    })

    bUserSelect.select2({
        placeholder: "Select managers",
        val: false,
        width: '100%',
    });

    roles.select2({
        placeholder: "Select roles",
        val: false,
        width: '100%',
    });

    liqProvidersSelect.select2({
        placeholder: "Select providers",
        val: false,
        width: '100%',
    });
    $('#paymentProviders').select2({
        placeholder: "Select providers",
        val: false,
        width: '100%',
    });
    $('#smsProviders').select2({
        placeholder: "Select SMS providers",
        val: false,
        width: '100%',
    });

    liqProvidersSelect.on('select2:select', function (e) {
        let data = e.params.data;
        addDefaultSelectionCheckbox(data.id, data.text)
    })


    liqProvidersSelect.on('select2:unselect', function (e) {
        let data = e.params.data;
        removeDefaultSelectionCheckbox(data.id, data.text)
    })


    bUserSelect.on('select2:select', function (e) {
        let data = e.params.data;
        addRoleSelectionBox(data.id, data.text)
    });

    bUserSelect.on('select2:unselect', function (e) {
        let data = e.params.data;
        removeRoleSelectionBox(data.id)
    });

    $('#cardIssuingSettingsCreate').on('click', function () {

        let url = $(this).data('create-issuing-settings-url');
        $.ajax({
            url: url,
            type: 'post',
            data: $(this).closest('form').serialize(),
            success: function (data) {
                if (data.success) {
                    $('#cardIssuing').modal('hide');
                    $('p.cardIssuingMessage').text('Card Issuing Settings were changed successfully');
                    setTimeout(function () {
                        $('p.cardIssuingMessage').text('')
                    }, 2500)
                }
            },
            error: function (data) {
                let errors = data.responseJSON.errors;
                $.each(errors, function (key, value) {
                    $('span.' + key).text(value)
                });
            }
        })
    })

    $('#projectsAll').on('click', function () {
        let part = $(this).is(':checked') ? 'all' : 'active';
        $.ajax({
            url: 'get-projects/' + part,
            success: function (data) {
                $("#projectsSection").empty();

                for (i = 0; i < data.length; i++) {
                    $('#projectsSection').append('<div class="col-md-4 flex-grow-1">' +
                        '<div class="card-default p-0 credit-card ml-3 mt-4 projectCard pb-4 cursor-pointer" data-edit-url="' + data[i].editUrl + '">' +
                        '<div class="d-flex justify-content-between align-items-center mr-2 ml-4 mt-4">' +
                        '<h4>' + data[i].name + '</h4>' +
                        '<div class="card-logo d-flex align-content-end ">' +
                        '<img src="' + data[i].logoPng + '" style="height: 100px; width: auto; object-fit: contain; object-position: center" class="img-fluid" alt=""> </div> </div> </div> </div>'
                    );
                }
            }
        });
    })

    $('#cardIssuingSettingsBtn').on('click', function () {
        $('span').text('');
        let url = $(this).data('get-issuing-settings-url');
        $.ajax({
            url: url,
            type: 'GET',
            data: {},
            success: function (data) {
                if (data.cardIssuingSettings) {
                    $('[name="issuer"]').val(data.cardIssuingSettings.issuer)
                    $('[name="audience"]').val(data.cardIssuingSettings.audience)
                    $('[name="appUrl"]').val(data.cardIssuingSettings.appUrl)
                    $('[name="appSite"]').val(data.cardIssuingSettings.appSite)
                }

            },
            error: function (data) {

            }
        })
    })

    $('.ticket-status-buttons').on('click', function () {
        if ($(this).hasClass('ticket-active')) {
            return false;
        }
        $(this).closest('.row').find('.ticket-active').removeClass('ticket-active').addClass('ticket-inactive');
        $(this).removeClass('ticket-inactive').addClass('ticket-active');
        let selector = $(this).data('setting');
        $('.projectSettings').prop('hidden', true);
        $('#' + selector).prop('hidden', false);
    })

    $('input').on('keypress, focus, keydown', function () {
        validation()
    })

    $('select').on('change', function () {
        validation()
    })
    validation()

});


function addRoleSelectionBox(id, name) {
    let managerRolesContainer = $('.manager-roles')
    let roles = managerRolesContainer.data('roles');
    let selectName = 'roles[' + id + '][]';

    let child = '<div class="d-flex flex-row mt-5 ' + id + '">' +
        '<div class="col-4"><h5>' + name + '</h5></div>' +
        '<div class="col-8 ml-4">' +
        '<select name="' + selectName + '" class="roles" data-roles="' + roles + '" multiple="multiple" style="width: 800px;">';

    roles.forEach(function (name) {
        child += '<option value="' + name + '">' + name + '</option>';
    })

    child += +'</select>'
        + '</div>'
        + '<div>';

    managerRolesContainer.append(child);

    $('select[name="' + selectName + '"]').select2({
        placeholder: "Select roles",
        val: false,
        width: '100%',
    })
}

function addDefaultSelectionCheckbox(id, name) {
    let liqProvidersContainer = $('.liq-providers')

    let child = '<div class="d-flex flex-row mt-2 ' + id + ' ">' +
        '<small>' +
        '<input type="radio" class="text-right mr-3 mt-2" id="liq' + id + '"' +
        (liqProvidersContainer.children().length === 0 ? ' checked ' : '') +
        'name="liqProvider" value="' + id + '"> </small>' +
        ' <label for="liq' + id + '">' + name + '</label>' + '</div>';
    liqProvidersContainer.append(child);
}

function removeRoleSelectionBox(id) {
    $('.' + id).remove()
}

function removeDefaultSelectionCheckbox(id) {
    let element = $('.' + id)
    let liqProvidersContainer = $('.liq-providers')
    if (liqProvidersContainer.children().length > 1 && element.find('#liq' + id).is(':checked')) {
        element.remove()
        liqProvidersContainer.find('input[type="radio"]:first').prop('checked', true)
    } else {
        element.remove()
    }

}

function validation() {

    $('#submitButton').prop('disabled', false);

    if (
        $('[name="name"]').val() &&
        $('[name="domain"]').val() &&
        $('[name="status"]').val() &&
        $('[name="companyName"]').val() &&
        $('[name="companyCountry"]').val() &&

        $('[name="individualRate"]').val() &&
        $('[name="corporateRate"]').val() &&
        $('[name="bankCardRate"]').val() &&

        $("#walletProvider").val() &&
        $("#cardProvider").val() &&
        $("#issuingProvider").val() &&

        $('#paymentProviders').val().length &&
        $('#liquidityProviders').val().length &&
        $('[name="liqProvider"]').val() &&
        $("#smsProviders").val().length &&
        $('#emailProvider').val() &&

        $("#bUsers").val().length
    ) {

        return true;
    }
    $('#submitButton').prop('disabled', true);
}
