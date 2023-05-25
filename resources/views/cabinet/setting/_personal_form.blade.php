<div class="modal fade" id="personalInformationModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_personal_information') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="personal-form" autocomplete="off" class="form dashboard-forms" role="form" method="post"
                  action="">
                {{ csrf_field() }}
                {{ method_field('patch') }}

                <div class="modal-body">
                    <div class="row">
                        <div class="form-group col-md-6 col-lg-3">
                            <label for="first_name">{{__('First name')}}</label>
                            <input autocomplete="off" class="form-control disabled_el" disabled type="text"
                                   name="first_name" id="first_name" required value="{{$profile->first_name }}">
                            <p class="error-text mt-3" data-error-target="first_name"></p>
                        </div>
                        <div class="form-group col-md-6 col-lg-3">
                            <label for="last_name">{{__('Last name')}}</label>
                            <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                   name="last_name" id="last_name" required value="{{ $profile->last_name}}">
                            <p class="error-text mt-3" data-error-target="last_name"></p>
                        </div>
                        @if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider())
                            <div class="form-group col-md-12 col-lg-6">
                                <label>{{__('Date of birth')}}</label>
                                <div class="form-group mb-0">

                                    <select class=" w110 mb-2 disabled_el " disabled name="day">
                                        @for($i = 1; $i<32; $i++)
                                            <option
                                                @if(($profile->date_of_birth ? Carbon\Carbon::parse($profile->date_of_birth)->format('d') : '') == $i )
                                                selected
                                                @endif
                                                value="{{$i<10 ? '0'.$i : $i}}">{{$i}}</option>
                                        @endfor
                                    </select>
                                    <select class="w110 mb-2  disabled_el " disabled name="month">
                                        <option value="" disabled hidden>Select...</option>

                                        @foreach(\App\Enums\Month::getList() as $i => $month)
                                            <option
                                                @if(($profile->date_of_birth ? Carbon\Carbon::parse($profile->date_of_birth)->format('m') : '') == $i)
                                                selected
                                                @endif
                                                value="{{$i}}">{{$month}}</option>
                                        @endforeach
                                    </select>

                                    <select class="w162 mb-2 disabled_el " disabled name="year" id="year">
                                        <option value="" disabled hidden>Select...</option>

                                        @for($i = date('Y'); $i >= 1920 ; $i--)
                                            <option
                                                @if( ($profile->date_of_birth ? Carbon\Carbon::parse($profile->date_of_birth)->format('Y') : '') == $i)
                                                selected
                                                @endif
                                                value="{{$i}}">{{$i}}</option>
                                        @endfor
                                    </select>
                                    <p class="error-text mt-3" data-error-target="day"></p>
                                    <p class="error-text mt-3" data-error-target="month"></p>
                                    <p class="error-text mt-3" data-error-target="year"></p>
                                </div>
                            </div>
                        @else
                            <div class="form-group col-md-12 col-lg-4">
                                <label for="country">{{ t('ui_country_residence')}}</label>
                                <select class="w-100 disabled_el " disabled name="country" id="country"
                                        style="width: 100%;">
                                    <option value="" disabled selected hidden>Select...</option>

                                    @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                        <option @if( $profile->country == $countryKey)
                                                selected
                                                @endif
                                                value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="country"></p>
                            </div>
                        @endif
                    </div>

                    @if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider())
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="phone">{{__('Phone number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="phone" id="phone" value="{{  $profile->cUser->phone}}">
                                <p class="error-text mt-3" data-error-target="phone"></p>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="emailPersonal">{{__('Email')}}</label>
                                <input autocomplete="off" class="form-control " disabled type="email" id="emailPersonal"
                                       value="{{ $profile->cUser->email }}">
                                <p class="error-text mt-3" data-error-target="email"></p>
                            </div>
                            <div class="form-group col-md-12 col-lg-4">
                                <label for="country">{{ t('ui_country_residence')}}</label>
                                <select class="w-100 disabled_el " disabled name="country" id="country"
                                        style="width: 100%;">
                                    <option value="" disabled selected hidden>Select...</option>

                                    @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                        <option @if( $profile->country == $countryKey)
                                                selected
                                                @endif
                                                value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="country"></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="city">{{__('City')}}</label>
                                <input autocomplete="off" class="form-control  disabled_el " disabled type="text"
                                       name="city" id="city" value="{{$profile->city}}">
                                <p class="error-text mt-3" data-error-target="city"></p>
                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="citizenship">{{__('Citizenship')}}
                                    @if(!in_array($profile->citizenship, \App\Models\Country::getCountries(false)) && $profile->citizenship)
                                        ({{ $profile->citizenship }})
                                    @endif
                                </label>
                                <select class="w-100 disabled_el " disabled name="citizenship" id="citizenship"
                                        style="width: 100%;">
                                    <option value="" disabled selected hidden>Select...</option>
                                    @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                        <option @if( $profile->citizenship == $country) selected @endif
                                        value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="citizenship"></p>
                            </div>
                            <div class="form-group col-md-12 col-lg-4">
                                <label for="zip_code">{{__('Zip code/Postcode')}}</label>

                                <input autocomplete="off" value="{{$profile->zip_code}}" disabled
                                       class="  disabled_el form-control" type="text" name="zip_code" id="zip_code">
                                <p class="error-text mt-3" data-error-target="zip_code"></p>
                            </div>

                        </div>

                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label for="address">{{__('Address')}}</label>
                                <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                       name="address" id="address" value="{{$profile->address}}">
                                <p class="error-text mt-3" data-error-target="address"></p>

                            </div>
                            <div class="form-group col-lg-6">
                                <label for="passport">{{__('ID/Passport Number')}}</label>
                                <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                       name="passport" id="passport" value="{{$profile->passport}}">
                                <p class="error-text mt-3" data-error-target="passport"></p>

                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="gender">{{__('Gender')}}</label>
                                <select class="w-100 disabled_el " disabled name="gender" id="gender"
                                        style="width: 100%;">
                                    <option value="" disabled selected hidden>Select...</option>
                                    @foreach(\App\Enums\Gender::NAMES as $g_key => $gender)
                                        <option @if( $profile->gender == $g_key) selected @endif
                                        value="{{$g_key}}">{{\App\Enums\Gender::getName($g_key)}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="gender"></p>
                            </div>
                        </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3 disabled_el loader"
                        disabled id="updatePersonalForm" type="submit">{{__('Save')}}
                    </button>

                    <button id="edit_user"
                            class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 change_btn"
                            type="button">{{__('Change')}}
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
