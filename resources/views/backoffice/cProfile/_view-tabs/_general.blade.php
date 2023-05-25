<div id="general" class="container-fluid tab-pane active"><br>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="addNewUser row">
                <div class="col-md-2 pl-0">
                    <h2>{{ t('profile_wallets_general_info') }}</h2>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0" type="submit" data-toggle="modal" data-target="#exampleEditUsers">{{ t('profile_wallets_edit_user') }}</button>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <select id="rate-category-id" class="w-100">
                            {!! $ratesOptions !!}
                        </select>
                        <p class="text-success" id="rateCategoryMessage"></p>
                    </div>
                </div>
                <div class="col-md-2 text-center pt-2">
                    <span><img src="images/level-1.png" class="mr-2" alt=""></span>{!! $profile->getVerificationName() !!}
                </div>
                <div class="col-md-2   text-center pt-2">
                    <span class="textRed font-weight-bold">{{ t('ui_two_fa') }}</span> {{ t('ui_cprofile_enable') }}
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-5">
        <div class="col-md-12 pl-0 pr-0">
            <div class="addNewUser row">
                @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                    <div class="col-md-2">
                        <label for="inputEmail">{{ t('ui_cprofile_company_name') }}</label>
                        <p class="font-weight-bold mt-2">{{$profile->company_name}}</p>
                    </div>
                    <div class="col-md-3">
                        <label for="inputEmail">{{ t('ui_cprofile_company_email') }}</label>
                        <p class="font-weight-bold mt-2">{{$profile->company_email}}</p>
                    </div>
                @else
                    <div class="col-md-2">
                        <label for="inputEmail">{{ t('ui_cprofile_first_name') }}</label>
                        <p class="text30">{{$profile->first_name}}</p>
                    </div>
                    <div class="col-md-3">
                        <label for="inputEmail">{{ t('ui_cprofile_last_name') }}</label>
                        <p class="text30">{{$profile->last_name}}</p>
                    </div>
                @endif
                <div class="col-md-3">
                    <label for="inputEmail">{{ t('profile_wallets_email') }}</label>
                    <p class="font-weight-bold mt-2">{{$profile->cUser->email}}</p>
                </div>
                <div class="col-md-2">
                    <label for="inputEmail">{{ t('ui_cprofile_phone') }}</label>
                    <p class="font-weight-bold mt-2">{{$profile->cUser->phone}}</p>
                </div>
                <div class="col-md-1">
                    <label for="inputEmail">{{ t('ui_country_residence') }}</label>
                    <p class="font-weight-bold mt-2">{{$profile->getCountryName()}}</p>
                </div>
                <div class="col-md-1">
                    <label for="inputEmail">{{ t('ui_cprofile_status') }}</label>
                    <p class="status-active font-weight-bold mt-2">{!! $profile->getStatusWithClass() !!}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="balance-price row">
                <div class="d-block mr-4">
                    <h3>  EUR balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
                <div class="d-block mr-4">
                    <h3>  USD balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
                <div class="d-block mr-4">
                    <h3>  GPB balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="balance-price row">
                <div class="d-block mr-4">
                    <h3>  BTC balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
                <div class="d-block mr-4">
                    <h3>  LTC balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
                <div class="d-block mr-4">
                    <h3>  XRP balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
{{--                <div class="d-block mr-4">--}}
{{--                    <h3>  ETH balance</h3>--}}
{{--                    <p class="mb-0"> €175.029,12</p>--}}
{{--                </div>--}}
                <div class="d-block mr-4">
                    <h3>  BCH balance</h3>
                    <p class="mb-0"> €175.029,12</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <div class="balance-price row">
                <div class="d-block mr-4 font-weight-bold">
                    <span class="themeColorRed">{{ t('profile_wallets_account_manager') }}</span> ALISA GOSHERD
                </div>
                <div class="d-block mr-4 font-weight-bold">
                    <span class="themeColorRed">{{ t('ui_cprofile_compliance_officer_id') }}</span> ALISA GOSHERD
                </div>
            </div>
        </div>
    </div>
    @include('backoffice.cProfile._view-tabs._activity', ['moreButton' => true])
</div>
