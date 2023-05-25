<?php
/** @var \App\Models\ApiClient $apiClient*/
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
        <div class="col-md-6">
            <form action="{{ route('api-clients.update', $apiClient) }}" method="POST">
                @method('PUT')
                @csrf
                <h3> {{ t('api_client_edit') }} </h3>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputName">{{ t('api_clients_name') }}</label> </h5>
                        <input id="inputName" name="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"  value="{{ $apiClient->name }}" required>
                    </div>
                    @error('name')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputKey">{{ t('api_clients_key') }}</label> </h5>
                        <input id="inputKey" name="key" type="text" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}"  value="{{ $apiClient->key }}" required>
                    </div>
                    @error('key')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5 d-flex flex-row">
                    <div class="form-label-group">
                        <h5> <label for="inputStatus">{{ t('api_clients_status') }}</label> </h5>
                        <select name="status" id="inputStatus">
                            @foreach(\App\Models\ApiClient::STATUS_NAMES as $key => $statusName)
                                <option value="{{ $key }}" @if($key == $apiClient->status) selected @endif>{{ t($statusName) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-label-group ml-5">
                        <h5> <label for="inputStatus">{{ t('projects') }}</label> </h5>

                        <select name="project_id" data-permission="{{ \App\Enums\BUserPermissions::ADD_AND_EDIT_API_CLIENTS }}"
                                class="mr-3 projectSelect" style="width:280px;">
                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                            @foreach($projectNames as $id => $project)
                                <option @if($apiClient->project_id == $id) selected @endif value="{{$id}}">{{$project}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="error text-danger projectSelectError"></div>

                    @error('status')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputToken"> {{ t('api_clients_token') }} </label> </h5>
                        <div class="d-flex justify-center">
                            <input id="inputToken" name="apiToken" type="text" class="form-control{{ $errors->has('key') ? ' is-invalid' : '' }}"  value="{{ $apiClient->token }}" required>
                            <button id="regenerateTokenButton" class="border-none" type="button" style="background: unset; cursor: pointer" title="{{ t('api_clients_regenerate_token') }}" >
                                <img src="{{ config('cratos.urls.theme') }}images/regenerate_icon.png" width="30" height="auto" alt="{{ t('api_clients_regenerate_token') }}">
                            </button>

                            <input class="copy-text-value w-75" id="copyApiTokenInput"
                                   style="position: absolute; top: -1500px; left: -1500px;"
                                   type="text" value="{{ $apiClient->token }}">
                            <button id="copyApiToken" type="button" class="btn btn-light token-copy"
                                    onclick="copyText(this)">
                                <i class="fa fa-copy" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    @error('apiToken')
                        <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputAccessToken">{{ t('api_access_token') }}</label> ({{ t('hour') }})</h5>
                        <input id="inputAccessToken" name="accessTokenExpiresTime" type="number" class="form-control{{ $errors->has('access_token_expires_time') ? ' is-invalid' : '' }}"  min="1" max="24"
                               value="{{ $apiClient->access_token_expires_time ?? '' }}" required>
                    </div>
                    @error('accessTokenExpiresTime')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                <div class="form-group mt-5">
                    <div class="form-label-group">
                        <h5> <label for="inputRefreshToken">{{ t('api_refresh_token') }}</label> ({{ t('day') }})</h5>
                        <input id="inputRefreshToken" name="refreshTokenExpiresTime" type="number" class="form-control{{ $errors->has('refresh_token_expires_time') ? ' is-invalid' : '' }}"  min="1" max="30"
                               value="{{ $apiClient->refresh_token_expires_time ?? '' }}" required>
                    </div>
                    @error('refreshTokenExpiresTime')
                    <p class="error-text">{{ $message }}</p>
                    @enderror
                </div>
                @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_AND_EDIT_API_CLIENTS], $apiClient->project_id))
                    <div class=" col-md-3 form-group mt-5 pl-0">
                        <div class="form-label-group">
                            <button class="btn btn-lg btn-primary themeBtn btn-block"
                                    type="submit">{{ t('save') }}</button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/backoffice/api-clients/index.js') }}"></script>
@endsection
