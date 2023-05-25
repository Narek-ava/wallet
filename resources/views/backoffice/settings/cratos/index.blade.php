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
                <h1 class="activeLink" style="display: inline-block">Settings </h1>
                @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS]))
                    <a class="btn" style="border-radius: 25px;background-color: #000;color: #fff"
                       href="{{ route('cratos.add.settings') }}">Create</a>
                @endif

                @if($settings->count())
                    <div class="row">
                        <div class="col-md-1 activeLink">No</div>
                        <div class="col-md-3 activeLink">Key</div>
                        <div class="col-md-4 activeLink">Content</div>
                        <div class="col-md-2 activeLink">Project</div>
                        <div class="col-md-2 activeLink"></div>
                        <div class="col-md-12" id="cratosSettings">
                            @foreach($settings as $key => $setting)
                                <div class="row providersAccounts-item">
                                    <div class="col-md-1">{{ $key + 1 }}</div>
                                    <div class="col-md-3 breakWord">{{ $setting->key }}</div>
                                    <div class="col-md-4 breakWord">{!! $setting->content !!}</div>
                                    <div class="col-md-2 breakWord">{{ $setting->project->name ?? ''}}</div>
                                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_AND_UPDATE_ADDRESS_SETTINGS], $setting->project_id))
                                        <div class="col-md-2">
                                            <a href="{{ route('cratos.setting.edit', ['id' => $setting->id]) }}"
                                               class="nav-link"
                                               style="color: black;text-decoration: underline;">Update</a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{ $settings->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
