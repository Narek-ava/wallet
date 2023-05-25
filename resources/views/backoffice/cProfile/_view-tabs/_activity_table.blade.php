<?php
/* @var \App\Models\Log $logs */
/* @var string $pageName */

$logs->setPageName($pageName);
?>

@if(!isset($moreButton))
<form action="" method="get"  class="row logForm">

    <div class="form-group col-md-8 float-left p-0">
        <div class=" col-md-6  float-left  ">
            <input
                value="{{$$logFrom}}" data-provide="datepicker" name="{{$logFrom}}"
                data-date-format="yyyy-mm-dd"
                class="filter_el form-control" placeholder="From"></div>
        <div class=" col-md-6  float-left ">
            <input value="{{$$logTo}}" data-provide="datepicker" name="{{$logTo}}"
                   data-date-format="yyyy-mm-dd"
                   class="filter_el form-control" placeholder="To"></div>


    </div>
    <div class="col-md-4 float-left ">
        <select class="w-100 filter_el" name="{{$logType}}">
            <option value="">{{t('ui_bo_c_profile_page_select_all')}}</option>
            @foreach($logTypesList as $logTypeKey)
                <option value="{{$logTypeKey}}"
                        @if ($$logType === (string)$logTypeKey)
                        selected
                    @endif
                >{{\App\Enums\LogType::getName($logTypeKey)}}</option>
            @endforeach
        </select>

    </div>
</form>
@endif
<div class="clearfix"> </div>

<div class="d-block mb-5 fs14">
    <div class="d-block">
        <div class="col-md-12 pt-4">
            <div class="row d-none d-md-flex">
                <div class="col-md-3 activeLink">{{t('ui_bo_c_profile_page_log_date')}}</div>
                <div class="col-md-3 activeLink">{{t('ui_bo_c_profile_page_log_ip')}}</div>
                <div class="col-md-6 activeLink">{{t('ui_bo_c_profile_page_log_action')}}</div>
            </div>
        </div>
        @foreach($logs as $key => $log)
            <div class="col-md-12 mt-4 history-element">
                <div class="row">
                    <div class="col-md-3 history-element-item activeLink">
                        {{$log->created_at->format('Y-m-d')}}
                        <br>
                        {{$log->created_at->format('H:i:s')}}
                    </div>
                    <div class="col-md-3 history-element-item activeLink">{{$log->ip}}</div>
                    <div class="col-md-6 history-element-item activeLink">{{t($log->action, $log->getReplacementsArray())}}</div>
                </div>
            </div>
        @endforeach
        @if(!isset($moreButton))
        {!! $logs->appends(request()->query())->links() !!}
        @endif
    </div>
</div>
