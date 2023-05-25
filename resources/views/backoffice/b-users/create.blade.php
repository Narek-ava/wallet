@extends('backoffice.layouts.backoffice')

@section('title', t('title_clients_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_clients_page') }}</h2>
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
            <form action="{{ route('b-users.store') }}" method="POST">
                @csrf
                <h3> {{ t('b_users_create') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputName">{{ t('b_user_first_name') }}</label></h5>
                        <input id="inputName" name="first_name" type="text"
                               class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}">
                    </div><br>
                    @error('first_name')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputName">{{ t('b_user_last_name') }}</label></h5>
                        <input id="inputName" name="last_name" type="text"
                               class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}">
                    </div><br>
                    @error('last_name')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputEmail">{{ t('b_user_email') }}</label></h5>
                        <input id="inputEmail" name="email" type="text"
                               class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" required>
                    </div><br>
                    @error('email')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5><label for="inputPhone">{{ t('b_user_phone') }}</label></h5>
                        <input id="inputPhone" name="phone" type="text"
                               class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}">
                    </div> <br>
                    @error('phone')
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
