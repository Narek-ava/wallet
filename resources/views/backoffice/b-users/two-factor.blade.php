<?php
/** @var \App\Models\Backoffice\BUser[] $bUsers */
?>

@extends('backoffice.layouts.backoffice')

@section('title', t('title_clients_page'))

@section('content')
    <div class="row">

        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_clients_page') }}</h2>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>



    <div class="row mt-5">
        <h2>{{ t('google_two_factor') }}</h2>

        <div class="col-md-12">
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
                    <h4>{{ session()->get('success') }}</h4>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
        <div class="col-md-8">
            <div>
                {!!  t('ui_2fa_google_register_text_1') !!}
                {{ t('ui_2fa_google_register_text_2') }}
                <div class="col-md-12">
                    <div id="2fa-google-qr-image">
                        {!! $twoFactorAuthData['qrImage'] !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <h4>{{ t('private_key') }}</h4>
                    <div id="2fa-google-secret" class="d-block font36bold">{{ $twoFactorAuthData['secret'] }}</div>
                    {{ t('enter_code_manually') }}

                    <p></p>
                        <p>
                            <button id="generate-new-code" class="btn btn-lg btn-primary themeBtn" type="button">
                                {{ t('generate_new_code') }}
                            </button>
                        </p>
                    <div class="row">
                        {{ t('ui_2fa_google_register_text_3') }}
                    </div>
                    <p></p>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="text" id="2fa-google-enable-confirm-code"
                                   class="form-control col-md-5" placeholder="Enter code" required>
                            <div class="error-text-list"></div>
                            <p class="text-success" id="successMessage"></p>
                            <p class="error-text"></p>
                        </div>
                    </div>
                    <div>
                        <button id="2fa-google-enable-confirm-button" class="btn btn-lg btn-primary themeBtn"
                                type="button">
                            {{ t('ui_2fa_google_register_button') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            hideStatuses()
            $('#generate-new-code').on('click', function () {
                window.location.reload();
            })
            $('#2fa-google-enable-confirm-button').on('click', function () {
                hideStatuses()
                var code =  $('#2fa-google-enable-confirm-code').val();
                if(!(/^([0-9]{1,6})$/.test(code))) {
                    $('.error-text').html('Invalid format');
                    $('.error-text').show();
                    return false;
                }

                $.ajax({
                    url: '{{ route('b-users.twoFactor.confirm') }}',
                    type:'post',
                    data: {'_token': '{{ csrf_token() }}', code },
                    success:function (data) {
                        if(data.success) {
                            $('#successMessage').text('Enable 2FA successfully!');
                            $('.text-success').show();
                            setTimeout(function () {
                                $('#2fa-google-enable-confirm-code').val('');
                                $('#successMessage').text('');
                            }, 2000);
                        } else {
                            $('.error-text').html(data.error);
                            $('.error-text').show();
                        }
                    }
                })
            })

            function hideStatuses() {
                $('.error-text').hide();
                $('.text-success').hide();
            }
        });


    </script>
@endsection
