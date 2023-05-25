@if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1)
    <div class="col-md-6 col-lg-3 personal-info-title">{{t('ui_c_profile_corporate_company_email')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->company_email}}</div>
@endif
<div class="col-md-6 col-lg-3 personal-info-title">{{__('Full company name')}}</div>
<div class="col-md-6 col-lg-3 personal-info-value">{{$profile->company_name}}</div>
@if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1)
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Registration date')}}</div>
    <div
        class="col-md-6 col-lg-3 personal-info-value">{{Carbon\Carbon::parse($profile->registration_date)->format('d.m.Y')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Registration number')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->registration_number}}</div>
@endif
<div class="col-md-6 col-lg-3 personal-info-title">{{ t('ui_country_residence')}}</div>
<div
    class="col-md-6 col-lg-3 personal-info-value">{{ \App\Models\Country::getCountryNameByCode($profile->country) }}</div>
@if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1)
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Legal address')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->legal_address}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Trading address')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->trading_address}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Beneficial owners (full name)')}}</div>
    <div
        class="col-md-6 col-lg-3 personal-info-value">{{ implode(', ' , $profile->getBeneficialOwnersForProfile()) }}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('CEOS (full name)')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ implode(', ', $profile->getCeosForProfile())}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Shareholders')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ implode(', ', $profile->getShareholdersForProfile()) }}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Company Phone number')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->company_phone}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Contact Phone number')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->cUser->phone}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Contact Email')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->contact_email}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{t('ui_c_profile_corporate_login_email')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->cUser->email}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Interface language')}}</div>
    <div
        class="col-md-6 col-lg-3 personal-info-value">{{ \App\Enums\Language::getName($profile->interface_language) }}</div>
@endif
