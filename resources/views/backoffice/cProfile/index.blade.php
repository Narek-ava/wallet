@extends('backoffice.layouts.backoffice',['showClients' => $type]) {{-- @todo --}}
@section('title', t('title_clients_page'))

@section('content')
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mb-3 large-heading-section">{{ t('title_clients_page') }}</h2>
                    <div class="row">
                        <div class="col-md-5 d-flex justify-content-between">
                            <div class="balance mb-4">
                                {{ t('backoffice_profile_page_header_body') }}
                            </div>
                        </div>
                        @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                    </div>
                </div>
            </div>
            @include('backoffice.cProfile._filter')
            <div class="row pb-5">
                <div class="col-md-12">
                    <div class="row">
                            <div class="users-list d-block mb-5 p-2">
                                <div class="d-block users-list-width-fix">
                                <div class="col-md-12 pt-4">
                                    <div class="row d-none d-md-flex">
                                        <div class="col-md-1 activeLink">{{ t('ui_settings') }}</div>
                                        <div class="col-md-1 activeLink" style="max-width: 120px;">
                                            <span data-sort="profile_id" class="sort-icon">ID<i class="fa fa-sort"></i></span>
                                        </div>
                                        {{-- @todo --}}
                                        @if($type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                                            <div class="col-md-1 activeLink">
                                                <span data-sort="first_name" class="sort-icon">{!! t('ui_first_name') !!} <i class="fa fa-sort"></i></span>
                                            </div>
                                            <div class="col-md-1 activeLink">
                                                <span data-sort="last_name" class="sort-icon">{!! t('ui_last_name') !!}<i class="fa fa-sort"></i></span>
                                            </div>
                                        @endif
                                        @if($type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                                            <div class="col-md-1 activeLink">
                                                <span data-sort="company_name" class="sort-icon">{{ t('ui_cprofile_company_name') }}<i class="fa fa-sort"></i></span>
                                            </div>
                                            <div class="col-md-1 activeLink">
                                                <span data-sort="company_email" class="sort-icon">{{ t('ui_cprofile_company_email') }}<i class="fa fa-sort"></i></span>
                                            </div>
                                        @endif
                                        <div class="col-md activeLink">
                                            <span data-sort="email" class="sort-icon">{!! t('ui_email') !!} <i class="fa fa-sort "></i> </span>
                                        </div>
                                        <div class="col-md-1 activeLink">{!! t('ui_verification') !!}</div>
                                        <div class="col-md-1 activeLink">{!! t('ui_project') !!}</div>
                                        <div class="col-md activeLink">{!! t('ui_manager') !!}</div>
                                        <div class="col-md-1 activeLink">{!! t('ui_total_balance') !!}</div>
                                        <div class="col-md-1 activeLink">{!! t('ui_last_login') !!}</div>
                                        <div class="col-md-1 activeLink">{!! t('ui_status') !!}</div>
                                        <div class="col-md-1 activeLink">{!! t('ui_refferal_of_user') !!}</div>
                                    </div>
                                </div>
                                @foreach($profiles as $key => $profile)
                                    <div class="col-md-12 mt-4 history-element">
                                        <div class="row">
                                            <div class="col-md-1 history-element-item orange">
                                                <a href="{{route('backoffice.profile', ['profileId' => $profile->id])}}"
                                                    class="btn btn-lg btn-primary themeBtn register-buttons round-border mb-0 mb-md-0"> View
                                                </a>
                                            </div>
                                            <div class="col-md-1 history-element-item" style="max-width: 120px;">{{$profile->profile_id}}</div>
                                            @if($type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                                                <div class="col-md-1 history-element-item" title="{{$profile->first_name}}">{{$profile->first_name}}</div>
                                                <div class="col-md-1 history-element-item" title="{{$profile->last_name}}">{{$profile->last_name}}</div>
                                            @else
                                                <div class="col-md-1 history-element-item" title="{{$profile->company_name}}">{{$profile->company_name}}</div>
                                                <div class="col-md-1 history-element-item" title="{{$profile->company_email}}">{{$profile->company_email}}</div>
                                            @endif
                                            <div class="col-md history-element-item" title="{{$profile->cUser ? $profile->cUser->email : ''}}">
                                                {{$profile->cUser ? $profile->cUser->email : ''}}
                                            </div>
                                            <div class="col-md-1 history-element-item" title="{{ !empty($complianceLevelList[$profile->compliance_level]) ? $complianceLevelList[$profile->compliance_level] : ''}}">
                                                {{ !empty($complianceLevelList[$profile->compliance_level]) ? $complianceLevelList[$profile->compliance_level] : ''}}
                                            </div>
                                            <div class="col-md history-element-item" title="{{$profile->cUser->project->name ?? '-'}}">{{$profile->cUser->project->name ?? '-'}}</div>
                                            <div class="col-md history-element-item" title="{{$profile->manager ? $profile->manager->email : '-'}}">{{$profile->manager ? $profile->manager->email : '-'}}</div>
                                            <div class="col-md-1 history-element-item" title="-">-</div>
                                            <div class="col-md-1 history-element-item" title="{{$profile->last_login }}">{{$profile->last_login }}</div>
                                            <div class="col-md-1 history-element-item">{!! $profile->getStatusWithClass() !!}</div>
                                            <div class="col-md-1 history-element-item" title="{{ $profile->ref ? $profile->getReferralName() : '-' }}">{{ $profile->ref ? $profile->getReferralName() : '-' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                        @if(!empty($profile))
                            {!! $profiles->appends(request()->query())->links() !!}
                         {{--   <ul class="pagination pt-4 d-none">
                                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                                <li class="page-item pl-1">page</li>
                                <li class="page-item strong"><a class="page-link" href="#">1</a></li>
                                <li class="page-item strong">of</li>
                                <li class="page-item strong"><a class="page-link" href="#">3</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>--}}
                        @endif
                        </div>
                </div>
            </div>
            @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_CLIENTS]))
                @if($type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                    @include('backoffice.cProfile._new-corporate-user-modal')
                @else
                    @include('backoffice.cProfile._new-user-modal')
                @endif
            @endif
    <script language="JavaScript" type="text/javascript">
        $(document).ready(function(){
            $('#filter .filter_el').change(function () {
                $('#filter').submit();
            })
            $('.account_type_checkbox input[type=radio]').change(function () {
                if ($('.account_type_checkbox input[type=radio]:checked').attr('id') == 'corporate') {
                    $('#corporate_fields').show();
                } else {
                    $('#corporate_fields').hide();
                }
            });
        });
    </script>
    @if (count($errors) > 0)
        <script type="text/javascript">
            $( document ).ready(function() {
                $('#newCProfile').modal('show');
            });
        </script>
    @endif
@endsection

