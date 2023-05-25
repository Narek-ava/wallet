@if(($profile->compliance_level == \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_0 && !$profile->hasPendingComplianceRequest()) || $complianceData['lastRequestIfDeclined']  || $complianceData['retryComplianceRequest'] )
    <div class="col-md-12 mt-4">
        <div class="row pb-4">
            <div class="col-md-12 pl-0">
                <h4 class="mb-5 text-left">{{t('ui_compliance_requests')}}</h4>
                <div class="col-md-4 pl-0">
                    <div class="compliance common-shadow-theme p-3 pt-4 pb-4 border-dark">
                        @if($profile->compliance_level == \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_0 && !$complianceData['lastRequestIfDeclined'] )
                        <div class="col"><h2 class="mb-3">{{t('ui_compliance_0_level_info_box_title')}}</h2>
                        </div>
                        <div class="row m-0">
                            <div class="col-lg-12">
                                <p>{{t('ui_compliance_0_level_info_box_text')}}</p>
                            </div>
                            @if($complianceData['sumSubNextLevelName'])
                                <div class="col-lg-12">
                                    <button
                                        class="btn btn-lg btn-primary themeBtn register-buttons verify_compliance"
                                        data-toggle="modal" data-target="#complianceModal">
                                        {{t('ui_compliance_0_level_info_box_button_text')}}
                                    </button>
                                </div>
                            @endif
                        </div>
                            @else
                            <div class="col-lg-12">
                                <h3 class="textBold complianceRequestMsg pt-0">{{ t('compliance_request') }}</h3>
                                <p class="textBold complianceRequestMsg pt-0">
                                    {{$complianceData['retryComplianceRequest'] ? $complianceData['retryComplianceRequest']->message : $complianceData['lastRequestIfDeclined']->message}}<br>
                                    {{$complianceData['retryComplianceRequest'] ? $complianceData['retryComplianceRequest']->description : $complianceData['lastRequestIfDeclined']->description}}
                                </p>
                            </div>
                            <div class="col-lg-12">
                                <button
                                    class="btn btn-lg btn-primary themeBtn register-buttons verify_compliance"
                                    data-toggle="modal" data-target="#complianceModal">
                                    {{t('ui_compliance_upload_documents_button_text')}}
                                </button>
                            </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
