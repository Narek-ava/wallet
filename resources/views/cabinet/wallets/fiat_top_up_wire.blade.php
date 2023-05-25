@extends('cabinet.layouts.cabinet')
@section('title', t('title_top_up_page') . strtoupper($fiatAccount->currency) . ' ' . t('title_wallet_page'))

@section('content')
    <style>
        .invalid {
            background: #ffdddd;
        }
        .bank-details-label {
            height: 345px;
        }
        .bank-details-label .checkmark.bank-details {
            height: 100%;
        }
        .bank-details-rows {
            font-size: 14px;
        }
    </style>
    <div class="row mb-5">
        <div class="col-md-12">
            <h3 class="mb-3 large-heading-section page-title">Top Up - {{ strtoupper($fiatAccount->currency) }} Wallet</h3>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>
        </div>
    </div>

    @include('cabinet.partials.session-message')
    <input type="hidden" id="fiatType" value="{{\App\Enums\AccountType::PAYMENT_PROVIDER_FIAT_TYPE_FIAT}}">
    @include('cabinet.wallets._fiat_wire_transfer_selection', ['submitAction' => $submitAction, 'currency' => $fiatAccount->currency])

    <div class="overlay"></div>

@endsection

