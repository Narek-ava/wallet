@extends('cabinet.layouts.cabinet-auth')

@section('content')

    <div class="login-form ml-auto mr-auto col-md-5">
        <h5 class="card-title text-center"><span>{{ t('b_user_set_password_header') }}</span></h5>
        <div class="common-form">
            <form method="POST" action="{{ route('b-user.new.password.post', ['token' => $token]) }}">
                @csrf
                <div class="row">
                    @foreach ($errors->all() as $error)
                        <p class="error-text">{{ $error }}</p>
                    @endforeach
                </div>

                <div class="form-group row">
                    <div class="form-label-group col-md-12">
                        <label for="password">{{ t('ui_password_reset_label_new') }}</label>
                        <input id="password" type="password"
                               class="form-control" name="password" required
                               autocomplete="new-password">
                    </div>

                </div>

                <div class="form-group row">
                    <div class="form-label-group col-md-12">
                        <label for="password-confirm">{{ t('ui_password_reset_label_confirm') }}</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                               required autocomplete="new-password">
                    </div>
                </div>

                <div class="row md-0">
                    <div class="form-label-group mb-4 col-md-4 mx-auto">
                        <button class="btn btn-lg btn-primary themeBtn btn-block" type="submit">{{ t('save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
