@extends('backoffice.layouts.backoffice',['showClients' => $profile->account_type, 'profileId' => $profile->profile_id])
@section('title', t('title_client_page') . $profile->profile_id)

@section('content')
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
        <div class="row mb-3 pb-2">
            <div class="col-md-12">
                <h2 class="mb-3 large-heading-section">
                    {{ t('backoffice_profile_page_header_title', ['profileId' => $profile->profile_id]) }}</h2>
                <div class="row">
                    <div class="col-md-4 d-flex justify-content-between">
                        <div class="balance mb-4">
                            {{ t('backoffice_profile_page_header_body') }}
                        </div>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col-md-12">
                @include('backoffice.partials.session-message')
                <div class="wallet-detail">
                    <a href="{{ route('backoffice.profile', $profile->id) }}" style="font-size: 22px; color: black">Client {{ $profile->profile_id }}/</a>
                    <a href="{{ route('backoffice.profile', $profile->id) }}" style="font-size: 22px; color: black">{{ t('title_wallets_page') }} /</a>
                    <h5 class="d-inline-block">{{ $fiatAccount->currency }}</h5>
                    <div class="container-fluid pl-0"><br>
                        <div class="col-md-12 mb-5 pl-0">
                            <div class="row">
                                <div class="col-md-6 pl-0">
                                    <div class="wallet-style wallet-btc btc mb-4" style="max-width: 60%">
                                        <div class="label">
                                            <img
                                                src="{{ asset('/cratos.theme/images/' . (\App\Enums\Currency::IMAGES[$fiatAccount->currency] ?? '')) }}"
                                                width="35px" alt="">
                                        </div>
                                        <div class="d-block">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h3 class="wallet-name">{{ $fiatAccount->currency }} {{  $fiatAccount->displayAvailableBalance()  }}</h3>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="textBold">{{ t('created_on') }}</span> <br><br>
                                                    <span
                                                        class="wallet-created-at">{{ $fiatAccount->created_at }}</span>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('backoffice.partials.transactions.transaction', ['client' => false, 'cryptoAccountDetail' => $fiatAccount, 'operations' => $operations])
                </div>
            </div>
        </div>
    </div>
    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
        @include('backoffice.cProfile._edit-corporate-modal')
    @else
{{--        @include('backoffice.cProfile._edit-modal')--}}
    @endif
    <script src="/js/backoffice/wallets.js" ></script>
    <script>
        $(document).ready(function () {
            $('body').on('click', '.blockUnblockButton', function () {
                let cryptoAccountDetailId = $(this).data('crypto-account-detail-id');
                $('#cryptoAccountDetailIdInput').val(cryptoAccountDetailId);
            });
            $('#uploadDocumentField').on("change", function(){
                var filename = $(this).val().split('\\').pop();
                $('#uploadFileName').html(filename);
            });
        });
    </script>

    <script>
        function getWithdrawFee(cryptoAccountId) {

            $.ajax({
                type: "POST",
                url: 'withdraw-crypto/get-withdraw-fee',
                data: {
                    "_token": "{{ csrf_token() }}",
                    "cryptoAccountId": cryptoAccountId,
                    "cProfileId" : "{{ $profile->id }}",
                    "amount": $('.withdraw-amount').val(),
                },
                success: function (response) {
                    $('.withdraw-fee').text(response.result);
                    $('.withdraw-fee-percent').text('(' + (response.commissionPercent ?? '-') + '%)');
                }
            });
        }

        $(document).ready(function () {
            if ('{{ $errors->any() }}') {
                if( '{{ $errors->has('to_wallet') || $errors->has('amount') || $errors->has('from_wallet')}}' ){
                    $('#withdrawCryptoModal').modal('show');
                }else if ('{{$errors->has('crypto_account_detail_id') || $errors->has('operation_id') || $errors->has('file') || $errors->has('reason')}}') {
                    $('#blockUnblockWalletModal').modal('show');
                }
            }
        });
    </script>


@endsection
