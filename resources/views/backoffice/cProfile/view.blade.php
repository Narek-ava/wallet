@extends('backoffice.layouts.backoffice',['showClients' => $profile->account_type, 'profileId' => $profile->profile_id])
@section('title', t('title_client_page') . $profile->profile_id)

@section('content')
    @php
        $update = false;
        $uAccount = null;
        $crypto = false;
        if ($accounts->count()) {
            $uAccount = $accounts->where('id', (old('u_account_id') ?? $accounts->first()->id))->first();
        }
    @endphp
    @if($errors->any())
        @foreach($errors->getMessages() as $key => $error)
            @if(str_starts_with($key, 'u_'))
                @php $update = true; @endphp
            @endif
            @if($key === 'wallet_address' || $key === 'crypto_currency')
                @php $crypto = true; @endphp
            @endif
            @break
        @endforeach
    @endif
    <div class="container-fluid p-0 ml-0 balance-outer crm-users-outer">
        <div class="row pb-5">
            <div class="col-md-12 pl-0">
                <div class="col-lg-5">
                    <h2 class="mb-3 large-heading-section">{{ t('backoffice_profile_page_header_title', ['profileId' => $profile->profile_id]) }}</h2>
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between">
                            <div class="balance mb-4">
                                {{ t('backoffice_profile_page_header_body') }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-md-12">
                @include('backoffice.partials.session-message')
                <div id="alert"></div>
                <div class="d-block clienInformationTabs">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs d-flex justify-content-between" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link pl-4 pr-4 active" data-toggle="tab" href="#general_info">{{ t('profile_wallets_general_info') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pl-4 pr-4" data-toggle="tab" href="#wallets">{{ t('ui_cabinet_menu_wallets') }}</a>
                        </li>
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::VIEW_OPERATION], $profile->cUser->project_id))
                            <li class="nav-item">
                                <a class="nav-link pl-4 pr-4" data-toggle="tab" id="operationsTabBtn"
                                   href="#transactions">{{ t('operations') }}</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link pl-4 pr-4" data-toggle="tab" href="#compliance">{{ t('compliance') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link pl-4 pr-4" data-toggle="tab"
                               href="#bankSettings">{{ t('ui_bank_settings') }}</a>
                        </li>
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_ANSWER_TICKETS], $profile->cUser->project_id))
                            <li class="nav-item">
                                <a class="nav-link pl-4 pr-4" data-toggle="tab"
                                   href="#tickets">{{ t('ui_tickets') }}</a>
                            </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link pl-4 pr-4" data-toggle="tab" href="#activity">{{ t('ui_bo_c_profile_page_activity') }}</a>
                         </li>
                        @if($profile->account_type === \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL && config('cratos.wallester.enabled') && $cardIssuingProviderExists)
                            <li class="nav-item">
                                <a class="nav-link pl-4 pr-4" data-toggle="tab"
                                   href="#cards">{{ t('ui_bo_c_profile_page_bank_card') }}</a>
                            </li>
                        @endif
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
{{--                        @include('backoffice.cProfile._view-tabs._general')--}}
                        @include('backoffice.cProfile._view-tabs._general_info')
                        @include('backoffice.cProfile._view-tabs._wallets')
                        @include('backoffice.cProfile._view-tabs._compliance')
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_ANSWER_TICKETS], $profile->cUser->project_id))
                            @include('backoffice.cProfile._view-tabs._tickets')
                        @endif
{{--                        @include('backoffice.cProfile._view-tabs._transactions')--}}
                        <div id="transactions" class="container-fluid tab-pane fade mt-5 pl-0"><br>
                            @include('backoffice.partials.transactions.index', ['client' => false])
                        </div>
                        @include('backoffice.cProfile._view-tabs._bank_settings')
                        <div id="activity" class="container-fluid tab-pane fade mt-5"><br>
                            <h2>{{t('ui_bo_c_profile_page_activity')}}</h2>
                        @include('backoffice.cProfile._view-tabs._activity')
                        </div>
                        <div id="cards" class="container-fluid tab-pane fade mt-5"><br>
                            <h2>{{t('ui_bo_c_profile_page_bank_card')}}</h2>
                            @include('backoffice.cProfile._view-tabs._bank_cards')
                        </div>
                        <div id="menu1" class="container-fluid tab-pane fade"><br>
                            <h3>Menu 1</h3>
                            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_CLIENTS], $profile->cUser->project_id))
        @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
            @include('backoffice.cProfile._edit-corporate-modal')
        @else
            @include('backoffice.cProfile._edit-modal')
        @endif
    @endif
