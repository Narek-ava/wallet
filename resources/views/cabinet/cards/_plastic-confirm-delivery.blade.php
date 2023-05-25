<!-- Modal -->
<div class="modal fade" id="confirmDelivery" role="dialog">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">

        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                    <h5 class="modal-title text-center">{{ t('confirm_delivery_title') }}</h5>
                    <button type="button" class="close closeSuccessModal" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
            </div>
            <form action="{{ route('confirm.delivery.plastic.card') }}" method="POST">
                @csrf
                <input type="hidden" class="confirmModalId" name="id">
                <div class="modal-body">
                    <p>{{ t('confirm_delivery_message') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-lg btn-primary themeBtn">{{ t('confirm') }}</button>
                </div>
            </form>
        </div>

    </div>
</div>
