<div class="container pl-0 ml-0">
    <div class="col-md-12">
    <h1>{{ t('order_history') }}</h1>

    <form action="" method="get"  class="row logForm">
            <div class="form-group col-md-8 float-left p-0">
                <div class=" col-md-6  float-left  ">
                    <input
                        value="{{ $logFrom ?? '' }}" data-provide="datepicker" name="logFrom"
                        data-date-format="yyyy-mm-dd" class="filter_el form-control" placeholder="From"></div>
                <div class=" col-md-6  float-left ">
                    <input value="{{ $logTo ?? '' }}" data-provide="datepicker" name="logTo"
                           data-date-format="yyyy-mm-dd" class="filter_el form-control" placeholder="To"></div>
            </div>
        </form>

    <div class="clearfix"> </div>

    <div class="d-block mb-5 fs14">
        <div class="d-block">
            <div class="col-md-12 pt-4">
                <div class="row d-none d-md-flex">
                    <div class="col-md-2 activeLink">{{ t('ui_cprofile_manager_id') }}</div>
                    <div class="col-md-6 activeLink text-center">{{t('ui_bo_c_profile_page_log_action')}}</div>
                    <div class="col-md-2 activeLink">{{t('ui_bo_c_profile_page_log_date')}}</div>
                    <div class="col-md-2 activeLink">{{t('ui_bo_c_profile_page_log_ip')}}</div>
                </div>
            </div>
            @if(!empty($logs))
                @php($logs->setPageName(\App\Enums\Enum::OPERATION_PAGE_NAME))
                @foreach($logs as $key => $log)
                    <div class="col-md-12 mt-12 history-element">
                        <div class="row">
                            <div class="col-md-3 history-element-item activeLink">{{$log->bUser->email}}</div>
                            <div class="col-md-5 history-element-item activeLink">{{t($log->action, $log->getReplacementsArray())}}</div>
                            <div class="col-md-2 history-element-item activeLink">
                                {{$log->created_at->format('Y-m-d')}}
                                <br>
                                {{$log->created_at->format('H:i:s')}}
                            </div>
                            <div class="col-md-2 history-element-item activeLink">{{$log->ip}}</div>
                        </div>
                    </div>
                @endforeach
                {!! $logs->appends(request()->query())->links() !!}
            @else
                <div class="col-md-12 mt-12 history-element">
                    <div class="row">
                        <div class="col-md-3 history-element-item activeLink"></div>
                        <div class="col-md-5 history-element-item activeLink"></div>
                        <div class="col-md-2 history-element-item activeLink"></div>
                        <div class="col-md-2 history-element-item activeLink"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
</div>
