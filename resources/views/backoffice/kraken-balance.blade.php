@extends('backoffice.layouts.backoffice')

@section('title', t('balance'))

@section('content')
    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('balance') }}</h2>
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

    @if(session()->has('error'))
        <div class="alert alert-error alert-dismissible fade show" role="alert" id="errorMessageAlert">
            <h4>{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div>
        <h5> <label for="projectId">{{ t('projects') }}</label> </h5>
        <select name="project_id" id="projectId" data-url="{{ route('get.kraken.balance') }}"
                class="mr-3 projectSelect" style="width:280px;">
            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
            @foreach($projectNames as $id => $projectName)
                <option @if($project->id == $id) selected @endif value="{{ $id }}">{{ $projectName }}</option>
            @endforeach
        </select>
    </div>

    <div class="row common-shadow-theme p-3 w-100  col-md-6 mt-5">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 text-left">

                    <div class="col-md-20">
                        <div class="row">
                            <div class="col-md-6 mt-2 textBold breakWord">{{ t('currency') }}</div>
                            <div class="col-md-6 mt-2 textBold breakWord ">{{ t('balance') }}</div>
                        </div>
                    </div>

                    <div class="col-md-12 mt-3">
                        @foreach($balanceArray as $currency => $balance)
                            <div class="row">
                                <div class="col-md-6 mt-2">{{ $currency }}</div>
                                <div class="col-md-6 mt-2">{{ $balance }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $('#projectId').on('change', function () {
            let projectId = $(this).val();
            let url = $(this).data('url')

            window.location.href = url + '?project_id=' + projectId
        })
    </script>
@endsection
