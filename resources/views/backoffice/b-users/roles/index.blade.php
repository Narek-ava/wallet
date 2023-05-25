@extends('backoffice.layouts.backoffice')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 pb-4">
            <div class="col-md-12">
                <h2 class="mb-3 mt-2 large-heading-section">{{ t('roles') }}</h2>
                <div class="row">
                    <div class="col-lg-5 d-block d-md-flex">
                        <p>{{ t('backoffice_profile_page_header_body') }}</p>
                    </div>
                    @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
                </div>
            </div>
        </div>

        @include('backoffice.b-users.roles._success-session')
        <div class="d-flex flex-column">
            <div class="d-flex flex-row" >
                <h2 class="activeLink" style="display: inline-block">{{ t('roles') }} </h2>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_ROLES]))
                    <div class="col-2">
                        <a class="btn btn-primary round-border register-buttons btn-sm themeBtnDark"
                           style="line-height: initial" href="{{ route('roles.create') }}"> {{ t('create') }} </a>
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                @if(!$roles->isEmpty())
                    <div class="row">
                        <div class="col-md-2 activeLink">No</div>
                        <div class="col-md-6 activeLink text-center">Name</div>
                        <div class="col-md-2 activeLink"></div>
                        <div class="col-md-12" id="cratosSettings">
                            @foreach($roles as $role)
                                <div class="row providersAccounts-item">
                                    <div class="col-md-2">{{ $loop->index + 1 }}</div>
                                    <div class="col-md-6 text-center">{{ $role->name }}</div>
                                    <div class="col-md-2">
                                        <a href="{{ route('roles.edit', ['role' => $role->id]) }}" class="nav-link"
                                           style="color: black;text-decoration: underline;">{{ t('ui_edit') }}</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{ $roles->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
