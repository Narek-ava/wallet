@extends('backoffice.layouts.backoffice')
@section('title', t('title_card_issuing_provider_page'))

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
        <h2 style="display: inline;margin-right: 25px;">{{ t('title_card_issuing_provider_page') }}</h2>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <button class="btn" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal" id="addProviderBtn" data-target="#provider">{{ t('add') }}</button>
        @endif
        <p>
            <input type="checkbox" id="providerAll"><label for="providerAll" style="margin-left: 15px">{{ t('ui_view_all') }}</label>
        </p>
    </div>
    <div class="col-md-12">
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
                    @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
                        <div class="editProvider" data-provider-id="{{ $provider->id }}">Edit</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-md-12 mt-5">
        @if($message = \Illuminate\Support\Facades\Session::get('success'))
            <div class="alert alert-success alert-dismissible">
                <h4>
                    {{ $message }}
                </h4>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_EDIT_PROVIDERS]))
            <div class="modal fade modal-center" id="provider" role="dialog">
                <div class="modal-dialog modal-dialog-center">
                    <!-- Modal content-->
                    <div class="modal-content" style="border:none;border-radius: 5px;padding: 25px;width: 500px">
                        <div class="modal-body">
                            <form name="providerForm" id="providerForm"
                                  action="{{ route('backoffice.card.issuing.provider.store') }}" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" id="providerToken">
                                <h3>{{ t('provider_card_new') }}</h3>
                                <button type="button" class="close" data-dismiss="modal"
                                        style="position: absolute; top: -10px;right: 0">&times;
                                </button>
                                <label for="name" class="activeLink">{{ t('ui_name') }}</label><br>
                                <input style="width: 350px;" type="text" id="name" name="name"
                                       value="{{ old('name') }}" required><br>
                                <span class="text-danger" id="providerName"></span><br>
                                <label for="status" class="activeLink">{{ t('ui_status') }}</label><br>
                                <select name="status" id="status" required>
                                    <option value=""></option>
                                    @foreach(\App\Enums\PaymentProvider::getList() as $value => $status)
                                        <option value="{{ $value }}">{{ $status }}</option>
                                    @endforeach
                                </select><br>
                                <span class="text-danger" id="providerStatus"></span><br>


                                <div class="d-flex flex-row justify-content-between">
                                    <div class="d-flex flex-column col-6 pl-0">
                                        <label for="api" class="activeLink mt-0 mb-0">{{ t('ui_api') }}</label>
                                        <select name="api" id="api" style="padding-right: 50px;" required data-url="{{ route('backoffice.get.card.issuing.provider.api.account') }}">
                                            <option value=""></option>
                                            @foreach(\App\Enums\CardIssuingApiProviders::CARD_ISSUING_API_PROVIDERS as $key)
                                                <option value="{{ $key }}">{{ t($key) }}</option>
                                            @endforeach
                                        </select><br>
                                        <span class="text-danger apiError"></span>
                                    </div>

                                    <div class="apiAccount d-none col-6 pl-0">
                                        <div class="d-flex flex-column">
                                            <label for="api_account" class="activeLink mt-0 mb-0">{{ t('ui_api_accounts') }}</label>
                                            <select name="api_account" id="api_account" style="padding-right: 50px;"
                                                    required>
                                                <option value=""></option>
                                            </select><br>
                                            <span class="text-danger apiAccountError"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-row justify-content-between mt-5">
                                    <div class="d-flex flex-column col-6 pl-0">
                                        <label for="plastic_card_amount" class="activeLink mt-0 mb-0">{{ t('wallester_plastic_card_order_amount') }}</label><br>
                                        <input style="border: 1px solid #c1c1c1;border-radius: 10px; line-height: 30px" id="plastic_card_amount" name="plastic_card_amount"><br>
                                        <span class="text-danger plasticError"></span>
                                    </div>

                                    <div class="col-6 pl-0">
                                        <div class="d-flex flex-column">
                                            <label for="virtual_card_amount" class="activeLink mt-0 mb-0">{{ t('wallester_virtual_card_order_amount') }}</label> <br>
                                            <input style="border: 1px solid #c1c1c1;border-radius: 10px; line-height: 30px" id="virtual_card_amount" name="virtual_card_amount"><br>
                                            <span class="text-danger virtualError"></span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="providerCreate" class="btn themeBtn"
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
            <script>
                $(document).ready(function () {
                    if ($('#containErrors').length) {
                        if('{{ old('secure') }}') {
                            getPaymentSystem('{{ old('secure') }}', '{{ old('payment_system') }}')
                        }
                        let oldCountries = '{!! json_encode(old('countries')) !!}';
                        if (oldCountries) {
                            $('#countries').val(JSON.parse(oldCountries));
                            $("#countries").select2({data: JSON.parse(oldCountries)});
                            $("#countries").trigger('change');
                        }
                    }

                    $('body').on('change', '#secure', function () {
                        getPaymentSystem($(this).val(), null)
                    });


                    function getPaymentSystem(secure, system) {
                        if(!secure) {
                            $("#paymentSystem").find('option').remove();
                            $("#paymentSystem").append(`<option value=""></option>`);
                        } else {
                            $.ajax({
                                url: 'get-payment-systems',
                                type: 'get',
                                success(data){
                                    if(data) {
                                        $("#paymentSystem").find('option').remove();
                                        $.each(data, function ($key, $type) {
                                            if (!system) {
                                                system = window.localStorage.getItem('payment_system');
                                                window.localStorage.removeItem('payment_system');
                                            }
                                            let selected = (system == $key ? 'selected' : '');
                                            $("#paymentSystem").append(`<option value="${$key}" ${selected}>${$type}</option>`);
                                        })
                                    }
                                }
                            });
                        }
                    }
                    @if($errors->any())
                    @if($errors->has('bank_detail_type'))
                    $('.correspondent_bank_details').removeAttr('hidden')
                    $('.intermediary_bank_details').removeAttr('hidden')
                    @elseif($errors->has('api_account'))
                    $('.apiAccount').addClass('d-block').removeClass('d-none')
                    @endif


                    @endif
                })
            </script>
@endsection
