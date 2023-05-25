<div class="modal fade bd-example-modal-sm-fiat mt-5 m-auto" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('provider_add_fiat_new') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="addWalletForm" action="{{ route('cabinet.wallets.add.fiat', ['cProfile' => $cProfile]) }}" method="post">
                <div class="modal-body">
                    <p>{{ t('ui_choose_fiat') }}</p>
                    @csrf
                    <label for="inputEmail" class="">{{ t('ui_fiat') }}</label>
                    <select id="selectFiat" class="w-100 mb-3" name="fiat" onchange="enableAddFiatButton()" style="border: 1px solid #bfb7b7;">
                        <option value="">{{ t('ui_select_coin') }}</option>
                        @foreach($allowedFiatForAccount as $fiat)
                            <option value="{{  $fiat }}">{{ $fiat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn themeBtn btnWhiteSpace addFiat" disabled>Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
