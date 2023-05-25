$(document).ready(function(){

    $("#close-sidebar").click(function() {
        $(".page-wrapper").toggleClass("toggled");
    });

    $("close-sidebar").click(function(){
        $("page-wrapper").addClass("toggled");
    });

    $('.JStableOuter table').scroll(function(e) {

        $('.JStableOuter thead').css("top", -$(".JStableOuter tbody").scrollTop());
        $('.JStableOuter thead tr th').css("top", $(".JStableOuter table").scrollTop());

    });

    $('[data-toggle=offcanvas]').click(function() {
        $('.row-offcanvas').toggleClass('active');
    });

    // Sorting clients
    $("body").on('click', '.sort-icon', function (event) {
        let sortBy = $( "#sort_by" ).val();
        let sort = $(this).attr('data-sort');
        $( "#sort_by" ).val(sort);
        let sortDirection = $("#sort_direction").val();
        $("#sort_direction").val(sortBy === sort && sortDirection === 'desc' ? 'asc' : 'desc');
        $("#filter").submit();
    });


    $('.change_btn').click(function (e) {
        e.preventDefault();
        $(this).closest('form').find('.disabled_el').removeAttr('disabled')
    })

    //change user status
    $('.btn-status-change').click(function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        if(confirm($('#change-status-confirm-msg').val()) == true) {
            $('#changed-status').val($(this).attr('data-status'));
            form.submit();
        }
    })

    let alreadyHaveData = false;
    $('#retry_compliance').click(function (e) {
        if (!alreadyHaveData) {
            e.preventDefault();
            $.ajax({
                url: '/backoffice/compliance/applicant-docs/'+$(this).attr('data-applicantid'),
                type: 'get',
            }).done(function (data) {
                if(!data.docs){
                    $('#docLoading').hide();
                    $('#exampleModalLabel').text('Images have been deleted.');
                }else {
                    let docs = data.docs;
                    for (let i = 0; i < docs.length; i++) {
                        $('#compliance_document_table tbody').append('<tr><td scope="row"><input name="docIds" class="compliance_doc_checkbox" checked type="checkbox" value="' + docs[i].images + '" ></td><td>' + docs[i].name + '</td></tr>')
                    }
                    $('#inspectionId').val(data.inspectionId)

                    $('#docLoading').hide();
                    $('#complianceDocs .modal-body').show();
                }
            }).fail(function (data) {
                console.log('fail', data)
            });
            alreadyHaveData = true;
        }

    })

    $('#retryCheckAll').change(function() {
        $('#compliance_document_table .compliance_doc_checkbox').prop('checked', $(this).prop('checked'))
    })

    $('.logForm .filter_el').change(function () {
       $(this).closest('form').submit();
    })

    $(function(){
        var hash = window.location.hash;
        hash && $('ul.nav a[href="' + hash + '"]').tab('show');

        $('.nav-tabs a').click(function (e) {
            $(this).tab('show');
            var scrollmem = $('body').scrollTop() || $('html').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });
        $('.navLink a').click(function (e) {
            $('.nav-tabs a[href="'+ $(this).attr('href')+'"]').click();
        });
    });

    $("form.has-confirm").submit(function (e) {
        var $message = $(this).data('message');
        if(!confirm($message)){
            e.preventDefault();
        }
    });
});

