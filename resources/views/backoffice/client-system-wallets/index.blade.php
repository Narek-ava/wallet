@extends('backoffice.layouts.backoffice')

@section('content')


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
        <div class="row">
            <div class="col-md-10">
                <div class="d-flex flex-row justify-content-start">
                    <h1 class="activeLink" style="display: inline-block">Settings </h1>
                    <select name="project_id" id="projectId" data-url="{{ route('client-wallets.index') }}" class="mr-3 ml-3" style="padding-right: 50px;">
                        <option value="" hidden>{{ t('ui_cabinet_default_select_option_text') }}</option>
                        @foreach($projectNames as $id => $project)
                            <option @if(request()->get('project_id') == $id) selected @endif value="{{ $id }}">{{ $project }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="error text-danger projectSelectError"></div>

                {{--                <a class="btn" style="border-radius: 25px;background-color: #000;color: #fff" href="{{ route('client-wallets.create') }}">Create</a>--}}

                @if($clientWallets->count())
                    <div class="row mt-5">
                        <div class="col-md-1 activeLink">No</div>
                        <div class="col-md-1 activeLink">Currency</div>
                        <div class="col-md-4 activeLink text-center">Wallet id</div>
                        <div class="col-md-3 activeLink text-center">Project</div>
                    </div>
                    <div id="cratosSettings" style="width: inherit !important;">
                        @foreach($clientWallets as $key => $clientWallet)
                            <div class="row providersAccounts-item">
                                <div class="col-md-1">{{ $key + 1 }}</div>
                                <div class="col-md-1">{{ $clientWallet->currency }}</div>
                                <div class="col-md-4 breakWord text-center" style="min-width: inherit">{{ $clientWallet->wallet_id ?? '-' }}</div>
                                <div class="col-md-3 text-center">{{ $clientWallet->project->name ?? '-' }}</div>
                                @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::EDIT_CLIENT_WALLETS], $clientWallet->project_id))
                                    <div class="col-md-1">
                                        <a href="{{ route('client-wallets.edit', ['client_wallet' => $clientWallet]) }}"
                                           class="nav-link"
                                           style="color: black;text-decoration: underline;">{{ t('ui_edit') }}</a>
                                    </div>
                                    <div class="col-md-1">
                                        @if($clientWallet->wallet_id)
                                            <form method="post"
                                                  action="{{ route('regenerate.webhook', ['clientSystemWallet' => $clientWallet]) }}">
                                                @csrf
                                                <button type="submit" class="nav-link border-none"
                                                        style="background-color: transparent; cursor: pointer">
                                                {{ t('ui_regenerate_webhook') }}
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
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
