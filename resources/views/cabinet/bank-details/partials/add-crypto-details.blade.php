<div class="modal fade " id="cryptoDetail" tabindex="-1" aria-labelledby="cryptoDetailLabel" style="display:none" aria-hidden="true" >
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('add_crypto_wallet') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form action="{{ route('cabinet.crypto.check.address') }}" method="post" name="cryptoDetailUpdate" id="bankDetailUpdate">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="walletAddress" class="font-weight-bold">{{ t('wallet_address') }}</label>
                                <input id="walletAddress" class="form-control" name="wallet_address" value="{{ old('wallet_address') }}">
                                @error('wallet_address')<p class="text-danger">{!! $message !!}</p>@enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="cryptoCurrency" class="font-weight-bold">{{ t('currency') }}</label>
                                <select id="cryptoCurrency" class="form-control grey-rounded-border" name="crypto_currency">
                                    <option hidden="" value="">{{ t('select_option') }}</option>
                                    @foreach(\App\Enums\Currency::getList() as $currency)
                                        <option value="{{ $currency }}" @if(old('crypto_currency') == $currency) selected @endif>{{ $currency }}</option>
                                    @endforeach
                                </select>
                                @error('crypto_currency')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-lg btn-primary themeBtn loader">{{ t('save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
