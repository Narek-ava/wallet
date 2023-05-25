@extends('backoffice.layouts.backoffice')

@section('content')
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successMessageAlert">
            <h4>{{ session()->get('success') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-error alert-dismissible fade show" role="alert" id="errorMessageAlert">
            <h4>{{ session()->get('error') }}</h4>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <div class="container-fluid">
        <div class="row mb-4 pb-4">
            <div class="col-md-12">
                <h2 class="mb-3 mt-2 large-heading-section">Notifications</h2>
                <div class="row">
                    <div class="col-lg-5 d-block d-md-flex">
                        <p>{{ t('backoffice_profile_page_header_body') }}</p>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h1 class="activeLink" style="display: inline-block">Add new setting </h1>
                <form action="{{ route('cratos.setting.create') }}" method="post">
                    @if(isset($setting))
                        <input type="hidden" name="_method" value="put">
                    @endif
                    @csrf
                    <label for="key" class="textBold">Key</label><br>
                    <input type="text" id="key" name="key"
                           value="{{ isset($setting) ? $setting->key : old('key') }}" @if(isset($setting)) {{ 'readonly' }} @endif><br>
                    @error('key')
                    <span class="textBold text-danger">{{ $message }}</span><br>
                    @enderror

                    <div class="mt-3">
                        <label for="projectId" class="textBold">Project</label><br>
                        <select name="project_id" id="projectId"
                                data-permission="{{ \App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS }}"
                                class="mr-3 projectSelect" style="width:280px;">
                            <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                            @foreach($projectNames as $project)
                                <option @if(isset($setting) && $setting->project_id == $project->id) selected
                                        @endif value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                        <div class="error text-danger projectSelectError"></div>

                        @error('project_id')
                        <span class="textBold text-danger">{{ $message }}</span><br>
                    @enderror
                    <div>
                        <label for="content" class="textBold mt-3">Content</label>
                        <textarea id="content" class="ckeditor form-control"
                                  name="content">{{ isset($setting) ? $setting->content : old('content') }}</textarea>
                        @error('content')
                        <span class="textBold text-danger">{{ $message }}</span><br>
                        @enderror
                    </div>
                    @if(session()->has('message'))
                        <span class="textBold text-danger">{{ session()->get('message') }}</span><br>
                    @endif
                    <button class="btn mt-4"
                            style="padding-left:10px;border-radius: 25px;background-color: #fe3d2b;color: #fff"
                            type="submit">Create
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

