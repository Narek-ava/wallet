<div id="compliance" class="container-fluid tab-pane fade mt-5 pl-0"><br>
    <h2>Compliance</h2>
    <br>
    <br>
    <div class="row pl-3">
        <div class="common-shadow-theme col-md-8 p-4" style="max-width: 450px;">
            <div class="row">
                <div class="col-6">
                    <div class="mb-0 verification_level_txt">{{t('ui_compliance_'.$profile->compliance_level.'_level_top_box_title')}}</div>
                    <h6
                        class="d-block font-weight-bold">{{t('ui_compliance_'.$profile->compliance_level.'_level_top_box_sub_title')}}</h6>
                </div>

                @if($complianceRequest = $profile->getPendingComplianceRequest())
                    <div class="col-6 compliance_status_block p-0">
                        <div>{{t('ui_cprofile_status')}}</div>
                        <h6 class="orange mb-4">{{\App\Enums\ComplianceRequest::getName(\App\Enums\ComplianceRequest::STATUS_PENDING)}}</h6>
                    </div>
                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id))
                        <form action="{{ route('backoffice.compliance.requestCancel') }}"
                              method="post">
                            {{ csrf_field() }}
                            <input type="hidden" value="{{ $complianceRequest->id }}" name="complianceId">
                            <button type="submit"
                                    style="position: absolute;font-size: 15px;top:35px;right:30px;cursor:pointer; background-color: transparent; border: none">
                                X
                            </button>
                        </form>
                        @if(config('app.env') != 'prod')
                            <div class="col-12 pt-4">
                                {{--@TODO REMOVE THIS LINKS SECTION  --}}
                                {{---------------------}}
                                <a href="{{route('backoffice.profile.sendTestCompletedCompliance', ['profileId' => $profile->id, 'success' => 1])}}"
                                   class="btn btn-lg btn-primary themeBtn mb-4">Send Test Completed Request SUCCESS</a>
                                <a href="{{route('backoffice.profile.sendTestCompletedCompliance', ['profileId' => $profile->id, 'success' => 0])}}"
                                   class="btn btn-lg btn-primary themeBtn themeBtnDark mb-4">Send Test Completed Request
                                    FAIL</a>
                                {{---------------------}}
                            </div>
                        @endif
                    @endif
                @elseif($lastApprovedComplianceRequest && !$retryComplianceRequest)
                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id))
                        <div class="col-12 compliance_status_block p-0 m-0 mt-3">
                            <button type="button" class="btn btn-lg btn-primary themeBtn" data-toggle="modal"
                                    data-target="#complianceDocs" id="retry_compliance"
                                    data-applicantid="{{$lastApprovedComplianceRequest->applicant_id}}">
                                {{t('ui_compliance_retry_button')}}
                            </button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
        @if($lastApprovedComplianceRequest && ($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id)))

        <div class="common-shadow-theme approved-compliance-box col-md-3">
            <div class="d-block">
                <form action="{{route('backoffice.compliance.renew', ['profileId' => $profile->id])}}" method="post"
                    class="has-confirm" data-message="{{t('ui_compliance_request_date_renew_confirmation')}}"
                >
                    {{ csrf_field() }}

                    <input required
                    value="" data-provide="datepicker" name="renewDate"
                    data-date-format="yyyy-mm-dd" data-date-start-date="{{$renewMinDate}}"
                    class="filter_el form-control" placeholder="{{t('ui_compliance_renew_datepicker_placeholder')}}">
                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mt-3" type="submit" >
                        {{t('ui_compliance_renew_button_text')}}</button>
                </form>

            </div>
        </div>
    @endif
    </div>
    <div class="clearfix"></div>
    @if($retryComplianceRequest || $lastRequestIfDeclined)
        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id))

            <div class="col-md-12 mt-5 pl-0">
                <h1 class="large-heading-section mb-5">{{t('ui_compliance_requests')}}</h1>
                <div class="col-md-5 pl-0">
                    <div class="compliance common-shadow-theme p-2">
                        <div class="row m-0">
                            <div class="col-lg-12">
                                <p class="textBold complianceRequestMsg">{{$retryComplianceRequest ? $retryComplianceRequest->message : $lastRequestIfDeclined->message}}</p>
                                <p class="textBold complianceRequestMsg mt-2">{{$retryComplianceRequest ? $retryComplianceRequest->description : $lastRequestIfDeclined->description}}</p>
                            </div>
                        </div>
                        <form action="{{ route('backoffice.compliance.requestCancel') }}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden"
                                   value="{{ $retryComplianceRequest ? $retryComplianceRequest->id : $lastRequestIfDeclined->id }}"
                                   name="complianceId">
                            <button type="submit"
                                    style="position: absolute;font-size: 15px;top:35px;right:30px;cursor:pointer; background-color: transparent; border: none">
                                X
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
    @if($lastApprovedComplianceRequest)
        <br>
        <br>
    <div class="row">
        <div class="common-shadow-theme approved-compliance-box col-md-4 mb-5 float-left">
            <div class="d-block">
                <p class="mb-3 textRed textBold">{{t('ui_compliance_user_account_verification_date_text')}}</p>
                <div class="textBold">{{Carbon\Carbon::parse($lastApprovedComplianceRequest->updated_at)->format('d.m.Y')}} | {{Carbon\Carbon::parse($lastApprovedComplianceRequest->updated_at)->format('h:i')}}</div>
            </div>
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::REQUEST_COMPLIANCE], $profile->cUser->project_id))

                <form action="{{ route('backoffice.compliance.requestCancel') }}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" value="{{ $lastApprovedComplianceRequest->id }}" name="complianceId">
                    <button type="submit"
                            style="position: absolute;font-size: 15px;top:35px;right:30px;cursor:pointer; background-color: transparent; border: none">
                        X
                    </button>
                </form>
            @endif
        </div>
        <div class=" col-md-4 mb-5 float-left padding-15">
            <div class="d-block">
                <p class="mb-3 textRed textBold">{{t('ui_compliance_user_account_verification_id')}}</p>
                <div class="textBold">
                    <a target="_blank" href="{{$lastApprovedComplianceRequest->getApplicantUrl()}}">
                        {{$lastApprovedComplianceRequest->applicant_id}}
                    </a></div>
            </div>
        </div>
        <div class=" col-md-3 mb-5 float-left padding-15">
            <div class="d-block">
                <p class="mb-3 textRed textBold">{{t('ui_compliance_user_account_report')}}</p>
                <div class="textBold"><a href="{{$lastApprovedComplianceRequest->getPdfUrl()}}" target="_blank">{{t('ui_compliance_user_download_report')}}</a></div>
            </div>
        </div>
    </div>

        @include('backoffice.cProfile._view-tabs._compliance_documents')

    @endif

    @include('cabinet.compliance._rates')


</div>
