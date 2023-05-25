<?php

use App\Models\Cabinet\CProfile;

?>
<div class="modal fade login-popup rounded-0 editUser" id="exampleEditUsers" tabindex="-1" role="dialog"
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
                        <h5 class="mb-4">{{ t('ui_edit_client') . ' #' . $profile->profile_id }}</h5>
                    </div>
                    <div class="col-md-5 mb-2">
                        <div class="form-label-group">
                            <form autocomplete="off" class="form " role="form" method="post"
                                  action="{{route('backoffice.profile.updateManager', ['profileId' => $profile->id])}}">
                                {{ csrf_field() }}
                                {{ method_field('patch') }}
                                <label for="inputEmail">{{ t('profile_wallets_account_manager') }}</label>
                                <div class="d-flex">
                                    <select name="manager_id" class="mr-3" style="width:280px;">
                                        <option value="" disabled
                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                        @foreach(\App\Models\Backoffice\BUser::accountManagersList() as $id => $email)
                                            <option
                                                {{ (old('manager_id', $profile->manager_id) == $id ? "selected":"") }} value="{{$id}}">{{$email}}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons"
                                            type="submit"> {{ t('ui_change') }} </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-5 mb-2">
                        <div class="form-label-group">
                            <form autocomplete="off" class="form " role="form" method="post"
                                  action="{{route('backoffice.profile.updateComplianceOfficer', ['profileId' => $profile->id])}}">
                                {{ csrf_field() }}
                                {{ method_field('patch') }}
                                <label for="inputEmail">{{ t('ui_cprofile_compliance_officer_id') }}</label>
                                <div class="d-flex">
                                    <select name="compliance_officer_id" class="mr-3" style="width:280px;">
                                        <option value="" disabled
                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                        @foreach(\App\Models\Backoffice\BUser::complianceManagersList() as $id => $email)
                                            <option
                                                {{ (old('compliance_officer_id', $profile->compliance_officer_id) == $id ? "selected":"") }} value="{{$id}}">{{$email}}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons" type="submit">
                                        {{ t('ui_change') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @include('backoffice.cProfile._status_change')
                <h5 class="mb-4">{{ t('ui_personal_information') }}</h5>
                <form autocomplete="off" class="form " role="form" method="post"
                      action="{{route('backoffice.profile.update', ['profileId' => $profile->id])}}">
                    {{ csrf_field() }}
                    {{ method_field('patch') }}
                    <div class="row">
                        <div class="form-group col-md-3">
                            {{-- @todo __() usages --}}
                            <label for="first_name">{{__('profile.backoffice.firstName')}}</label>
                            <input autocomplete="off" class="form-control disabled_el" disabled type="text"
                                   name="first_name" id="first_name"
                                   required
                                   value="{{old('first_name', $profile->first_name) }}">
                            @error('first_name')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label class="col-md-12">{{__('profile.backoffice.lastName')}}</label>
                            <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                   name="last_name"
                                   required value="{{old('last_name', $profile->last_name)}}">
                            @error('last_name')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-5">
                            <label for="inputEmail">{{ t('ui_cprofile_date_of_birth') }}</label>
                            <div class="form-group">
                                <select class=" w110 disabled_el " disabled name="day" required>
                                    @for($i = 1; $i<32; $i++)
                                        <option
                                            @if(old('day', $profile->date_of_birth  ? Carbon\Carbon::parse($profile->date_of_birth)->format('d') : '') == $i )
                                                selected
                                            @endif
                                            value="{{$i<10 ? '0'.$i : $i}}">{{$i}}</option>
                                    @endfor
                                </select>
                                @error('day')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                                <select class="w110  disabled_el " disabled name="month" required>
                                    <option value="" disabled
                                            hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @foreach(\App\Enums\Month::getList() as $i => $month)
                                        <option
                                            @if(old('month', $profile->date_of_birth  ? Carbon\Carbon::parse($profile->date_of_birth)->format('m') : '') == $i)
                                                selected
                                            @endif
                                            value="{{$i}}">{{$month}}</option>
                                    @endforeach                            </select>
                                @error('month')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                                <select class="w110 disabled_el " disabled name="year" required>
                                    <option value="" disabled
                                            hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @for($i = date('Y'); $i >= 1920 ; $i--)
                                        <option
                                            @if(old('year', $profile->date_of_birth  ? Carbon\Carbon::parse($profile->date_of_birth)->format('Y') : '') == $i)
                                                selected
                                            @endif
                                            value="{{$i}}">{{$i}}</option>
                                    @endfor                            </select>
                                @error('year')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group col-md-5">
                            <label for="inputEmail">{{ t('ui_country_residence') }}</label>
                            <select class="w-100 disabled_el " disabled name="country" required>
                                <option value="" disabled selected
                                        hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                    <option
                                        @if(old('country', $profile->country) == $countryKey)
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


                    <div class="row">

                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{ t('profile_phone_number') }}</label>
                            <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                   required
                                   name="phone"
                                   value="{{ old('phone', $profile->cUser->phone)}}">
                            @error('phone')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{ t('profile_wallets_email') }}</label>
                            <input autocomplete="off" class="form-control " disabled type="email"
                                   value="{{  old('email', $profile->cUser->email )}}">
                        </div>
                        <div class="form-group col-md-5">
                            <label for="inputEmail">{{ t('ui_country_residence') }}</label>
                            <select class="w-100 disabled_el " disabled name="country" required>
                                <option value="" disabled selected
                                        hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                    <option
                                        @if(old('country', $profile->country) == $countryKey)
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

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{ t('ui_cprofile_city') }}</label>
                            <input autocomplete="off" class="form-control  disabled_el " disabled type="text"
                                   required
                                   name="city"
                                   value="{{old('city', $profile->city)}}">
                            @error('city')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="citizenship">
                                {{ t('ui_cprofile_citizenship') }}
                                @if(!in_array($profile->citizenship, \App\Models\Country::getCountries(false)) && $profile->citizenship)
                                    ({{ $profile->citizenship }})
                                @endif
                            </label>
                            <select class="form-control w-100 disabled_el " disabled name="citizenship"
                                    id="citizenship" style="width: 100%;" required>
                                <option value="" disabled selected hidden>Select...</option>
                                @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                    <option @if( $profile->citizenship == $country) selected @endif
                                    value="{{ $countryKey }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            @error('citizenship')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-5">
                            <label for="inputEmail">{{ t('ui_zip_code_postcode') }}</label>
                            <input autocomplete="off" value="{{old('zip_code',$profile->zip_code)}}" disabled
                                   required
                                   class="  disabled_el form-control" type="text" name="zip_code">
                            @error('zip_code')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="row mb-5">
                        <div class="form-group col-md-6">
                            <label for="inputEmail">{{ t('ui_cprofile_address') }}</label>
                            <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                   required
                                   name="address"
                                   value="{{old('address',$profile->address)}}">
                            @error('address')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-6">
                            <label for="passport">{{ t('ui_cprofile_passport') }}</label>
                            <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                   required
                                   name="passport"
                                   value="{{old('passport',$profile->passport)}}">
                            @error('passport')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-5">

                        <div class="form-group col-md-3">
                            <label for="gender">
                                {{ t('ui_cprofile_gender') }}
                            </label>
                            <select class="form-control w-100 disabled_el " disabled name="gender"
                                    id="gender" style="width: 100%;" required>
                                <option value="" disabled selected hidden>Select...</option>
                                @foreach(\App\Enums\Gender::NAMES as $genderKey => $gender)
                                    <option @if( $profile->gender == $genderKey) selected @endif
                                    value="{{ $genderKey }}">{{ \App\Enums\Gender::getName($genderKey) }}</option>
                                @endforeach
                            </select>
                            @error('gender')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-group col-md-5 d-flex align-items-end">
                            <button disabled
                                    class="  disabled_el btn btn-lg btn-primary themeBtn register-buttons mr-3"
                                    type="submit">{{ t('save') }}
                            </button>
                            <button disabled
                                    class="  disabled_el btn btn-lg btn-primary themeBtn register-buttons mr-3"
                                    type="reset">{{ t('ui_cancel') }}
                            </button>
                            <button
                                class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 change_btn">{{ t('ui_change') }}</button>
                        </div>
                    </div>

                </form>
                <div class="row">
                    <div class="form-group col-md-8 ">
                        @include('backoffice.cProfile._change_email')
                    </div>
                    <div class="form-group col-md-8 ">
                        @include('backoffice.cProfile._change_timezone')
                    </div>
                    <div class="form-group col-md-4 mt-5">
                        <div class="form-group col-md-12 text-center">
                            <h6 class="font-weight-bold text-center mb-4">{{ t('profile_wallets_password') }}</h6>
                            <a href="{{ route('dashboard.reset.client.password', ['id' => $profile->cUser->id]) }}"
                               class="btn btn-lg btn-primary themeBtn register-buttons"
                               type="submit">{{ t('ui_reset') }}</a>
                        </div>
                        <div class="col-md-12 text-center">
                            <h6 class="font-weight-bold text-center mb-4">{{ t('ui_two_fa') }}</h6>
                            <a href="{{ route('dashboard.reset.client.2fa', ['id' => $profile->cUser->id]) }}"
                               class="btn btn-lg btn-primary themeBtn register-buttons"
                               type="submit">{{ t('ui_reset') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
