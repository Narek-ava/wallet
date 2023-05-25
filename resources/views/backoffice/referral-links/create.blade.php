@extends('backoffice.layouts.backoffice')

@section('title', t('settings'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('settings') }}</h2>
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
            <form action="{{ route('referral-links.store') }}" method="POST">
                @csrf
                <input type="hidden" name="partner_id" value="{{ $partner_id }}">
                <h3> {{ t('create_referral_link') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputName"> {{ t('referral_link_name') }}</label></h5>
                        <input id="inputName" name="name" type="text"
                               class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>
                    </div>
                    @error('name')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('individual_rate_name') }}</label></h5>
                        <select name="individual_rate_templates_id" id="inputStatus" required>
                            @foreach($rates as $key => $rate)
                                <option value="{{ $rate->id }}">{{ $rate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('corporate_rate_name') }}</label></h5>
                        <select name="corporate_rate_templates_id" required>
                            @foreach($rates as $key => $rate)
                                <option value="{{ $rate->id }}">{{ $rate->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('status')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('activated_date') }}</label></h5>
                        <input class="date-inputs display-sell w-50" name="activation_date" id="from" value="{{ request()->from }}" autocomplete="off" placeholder="Activate date" required>
                    </div>
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputStatus">{{ t('deactivated_date') }}</label></h5>
                        <input class="date-inputs display-sell w-50" name="deactivation_date" id="date" value="{{ request()->to }}" autocomplete="off" placeholder="Deactivate date" required>
                    </div>
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputToken"> {{ t('registration_link') }} </label></h5>
                        <div class="d-flex justify-center">
                            <input id="inputToken" type="text" readonly
                                   class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}" required placeholder="">
                        </div>
                    </div>
                    @error('token'))
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
