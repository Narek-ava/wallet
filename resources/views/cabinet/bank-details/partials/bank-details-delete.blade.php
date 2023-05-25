@if($uAccount)
    <div class="modal fade bd-example-modal-sm" id="bankDetailDelete" tabindex="-1" aria-labelledby="bankDetailDeleteLabel" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content modal-content-centered">
                <div class="modal-header">
                    <h5 class="modal-title">{{ t('delete_template') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('cabinet.bank.details.delete') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>{{ t('delete_template_text') }}</p>
                        <input id="deleteBankAccountId" type="hidden" name="account_id" value="{{ $uAccount->id }}">
                        @error('account_id')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-lg btn-primary themeBtn">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
