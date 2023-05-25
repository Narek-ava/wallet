@extends('backoffice.layouts.backoffice')
@section('title', t('projects'))

@section('content')
    <div class="row mb-3 pb-3">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('projects') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        Platform is operated by {{ config('cratos.company_details.name') }} Registry code {{config('cratos.company_details.registry')}}, registered at
                        {{config('cratos.company_details.address')}}, {{config('cratos.company_details.city')}},  {{ config('cratos.company_details.zip_code') }}.
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>

    @include('backoffice.projects.session_messages')

    <div class="d-flex flex-row justify-content-start ml-3 pl-3">
        <h4>{{ t('projects') }}</h4>
        <div class="col-4 mt-0 pt-0">
            <a type="button" href="{{ route('projects.create') }}" class="btn themeBtn round-border">
                {{ t('create') }}
            </a>
        </div>
    </div>
    <p class="ml-3 pl-3">
        <input type="checkbox" id="projectsAll"><label for="providerAll" style="margin-left: 15px">View all</label>
    </p>

    <div class="d-flex flex-row justify-content-start flex-wrap mt-5" id="projectsSection">
    @foreach($projects as $project)
        <div class="col-md-4 flex-grow-1">
            <div class="card-default p-0 credit-card ml-3 mt-4 projectCard pb-4 cursor-pointer" data-edit-url="{{ route('projects.edit', $project->id) }}">
                <div class="d-flex justify-content-between align-items-center mr-2 ml-4 mt-4">
                    <h4>{{ $project->name }}</h4>
                    <div class="card-logo d-flex align-content-end ">
                        <img src="{{ $project->logoPng }}" style="height: 100px; width: auto; object-fit: contain; object-position: center" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>
@endsection

@section('scripts')
    <script src="/js/backoffice/projects/projects.js"></script>
@endsection
