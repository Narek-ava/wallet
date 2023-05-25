@extends('backoffice.layouts.backoffice')
@section('title', t('operations'))

@section('content')
    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section"> {{ t('cratos_sandbox') }} </h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>

    <form class="form " role="form" method="post">
        @csrf
        <h5>Please enter Payment form script</h5>
        <br>
        <textarea class="form-control col-md-3" rows="2" name="script">
            {{ request()->get('script') }}
        </textarea>
        <button type="submit" class="btn btn-lg btn-primary themeBtn mt-2">Test script</button>
    </form>

    @if(request()->has('script'))
        {!! request()->get('script') !!}
    @endif

@endsection


@section('scripts')
    <style>
        .cratos-form {
            height: 1200px;
        }
    </style>
@endsection
