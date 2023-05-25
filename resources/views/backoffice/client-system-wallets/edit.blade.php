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
        <div class="col-md-6">
            <form action="{{ route('client-wallets.update', ['client_wallet' => $clientWallet]) }}" method="POST">
                @csrf
                @method('PUT')
                <h3> {{ t('client_system_wallet_update') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputWalletId">{{ t('ui_wallet_id') }}</label></h5>
                        <input autocomplete="off" id="inputWalletId" name="walletId" type="text" value="{{ $clientWallet->wallet_id }}"
                               class="form-control{{ $errors->has('walletId') ? ' is-invalid' : '' }}" required>
                    </div>
                    @error('walletId')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="passphraseInput">{{ t('ui_passphrase') }}</label></h5>
                        <input autocomplete="off" type="password" autocomplete="false" name="passphrase" id="passphraseInput" class="form-control{{ $errors->has('passphrase') ? ' is-invalid' : '' }}">
                        @error('passphrase')
                        <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    @error('key')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputCurrency">{{ t('currency') }}</label></h5>
                        <select class="form-control" style="min-width: 100px" name="currency" id="inputCurrency">
                            <option value=""></option>
                        @foreach(\App\Enums\Currency::getList() as $key => $currency)
                                <option value="{{ $currency }}" @if($currency == $clientWallet->currency) selected @endif>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('currency')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class=" col-md-3 form-group mt-5 pl-0">
                    <div class="form-label-group">
                        <button class="btn btn-lg btn-primary themeBtn btn-block"
                                type="submit">{{ t('save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