@endsection
@section('scripts')
    <script type="text/javascript">
        $('body').on('change', '#rate-category-id', function () {
            let rateTemplateId = $(this).val();
            $.ajax({
                url: '{{ route('change.cprofile.rate.template.id') }}',
                type:'post',
                data: {'_token': '{{ csrf_token() }}', rateTemplateId, profileId: '{{ $profile->id }}'},
                success:function (data) {
                    if (data) {
                        $('#rateCategoryMessage').text('Changed successfully!');
                        setTimeout(function () {
                            $('#rateCategoryMessage').text('');
                        }, 2000);
                    }
                }
            })
        })

        $('body').on('change', '#is_merchant', function () {
            let isMerchant = $(this).prop('checked');
            $.ajax({
                url: '{{ route('update.is.merchant') }}',
                type:'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'isMerchant': isMerchant,
                    profileId: '{{ $profile->id }}'},
                success:function (data) {
                    if (data) {
                        $('#isMerchantMessage').text('Changed successfully!').show();
                        setTimeout(function () {
                            $('#isMerchantMessage').text('').hide();
                        }, 2000);
                        if (data.isMerchant) {
                            $('.webhookSettings').removeClass('d-none').addClass('d-block')
                        } else {
                            $('.webhookSettings').removeClass('d-block').addClass('d-none')
                        }
                    } else {
                        $('#is_merchant').prop('checked', true);
                        $('#isMerchantMessageError').text('Unable change').show();
                        setTimeout(function () {
                            $('#isMerchantMessageError').text('').hide()
                        }, 2000);
                    }
                }
            })
        })
    </script>

    <script>
        @if($errors->any())
            @if($errors->has('bank_detail_type'))
               $('.correspondent_bank_details').removeAttr('hidden')
               $('.intermediary_bank_details').removeAttr('hidden')
            @endif
        @endif
    </script>

    <script>
        $('body').ready(function () {
            if ('{{ $update }}') {
                $("#bankDetailUpdateModal").modal('show');
            }

            $('body').on('click', '#changeButton', function () {
                $('#changeButton').addClass('d-none');
                $('#saveButton').removeClass('d-none');
                $('.disabledField').each(function () {
                    $(this).removeAttr('disabled');
                });
            });
            let hashtag = '';
            if (document.URL.indexOf('#')+1) {
                hashtag = document.URL.substr(document.URL.indexOf('#')+1);
            }
            if ((hashtag === '' || hashtag === 'wallets') && '{{ $errors->any() }}') {
                $('#exampleEditUsers').modal('show');
            }

            if (hashtag === 'bankSettings' && '{{ $errors->any() }}') {
                if (('{{ $errors->has('wallet_address') || $errors->has('crypto_currency') }}')){
                    $('#cryptoDetail').modal('show');
                } else if (!'{{ $update }}') {
                    $('#bankDetail').modal('show');
                }
            }

            if (hashtag === 'tickets' && '{{ $errors->any() }}' && '{{ $errors->has('question') || $errors->has('subject') || $errors->has('file') }}') {
                $('#addTicket').modal('show');
            }


            $('#bankDetail').on('hidden.bs.modal', function () {
                $('#bankDetail .text-danger').text('');
                $('#templateName').attr('value', '');
                $('#iban').attr('value', '');
                $('#swift').attr('value', '');
                $('#account_number').attr('value', '');
                $('#account_holder').attr('value', '');
                $('#bank_name').attr('value', '');
                $('#bank_address').attr('value', '');
                $('#country').val(0);
                $('#currency').val(0);
            });

            $('#cryptoDetail').on('hidden.bs.modal', function () {
                $('#cryptoDetail .text-danger').text('');
                $('#walletAddress').attr('value', '');
            });

            function getWithZero(val) {
                if (val < 10){
                    return '0' + val;
                }
                return val;
            }

            function getCorrectDate(date) {
                return getWithZero(date.getFullYear()) + '-' +
                    getWithZero(date.getMonth() + 1) + '-' +
                    getWithZero(date.getDate()) +  ' ' +
                    getWithZero(date.getHours()) +  ':' +
                    getWithZero(date.getMinutes());
            }

            $('body').on('click', '.bankDetailBlock', function () {
                $("#bankDetailUpdateModal").modal('show');

                $('.text-danger').each(function () {
                    $(this).text('');
                });
                let accountId = $(this).data('account-id');
                $('#deleteBankAccountId').val(accountId);
                $('.bankDetailBlock').each(function () {
                    $(this).removeClass('bankDetailBlockBorderActive').addClass('bankDetailBlockBorderInactive');
                    $(this).children('p.date-styles').remove();
                });
                $(this).addClass('bankDetailBlockBorderActive').removeClass('bankDetailBlockBorderInactive');
                let detail = $(this);
                {{--            $('#deleteBankDetail').attr('href', '{{ route('backoffice.bank.details') }}' + '/' + accountId);--}}
                $.ajax({
                    url: '/backoffice/account/' + accountId,
                    success: function (data) {
                        console.log(data);
                        if (data) {
                            let createdAt = new Date(data.created_at);
                            let updatedAt = new Date(data.updated_at);
                            detail.append('<p class="date-styles">Cretated: ' + getCorrectDate(createdAt) + '<br>Last change: ' + getCorrectDate(updatedAt) + '</span></p>');
                            $('input[name=u_account_id]').attr('value', data.id);
                            $('#updateTemplateName').attr('value', data.name);
                            $('#updateIban').attr('value', data.wire.iban);
                            $('#updateSwift').attr('value', data.wire.swift);
                            $('#updateAccountHolder').attr('value', data.wire.account_beneficiary);
                            $('#updateAccountNumber').attr('value', data.wire.account_number);
                            $('#updateBankName').attr('value', data.wire.bank_name);
                            $('#updateBankAddress').attr('value', data.wire.bank_address);
                            $('#updateCountry').val(data.country);
                            $('#updateCurrency').val(data.currency);
                            $('#updateType').val(data.account_type);
                            if (data.account_type == {{ \App\Enums\AccountType::TYPE_WIRE_SWIFT }}) {
                                $('.u_correspondent_bank_details').removeAttr('hidden')
                                $('.u_intermediary_bank_details').removeAttr('hidden')
                                $('#updateCorrespondentBank').val(data.wire.correspondent_bank);
                                $('#updateCorrespondentBankSwift').val(data.wire.correspondent_bank_swift);
                                $('#updateIntermediaryBank').val(data.wire.intermediary_bank);
                                $('#updateIntermediaryBankSwift').val(data.wire.intermediary_bank_swift);
                            }else {
                                $('.u_correspondent_bank_details').attr('hidden', true)
                                $('.u_intermediary_bank_details').attr('hidden', true)
                            }

                        }
                    }
                })
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('body').on('click', '#createTicketIcon', function () {$('#createTicketFile').click();});
            $('body').on('click', '#updateTicketIcon', function () {$('#updateTicketFile').click();});
            $('#createTicketFile').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileName').html(filename);
            });
            $('#updateTicketFile').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileNameTicket').html(filename);
            });
            $('#addTicket').on('hidden.bs.modal', function () {
                $('.text-danger').each(function () {
                    $(this).text('');
                })
            })
            setTimeout(function () {
                $('#alertMessage').remove();
            }, 2000);

            $(document).ready(function () {
                if ('{{ old('ticket-id') }}') {
                    let id = "{{ old('ticket-id') }}";
                    $('div[data-ticket-id="' + id + '"]').click();
                }
            });

            function getWithZero(val) {
                if (val < 10){
                    return '0' + val;
                }
                return val;
            }

            function getCorrectDate(date) {
                return getWithZero(date.getFullYear()) + '-' +
                    getWithZero(date.getMonth() + 1) + '-' +
                    getWithZero(date.getDate()) +  ' ' +
                    getWithZero(date.getHours()) +  ':' +
                    getWithZero(date.getMinutes()) +  ':' +
                    getWithZero(date.getSeconds());
            }

            function viewMessage(id, type) {
                $.ajax({
                    url: '/backoffice/view-message/' + id + '/{{ \App\Enums\TicketMessages::VIEW_BUSER }}'
                })
            }

            $('body').on('click', '.ticket-style', function () {
                $('#messageBox').removeClass('d-none');
                let ticket = $(this);
                ticket.removeClass('ticket-inactive').addClass('ticket-active');
                let ticketId = ticket.data('ticket-id');
                $('#ticketIdMessageForm').val(ticketId);
                $('.ticket-style').each(function () {
                    if ($(this).data('ticket-id') !== ticketId) {
                        $(this).removeClass('ticket-active').addClass('ticket-inactive');
                    }
                });
                $.ajax({
                    url: '/backoffice/ticket/' + ticketId + '/' + '{{ $profile->cUser->id }}',
                    success: function (data) {
                        if (data) {
                            console.log(data)
                            $('#closeTicketButton').html('');
                            if (data.status !== '{{ \App\Enums\TicketStatuses::getName(\App\Enums\TicketStatuses::STATUS_CLOSE) }}') {
                                $('#closeTicketButton').prepend('<a href="' + '{{ route('backoffice.close.ticket', ['id' => false]) }}' + '/' + data.id + '" class="btn themeBtn close-ticket-btn" style="border-radius: 20px" type="submit">' + '{{ t('close_ticket') }}' + '</a>');
                            }
                            $('#id').text(data.ticket_id);
                            $('#subject').text(data.subject);
                            $('#status').text(data.status);
                            $('#messagingBlock').html('');
                            if ($.isEmptyObject(data.messages)) {
                                $('#messagingBlock').prepend('<div class="col-md-12 message-block">\n<h1>' + '{{ t('empty_messages')}}' + '</h1></div>');
                            }
                            let allMessagesViewed = false;
                            for (let ticketMessage of data.messages) {
                                let statusIcon = ticketMessage.viewed ? 'statusIconInactive' : 'statusIconActive';
                                if (!ticketMessage.viewed) {
                                    allMessagesViewed = true;
                                }
                                let messageBlock = '<div class="col-md-12 message-block">\n' +
                                    '<div class="statusIconMessage ' + statusIcon + '"></div>\n' +
                                    '<p><span class="textBold">' + ticketMessage.creatorName + '</span> ' + getCorrectDate(new Date(ticketMessage.created_at));
                                if (ticketMessage.file) {
                                    messageBlock += ' <i class="fa fa-file"></i><span class="pointerClass underlineText"><a href="/backoffice/backoffice-download-ticket-message-pdf-file/' + ticketMessage.file + '">' + ticketMessage.file + '</a></span></p>\n';
                                }
                                messageBlock += '<p>' + ticketMessage.message + '</p>\n' +
                                    '</div>';
                                $('#messagingBlock').prepend(messageBlock);
                            }
                            if (!allMessagesViewed) {
                                ticket.find(">:first-child").removeClass('statusIconActive').addClass('statusIconInactive');
                                let count = parseInt($('.backofficeTicketsCount').text());
                                if (count > 1) {
                                    $('.backofficeTicketsCount').html(--count);
                                } else {
                                    $('.backofficeTicketsCount').addClass('d-none');
                                }
                            }
                            viewMessage(data.id, 5);
                        }
                    }
                });
            });

            function searchTicket(sInput)
            {
                $.ajax({
                    url: '{{ route('cabinet.help.desk') }}',
                    data: {sInput}
                });
            }

            $('body').on('change', '#search', function () {
                let search = $(this).val();
                let status = $('.requestStatus').text();
                if($.isNumeric(search)) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?sInput=' + search + '&id=1&status=' + status;
                } else if (search.length > 2) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?sInput=' + search + '&id=0&status=' + status;
                } else if (search == '' && !search) {
                    location.href = '{{ route('cabinet.help.desk') }}' + '?status=' + status;
                }
            });
        });

        $('body').on('click', '.ticket-status-buttons', function () {
            if ($(this).hasClass('ticket-inactive')) {
                $('.ticket-status-buttons').each(function () {
                    $(this).removeClass('ticket-active').addClass('ticket-inactive');
                });
                $('.requestStatus').removeClass('requestStatus');
                $('#messageBox').addClass('d-none');
                $(this).removeClass('ticket-inactive').addClass('ticket-active');
                $(this).find(">:first-child").addClass('requestStatus');
                $('.tickets').each(function () {
                    if ($(this).hasClass('d-none')) {
                        $(this).removeClass('d-none');
                    } else {
                        $(this).addClass('d-none');
                    }
                });
            }
        });

    </script>
    <script>
        $(document).ready(function () {
            $('body').on('click', '.walletItem', function () {
                let walletBlock = $(this).parent().parent();
                let accountId = $(this).data('account-id');
                $.ajax({
                    url: '{{ route('backoffice.account.drop') }}',
                    type: 'post',
                    data: {accountId, '_token': '{{ csrf_token() }}'},
                    success: function (data) {

                        let messageType = '';
                        let message = '';
                        if (data){
                            walletBlock.remove();
                            messageType = '{{ t('enum_log_result_success') }}';
                            message = '{{ t('drop_client_wallet_successful') }}';
                        } else {
                            messageType = '{{ t('enum_log_result_warning') }}';
                            message = '{{ t('drop_client_wallet_not_successful') }}';
                        }
                        if ($('#walletsSection').children().length == 0) {
                            let emptyWallets = '<h6 class="mt-3">' + '{{ t('have_not_crypto_wallets_yet') }}' + '</h6>';
                            $('#walletsSection').html(emptyWallets);
                        }
                        $('#alert').html(
                            '<div id="walletDropAlert" class="alert alert-' + messageType + ' alert-dismissible fade show" role="alert">' +
                            '<h4>' + message + '</h4>' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                            '<span aria-hidden="true">&times;</span>' +
                            '</button>' +
                            '</div>');
                        setTimeout(function () {
                            $('#walletDropAlert').remove();
                        }, 20000)

                    }
                })
            });
        })
    </script>

    <script src="/js/cabinet/wallester-order-card.js"></script>
    <script>
        $(document).ready(function () {
            $('#cardType').change(function () {
                let cardType = $(this).val();
                if ('{{ \App\Enums\WallesterCardTypes::TYPE_VIRTUAL }}' == cardType) {
                    $('#cardDeliveryAddress').hide();
                } else {
                    $('#cardDeliveryAddress').show();
                }
            })
        });

        @if($errors->any())
        @if($errors->has('open_card_order_modal'))
        $('#credit-card').click();
        @endif
        @endif
    </script>
@endsection