$(document).ready(function () {
    let modalTitleElement = $('#modalTitleValue');
    let addTitleButton = $('#addTitle');
    let titleElement = $('#title');
    addTitleButton.on('click', function () {
        titleElement.val(modalTitleElement.val());
        if (modalTitleElement.val()) {
            $('#addNewTagModal').modal('hide');
            $('#titleSuccessfulMessage').text('Tittle Added successfully');
            setTimeout(function () {
                $('#titleSuccessfulMessage').text('');
            }, 2000);
        } else {
            $('#addTitleDangerMessage').text('Tittle is empty')
        }
        $('#tag').append('<option selected>'+modalTitleElement.val()+'</option>')
        setTimeout(function () {
            $('#addTitleSuccessMessage').text('');
            $('#addTitleDangerMessage').text('')
        }, 1500)
    });

    $("#from").datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        todayBtn: "linked",
        endDate: new Date(),
        autoclose: true,
        todayHighlight: true
    });
    $( "#to" ).datepicker({
        format: "yyyy-mm-dd",
        weekStart: 1,
        todayBtn: "linked",
        endDate: new Date(),
        autoclose: true,
        todayHighlight: true
    });
    $( "#date" ).datepicker({
        format: 'yyyy-mm-dd',
        orientation: "auto",
        autoclose: true
    });

    $('body').on('click', '.message', function () {
        var notificationId = $(this).data('id');
        var shortMessageEl = $('#shortMessage'+notificationId);
        var messageEl = $('#message'+notificationId);
        if( shortMessageEl.attr('hidden') !== undefined ) {
            shortMessageEl.removeAttr('hidden')
        } else {
            shortMessageEl.attr('hidden', true);
            $(this).removeClass('notificationMoreMessageDown').addClass('notificationMoreMessageUp')
        }

        if( messageEl.attr('hidden') !== undefined ) {
            messageEl.removeAttr('hidden')
        } else {
            messageEl.attr('hidden', true)
            $(this).removeClass('notificationMoreMessageUp').addClass('notificationMoreMessageDown')
        }
    });

    if (window.location.href.indexOf('?') !== -1 && window.location.href.indexOf('notifications-history') !== -1) {
        window.location.hash = 'historyOfNotifications';
    }

    setTimeout(function () {
        $('#successMessageAlert').fadeOut("slow");
    }, 5000);

    let statuses = [];
    statuses[1] = 'Active';
    statuses[2] = 'Disabled';
    statuses[3] = 'Suspended';

    function mydate(givenDate) {
        let date = new Date(givenDate);
        let year = date.getFullYear();
        let month = date.getMonth() + 1;
        let day = date.getDate();
        let hour = date.getHours();
        let minute = date.getMinutes();
        let second = date.getSeconds();
        if (month < 10) { month = '0' + month; }
        if (day < 10) { day = '0' + day; }
        if (hour < 10) { hour = '0' + hour; }
        if (minute < 10) { minute = '0' + minute; }
        if (second < 10) { second = '0' + second; }
        let result = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
        return result;
    }

    $('body').on('click', '#providerAll', function () {
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let part = $(this).is(':checked') ? 'all' : 'active';
        $.ajax({
            url: 'get-providers/' + part + '/' + providerType,
            success: function (datas) {
                $("#providersSection").empty();
                for (i = 0; i < datas.length; i++) {
                    let dateCreate = new Date(datas[i].created_at);
                    let dateUpdate = new Date(datas[i].updated_at);
                    let editBlock = '';
                    $('#providersSection').prepend('<div class="col-md-3 providers-section" data-provider-id="'+datas[i].id+'">'+
                        '<p class="activeLink provider-name">'+datas[i].name+'</p>'+
                        '<p class="providers-section-dates">Created: '+ mydate(dateCreate) +'</p>'+
                        '<p class="providers-section-dates">Last change: '+ mydate(dateUpdate) +'</p>'+
                        '<div class="providers-section-status">'+
                        (statuses[datas[i].status] ? statuses[datas[i].status] : '') +
                        '</div>'+ '<div class="editProvider" style="position:absolute;bottom: 5px;right: 10px" data-provider-id="'+ datas[i].id +'">Edit</div>' +
                        '</div>');
                }
                if ($('#providersSection').children().length) {
                    $('#accountsHeaderSection button').remove();
                    $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>')
                    $('#providerId').val(datas[0].id);
                }
                $('#providersSection').children('div').first().click();
            }
        });
    });

    $('body').on('click', '#complianceProviderAll', function () {
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let part = $(this).is(':checked') ? 'all' : 'active';
        $.ajax({
            url: 'get-compliance-providers/' + part,
            success: function (datas) {
                $("#providersSection").empty();
                for (i = 0; i < datas.length; i++) {
                    let dateCreate = new Date(datas[i].created_at);
                    let dateUpdate = new Date(datas[i].updated_at);
                    let editBlock = '';
                    $('#providersSection').prepend('<div class="col-md-3 providers-section" data-provider-id="'+datas[i].id+'">'+
                        '<p class="activeLink provider-name">'+datas[i].name+'</p>'+
                        '<p class="providers-section-dates">Created: '+ mydate(dateCreate) +'</p>'+
                        '<p class="providers-section-dates">Last change: '+ mydate(dateUpdate) +'</p>'+
                        '<div class="providers-section-status">'+
                        (statuses[datas[i].status] ? statuses[datas[i].status] : '') +
                        '</div>'+ '<div class="editComplianceProvider" style="position:absolute;bottom: 5px;right: 10px" data-provider-id="'+ datas[i].id +'">Edit</div>' +
                        '</div>');
                }
                if ($('#providersSection').children().length) {
                    $('#accountsHeaderSection button').remove();
                    $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>')
                    $('#providerId').val(datas[0].id);
                }
                $('#providersSection').children('div').first().click();
            }
        });
    });

    $('body').on('click', '#kytProviderAll', function () {
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let part = $(this).is(':checked') ? 'all' : 'active';
        $.ajax({
            url: 'get-kyt-providers/' + part,
            success: function (datas) {
                $("#providersSection").empty();
                for (i = 0; i < datas.length; i++) {
                    let dateCreate = new Date(datas[i].created_at);
                    let dateUpdate = new Date(datas[i].updated_at);
                    let editBlock = '';
                    $('#providersSection').prepend('<div class="col-md-3 providers-section" data-provider-id="'+datas[i].id+'">'+
                        '<p class="activeLink provider-name">'+datas[i].name+'</p>'+
                        '<p class="providers-section-dates">Created: '+ mydate(dateCreate) +'</p>'+
                        '<p class="providers-section-dates">Last change: '+ mydate(dateUpdate) +'</p>'+
                        '<div class="providers-section-status">'+
                        (statuses[datas[i].status] ? statuses[datas[i].status] : '') +
                        '</div>'+ '<div class="editComplianceProvider" style="position:absolute;bottom: 5px;right: 10px" data-provider-id="'+ datas[i].id +'">Edit</div>' +
                        '</div>');
                }
                if ($('#providersSection').children().length) {
                    $('#accountsHeaderSection button').remove();
                    $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>')
                    $('#providerId').val(datas[0].id);
                }
                $('#providersSection').children('div').first().click();
            }
        });
    });

    $('body').on('click', '#partnerAll', function () {
        let partnerLink = $(this).data('link-create-url');
        $.ajax({
            url: 'get-partners',
            success: function (datas) {
                $("#providersSection").empty();
                for (i = 0; i < datas.length; i++) {
                    let dateCreate = new Date(datas[i].created_at);
                    let dateUpdate = new Date(datas[i].updated_at);
                    let editBlock = '';
                    $('#providersSection').prepend('<div class="col-md-3 partners-section" data-link-create-url="'+partnerLink+ '/'  +datas[i].id+'" data-provider-id="'+datas[i].id+'">'+
                        '<p class="activeLink provider-name">'+datas[i].name+'</p>'+
                        '<p class="providers-section-dates">Created: '+ mydate(dateCreate) +'</p>'+
                        '<p class="providers-section-dates">Last change: '+ mydate(dateUpdate) +'</p>'+
                        '</div>'+ '<div class="editPartner" style="position:absolute;bottom: 5px;right: 10px" data-provider-id="'+ datas[i].id +'">Edit</div>' +
                        '</div>');
                }
                if ($('#providersSection').children().length) {
                    $('#accountsHeaderSection button').remove();
                    $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addReferralLinkBtn" data-redirect-url="'+partnerLink+ '/'  +datas[0].id+'">Add</button>')
                    $('#providerId').val(datas[0].id);
                }
                $('#providersSection').children('div').first().click();
            }
        });
    });

    $('body').on('click', '.editProvider', function () {
        let providerId = $(this).data('provider-id');
        let apiAccountSelectBox = $(".apiAccount");

        apiAccountSelectBox.removeClass('d-block').addClass('d-none')


        $('#provider').modal('show');
        $('#providerName').text('');
        $('#providerStatus').text('');
        $('#providerProject').text('');
        $('#providerApi').text('');
        $.ajax({
            url: 'get-provider/'+providerId,
            success: (data) => {
                $('input[name="name"]').val(data.name);
                $("#type").val(data.type).change();
                $("#status").val(data.status).change();
                $("#api").val(data.api).change();

                $(document).ajaxComplete(() => {
                    if(data.api) {
                        apiAccountSelectBox.removeClass('d-none').addClass('d-block')

                        $("#api_account").val(data.api_account).change();
                    }
                })

                if(data.plastic_card_amount) {
                    $('#plastic_card_amount').val(data.plastic_card_amount)
                }
                if (data.virtual_card_amount) {
                    $('#virtual_card_amount').val(data.virtual_card_amount)
                }

                if (!$("#paymentProviderForm input[name=_method]").val()) {
                    $('form[name="providerForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
                }
                if (!$("#paymentProviderForm input[name=provider_id]").val()) {
                    $('form[name="providerForm"]').prepend('<input type="hidden" name="provider_id" value="' + data.id + '"/>')
                }
            }
        })
    });

    $('body').on('click', '.editPartner', function () {
        let providerId = $(this).data('partner-id');
        $('#partner').modal('show');
        $('#partnerName').text('');
        $.ajax({
            url: 'get-partner/'+providerId,
            success: function (data) {
                $('input[name="name"]').val(data.name);
                if (!$("#paymentProviderForm input[name=_method]").val()) {
                    $('form[name="providerForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
                }
                if (!$("#paymentProviderForm input[name=provider_id]").val()) {
                    $('form[name="providerForm"]').prepend('<input type="hidden" name="partner_id" value="' + data.id + '"/>')
                }
            }
        })
    });




    function resetForm() {
        $('#paymentProviderForm').trigger("reset");
        $('#providerForm').trigger("reset");
        $('select').prop('selectedIndex',0);
        $('input[name="_method"]').remove();
        $('input[name="provider_id"]').remove();
        $('#providerName').html('');
        $('#providerStatus').html('');
        $('#providerProject').html('');
        $('.text-danger').text('');
        $('.apiAccount').removeClass('d-block').addClass('d-none');
        $('#virtual_card_amount').val();
        $('#plastic_card_amount').val();
        // $("#projects").val(null).trigger('change');

    }

    $('body').on('click', '#addProviderBtn', function () {
        resetForm();
    });

    $('body').on('click', '#addPartnerrBtn', function () {
        resetForm();
    });

    $('body').on('click', '#addLiquidityProviderBtn', function () {
        resetForm();
    });

    $('body').on('click', '#closeButton', function () {
       $('#addAccount').modal('hide');
       $('#addAccountSepa').modal('hide');
       $('#addAccountBtc').modal('hide');
       $('#addRateTemplates').modal('hide');
       $('#cardsRateTemplates').modal('hide');
    });

    $('#countries').select2({
        placeholder: "Select countries",
        val: false
    });

    $('#wireAccountType').select2({
        placeholder: "Select type",
        val: false
    });

    $(document).ready(function () {
        if (typeof countries === 'undefined') {
            $("#countries").select2("val", false);
            $("#countries").select2().trigger('change');
        }
    });

    $(document).ready(function () {
        if (typeof countries === 'undefined') {
            $("#wireAccountType").select2("val", false);
            $("#wireAccountType").select2().trigger('change');
        }
    });

    $('body').on('click', '.providers-section', function () {

        let providerType = $(this).data('provider-type');
        if (!providerType) {
            let locArr = location.href.split('/');
            providerType = locArr[locArr.length - 1];
        }

        let providerId = $(this).data('provider-id');
        let isForDashboard = $(this).data('is-dashboard');

        // $('#paymentProviderId').val(providerId);
        // $('#providerIdSepa').val(providerId);
        // $('#providerIdBtc').val(providerId);
        $('#providersAccounts').html('');
        $('#providersSection').children('div').each(function () {
            $(this).removeClass('red-border');
        });
        $(this).addClass('red-border');
        $.ajax({
            url: 'get-provider-accounts/'+providerId,
            success: function (data) {
                let no = 1;
                let textCenter = isForDashboard ? 'text-center' : '';
                for (i = 0; i < data.length; i++) {
                    let date = new Date(data[i].created_at);
                    let column = 2;
                    let country = '';
                    let address = '';
                    let currentCountry = data[i].country ? data[i].country : '';
                    if (providerType !== 'wallet-providers' && providerType !== 'credit-card-providers'){
                        country = '<div class="col-md-1">'+ currentCountry +'</div>';
                    }else if(providerType === 'credit-card-providers') {
                        country = '<div class="col-md-2">'+ currentCountry +'</div>';
                    }else {
                        column = 3;
                    }
                    $('#providersAccounts').css({"width":"100%"})
                    if (isForDashboard) {
                        $('#wallet_provider_account').attr('hidden', true)
                        $('#card_provider_accounts').attr('hidden', true)
                        $('#other_provider_accounts').attr('hidden', true)
                    }
                    if (providerType === 'credit-card-providers'){
                        address = '';
                        if (isForDashboard) {
                            $('#card_provider_accounts').attr('hidden', false)
                        }

                        let systemClass = isForDashboard ? 'col-md-1  text-center' : 'col-md-2';

                        let appendItem = '<div class="row providersAccounts-item">' +
                            '<div class="col-md-1">'+ no +'</div>' +
                            '<div class="' + systemClass + '">'+ data[i].card_account_detail.paymentSystemName +'</div>' +
                            '<div class="col-md-1 ' + textCenter + '">'+ data[i].type +'</div>' +
                            '<div class="col-md-1 ' + textCenter + '">'+ data[i].card_account_detail.cardSecureName +'</div>' +
                            '<div class="col-md-1 ' + textCenter + ' mr-3 ">'+ data[i].currency +'</div>' +
                            '<div class="col-md-1 ' + textCenter + '">'+ data[i].card_account_detail.regionName +'</div>' +
                            '<div class="col-md-2 ' + textCenter + '">'+ mydate(date) +'</div>';
                        if (isForDashboard) {
                            appendItem += '<div class="col-md-1 ' + textCenter + '">' + data[i].formatedBalance + '</div>';
                        }

                        appendItem += '<div class="col-md-1 ' + textCenter + '">' + data[i].statusName + '</div>';
                        if (isForDashboard) {
                            appendItem += '<div style="cursor:pointer;" class="col-md-1 text-center" id="accountDetail" > <a href="' + data[i].detailViewLink + '" > Detail </a> </div>';
                        }else {
                            appendItem +=  '<div style="cursor:pointer;" class="col-md-1" data-account-id="'+data[i].id+'" id="accountView">View</div>' +
                                '</div>';
                        }
                        $('#providersAccounts').append(appendItem)
                        no++;

                    } else {
                        if (providerType == 'wallet-providers') {
                            address = '<div class="col-md-2 '+ textCenter + '" style="overflow-wrap: break-word">'+ data[i].crypto_account_detail.address +'</div>';
                        } else {
                            address = '<div class="col-md-2 '+ textCenter + '" style="overflow-wrap: break-word">';
                            if(data[i].wire.iban) {
                                address += data[i].wire.iban;
                            }
                            address += '</div>';
                        }
                        if (isForDashboard && providerType !== 'wallet-providers') {
                            $('#other_provider_accounts').attr('hidden', false)
                        }else {
                            $('#wallet_provider_account').attr('hidden', false)
                        }

                        let nameClass = isForDashboard ? 'col-md-1 overflow-hidden' : 'col-md-2';
                        let appendItem = '<div class="row providersAccounts-item">' +
                            '<div class="col-md-1">'+ no +'</div>' +
                            '<div class="' + nameClass + '">'+ data[i].name +'</div>' +
                            '<div class="col-md-1 '+ textCenter + '">'+ data[i].type +'</div>' +
                            '<div class="col-md-1 '+ textCenter + '">'+ data[i].currency +'</div>' +
                            country +
                            address +
                            '<div class="col-md '+ textCenter + '">'+ mydate(date) +'</div>';
                            if(isForDashboard) {
                                appendItem += '<div class="col-md-1 '+ textCenter + '">'+ data[i].formatedBalance +'</div>';
                            }
                            appendItem +='<div class="col-md-1 '+ textCenter + '">'+ data[i].status +'</div>';
                        if (isForDashboard) {
                            $('#accountId').val(data[i].id)

                            appendItem += '<div style="cursor:pointer;" class="col-md-1" id="accountDetail" > <a href="' + data[i].detailViewLink + '" > Detail </a> </div>';
                        }else {
                            appendItem += '<div style="cursor:pointer;" class="col-md-1" data-account-id="'+data[i].id+'" id="accountView">View</div>' +
                                '</div>';
                        }
                        $('#providersAccounts').append(appendItem);
                        no++;
                    }
                }
            }
        });
    });

    $('body').on('click', '.partners-section', function () {

        let providerId = $(this).data('provider-id');
        let linkCreateUrl = $(this).data('link-create-url');
        $('#addReferralLinkBtn').attr('data-redirect-url', linkCreateUrl);

        $('#providersAccounts').html('');
        $('#providersSection').children('div').each(function () {
            $(this).removeClass('red-border');
        });
        $(this).addClass('red-border');
        $.ajax({
            url: 'get-partner-links/'+providerId,
            success: function (data) {
                let no = 1;
                 for (i = 0; i < data.length; i++) {

                    $('#providersAccounts').css({"width":"100%"})

                        let appendItem = '<div class="row providersAccounts-item">' +
                            '<div class="col-md-1">'+ no +'</div>' +
                            '<div class="col-md-1">'+ data[i].name +'</div>' +
                            '<div class="col-md-2">'+ data[i].individual_rate_templates +'</div>' +
                            '<div class="col-md-2">'+ data[i].corporate_rate_templates +'</div>' +
                            '<div class="col-md-2">'+ data[i].activation_date +'</div>' +
                            '<div class="col-md-2">'+ data[i].deactivation_date +'</div>';

                     appendItem +=  '<div style="cursor:pointer;" class="col-md-1" data-link-view-url="'+data[i].edit_url+'" id="linkView">View/Edit</div>' +
                         '</div>';

                        $('#providersAccounts').append(appendItem)
                        no++;
                }
            }
        });
    });

    $('body').on('click', '#linkView', function () {
        let url = $(this).data('link-view-url');
        window.location.replace(url)
    });

    $('body').on('click', '#addAccountBtn', function () {
        $("#paymentSystem").find('option').remove();
        $("#paymentSystem").append(`<option value=""></option>`);
        $("input[type=text], textarea").val("");
        $(".text-danger").each(function () {
            $(this).text('');
        });
        $('select').prop('selectedIndex',0);
        $('input[name="_method"]').remove();
        $('input[name="account_id"]').remove();
        $('#providersSection').children('div').each(function () {
            if ($(this).hasClass('red-border')){
                $('#providerId').val($(this).data('provider-id'));
            }
        });
        $('#accountHeader').text('Add new account');
        $('#accountHeaderSepa').text('Add new account');
        $('#accountHeaderBtc').text('Add new account');
        $('#copyRate').attr('hidden', true);
        $('.copyRate').attr('hidden', true);
        $('#copyNameAccount').attr('hidden', true);
        $("#countries").select2("val", false);
        $("#countries").trigger('change');
        $("#wireAccountType").select2("val", false);
        $("#wireAccountType").trigger('change');
        let typeSwift = $('.type_swift').val();
        controlSwiftDetails(typeSwift, $('.account_type').val())
    });

    $('body').on('click', '#addReferralLinkBtn', function () {
        let url = $(this).data('redirect-url');
        window.location.replace(url)
    });

    var type = $('#accountTypeOldInput').data('type');
    $(document).ready(function () {
        if($('#containErrors').length){
            let oldRateId = $('#oldRateId').val();
            $.ajax({
                url: 'get-rate-template-countries/' + oldRateId,
                success: function (fullData) {
                    let countries = [];
                    for (var country in fullData?.template?.countries) {
                        countries.push(fullData.template.countries[country].country);
                    }
                    $('#countries').val(countries);
                    $("#countries").select2({data:countries});
                    $("#countries").trigger('change');
                }
            });
            $('#addRateTemplates').modal('show');
        }

        if($('#containBankCardRateErrors').length) {
            $('#cardsRateTemplates').modal('show');
        }
        if ($('#containBankCardRateErrorsUpdate').length) {
            $('#cardsRateTemplatesUpdate').modal('show');
        }
    });
    if (type && type === 1) {
        $('#addAccountSepa').modal('show');
    }
    if (type === 0) {
        $('#addAccountSepa').modal('show');
    }
    if (type && type === 2) {
        $('#addAccountBtc').modal('show');
    }
    $('#typeAccountSepa').val(type).change();

    $('body').on('click', '#accountView', function () {
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let accountId = $(this).data('account-id');
        $('#accountHeader').text('Edit account');
        $('#accountHeaderSepa').text('Edit account');
        $('#accountHeaderBtc').text('Edit account');
        $('#copyRate').removeAttr('hidden');
        $('.text-danger').each(function () {
            $(this).text('');
        });
        $.ajax({
            url: 'get-account/'+accountId,
            success: function (data) {
                if (data) {
                    if (data.crypto_account_detail){
                        $('input[name=btc_crypto_wallet]').val(data.crypto_account_detail.address);
                        $('input[name=crypto_wallet]').val(data.crypto_account_detail.address);
                        $('#currencyBtc').val(data.crypto_account_detail.coin).change();
                        $('#walletId').val(data.crypto_account_detail.wallet_id);
                        $('#labelKraken').val(data.crypto_account_detail.label_in_kraken);
                    }
                    let commissions = [data.to_commission, data.from_commission, data.internal_commission, data.refund_commission, data.chargeback_commission];
                    commissions = commissions.filter(el => el !== null);
                    for (var i = 0; i < commissions.length; i++) {
                        $('input[name="percent_commission['+commissions[i].type+']"]').val(commissions[i].percent_commission);
                        $('input[name="fixed_commission['+commissions[i].type+']"]').val(commissions[i].fixed_commission);
                        $('input[name="min_commission['+commissions[i].type+']"]').val(commissions[i].min_commission);
                        $('input[name="max_commission['+commissions[i].type+']"]').val(commissions[i].max_commission);
                        if (commissions[i].blockchain_fee){
                            $('input[name="blockchain_fee"]').val(commissions[i].blockchain_fee);
                        }
                        if (commissions[i].chargeback_commission){
                            $('input[name="percent_commission['+commissions[i].type+']"]').val(commissions[i].percent_commission);
                            $('input[name="fixed_commission['+commissions[i].type+']"]').val(commissions[i].fixed_commission);
                        }
                    }
                    if (!$("#accountForm input[name=_method]").val()) {
                        $('form[name="accountForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
                    }
                    if (!$("#accountForm input[name=account_id]").val()) {
                        $('form[name="accountForm"]').prepend('<input type="hidden" name="account_id" value="' + accountId + '"/>')
                    }
                    if (!$("#liquidityAccountForm input[name=_method]").val()) {
                        $('#liquidityAccountForm').prepend('<input type="hidden" name="_method" value="put"/>')
                    }
                    if (!$("#liquidityAccountForm input[name=account_id]").val()) {
                        $('#liquidityAccountForm').prepend('<input type="hidden" name="account_id" value="' + accountId + '"/>')
                    }
                    let countries = [];
                    for (var key in data.countries) {
                        countries.push(data.countries[key].country);
                    }
                    $('#countries').val(countries);
                    $("#countries").select2({data:countries});
                    $("#countries").trigger('change');

                    let wireAccountType = [];
                    for (var key in data.wireAccountType) {
                        wireAccountType.push(data.wireAccountType[key].type);
                    }
                    $('#wireAccountType').val(wireAccountType);
                    $("#wireAccountType").select2({data:wireAccountType});
                    $("#wireAccountType").trigger('change');
                    for (var key in data.limit) {
                        $('input[name="'+key+'"]').val(data.limit[key]);
                    }
                    $('input[name=btc_name]').val(data.name);
                    $('input[name=btc_minimum_balance_alert]').val(data.minimum_balance_alert);
                    for (var key in data) {
                        var lowerKey = key;
                        let cardProvider = false;
                        if (key == 'account_id') {
                            continue;
                        }
                        if (key === 'status') {
                            $("#statusAccount").val(data.status).change();
                            $("#statusAccountBtc").val(data.status).change();
                            $("#statusAccountSepa").val(data.status).change();
                        }
                        if (key === 'region') {
                            $("#region").val(data.region).change();
                        }
                        if (key === 'secure') {
                            $("#secure").val(data.secure).change();
                        }
                        $('input[name="'+lowerKey+'"]').val(data[key]);
                        if (key === 'currency') {
                            $("#currency").val(data[key]).change();
                            $("#currencySepa").val(data[key]).change();
                        }

                        if (key === 'fiat_type') {
                            $("#fiat_type").val(data[key]).change();
                        }

                        if (key === 'country') {
                            $("#country").val(data[key]).change();
                        }
                        if (key === 'account_type') {
                            let typeSwift = $('.type_swift').val();
                            $("#typeAccount").val(data[key]).change();
                            controlSwiftDetails(typeSwift, $("#typeAccount").val())
                        }
                        if (key === 'c_profile_id') {
                            $("#cProfileId").val(data[key]).change();
                        }
                        if (key === 'crypto_wallet') {
                            $('input[name="'+key+'"]').val(data[key]);
                        }
                    }

                    for (var key in data.wire) {
                        $('input[name="'+key+'"]').val(data.wire[key]);
                    }

                    if (data.currency != 4 || data.currency != 5) {
                        let commissions = [data.to_commission, data.from_commission, data.internal_commission, data.refund_commission];
                        commissions = commissions.filter(el => el !== null);
                        for (var i = 0; i < commissions.length; i++) {
                            $('input[name="btc_percent_commission['+commissions[i].type+']"]').val(commissions[i].percent_commission);
                            $('input[name="btc_fixed_commission['+commissions[i].type+']"]').val(commissions[i].fixed_commission);
                            $('input[name="btc_min_commission['+commissions[i].type+']"]').val(commissions[i].min_commission);
                            $('input[name="btc_max_commission['+commissions[i].type+']"]').val(commissions[i].max_commission);
                        }
                        for (var key in data.limit) {
                            $('input[name="btc_'+key+'"]').val(data.limit[key]);
                        }
                        for (var key in data.wire) {
                            $('input[name="btc_'+key+'"]').val(data.wire[key]);
                        }
                        for (var key in data) {
                            if (key === 'crypto_wallet') {
                                $('input[name="btc_'+key+'"]').val(data[key]);
                            }
                        }
                    }


                    if (providerType != 'liquidity-providers'){
                        $('#addAccount').modal('show');
                    } else {
                        if (data.currency == 'USD' || data.currency == 'EUR') {
                            $('#addAccountSepa').modal('show');
                        } else {
                            $('#addAccountBtc').modal('show');
                        }
                    }
                    $('#typeAccountSepa').val(data.account_type).change();
                    if (data.card_account_detail) {
                        let card = data.card_account_detail;
                        $("#card_type").val(card.type).change();
                        $("#region").val(card.region).change();
                        $("#secure").val(card.secure).change();
                        window.localStorage.setItem('payment_system', card.payment_system);

                    }
                }
            }
        })
    });



    $('body').on('change', '#typeAccountSepa', function () {
        let typeSwift = $('.type_swift').val();
        controlSwiftDetails(typeSwift, $(this).val())
        if ($(this).val() == 2) {
           $('#addAccountSepa').modal('hide');
           setTimeout(function () {
               $('#addAccountBtc').modal('show');
           }, 500);
           $('#typeAccountBtc').val($(this).val()).change();
       }
    });

    $('body').on('change', '#typeAccountBtc', function () {
       if ($(this).val() == 1 || $(this).val() == 0) {
           $('#addAccountBtc').modal('hide');
           setTimeout(function () {
               $('#addAccountSepa').modal('show');
           }, 500)
           $('#typeAccountSepa').val($(this).val()).change();
       }
    });

    $('.transaction-type').on('change', function () {
        if ($(this).val() == $('.returnedStatus').val()) {
            $('.operation-substatus').attr('hidden', false)
        }else {
            $('.operation-substatus').attr('hidden', 'hidden')
        }
    })


    $('#api').on('change', function () {
        if ($(this).val()) {
            let url = $(this).data('url')
            $.ajax({
                url: url,
                type: 'get',
                data: { 'api': $('#api').val()},
                success: (data) => {
                    $(".apiAccount").removeClass('d-none').addClass('d-block');
                    let apiAccountSelect = '<option></option>';
                    for (const [key, value] of Object.entries(data.apiAccounts)) {
                        apiAccountSelect += '<option value="' + value + '">' + value + '</option>';
                    }
                    $('#api_account').html(apiAccountSelect)
                },
            });
        }
    })

    $('body').on('click', '#providerCreate', function () {
        $('.text-danger').text('')
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let name = $('#name').val();
        let status = $('#status').val();
        let api = $('#api').val()
        let api_account = $('#api_account').val()

        $(".apiAccount").removeClass('d-block').addClass('d-none')

        if (!name) {
            $('#providerName').text('The name is required.')
        } else {
            var nameProvider = new RegExp('^([.\'`0-9\p{Latin}]?[\ \-]?)+[a-zA-Z0-9 ?]+$', 'i');
            if (!nameProvider.test(name)) {
                $('#providerName').text('Field is invalid.')
            } else {
                $('#providerName').text('')
            }
        }
        if (!status) {
            $('#providerStatus').text('The status is required.')
        } else {
            $('#providerStatus').text('')
        }

        let apiIsCorrect = ((providerType !== 'liquidity-providers') && (providerType !== 'credit-card-providers') && (providerType !== 'wallet-providers')) || api;

        if (!apiIsCorrect) {
            $('.apiError').text('The api is required.')
        } else {
            $('.apiError').text('')
        }

        let plastic_card_amount = null;
        let virtual_card_amount = null;
        let cardAmountsAreCorrect = true;
        if (providerType === 'card-issuing-providers') {
            plastic_card_amount = $('#plastic_card_amount').val()
            virtual_card_amount = $('#virtual_card_amount').val()

            if (!plastic_card_amount) {
                cardAmountsAreCorrect = false;
                $('.plasticError').text('The plastic card amount is required.')
            }
            if (!virtual_card_amount) {
                cardAmountsAreCorrect = false;
                $('.virtualError').text('The virtual card amount is required.')
            }
        }

        if ((!status || !name || !cardAmountsAreCorrect) && api) {
            $(".apiAccount").removeClass('d-none').addClass('d-block')
        }



        let data = {name,status, api, api_account, plastic_card_amount, virtual_card_amount};
        if (name && status && apiIsCorrect && cardAmountsAreCorrect) {
            let method = 'post';
            if ($("#providerForm input[name=_method]").val()) {
                method = $("#providerForm input[name=_method]").val();
                data.provider_id = $('#providerForm input[name="provider_id"]').val();
            }
            data.providerType = providerType;
            $.ajax({
                url: 'payment-provider',
                headers: {'X-CSRF-TOKEN': $('#providerToken').val()},
                data,
                method,
                success: function (data) {
                    if (!$('#providersSection').children().length && (statuses[status] === 'Active')) {
                        $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>')
                        $('#providerId').val(data.id);
                    }
                    $('#name').val('');
                    $('#status').val('');
                    $('#provider').modal('hide');
                    if (method === 'put') {
                        $('div[data-provider-id="'+data.id+'"]').remove();
                    }
                    if (statuses[data.status] === 'Active' || statuses[data.status] === 'Suspended' || $('#providerAll').is(':checked')) {
                        let dateCreate = new Date(data.created_at);
                        let dateUpdate = new Date(data.updated_at);
                        $('#providersSection').prepend('<div class="col-md-3 providers-section" data-provider-id="' + data.id + '" style="cursor:pointer;">' +
                            '<p class="activeLink  provider-name">' + data.name + '</p>' +
                            '<p class="providers-section-dates">Created: ' + mydate(dateCreate) + '</p>' +
                            '<p class="providers-section-dates">Last change: ' + mydate(dateUpdate) + '</p>' +
                            '<div class="providers-section-status">' +
                            (statuses[data.status] ? statuses[data.status] : '') +
                            '</div>' +
                            '<div style="cursor:pointer;position:absolute;bottom: 5px;right: 10px" class="editProvider" data-provider-id="'+data.id+'">Edit</div>'+
                            '</div>')
                    }
                    if (status != 1) {
                        $('#providersAccounts').html('');
                        $('#providersSection').children('div').first().click();
                    }
                },
                error: function (data) {
                    var errors = $.parseJSON(data.responseText);
                    let hasApiError = false;
                    let hasApiAccountError = false;
                    for (let mes in errors.errors) {
                        if (mes === 'api_account') {
                            hasApiAccountError = true;
                        } else if (mes === 'api') {
                            hasApiError = true;
                        }
                        $('#'+mes).next().next().text(errors.errors[mes][0]);
                    }

                    if (!hasApiError && hasApiAccountError) {
                        $(".apiAccount").removeClass('d-none').addClass('d-block')
                    }
                    if (hasApiError) {
                        $('#api_account').next().next().text('')
                    }
                }
            })
        }
    });
    $('body').on('click', '#complianceProviderCreate', function () {
        let locArr = location.href.split('/');
        let providerType = locArr[locArr.length - 1];
        let name = $('#name').val();
        let status = $('#status').val();
        let api = $('#api').val()
        if (!name) {
            $('#providerName').text('The name is required.')
        } else {
            var nameProvider = new RegExp('^([.\'`0-9\p{Latin}]?[\ \-]?)+[a-zA-Z0-9 ?]+$', 'i');
            if (!nameProvider.test(name)) {
                $('#providerName').text('Field is invalid.')
            } else {
                $('#providerName').text('')
            }
        }

        if (!status) {
            $('#providerStatus').text('The status is required.')
        } else {
            $('#providerStatus').text('')
        }

        let apiIsCorrect = (providerType !== 'liquidity-providers') || api;

        if (!apiIsCorrect) {
            $('#providerApi').text('The api is required.')
        } else {
            $('#providerApi').text('')
        }

        let configs = {};
        $(this).closest('form').serializeArray().map(function(x){configs[x.name] = x.value;});

        let data = {name,status, api, configs};
        if (name && status && apiIsCorrect) {
            let method = 'post';
            if ($("#providerForm input[name=_method]").val()) {
                method = $("#providerForm input[name=_method]").val();
                data.provider_id = $('#providerForm input[name="provider_id"]').val();
            }
            data.providerType = providerType;
            $.ajax({
                url: 'compliance-providers',
                headers: {'X-CSRF-TOKEN': $('#providerToken').val()},
                data,
                method,
                success: function (data) {
                    if (!$('#providersSection').children().length && (statuses[status] === 'Active')) {
                        $('#accountsHeaderSection').append('<button class="btn" style="border-radius: 25px;background-color: #fe3d2b;color: #fff" data-toggle="modal" id="addAccountBtn" data-target="#addAccount">Add</button>')
                        $('#providerId').val(data.id);
                    }
                    $('#name').val('');
                    $('#status').val('');
                    $('#provider').modal('hide');
                    if (method === 'put') {
                        $('div[data-provider-id="'+data.id+'"]').remove();
                    }
                    if (statuses[data.status] === 'Active' || statuses[data.status] === 'Suspended' || $('#providerAll').is(':checked')) {
                        let dateCreate = new Date(data.created_at);
                        let dateUpdate = new Date(data.updated_at);
                        $('#providersSection').prepend('<div class="col-md-3 providers-section" data-provider-id="' + data.id + '" style="cursor:pointer;">' +
                            '<p class="activeLink  provider-name">' + data.name + '</p>' +
                            '<p class="providers-section-dates">Created: ' + mydate(dateCreate) + '</p>' +
                            '<p class="providers-section-dates">Last change: ' + mydate(dateUpdate) + '</p>' +
                            '<div class="providers-section-status">' +
                            (statuses[data.status] ? statuses[data.status] : '') +
                            '</div>' +
                            '<div style="cursor:pointer;position:absolute;bottom: 5px;right: 10px" class="editProvider" data-provider-id="'+data.id+'">Edit</div>'+
                            '</div>')
                    }
                    if (status != 1) {
                        $('#providersAccounts').html('');
                        $('#providersSection').children('div').first().click();
                    }
                },
                error: function (data) {
                    var errors = $.parseJSON(data.responseText);
                    for (let mes in errors.errors) {
                        $('#'+mes).next().next().text(errors.errors[mes][0]);
                    }
                }
            })
        }
    });

    function getCurrency(objectCurrency, currency)
    {
        for (let propValue in objectCurrency) {
            if (objectCurrency[propValue] == currency) {
                return propValue
            }
        }
        return null;
    }

    $('body').on('click', '.updateRateProvider', function () {
        $(".text-danger").each(function () {
            $(this).remove();
        });
        $('#addRateTemplates').modal('show');
        let rateTemplateId = $(this).data('rate-template-id');
        $('#accountHeader').text('Edit rate template');
        $('#rateHeader').text('Edit rate plan');
        $('#copyRate').removeAttr('hidden');
        $.ajax({
           url: 'get-rate-template-countries/'+rateTemplateId,
           success: function (fullData) {
               let data = fullData.template;
               $("#countries").select2("val", false);
               $("#countries").select2("val", false);
               $("#countries").trigger('change');
               $("#wireAccountType").select2("val", false);
               $("#wireAccountType").select2("val", false);
               $("#wireAccountType").trigger('change');
               if (!$("#rateTemplateForm input[name=_method]").val()) {
                   $('form[name="rateTemplateForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
               }
               if ($("#rateTemplateForm input[name=rate_template_id]").val()) {
                   $("#rateTemplateForm input[name=rate_template_id]").remove();
               }
               $('form[name="rateTemplateForm"]').prepend('<input type="hidden" name="rate_template_id" value="'+rateTemplateId+'"/>')
               let limit = data.limits;
               let commission = data.commissions;
               for (var limitKey in limit) {
                   $('input[name="transaction_amount_max[]"]').eq(limitKey).val(limit[limitKey].transaction_amount_max);
                   $('input[name="monthly_amount_max[]"]').eq(limitKey).val(limit[limitKey].monthly_amount_max);
                   $('input[name="transaction_amount_min[]"]').eq(limitKey).val(limit[limitKey].transaction_amount_min);
               }
               for (var current of commission) {
                   $('input[name="fixed_commission['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.fixed_commission);
                   $('input[name="percent_commission['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.percent_commission);
                   $('input[name="min_commission['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.min_commission);
                   $('input[name="max_commission['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.max_commission);
                   $('input[name="min_amount['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.min_amount);
                   $('input[name="refund_transfer_percent['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.refund_transfer_percent);
                   $('input[name="refund_transfer['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.refund_transfer);
                   $('input[name="refund_minimum_fee['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.refund_minimum_fee);
                   $('input[name="blockchain_fee['+getCurrency(fullData.currencies, current.currency)+']['+current.commission_type+']['+current.type+']"]').val(current.blockchain_fee);
               }
               for (var attr in data) {
                   $('input[name="'+attr+'"]').val(data[attr]);
                   if (attr === 'status') {
                       $('#status').val(data.status).change();
                   }
               }
               if (data.is_default) {
                   $('#isDefault').prop('checked', true);
                   $('#isDefault').attr('disabled', true);
                   $('#status').attr('disabled', true);
                   $('#updateDefault').val('true');
               }else {
                   $('#isDefault').removeAttr('disabled');
                   $('#status').removeAttr('disabled');
                   $('#updateDefault').val('');
               }
               if (data.type_client) {
                   $(document).ready(function () {
                       $("#typeClient").val(data.type_client).change();
                   })
               }
               if (data.currency) {
                   $(document).ready(function () {
                       $("#currency").val(data.currency).change();
                   })
               }
               if (data.is_default) {
                   $('#isDefault').prop('checked', true);
               } else {
                   $('#isDefault').prop('checked', false);
               }
               let countries = [];
               for (var country in data.countries) {
                   countries.push(data.countries[country].country);
               }
               $('#countries').val(countries);
               $("#countries").select2({data:countries});
               $("#countries").trigger('change');
               $('#wireAccountType').val(countries);
               $("#wireAccountType").select2({data:countries});
               $("#wireAccountType").trigger('change');
               $('#addRateTemplates').modal('show');

               $('#projectId').val(data.project_id).change();
           }
        });
    });

    $('body').on('click', '.updateBankCardRateProvider', function () {
        $(".text-danger").each(function () {
            $(this).remove();
        });
        $('#cardsRateTemplatesUpdate').modal('show');
        let rateTemplateId = $(this).data('rate-template-id');
        $('#accountHeaderUpdate').text('Edit bank card rate template');
        $('#bankCardRateHeaderUpdate').text('Edit bank card rate plan');
        $.ajax({
           url: 'get-card-rate-template/'+rateTemplateId,
           success: function (fullData) {

               if ($("#cardsRateTemplatesUpdate input[name=bank_card_rate_template_id]").val()) {
                   $("#cardsRateTemplatesUpdate input[name=bank_card_rate_template_id]").remove();
               }
               $('#cardsRateTemplatesUpdate form').prepend('<input type="hidden" name="bank_card_rate_template_id" value="'+rateTemplateId+'"/>')

               let data = fullData.template;
               let cardTemplatesUpdate = $('#cardsRateTemplatesUpdate');
               cardTemplatesUpdate.find('[name="bankCardRateName"]').val(data.name);
               cardTemplatesUpdate.find('[name="status"]').val(data.status);
               cardTemplatesUpdate.find('[name="bankCardOverviewType"]').val(data.overview_type);
               cardTemplatesUpdate.find('[name="bankCardOverviewFee"]').val(data.overview_fee);
               cardTemplatesUpdate.find('[name="bankCardTransactionsType"]').val(data.transactions_type);
               cardTemplatesUpdate.find('[name="bankCardTransactionsFee"]').val(data.transactions_fee);
               cardTemplatesUpdate.find('[name="bankCardFeesType"]').val(data.fees_type);
               cardTemplatesUpdate.find('[name="bankCardFeesFee"]').val(data.fees_fee);
               cardTemplatesUpdate.find('[name="projectId"]').val(data.project_id).change();
               cardTemplatesUpdate.modal('show');
           }
        });
    });

    $('body').on('click', '#cardsTemplates', function () {
        $(".text-danger").each(function () {
            $(this).remove();
        });
        $('#bankCardRateTemplateForm').trigger("reset");
        $('#bankCardRateHeader').text("Add new bank card rate plan");
        $('#bankCardRateTemplateForm').attr('method', 'post');
        $('#bankCardRateTemplateForm input[name="bank_card_rate_template_id"]').remove();
    });


    $('body').on('click', '#addRateTemplate', function () {
        $(".text-danger").each(function () {
            $(this).remove();
        });
        $('#rateTemplateForm').trigger("reset");
        $('#rateHeader').text("Add new rate plan");
        $('select').prop('selectedIndex',0);
        $('input[name="_method"]').remove();
        $('input[name="rate_template_id"]').remove();
        $('#countries').val([]);
        $("#countries").select2("val", false);
        $("#countries").trigger('change');
        $('#wireAccountType').val([]);
        $("#wireAccountType").select2("val", false);
        $("#wireAccountType").trigger('change');
        $('#accountHeader').text('Add new rate template');
        $('#copyRate').attr('hidden', true);
        $('.copyRate').attr('hidden', true);
        $('#copyNameAccount').attr('hidden', true);

        $("#projectId").trigger('change');

    });

    $('body').on('click', '#clientRatesAll', function () {
        let part = $(this).is(':checked') ? 'all' : 'active';

        let projectId = $('#project').val();
        let url = $(this).data('url')

        window.location.href = url + '?part=' + part + '&project_id=' + projectId
    });


    $('body').on('change', '#status', function () {
        if ($(this).val() == 1) {
            $('#isDefault').prop('disabled', false);
        } else if ($(this).val() == 2 && !$('#isDefault').is(":checked")) {
            $('#isDefault').prop('disabled', true);
            $('#isDefault').prop('checked', false);
        }
    });

    $('body').on('click', '.transaction-history-tab', function () {
        $('.transaction-history-tab').each(function () {
            $(this).removeClass('tab-active');
            $(this).addClass('tab-inactive');
        });
        $(this).addClass('tab-active');
        $('#hash').val($(this).children('a').attr('id'))
    });

    $(document).ready(function () {
        if (($('#addRateTemplates').data('bs.modal') || {})._isShown) {
            if ($('#oldRateId').val()) {
                if (!$("#rateTemplateForm input[name=_method]").val()) {
                    $('form[name="rateTemplateForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
                }
            }
        }
    })
    $('#copyRate').on('click', function () {
        $('.copyRate').removeAttr('hidden');
        $('#copyNameAccount').removeAttr('hidden');
    })

    $('#copyRateSave').on('click', function () {
        $('#makeCopy').val(true);
        $('input[name="_method"]').remove();
        $('input[name="rate_template_id"]').remove();
        $('#rateTemplateForm').trigger('submit');
    })

    $('#copyProviderSave').on('click', function () {
        $('#makeCopy').val(true);
        $('input[name="_method"]').remove();
        $('input[name="rate_template_id"]').remove();
        $('#accountForm').trigger('submit');
    })

    let typeSwift = $('.type_swift').val();

    $('#typeAccount').on('change', function () {
        controlSwiftDetails(typeSwift, $(this).val())
    })

    $('#type').on('change', function () {
        controlSwiftDetails(typeSwift, $(this).val())
    })

});


function controlSwiftDetails(swiftType, currentType)
{
    if(currentType === swiftType) {
        $('.correspondent_bank_details').removeAttr('hidden')
        $('.intermediary_bank_details').removeAttr('hidden')
    }else {
        $('.correspondent_bank_details').attr('hidden', true)
        $('.intermediary_bank_details').attr('hidden', true)
    }
}
