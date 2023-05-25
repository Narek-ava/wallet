<!-- Modal -->
<div class="modal fade" id="blockWallet" tabindex="-1" role="dialog" aria-labelledby="blockWalletTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-dark" id="blockWalletLongTitle">{{ t('withdrawal_crypto_block_wallet') }}</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-dark">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="exampleFormControlFile1">{{ t('upload_document') }}</label>
                                    <input type="file" class="form-control-file" id="exampleFormControlFile1">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <p>{{ t('wallet_blocking_reason') }}</p>
                                <p>{{ t('wallet_blocking_reason_description') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <p>{{ t('wallet_reason_of_block') }}</p>
                        <input class="form-control border-bottom" type="text" placeholder="">
                        <input class="form-control border-bottom" type="text" placeholder="">
                        <input class="form-control border-bottom" type="text" placeholder="">

                        <button type="button" class="btn btn-dark themeBtnDark mt-4 w-25">{{ t('unlock') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
