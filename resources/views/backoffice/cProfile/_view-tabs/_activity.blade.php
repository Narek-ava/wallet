<div class="row mt-5 mb-5">
    <div class="col-md-6">
        <div class="row">
            <div class="activity col-md-12">
                <h2 class="float-left">{{t('ui_bo_c_profile_page_last_user_activity')}}</h2>
                @if(!isset($moreButton))
                <div class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 float-left download_user_activity">
                    <a href="{{ request()->fullUrlWithQuery(['userLogExport' => 1]) }} "
                       class="text-white  text-center pt-2">{{t('ui_bo_c_profile_page_download_btn')}}</a>
                </div>
                @endif
                <div class="clearfix"> </div>
                @include('backoffice.cProfile._view-tabs._activity_table', ['logFrom' => 'userLogFrom', 'logTo' => 'userLogTo',
'logs' => $userLogs,'pageName' => \App\Enums\Enum::USER_PAGE_NAME, 'logType' => 'userLogType', 'logTypesList' => \App\Enums\LogType::USER_LOG_TYPES])
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <div class="activity col-md-12">
                <div class="activity col-md-12">
                    <h2 class="float-left">{{t('ui_bo_c_profile_page_last_cratos_activity')}}</h2>
                    @if(!isset($moreButton))
                    <div class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 float-left download_user_activity">
                        <a href="{{ request()->fullUrlWithQuery(['managerLogExport' => 1]) }} "
                           class="text-white  text-center pt-2">{{t('ui_bo_c_profile_page_download_btn')}}</a>
                    </div>
                    @endif
                    <div class="clearfix"> </div>
                    @include('backoffice.cProfile._view-tabs._activity_table', ['logFrom' => 'managerLogFrom',
'logTo' => 'managerLogTo', 'logs' => $managerLogs,'pageName' => \App\Enums\Enum::MANAGER_PAGE_NAME, 'logType' => 'managerLogType', 'logTypesList' => \App\Enums\LogType::MANAGER_LOG_TYPES])
                </div>
            </div>
        </div>
    </div>
    @if(isset($moreButton))
        <div class="navLink btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 float-left">
            <a href="#activity"
               class="text-white  text-center pt-2">{{t('ui_bo_c_profile_page_more_btn')}}</a>
        </div>
    @endif
</div>
