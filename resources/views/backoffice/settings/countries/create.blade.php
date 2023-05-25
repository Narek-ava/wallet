@extends('backoffice.layouts.backoffice')

@section('title', t('title_countries_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_countries_page') }}</h2>
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

    <div class="row mt-5">
        <div class="col-md-6">
            <form action="{{ route('countries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <h3> {{ t('ui_country_create') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputName">{{ t('ui_country_name') }}</label></h5>
                        <input id="inputName" name="name" type="text"
                               class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>
                    </div>
                    <br>
                    @error('name')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputCode">{{ t('ui_country_code') }}</label></h5>
                        <input id="inputCode" name="code" type="text"
                               class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}" required>
                    </div>
                    <br>
                    @error('code')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputCode">{{ t('ui_country_code_iso_3') }}</label></h5>
                        <input id="inputCodeISO3" name="code_ISO3" type="text"
                               class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}" required>
                    </div>
                    <br>
                    @error('code_ISO3')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputBanned">{{ t('ui_country_banned') }}</label></h5>
                        <select name="isBanned" id="inputBanned">
                            @foreach(\App\Models\Country::BANNED_NAMES as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    @error('isBanned')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputAlphanumericSender">{{ t('ui_country_alphanumeric_sender') }}</label></h5>
                        <select name="isAlphanumericSender" id="inputAlphanumericSender">
                            @foreach(\App\Models\Country::ALPHANUMERIC_SENDER_NAMES as $key => $name)
                                <option value="{{ $key }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    @error('isAlphanumericSender')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputToken"> {{ t('ui_phone_code') }} </label></h5>
                        <div class="d-flex justify-center">
                            <input id="inputPhoneCode" name="phoneCode" type="text"
                                   class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}" required>
                        </div>
                    </div>
                    <br>
                    @error('phoneCode')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="countryFlag"> {{ t('country_flag') }} </label></h5>
                        <p class="text-danger">* The size of the country flag must be no more than 1024 KB and in PNG format.</p>
                        <div class="d-flex justify-center">
                            <label id="labelFile" for="files"> {{ t('ui_compliance_upload_documents_button_text') }}</label>
                            <input id="inputCountryFlag" name="countryFlag" type="file" style="display: none">
                            <img src="" id="countryFlagUploadImage" style="display: none">
                            <p id="countryFlagStatus" class="text-success"></p>
                        </div>
                    </div>
                    <br>
                    @error('countryFlag')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class=" col-md-3 form-group mt-5 pl-0">
                    <div class="form-label-group">
                        <button class="btn btn-lg btn-primary themeBtn btn-block"
                                type="submit">{{ t('save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#labelFile').click(function (){
            $('#inputCountryFlag').click();
        })
        $(document).on('change', '#inputCountryFlag', function () {
            var file = $('#inputCountryFlag')[0].files[0];
            var fileName = file.name;
            $('#countryFlagStatus').text(fileName + ' was successfully selected.');
            $('#countryFlagUploadImage').attr('src', URL.createObjectURL(file)).show()
        })
    </script>
@endsection

