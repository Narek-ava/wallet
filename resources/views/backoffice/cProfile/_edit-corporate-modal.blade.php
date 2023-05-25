<?php
// @todo Maybe modal views aint equals to editing fors
use App\Models\Cabinet\CProfile;

/*echo '<pre>';
    var_dump($errors->any());
die;*/


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
                        <h5 class="mb-4">{{ t('ui_edit_client') . ' #' . $profile->profile_id }} </h5>
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
                                            type="submit">{{ t('ui_change') }}</button>
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
                                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons"
                                            type="submit">{{ t('ui_change') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @include('backoffice.cProfile._status_change')
                    <h5 class="mb-4">{{ t('ui_personal_information') }}</h5>
                    <form autocomplete="off" class="form col-md-10" role="form" method="post"
                          action="{{route('backoffice.profile.updateCorporate', ['profileId' => $profile->id])}}">
                        {{ csrf_field() }}
                        {{ method_field('patch') }}
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="emailPersonal">{{__('Company Email')}}</label>
                                <input autocomplete="off" class="form-control  disabled_el" disabled type="email"
                                       required
                                       name="company_email" id="emailPersonal"
                                       value="{{ old('company_email', $profile->company_email)}}">
                                @error('company_email')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="company_name">{{__('Full company name')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       required
                                       name="company_name" id="company_name"
                                       value="{{old('company_name', $profile->company_name)}}">
                                @error('company_name')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-5">
                                <label>{{__('Registration date')}}</label>
                                <div class="form-group">
                                    <select class=" w110 disabled_el " disabled name="day" required>
                                        @for($i = 1; $i<32; $i++)
                                            <option
                                                @if(old('day', $profile->registration_date  ? Carbon\Carbon::parse($profile->registration_date)->format('d') : '') == $i )
                                                    selected
                                                @endif
                                                {{-- @todo  логика во view --}}
                                                value="{{$i<10 ? '0'.$i : $i}}">{{$i}}</option>
                                        @endfor
                                    </select>
                                    @error('day')
                                    <div class="error text-danger">{{ $message }}</div> @enderror
                                    <select class="w110  disabled_el " disabled name="month" required>
                                        <option value="" disabled
                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                        @foreach(\App\Enums\Month::getList() as $i => $month)
                                            <option
                                                @if(old('month', $profile->registration_date  ? Carbon\Carbon::parse($profile->registration_date)->format('m') : '') == $i)
                                                    selected
                                                @endif
                                                value="{{$i}}">{{$month}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('month')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror

                                    <select class="w110 disabled_el " disabled name="year" required>
                                        <option value="" disabled
                                                hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                        {{-- @todo --}}
                                        @for($i = date('Y'); $i >= 1920 ; $i--)
                                            <option
                                                @if(old('year', $profile->registration_date  ? Carbon\Carbon::parse($profile->registration_date)->format('Y') : '') == $i)
                                                    selected
                                                @endif
                                                value="{{$i}}">{{$i}}</option>
                                        @endfor                            </select>
                                    @error('year')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="registration_number">{{__('Registration number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " type="text" required
                                       name="registration_number" id="registration_number" disabled
                                       value="{{old('registration_number', $profile->registration_number)}}">
                                @error('registration_number')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-5">
                                <label for="country">{{ t('ui_country_residence')}}</label>
                                <select class="w-100 disabled_el " name="country" id="country" required>
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
                            <div class="form-group col-md-6">
                                <label for="legal_address">{{__('Legal address')}}</label>
                                <input autocomplete="off" class=" disabled_el  form-control" disabled type="text"
                                       required
                                       name="legal_address" id="legal_address"
                                       value="{{old('legal_address', $profile->legal_address)}}">
                                @error('legal_address')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="trading_address">{{__('Trading address')}}</label>
                                <input autocomplete="off" class="disabled_el   form-control" disabled type="text"
                                       required
                                       name="trading_address" id="trading_address"
                                       value="{{old('trading_address', $profile->trading_address)}}">
                                @error('trading_address')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row beneficial-owners">
                            <div class="form-group col-md-12">
                                <label>{{__('Beneficial owner (full name)')}}</label>
                                <div class="row">
                                    <div class="form-group col-md-3 beneficial-owner">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="beneficial_owners[]" id="beneficial_owner_0"
                                               value="{{old('beneficial_owner_0', $profile->getMainBeneficialOwner())}}">
                                        @error('beneficial_owner_0')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
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
                                            @error('beneficial_owner_' . $number)
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
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
                                @if($errors->any())
                                    @foreach($errors->getMessages() as $key => $error)
                                        @if(strpos($key, 'beneficial_owners') !== false)
                                            <div class="error text-danger">{{ $error[0] }}</div>
                                            @break
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                        </div>
                        <div class="row ceos">
                            <div class="form-group col-md-12">
                                <label>{{__('CEO (full name)')}}</label>
                                <div class="row">
                                    <div class="form-group col-md-3 ceo">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="ceos[]" id="ceo_0"
                                               value="{{old('ceo_0', $profile->getMainCeo())}}">
                                        @error('ceo_0')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
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
                                            @error('ceo' . $index)
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
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
                                @if($errors->any())
                                    @foreach($errors->getMessages() as $key => $error)
                                        @if(strpos($key, 'ceos') !== false)
                                            <div class="error text-danger">{{ $error[0] }}</div>
                                            @break
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="row shareholders">
                            <div class="form-group col-md-12">
                                <label>{{__('Shareholder (full name)')}}</label>
                                <div class="row">
                                    <div class="form-group col-md-3 shareholder">
                                        <input autocomplete="off" class="form-control disabled_el" disabled
                                               type="text"
                                               data-number="0"
                                               required
                                               name="shareholders[]" id="shareholder_0"
                                               value="{{old('shareholder_0', $profile->getMainShareholder())}}">
                                        @error('shareholder_0')
                                        <div class="error text-danger">{{ $message }}</div>
                                        @enderror
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
                                            @error('shareholder' . $index)
                                            <div class="error text-danger">{{ $message }}</div>
                                            @enderror
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
                                @if($errors->any())
                                    @foreach($errors->getMessages() as $key => $error)
                                        @if(strpos($key, 'shareholders') !== false)
                                            <div class="error text-danger">{{ $error[0] }}</div>
                                            @break
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label for="company_phone">{{__('Company Phone number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       required
                                       name="company_phone" id="company_phone"
                                       value="{{old('company_phone', $profile->company_phone)}}">
                                @error('company_phone')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="contact_phone">{{__('Contact Phone number')}}</label>
                                <input autocomplete="off" class="form-control disabled_el " disabled type="text"
                                       required
                                       name="contact_phone" id="contact_phone"
                                       value="{{old('contact_phone', $profile->cUser->phone)}}">
                                @error('contact_phone')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="linkedin_link">{{__('Contact Email')}}</label>
                                <input autocomplete="off" class="form-control disabled_el" disabled type="text" required
                                       name="contact_email" id="contact_email"
                                       value="{{old('contact_email', $profile->contact_email)}}">
                                @error('contact_email')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label for="linkedin_link">{{t('ui_c_profile_corporate_login_email')}}</label>
                                <input autocomplete="off" class="form-control" disabled type="text" required
                                       id="login_email"
                                       value="{{$profile->cUser->email}}">
                                @error('login_email')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label for="address">{{__('Interface language')}}</label>
                                <select class="w-100 disabled_el " disabled name="interface_language"
                                        id="interface_language" required>
                                    <option value="" disabled selected
                                            hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                                    @foreach(\App\Enums\Language::getList() as $langCode => $langName)
                                        <option
                                            @if(old('interface_language', $profile->interface_language) == $langCode)
                                                selected
                                            @endif
                                            value="{{$langCode}}">{{$langName}}</option>
                                    @endforeach
                                </select>
                                @error('interface_language')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="webhookSettings @if(!$profile->is_merchant) d-none @endif">
                            <div class="row ">
                                <div class="form-group col-lg-6">
                                    <label for="webhook_url">{{t('ui_webhook_url')}}</label>
                                    <input autocomplete="off" class="form-control disabled_el" disabled type="text"
                                           id="webhook_url" name="webhook_url"
                                           value="{{$profile->webhook_url}}">
                                    @error('webhook_url')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-lg-6">
                                    <label for="webhook_url">{{t('ui_secret_key')}}</label>
                                    <input class="copy-text-value w-75" id="textSecretKey"
                                           style="position: absolute; top: -1500px; left: -1500px;" type="text"
                                           value="{{  $profile->getSecretKey()  }}">
                                    <button id="SecretKey" class="btn btn-light wallet-copy"
                                            onclick="copyText(this.id)">
                                        <span class="wallet-address">{{ $profile->getSecretKey() }}</span>
                                        <i class="fa fa-copy" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="form-group col-md-5 d-flex align-items-end">
                                <button disabled
                                        class="  disabled_el btn btn-lg btn-primary themeBtn register-buttons mr-3"
                                        type="submit">{{ t('save') }}</button>
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
                                <h6 class="font-weight-bold text-center mb-4">{{ t('profile_wallets_password') }}</h6><a
                                    href="{{ route('dashboard.reset.client.password', ['id' => $profile->cUser->id]) }}"
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
</div>
<script src="/js/ceo-and-beneficial-owners.js"></script>
