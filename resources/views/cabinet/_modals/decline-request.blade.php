<div class="modal fade" id="declineRequest" tabindex="-1" role="dialog" aria-labelledby="declineRequestLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="declineRequestLabel">{{ t('operation_request_cancel_confirm') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="text-center operation-info"></h5>
            </div>
            <div class="modal-footer">
                <form id="decline-operation-form" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-lg btn-primary themeBtn">{{ t('yes') }}</button>
                    <button type="button" class="btn btn-secondary" style="border-radius: 30px" data-dismiss="modal">{{ t('operation_request_close_button') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

