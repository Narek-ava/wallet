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

    <div class="row mt-5">
        <div class="col-lg-10 col-md-10">
            <form action="{{ route('roles.update', ['role' => $role->id]) }}" method="POST">
                @csrf
                @method('PUT')
                <h3> {{ t('update_role') }} </h3>

                <div class="form-group row">
                    <div class="form-group col-6 mt-5">
                        <div class="form-label-group">
                            <h5><label for="roleName">{{ t('role_name') }}</label></h5>
                            <input id="roleName" name="name" type="text" value="{{ $role->name }}"
                                   class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" required>
                        </div>
                        @error('name')
                        <p class="error-text">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="col-12 form-group row mt-3">
                    <div style="width: 100%">
                        <h3>{{ t('permissions') }}</h3>
                        @error('permissions')<p class="text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div class="d-flex flex-wrap">
                        <div class="d-flex flex-row mb-2">
                            <input type="checkbox" id="selectPermissions"/>
                            <label class="ml-2 mb-0" id="labelSelectText">{{ t('select_all') }}</label>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap">
                        @foreach(\App\Enums\BUserPermissions::PERMISSIONS_WITH_GROUPS as $name => $permissionGroup)
                            <div class="d-flex flex-column" style="flex: 1 0 33%;">
                                <h5 class="mt-5">{{ t($name) }}</h5>
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-row mb-4">
                                        <input class="checkboxPermissions groupPermissionsInput" type="checkbox" id="{{ $name }}Input" data-group="{{$name}}"
                                        />
                                        <label class="ml-2 mb-0 groupPermissionsLabel {{ $name }}Label">{{ t('select_all') }}</label>
                                    </div>
                                    @foreach($permissionGroup as $permission)
                                        <div class="d-flex flex-row mb-2">
                                            <input class="checkboxPermissions {{$name}}Permissions" type="checkbox" name="permissions[]" id="{{ $permission }}"
                                                   @if($role->checkPermissionTo($permission)) checked @endif
                                                   value="{{ $permission }}"/>
                                            <label class="ml-2 mb-0" for="{{ $permission }}">{{ $permission }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_ROLES]))
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
    <script src="/js/backoffice/roles.js"></script>
@endsection
