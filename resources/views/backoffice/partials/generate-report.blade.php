@extends('backoffice.layouts.backoffice')
@section('title', t('ui_merchant_operations'))

@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
            <h4>{{ session()->get('success') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('payment_form') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex justify-content-between">
                    <div class="balance mr-2">
                        <p>{{ t('dashboard_title') }}</p>
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>

    <div class="container">
        <h2>{{ t('ui_merchant_operations') }}</h2>

        <form method="post" action="{{ route('backoffice.merchant.operations.pdf.post') }}">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="font-weight-bold mb-0">{{ t('date') }}</label>
                        <input class="date-inputs display-sell w-100" name="from" id="from" value="{{ request()->from }}" autocomplete="off" placeholder="From date">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <input class="date-inputs display-sell w-100" name="to" id="to" value="{{ request()->to }}" autocomplete="off" placeholder="To date">
                    </div>
                </div>
                <div class="col-md-2 ">
                    <div class="form-group">
                        <button type="submit" class="btn themeBtnWithoutHover"> {{ t('generate') }} </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection
