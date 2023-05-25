<?php
    /** @var \App\Models\ApiClient[] $apiClients*/
?>

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

    @if(session()->has('error'))
        <div class="alert alert-error alert-dismissible fade show" role="alert" id="errorMessageAlert">
            <h4>{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row mt-5">
        <h2>{{ t('ui_api_clients') }}</h2>
        <div class="col-md-2">
            @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_API_CLIENTS]))
                <a type="button" class="btn themeBtnWithoutHover" href="{{ route('api-clients.create') }}">
                    {{ t('create_new') }}
                </a>
            @endif
        </div>

        <div class="col-md-2">
            <select class="w-100 filter_el" id="projectId" data-url="{{ route('api-clients.index') }}">
                <option value="">All</option>
                @foreach($activeProjects as $project)
                    <option @if(request()->project_id == $project->id) selected
                            @endif value="{{ $project->id  }}">{{ $project->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12">
            @if(session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
                    <h4>{{ session()->get('success') }}</h4>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="col-md-12">
            <div class="row" id="merchantFormsSection">
                @foreach($apiClients as $apiClient)
                    <div class="col-md-3 api-clients-forms-section" data-merchant-id="{{$apiClient->id}}" style="cursor:pointer;">
                        <p class="activeLink provider-name">{{ $apiClient->name ?? '' }}</p>
                        <p class="providers-section-dates">Created: {{ $apiClient->created_at }}
                        </p>
                        <div class="providers-section-status">{{ t(\App\Models\ApiClient::STATUS_NAMES[$apiClient->status]) }}</div>
                        <a class="border-none" href="{{ route('api-clients.edit', $apiClient) }}">
                            <img src="{{ config('cratos.urls.theme') }}images/edit_pencil.png" width="20" height="20" alt="">
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/backoffice/api-clients/index.js') }}"></script>
@endsection
