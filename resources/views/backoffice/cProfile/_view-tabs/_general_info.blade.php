<div id="general_info" class="container-fluid tab-pane fade active show">
    <div class="general-info-block">
        <div class="row mt-5 mb-5">
            <div class="col-md-6">
                <div class="addNewUser row">
                    <div class="col-md-7 pl-0" style="max-width: 220px;">
                        <h2>{{ t('profile_wallets_general_info') }}</h2>
                    </div>
                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_CLIENTS], $profile->cUser->project_id))
                        <div class="col-md-5">
                            <button class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0"
                                    type="submit"
                                    data-toggle="modal"
                                    data-target="#exampleEditUsers">{{ t('profile_wallets_edit_user') }}
                            </button>
                        </div>
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('profile_wallets_account_type') }}</label>
                        <p>{{$profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? t('enum_type_individual') : t('enum_type_corporate')}}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('ui_cprofile_created_at') }}</label>
                        <p>{{ $profile->created_at }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('profile_wallets_email') }}</label>
                        <p>{{$profile->cUser->email ?? ''}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ $profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? t('ui_cprofile_first_name') : t('ui_cprofile_company_name') }}</label>
                        <p>{{ $profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? $profile->first_name : $profile->getFullName()}}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ $profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? t('ui_cprofile_last_name') : t('ui_cprofile_registration_number')}}</label>
                        <p>{{$profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? $profile->last_name : $profile->registration_number}}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('ui_cprofile_status') }}</label>
                        <p>{!! $profile->getStatusWithClass() ?? '' !!}</p>
                    </div>
                </div>
                <div class="row">
                    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                        <div class="col-md-4">
                            <label class="font-weight-bold mt-2"
                                   for="inputEmail">{{ t('ui_cprofile_company_phone') }}</label>
                            <p>{{$profile->company_phone ?? ''}}</p>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('ui_cprofile_phone') }}</label>
                        <p>{{$profile->cUser->phone ?? ''}}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2" for="inputEmail">{{ t('ui_country_residence') }}</label>
                        <p>{{$profile->getCountryName() ?? ''}}</p>
                    </div>
                    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL)
                        <div class="col-md-4">
                            <label class="font-weight-bold mt-2" for="inputEmail">{{ t('profile_wallets_account_manager') }}</label>
                                <p>{{ $profile->manager->email ?? '' }}</p>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <label class="font-weight-bold mt-2">{{ t('ui_timezone_information') }}</label>
                        <p>{{$profile->timezone}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-5">
                <div class="row mt-4">
                    <div class="col-md-6 ml-5 mt-2">
                        <label class="font-weight-bold mt-3" for="inputEmail">{{ t('ui_menu_compliance') }}</label>
                        <p>{{$profile->compliance_level}}</p>
                    </div>
                        <div class="col-md-4 ml-5 mt-2">
                            <label class="font-weight-bold mt-3" for="inputEmail">{{ t('ui_menu_project') }}</label>
                            <p>{{$profile->cUser->project->name ?? '-' }}</p>
                        </div>
                </div>

                <div class="row">
                    <div class="col-md-6 ml-5">
                        <label class="font-weight-bold mt-2" for="rate-category-id">{{ t('profile_wallets_rates_type') }}</label>
                        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_CLIENTS], $profile->cUser->project_id))
                            <select id="rate-category-id" class="w-100">
                                {!! $ratesOptions !!}
                            </select>
                            <p class="text-success" id="rateCategoryMessage"></p>
                        @else
                            <p>{{ $rateTemplate->name }}</p>
                        @endif
                    </div>
                </div>
                @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                    <div class="row">
                        <div class="col-md-6 ml-5">
                            <label class="font-weight-bold mt-2"
                                   for="is_merchant">{{ t('profile_wallets_is_merchant') }}</label> <br>
                            <input id="is_merchant" name="is_merchant" type="checkbox" @if($profile->is_merchant) checked @endif>
                            <p class="text-success hide" id="isMerchantMessage"></p>
                            <p class="text-danger hide" id="isMerchantMessageError"></p>
                        </div>
                    </div>
                @endif

                <div class="row">

                </div>
            </div>
        </div>
        @include('backoffice.cProfile._view-tabs._activity')
    </div>
</div>


