{{-- @todo why 'peronal+corporate'? --}}

<div class="modal fade" id="personalInformationModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_personal_information') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="personal-corporate-form" autocomplete="off" class="form dashboard-forms" role="form" method="post"
                  action="">
                {{ csrf_field() }}
                {{ method_field('patch') }}

                <div class="modal-body">
                    @if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1 || !$profile->cUser->project->complianceProvider())

                        <div class="row">

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="emailPersonal">{{t('ui_c_profile_corporate_company_email')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="email"
                                       name="company_email" id="emailPersonal" value="{{ $profile->company_email}}">
                                <p class="error-text mt-3" data-error-target="company_email"></p>
                            </div>

                            <div class="form-group col-lg-3 col-md-6">
                                <label for="company_name">{{__('Full company name')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="company_name" id="company_name" required
                                       value="{{$profile->company_name}}">
                                <p class="error-text mt-3" data-error-target="company_name"></p>
                            </div>

                            <div class="form-group col-md-12 col-lg-6">
                                <label>{{__('Registration date')}}</label>
                                <div class="form-group mb-0">

                                    <select class=" w110 mb-2 disabled_el " disabled name="day">
                                        @for($i = 1; $i<32; $i++)
                                            <option
                                                @if(old('day', $profile->registration_date ? Carbon\Carbon::parse($profile->registration_date)->format('d') : '') == $i )
                                                selected
                                                @endif
                                                value="{{$i<10 ? '0'.$i : $i}}">{{$i}}</option>
                                        @endfor
                                    </select>

                                    <select class="w110 mb-2  disabled_el " disabled name="month">
                                        <option value="" disabled hidden>{{ t('select_option') }}</option>

                                        @foreach(\App\Enums\Month::getList() as $i => $month)
                                            <option
                                                @if(old('month', $profile->registration_date ? Carbon\Carbon::parse($profile->registration_date)->format('m') : '') == $i)
                                                selected
                                                @endif
                                                value="{{$i}}">{{$month}}</option>
                                        @endforeach
                                    </select>

                                    <select class="w162 mb-2 disabled_el " disabled name="year" id="year">
                                        <option value="" disabled hidden>{{ t('select_option') }}</option>

                                        @for($i = date('Y'); $i >= 1920 ; $i--)
                                            <option
                                                @if(old('year', $profile->registration_date ? Carbon\Carbon::parse($profile->registration_date)->format('Y') : '') == $i)
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

                        </div>

                        <div class="row">

                            <div class="form-group col-md-6 col-lg-4">
                                <label for="registration_number">{{__('Registration number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="registration_number" id="registration_number"
                                       value="{{ $profile->registration_number}}">
                                <p class="error-text mt-3" data-error-target="registration_number"></p>

                            </div>

                            <div class="form-group col-md-12 col-lg-4">
                                <label for="country">{{ t('ui_country_residence')}}</label>
                                <select class="disabled_el " disabled name="country" id="country"
                                        style="width: 100% !important">
                                    <option value="" disabled selected hidden>{{ t('select_option') }}</option>

                                    @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                        <option @if(old('country', $profile->country) == $countryKey)
                                                selected
                                                @endif
                                                value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="country"></p>

                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="legal_address">{{__('Legal address')}}</label>
                                <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                       name="legal_address" id="legal_address" value="{{$profile->legal_address}}">
                                <p class="error-text mt-3" data-error-target="legal_address"></p>

                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12 col-lg-6">
                                <label for="trading_address">{{__('Trading address')}}</label>
                                <input autocomplete="off" disabled class=" disabled_el  form-control" type="text"
                                       name="trading_address" id="trading_address"
                                       value="{{$profile->trading_address}}">
                                <p class="error-text mt-3" data-error-target="trading_address"></p>

                            </div>
                        </div>
                        <div class="row beneficial-owners">
                            <div class="form-group col-md-12">
                                <label>{{__('Beneficial owner (full name)')}}</label>
                                <div class="row mt-0">
                                    <div class="form-group col-md-3 beneficial-owner">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="beneficial_owners[]" id="beneficial_owner_0"
                                               value="{{old('beneficial_owner_0', $profile->getMainBeneficialOwner())}}">
                                        @if(count($profile->getBeneficialOwnersForProfile()) <= 1)
                                            <button type="button" class="plusBeneficialOwnerBtn disabled_el" disabled
                                                    data-number="0">
                                                <img src="{{ config('cratos.urls.theme') }}images/plus.png" width="30"
                                                     height="30" alt="">
                                            </button>
                                        @endif
                                    </div>
                                    @foreach($profile->getBeneficialOwnersForProfile() as $number => $beneficialOwner)
                                        @if($number == 0)
                                            @continue
                                        @endif
                                        <div class="form-group col-md-3 beneficial-owner">
                                            <input autocomplete="off" class="form-control disabled_el" disabled
                                                   type="text"
                                                   data-number="{{ $number }}"
                                                   required
                                                   name="beneficial_owners[]" id="beneficial_owner_{{$number}}"
                                                   value="{{old('beneficial_owner_' . $number, $beneficialOwner)}}">
                                            <button type="button" class="minBeneficialOwnerBtn disabled_el" disabled
                                                    data-number="{{ $number }}">
                                                <img src="{{ config('cratos.urls.theme') }}images/minus.png"
                                                     width="15"
                                                     height="15" alt="">
                                            </button>
                                            @if($loop->last)
                                                <button type="button" class="plusBeneficialOwnerBtn disabled_el"
                                                        disabled data-number="{{ $number }}">
                                                    <img src="{{ config('cratos.urls.theme') }}images/plus.png"
                                                         width="30"
                                                         height="30" alt="">
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <p class="error-text" data-error-target="beneficial_owners"></p>
                            </div>

                        </div>

                        <div class="row ceos">
                            <div class="form-group col-md-12">
                                <label>{{__('CEO (full name)')}}</label>
                                <div class="row mt-0">
                                    <div class="form-group col-md-3 ceo">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="ceos[]" id="ceo_0"
                                               value="{{old('ceo_0', $profile->getMainCeo())}}">
                                        @if(count($profile->getCeosForProfile()) <= 1)
                                            <button type="button" class="plusCeoBtn disabled_el" disabled
                                                    data-number="0">
                                                <img src="{{ config('cratos.urls.theme') }}images/plus.png" width="30"
                                                     height="30" alt="">
                                            </button>
                                        @endif

                                    </div>
                                    @foreach($profile->getCeosForProfile() as $index => $ceo)
                                        @if($index == 0)
                                            @continue
                                        @endif
                                        <div class="form-group col-md-3 beneficial-owner">
                                            <input autocomplete="off" class="form-control disabled_el" disabled
                                                   type="text"
                                                   data-number="{{ $index }}"
                                                   required
                                                   name="ceos[]" id="ceo_{{$index}}"
                                                   value="{{old('ceo_' . $index, $ceo)}}">
                                            <button type="button" class="minCeoBtn disabled_el" disabled
                                                    data-number="{{ $index}}">
                                                <img src="{{ config('cratos.urls.theme') }}images/minus.png"
                                                     width="15"
                                                     height="15" alt="">
                                            </button>
                                            @if($loop->last)
                                                <button type="button" class="plusCeoBtn disabled_el"
                                                        disabled data-number="{{ $index }}">
                                                    <img src="{{ config('cratos.urls.theme') }}images/plus.png"
                                                         width="30"
                                                         height="30" alt="">
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <p class="error-text" data-error-target="ceos"></p>
                            </div>
                        </div>

                        <div class="row shareholders">
                            <div class="form-group col-md-12">
                                <label>{{__('Shareholder (full name)')}}</label>
                                <div class="row mt-0">
                                    <div class="form-group col-md-3 shareholder">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="shareholders[]" id="shareholder_0"
                                               value="{{old('shareholder_0', $profile->getMainShareholder())}}">
                                        @if(count($profile->getShareholdersForProfile()) <= 1)
                                            <button type="button" class="plusShareholderBtn disabled_el" disabled
                                                    data-number="0">
                                                <img src="{{ config('cratos.urls.theme') }}images/plus.png" width="30"
                                                     height="30" alt="">
                                            </button>
                                        @endif
                                    </div>
                                    @foreach($profile->getShareholdersForProfile() as $index => $shareholder)
                                        @if($index == 0)
                                            @continue
                                        @endif
                                        <div class="form-group col-md-3 beneficial-owner">
                                            <input autocomplete="off" class="form-control disabled_el" disabled
                                                   type="text"
                                                   data-number="{{ $index }}"
                                                   required
                                                   name="shareholders[]" id="shareholder_{{$index}}"
                                                   value="{{old('shareholder_' . $index, $shareholder)}}">
                                            <button type="button" class="minShareholderBtn disabled_el" disabled
                                                    data-number="{{ $index}}">
                                                <img src="{{ config('cratos.urls.theme') }}images/minus.png"
                                                     width="15"
                                                     height="15" alt="">
                                            </button>
                                            @if($loop->last)
                                                <button type="button" class="plusShareholderBtn disabled_el"
                                                        disabled data-number="{{ $index }}">
                                                    <img src="{{ config('cratos.urls.theme') }}images/plus.png"
                                                         width="30"
                                                         height="30" alt="">
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <p class="error-text" data-error-target="shareholders"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="beneficial_owner">{{__('Company Phone number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="company_phone" id="company_phone" value="{{ $profile->company_phone}}">
                                <p class="error-text mt-3" data-error-target="company_phone"></p>

                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="beneficial_owner">{{ __('Contact Phone number') }}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="contact_phone" id="contact_phone" value="{{ $profile->cUser->phone}}">
                                <p class="error-text mt-3" data-error-target="contact_phone"></p>

                            </div>
                            <div class="form-group col-md-6 col-lg-4">
                                <label for="linkedin_link">{{__('Contact Email')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="contact_email" id="contact_email" value="{{ $profile->contact_email}}">
                                <p class="error-text mt-3" data-error-target="contact_email"></p>

                            </div>

                            <div class="form-group col-md-12 col-lg-4">
                                <label for="linkedin_link">{{t('ui_c_profile_corporate_login_email')}}</label>
                                <input autocomplete="off" class="form-control" name="email" disabled type="text"
                                       value="{{$profile->cUser->email}}">
                                <p class="error-text mt-3" data-error-target="email"></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label for="address">{{__('Interface language')}}</label>
                                <select class="w-100 disabled_el " disabled name="interface_language"
                                        id="interface_language">
                                    <option value="" disabled selected hidden>{{ t('select_option') }}</option>

                                    @foreach(\App\Enums\Language::getList() as $langCode => $langName)
                                        <option @if($profile->interface_language == $langCode)
                                                selected
                                                @endif
                                                value="{{$langCode}}">{{$langName}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="interface_language"></p>

                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="form-group col-lg-3 col-md-6">
                                <label for="company_name">{{__('Full company name')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       name="company_name" id="company_name" required
                                       value="{{$profile->company_name}}">
                                <p class="error-text mt-3" data-error-target="company_name"></p>
                            </div>
                            <div class="form-group col-md-12 col-lg-4">
                                <label for="country">{{ t('ui_country_residence')}}</label>
                                <select class="disabled_el " disabled name="country" id="country"
                                        style="width: 100% !important">
                                    <option value="" disabled selected hidden>{{ t('select_option') }}</option>

                                    @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                                        <option @if(old('country', $profile->country) == $countryKey)
                                                selected
                                                @endif
                                                value="{{$countryKey}}">{{$country}}</option>
                                    @endforeach
                                </select>
                                <p class="error-text mt-3" data-error-target="country"></p>

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
