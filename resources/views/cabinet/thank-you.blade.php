@extends('cabinet.layouts.cabinet-auth')

@section('content')
    <div class="login-form login-form-outer ml-auto mr-auto">
        @if($successMessage)
            <div class="alert alert-success alert-dismissible">
                <h4>
                    {{t('ui_thank_you_message')}}
                    <br>
                    {{$successMessage}}
                </h4>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @else
            <div class="alert alert-danger alert-dismissible">
                <h4>{{$errorMessage}}</h4>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>

@endsection
