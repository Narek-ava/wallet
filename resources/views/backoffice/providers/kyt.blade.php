@extends('backoffice.layouts.backoffice')
@section('title', t('title_card_provider_page'))

@section('content')
    @if (isset($errors) && count($errors) > 0)
        <div id="containErrors"></div>
    @endif
    <div class="row mb-4 pb-4">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_settings_page') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <h2 style="display: inline;margin-right: 25px;">{{ t('title_kyt_provider_page') }}</h2>
        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal" id="addProviderBtn" data-target="#provider">{{ t('add') }}</button>
        @endif
        <p>
            <input type="checkbox" id="kytProviderAll"><label for="providerAll" style="margin-left: 15px">{{ t('ui_view_all') }}</label>
        </p>
    </div>
    <div class="col-md-12">
        @if($message = \Illuminate\Support\Facades\Session::get('success'))
            <div class="alert alert-success alert-dismissible">
                <h4>
                    {{ $message }}
                </h4>
            </div>
        @endif
        <div class="row" id="providersSection">
            @foreach($providers as $provider)
                <div class="@if(\Illuminate\Support\Facades\Session::has('success') &&
                   \Illuminate\Support\Facades\Session::get('payment_provider_id') ==  $provider->id )
                    red-border
                @elseif(! \Illuminate\Support\Facades\Session::has('success')) {{ $provider->id === $providerId ? 'red-border' : '' }}
                @endif col-md-3 providers-section" data-provider-id="{{$provider->id}}" style="cursor:pointer;">
                    <p class="activeLink provider-name">{{ $provider->name }}</p>
                    <p class="providers-section-dates">Created: {{ $provider->created_at }}</p>
                    <p class="providers-section-dates">Last change: {{ $provider->updated_at }}</p>
                    <div class="providers-section-status">{{ \App\Enums\PaymentProvider::getName($provider->status)}}</div>
                    @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
                        <div class="editKytProvider" data-provider-id="{{ $provider->id }}">Edit</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12 mt-5">
        @if($currentAdmin->isAllowed([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <div class="modal fade modal-center" id="provider" role="dialog">
                <div class="modal-dialog modal-dialog-center">
                    <!-- Modal content-->
                    <div class="modal-content" id="complianceProvider" style="border:none;border-radius: 5px;padding: 25px;width: 700px">
                        <div class="modal-body">
                            <form name="providerForm" id="providerForm" method="POST">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" id="providerToken">
                                <h3>{{ t('provider_kyt_new') }}</h3>
                                <button type="button" class="close" data-dismiss="modal"
                                        style="position: absolute; top: -10px;right: 0">&times;
                                </button>
                                <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                                <input style="width: 350px;" type="text" id="name" name="name"
                                       value="{{ old('name') }}" required><br>
                                @error('name')
                                <span class="text-danger">{{ $message }}</span><br>
                                @enderror
                                <span class="text-danger" id="providerName"></span><br>

                                <label for="status" class="activeLink">{{ t('ui_status') }}</label><br>
                                <select name="status" id="status" style="padding-right: 50px;" required>
                                    @foreach(\App\Enums\PaymentProvider::getList() as $value => $status)
                                        <option value="{{ $value }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                <p class="error-text">{{ $message }}</p>
                                @enderror
                                <br><br>
                                <span class="text-danger" id="providerStatus"></span><br>

                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="api" class="activeLink">{{ t('kyt_provider') }}</label><br>
                                        <select name="api" id="api" style="padding-right: 50px;" required>
                                            @foreach($complianceProviders as $value => $status)
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                        @error('api')
                                        <p class="error-text">{{ $message }}</p>
                                        @enderror

                                        <span class="text-danger" id="providerApi"></span>

                                    </div>
                                    <div class="col-md-5">

                                        <label for="api_account" class="activeLink" style="margin-left: 15px">{{ t('account') }}</label><br>
                                        <select name="api_account" id="api_account" style="padding-right: 50px;" required>
                                            <option value=""></option>
                                        </select>
                                        @error('api_account')
                                        <p class="error-text">{{ $message }}</p>
                                        @enderror

                                        <span class="text-danger" id="apiAccount"></span>

                                    </div>
                                </div>

                                <br><br>
                                <button type="submit" class="btn themeBtn"
                                        style="border-radius: 25px">Save
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @endsection

        @section('scripts')

            <script type="text/javascript">
                $(document).ready(function () {
                    @if($errors->any())
                    @if($errors->has('name'))
                    $('#provider').modal('show');
                    @endif
                    @endif
                    var complianceProvider = $('#api').val();
                    generateProviderAccounts(complianceProvider)

                    $('body').on('change', '#api', function () {
                        generateProviderAccounts($(this).val())
                    });

                    $('body').on('click', '#addProviderBtn', function () {
                        $('form[name="providerForm"]').attr('action', '')
                    });


                    $('body').on('click', '.editKytProvider', function () {
                        let providerId = $(this).data('provider-id');
                        console.log(providerId)
                        $('#provider').modal('show');
                        $('#providerName').text('');
                        $('#providerStatus').text('');
                        $('#providerProject').text('');
                        $('#providerApi').text('');
                        $('#accountApi').text('');
                        $.ajax({
                            url: 'get-kyt-provider/'+providerId,
                            success: function (data) {
                                console.log(data)
                                $('input[name="name"]').val(data.name);
                                $('input[name="label"]').val(data.label);
                                $("#type").val(data.type).change();
                                $("#status").val(data.status).change();
                                $("#api").val(data.api).change();
                                generateProviderAccounts(data.api, data.api_account)
                                if (providerId) {
                                    const form = $('form[name="providerForm"]');
                                    // $('form[name="providerForm"]').prepend('<input type="hidden" name="_method" value="put"/>')
                                    form.attr('action', '{{ route('backoffice.kyt.provider.update') }}')

                                    $('#providerIdInput').remove();

                                    form.prepend('<input type="hidden" id="providerIdInput" name="provider_id" value="' + providerId + '"/>')
                                }
                            }
                        })
                    });

                    function generateProviderAccounts(providerKey, account = null) {

                        $.ajax({
                            url: 'get-kyt-provider-accounts/' + providerKey,
                            type: 'get',
                            success(data) {
                                if (data.accounts) {
                                    var providerAccountOptions = '';
                                    data.accounts.forEach(function (value, index) {
                                        var select = value == account ? 'selected' : '';
                                        providerAccountOptions += '<option ' + select + ' value="' + value + '">' + value + '</option>';
                                    })
                                    $("#api_account").html(providerAccountOptions).change();
                                }
                            }
                        });
                    }

                });
            </script>

@endsection

