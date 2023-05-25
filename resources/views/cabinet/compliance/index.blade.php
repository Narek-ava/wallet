@extends('cabinet.layouts.cabinet')

@section('title', t('compliance_browser_page_title'))

@section('content')
    @include('cabinet._modals._success')
    <div class="col-md-12 p-0">
        <h2 class="mb-3 large-heading-section page-title"
            style="margin-bottom: 20px !important;">{{ !empty($complianceData) ? t('ui_menu_compliance') : t('ui_limits') }}</h2>
        @if(!empty($complianceData))
            <div class="row">
                <div class="col-lg-5">
                    <div class="row pl-3">
                        <div class="mb-4 w-100 pl-1">
                            <div class="d-block">
                                <h6 class="mb-3">
                                    {{ t('ui_verification_level') }}
                                </h6>
                                <div class="d-block">
                                    {{t('ui_compliance_'.$profile->compliance_level.'_level_top_box_title')}}
                                    -
                                    {{t('ui_compliance_'.$profile->compliance_level.'_level_top_box_sub_title')}}
                                </div>
                            </div>
                        </div>
                        <div class="mb-2">
                            @if($profile->hasPendingComplianceRequest())
                                <div class="d-block compliance_status_block">
                                    <p>{{t('ui_cprofile_status')}}</p>
                                    <p class="orange">{{\App\Enums\ComplianceRequest::getName(\App\Enums\ComplianceRequest::STATUS_PENDING)}}</p>
                                </div>
                            @elseif($complianceData['nextLevelButtons'] && !(($profile->compliance_level == \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_0 && !$profile->hasPendingComplianceRequest()) || $complianceData['lastRequestIfDeclined'] || $complianceData['retryComplianceRequest']))
                                @foreach($complianceData['nextLevelButtons'] as $key => $nextLevelKey)
                                    <p>
                                        @if(!$key)
                                            <button class="btn btn-lg btn-primary themeBtn   verify_compliance"
                                                    data-toggle="modal" data-target="#complianceModal">
                                                {{t('ui_compliance_update_btn_text', ['level' => $nextLevelKey])}}
                                            </button>
                                        @else
                                            <button disabled
                                                    class="btn btn-lg btn-primary themeBtn ">{{t('ui_compliance_update_btn_text', ['level' => $nextLevelKey])}}</button>
                                        @endif
                                    </p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => false])
            </div>
        @endif
    </div>
    <div class="col-12 pl-0 mt-4">
        @include('cabinet.partials.session-message')
    </div>
    @if(!empty($complianceData))
        @include('cabinet.compliance._request_box')
    @endif
    @if(!empty($complianceData) && $complianceData['sumSubNextLevelName'])
        <!-- Modal -->
        <div class="modal fade" id="complianceModal" tabindex="-1" role="dialog" aria-labelledby="complianceModalTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="complianceModalTitle">{{t('ui_menu_compliance')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body" id="compliance-websdk-container">
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- <div class="col-md-12 mt-5">
        <div class="row">
            <div class="col-md-3 upgradeBlock common-shadow-theme">
                <h3>Request</h3>
                <p>Please upgrade your verification level to second stage.</p>
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 br20">Upgrade</button>
            </div>
            <div class="col-md-3 upgradeBlock common-shadow-theme">
                <h3>Additional request</h3>
                <p>Provide sourceof Funds on transaction # 48</p>
                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 br20">Upgrade</button>
            </div>
        </div>
    </div> -->
    @if(!empty($complianceData))
        <div class="col-md-12 mt-5">
            <div class="row complianceLevelContainer">
                <div class="complianceLevelBlock">
                    <div class="textBold activeLevel"></div>
                    <h5>{{ t('enum_compliance_level_level_0') }}</h5>
                </div>
                <div class="dashedBlock"></div>
                <div class="complianceLevelBlock">
                    @if($profile->compliance_level >= 1)
                        <div class="textBold activeLevel"></div>
                    @else
                        <div class="textBold inactiveLevel"></div>
                    @endif
                    <h5>{{ t('enum_compliance_level_level_1') }}</h5>
                </div>
                <div class="dashedBlock"></div>
                <div class="complianceLevelBlock">
                    @if($profile->compliance_level >= 2)
                        <div class="textBold activeLevel"></div>
                    @else
                        <div class="textBold inactiveLevel"></div>
                    @endif
                    <h5>{{ t('enum_compliance_level_level_2') }}</h5>
                </div>
                <div class="dashedBlock"></div>
                <div class="complianceLevelBlock">
                    @if($profile->compliance_level >= 3)
                        <div class="textBold activeLevel"></div>
                    @else
                        <div class="textBold inactiveLevel"></div>
                    @endif
                    <h5>{{ t('enum_compliance_level_level_3') }}</h5>
                </div>
            </div>
        </div>
    @endif
    <br>
    @include('cabinet.compliance._rates')
@endsection
@if(!empty($complianceData) && $complianceData['sumSubNextLevelName'])
    @section('scripts')
        @switch($complianceProvider->api)
            @case(\App\Enums\ComplianceProviders::SUMSUB)
                <script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
                <script src="/js/cabinet/compliance.js"></script>
                <script>
                    window.env = '{{ config('app.env')}}';
                    $('.verify_compliance').click(function () {
                        launchWebSdk('{{$complianceData['sumSubApiUrl']}}', '{{$complianceData['sumSubNextLevelName']}}', '{{$complianceData['token']}}', '{{$profile->cUser->email}}', '{{$profile->cUser->phone}}', null, '{{$complianceData['contextId']}}') //TODO add translation messages
                    })
                </script>
                @break
        @endswitch
    @stop
@endif
