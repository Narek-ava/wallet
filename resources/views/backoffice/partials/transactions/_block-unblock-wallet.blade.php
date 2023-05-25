<!-- Modal -->
<div class="modal fade" id="blockUnblockWalletModal" tabindex="-1" role="dialog" aria-labelledby="blockWalletTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-dark"
                    id="blockWalletLongTitle">
                    @if($cryptoAccountDetail->blocked)
                        {{ t('unblock_wallet') }}
                    @else
                        {{ t('block_wallet') }}
                    @endif
                   </h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-dark">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ $cryptoAccountDetail->blocked ? route('backoffice.wallet.unblock') : route('backoffice.wallet.block') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="crypto_account_detail_id" id="cryptoAccountDetailIdInput">
                    <div class="col-md-12">
                        <div class="row">
                            @if(!$cryptoAccountDetail->blocked)
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h6>{{ t('operation_number') }}</h6>
                                        <select id="" class="form-control grey-rounded-border"
                                                name="operation_id">
                                            @foreach($cryptoAccountDetail->operations() as $operation)
                                                <option value="{{ $operation->id }}">{{ $operation->operation_id }}</option>
                                            @endforeach
                                        </select>
                                        @error('operation_id')
                                        <small class="error text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="uploadDocumentField">{{ t('upload_document') }}</label>
                                    <input type="file" class="form-control-file"
                                           name="file" value="" id="uploadDocumentField">
                                    <span class="textBold" id="uploadFileName"></span>
                                    @error('file')<p class="error-text">{{ $message }}</p>@enderror
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
                        <textarea placeholder="Type reason here..." name="reason" id="replyMessage" cols="30" rows="7" class="">{{ old('reason') }}</textarea>
                        @error('reason')<p class="error-text">{{ $message }}</p>@enderror
                        <button type="submit" class="block-wallet btn btn-dark themeBtnDark mt-4 text-center">
                            {{ $cryptoAccountDetail->blocked ? t('unblock') : t('block') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
