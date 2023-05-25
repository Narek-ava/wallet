<div class="modal fade" id="complianceDocs" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    {{t('ui_compliance_retry_modal_title')}}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id))

                <form autocomplete="off" class="form " role="form" method="post"
                      action="{{route('backoffice.compliance.requestDocumentsDelete')}}">
                    <p id="docLoading" class="align-content-center">
                        <img src="{{ config('cratos.urls.theme') }}images/loader.gif">
                    </p><h5
                        class="no-image-message d-none align-content-center">{{ t('ui_c_profile_images_deleted') }}</h5>
                    <div class="modal-body" style="display: none">
                        <div class="d-block">
                            {{ csrf_field() }}
                            <input type="hidden" name="inspectionId" id="inspectionId">
                            <input type="hidden" name="complianceRequestId"
                                   value="{{$lastApprovedComplianceRequest->id}}">
                            <table id="compliance_document_table" class="table  dt-responsive nowrap">
                                <thead>
                                <tr>
                                    <th scope="col">
                                        <input type="checkbox" checked id="retryCheckAll">
                                    </th>
                                    <th scope="col" class="textRed textBold">
                                        {{t('ui_compliance_retry_modal_document_name')}}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="col-md-12 mt-5">
                                <label class="form-check-label">
                                    {{t('ui_compliance_retry_modal_message_for_user_label')}}
                                </label>
                                <textarea class="form-control" name="requiredDocsMessage" required></textarea>
                            </div>

                            <div class="col-md-12 mt-5">
                                <label class="form-check-label">
                                    {{t('ui_compliance_retry_modal_message_for_user_label_description')}}
                                </label>
                                <textarea class="form-control" name="docsMessageRequestDescription" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="btn btn-primary">{{t('ui_compliance_retry_modal_request_again_button')}}
                        </button>

                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{t('ui_compliance_retry_modal_close_button')}}</button>
                    </div>
                </form>

            @endif
        </div>
    </div>
</div>
