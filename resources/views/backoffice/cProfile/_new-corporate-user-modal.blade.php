<div class="modal fade login-popup rounded-0 editUser" id="newCProfile" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content common-shadow-theme">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <img src="{{ config('cratos.urls.theme') }}images/close.png" alt="">
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-5">
                    <div class="col-md-12">
                        <h5 class="mb-4">{{ t('ui_add_client') }}</h5>
                    </div>
                </div>
                <form autocomplete="off" class="form " role="form" method="post"
                      action="{{route('backoffice.profile.storeCorporate')}}">
                    {{ csrf_field() }}
                    <div class="row mb-5">
                        <div class="form-group col-md-4">
                            <label for="inputEmail">{{ t('profile_wallets_account_manager') }}</label>
                            <div class="d-flex">
                                <select name="manager_id" class="mr-3" style="width:280px;">
                                    <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @foreach(\App\Models\Backoffice\BUser::accountManagersList() as $id => $email)
                                        <option
                                            {{ (old('manager_id') == $id ? "selected":"") }} value="{{$id}}">{{$email}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputEmail">{{ t('ui_cprofile_compliance_officer_id') }}</label>
                            <div class="d-flex">
                                <select name="compliance_officer_id" class="mr-3" style="width:280px;">
                                    <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @foreach(\App\Models\Backoffice\BUser::complianceManagersList() as $id => $email)
                                        <option
                                            {{ (old('compliance_officer_id') == $id ? "selected":"") }} value="{{$id}}">{{$email}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="inputEmail">{{ t('ui_project') }}</label>
                            <div class="d-flex">
                                <select name="project_id" data-permission="{{ \App\Enums\BUserPermissions::ADD_EDIT_CLIENTS }}" class="mr-3 projectSelect" style="width:280px;">
                                    <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @foreach($projectNames as $id => $project)
                                        <option
                                            {{ (old('project_id') == $id ? "selected":"") }} value="{{$id}}">{{$project}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="error text-danger projectSelectError"></div>
                            @error('project_id')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{t('ui_c_profile_corporate_login_email')}}</label>
                            <input required autocomplete="off" class="form-control" type="email" name="email"
                                   value="{{ old('email')}}">
                            @error('email')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label class="">{{ t('profile_wallets_password') }}</label>
                            <input required autocomplete="off" class="form-control" type="password" name="password">
                            @error('password')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{ t('profile_phone_number') }}</label>
                            <input required autocomplete="off" class="form-control" type="text" name="phone"
                                   value="{{   old('phone')}}">
                            @error('phone')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <input class="form-check-input" type="hidden" name="account_type" id="individual"
                               value="{{$type}}">
                        <div class="form-group col-md-3">
                            <label for="company_name">{{__('Full company name')}}</label>
                            <input autocomplete="off" class="form-control  " type="text"
                                   name="company_name" id="company_name"
                                   required value="{{old('company_name')}}">
                            @error('company_name')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    <div class="row">
                        <div class="form-group col-md-5">
                            <label for="country">{{ t('ui_country_residence')}}</label>
                            <select class="w-100  " name="country" id="country">
                                <option value="" disabled selected hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                    <option
                                        @if(old('country') == $countryKey)
                                        selected
                                        @endif
                                        value="{{$countryKey}}">{{$country}}</option>
                                @endforeach
                            </select>
                            @error('country')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="form-group col-md-5 d-flex align-items-end">
                            <button class="btn btn-lg btn-primary themeBtn register-buttons mr-3" type="submit">{{ t('save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script src="/js/ceo-and-beneficial-owners.js"></script>
@endsection
