<div class="col-md-6 col-lg-3 personal-info-title">{{__('First name')}}</div>
<div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->first_name}}</div>
<div class="col-md-6 col-lg-3 personal-info-title">{{__('Last name')}}</div>
<div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->last_name}}</div>
@if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1)
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Date of birth')}}</div>
    <div
        class="col-md-6 col-lg-3 personal-info-value">{{Carbon\Carbon::parse($profile->date_of_birth)->format('d.m.Y')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Phone number')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->cUser->phone}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Email')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->cUser->email}}</div>
@endif
<div class="col-md-6 col-lg-3 personal-info-title">{{ t('ui_country_residence')}}</div>
<div
    class="col-md-6 col-lg-3 personal-info-value"> {{ \App\Models\Country::getCountryNameByCode($profile->country) }}</div>
@if($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1)
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('City')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->city}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Citizenship')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ $profile->citizenship }}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Zip code/Postcode')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->zip_code}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Address')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->address}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('Gender')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{ \App\Enums\Gender::getName($profile->gender)}}</div>
    <div class="col-md-6 col-lg-3 personal-info-title">{{__('ID/Passport number')}}</div>
    <div class="col-md-6 col-lg-3 personal-info-value">{{$profile->passport}}</div>
@endif
