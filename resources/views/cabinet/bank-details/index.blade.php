@extends('cabinet.layouts.cabinet')
@section('title', t('bank_details'))

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
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="row mb-5">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section page-title">{{ t('bank_details') }}</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance">
                                {{ t('ui_webatach_request') }}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
                    </div>
                </div>
            </div>
        </div>
    </div>
{{--    <a href="{{route('backoffice.profile.sendTestCompletedCard', ['success' => 1])}}"--}}
{{--       class="btn btn-lg btn-primary themeBtn mb-4">Success</a>--}}
{{--    <a href="{{route('backoffice.profile.sendTestCompletedCard', ['success' => 0])}}"--}}
{{--       class="btn btn-lg btn-primary themeBtn mb-4">Fail</a>--}}

    @include('cabinet.partials.session-message')
    @include('cabinet.bank-details.partials.bank-details')
    @include('cabinet.bank-details.partials.crypto-wallets')
    @include('cabinet.bank-details.partials.bank-details-view')
    @include('cabinet.bank-details.partials.bank-details-delete')
    @include('cabinet.bank-details.partials.add-bank-details')
    @include('cabinet.bank-details.partials.add-crypto-details')

    <div class="overlay"></div>

@endsection

@section('scripts')
    <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
    <script src="/js/cabinet/compliance.js"></script>
    <script>
        $(document).ready(function () {
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

            if ('{{ $errors->any() }}' && !'{{ $update }}' && !'{{ $crypto }}') {
                $('#bankDetail').modal('show');
            }
            if ('{{ $crypto }}') {
                $('#cryptoDetail').modal('show');
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

            $('#type').on('change', function () {
                let swiftType = $('.type_swift').val();
                if($(this).val() === swiftType) {
                    $('.correspondent_bank_details').removeAttr('hidden')
                    $('.intermediary_bank_details').removeAttr('hidden')
                }else {
                    $('.correspondent_bank_details').attr('hidden', true)
                    $('.intermediary_bank_details').attr('hidden', true)
                }
            })

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
                $('#deleteBankDetail').attr('href', '{{ route('cabinet.bank.details') }}' + '/' + accountId);
                $.ajax({
                    url: 'account/' + accountId,
                    success: function (data) {
                        if (data) {
                            let endDate = data.wire.updated_at > data.updated_at ? data.wire.updated_at : data.updated_at;
                            let createdAt = new Date(data.created_at);
                            let updatedAt = new Date(endDate);
                            $(".dates-container").html('<p class="date-styles"><br>Cretated: ' + getCorrectDate(createdAt) + '<br>Last change: ' + getCorrectDate(updatedAt) + '</span></p>');
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

                            $(".bank-template").removeClass("d-none");
                            $('#updateCountry').change();
                            $('#updateCurrency').change();
                            $('#updateType').change();
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

            @if($errors->any())
                @if($errors->has('bank_detail_type'))
                    $('.correspondent_bank_details').removeAttr('hidden')
                    $('.intermediary_bank_details').removeAttr('hidden')
                @endif
            @endif
        });
    </script>
@endsection
