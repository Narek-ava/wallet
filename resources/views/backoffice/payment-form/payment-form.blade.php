@extends('backoffice.layouts.backoffice')
@section('title', t('payment_form'))

@section('content')

    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('payment_form') }}</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex justify-content-between">
                    <div class="balance mr-2">
                        <p>{{ t('dashboard_title') }}</p>
                    </div>
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
        <h2>{{ t('ui_merchant_forms') }}</h2>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]))
            <div class="col-md-2">
                <button type="button" class="btn themeBtnWithoutHover" id="createPaymentFormButton" data-toggle="modal"
                        data-target="#paymentForm">
                    {{ t('create_new') }}
                </button>
                @include('backoffice.payment-form._create-payment-form')
            </div>
        @endif
        <form id="filterForm" method="get" >
            <select name="status" class="ml-4" id="status" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\PaymentFormStatuses::NAMES as $key => $status)
                    <option value="{{ $key }}" @if(request()->get('status') == $key) selected @endif> {{ t($status) }} </option>
                @endforeach
            </select>
            <select name="project_id" class="mb-5 mt-0" id="project_id" style="padding-right: 50px;">
                <option value="">All Projects</option>
                @foreach($activeProjects as $projectId => $projectName)
                    <option value="{{ $projectId }}" @if(request()->get('project_id') == $projectId) selected @endif> {{ $projectName }} </option>
                @endforeach
            </select>
            <select name="paymentFormType" class="ml-4 d-none" id="paymentFormType" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\PaymentFormTypes::NAMES as $key => $paymentFormType)
                    <option value="{{ $key }}" @if(request()->get('paymentFormType') == $key) selected @endif> {{ t($paymentFormType) }} </option>
                @endforeach
            </select>
        </form>


        <div class="col-md-12">
            <div class="row" id="merchantFormsSection">
                @foreach($merchantForms as $merchantForm)
                    <div class="col-md-3 payment-forms-section" data-merchant-id="{{$merchantForm->id}}" style="cursor:pointer;">
                        <p class="activeLink provider-name">{{ $merchantForm->name ?? '' }}</p>
                        <p class="providers-section-dates d-none">Type: {{ \App\Enums\PaymentFormTypes::getName($merchantForm->type) }}</p>
                        <p class="providers-section-dates">Created: {{ $merchantForm->created_at }}
                            @if( in_array($merchantForm->type, \App\Enums\PaymentFormTypes::MERCHANT_PAYMENT_FORMS)   )
                                <br><strong>ID - {{ $merchantForm->cProfile->profile_id ?? ''}}</strong>
                            @endif
                        </p>
                        <div class="providers-section-status">{{ \App\Enums\PaymentFormStatuses::getName($merchantForm->status) }}</div>
                        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]))
                            <button data-target="#paymentForm" type="button"
                                    class="editMerchantForm border-none updatePaymentForm"
                                    style="background: unset; cursor: pointer" data-toggle="modal"
                                    data-form-id="{{ $merchantForm->id }}"
                                    data-form-url="{{ route('backoffice.get.payment.form', ['paymentForm' => $merchantForm->id]) }}">
                                <img src="{{ config('cratos.urls.theme') }}images/edit_pencil.png" width="20"
                                     height="20" alt="">
                            </button>
                        @endif
                        <button class="showScript border-none" type="button" style="background: unset; cursor: pointer" data-toggle="modal" data-target="#showScriptForm{{ $merchantForm->id }}" title="{{ t('show_script_for_merchant') }}" >
                            <img src="{{ config('cratos.urls.theme') }}images/copy.png" width="20" height="auto" alt="{{ t('show_script_for_merchant') }}">
                        </button>
                    </div>
                    @include('backoffice.payment-form._show-script-form', ['paymentForm' => $merchantForm])
                @endforeach
            </div>
        </div>
    </div>
    <br><br>
    <div class="row">
        <h2>{{ t('ui_crypto_merchant_forms') }}</h2>
        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]))

            <div class="col-md-2">
                <button type="button" class="btn themeBtnWithoutHover" id="createCryptoPaymentFormButton"
                        data-toggle="modal"
                        data-target="#paymentCryptoForm">
                    {{ t('create_new') }}
                </button>
                @include('backoffice.payment-form._create-crypto-payment-form')
            </div>
        @endif
        <form id="filterCryptoForm" method="get" >
            <input type="hidden" name="cryptoPaymentFormType" value="{{ \App\Enums\PaymentFormTypes::TYPE_CRYPTO_TO_CRYPTO_FORM }}">
            <select name="status_crypto" class="ml-4" id="cryptoMerchantStatus" style="padding-right: 50px;">
                <option value=""> All</option>
                @foreach(App\Enums\PaymentFormStatuses::getList() as $key => $status)
                    <option value="{{ $key }}" @if(request()->get('status_crypto') == $key) selected @endif> {{ $status }} </option>
                @endforeach
            </select>
        </form>


        <div class="col-md-12">
            <div class="row" id="merchantCryptoFormsSection">
                @foreach($merchantCryptoForms as $merchantForm)
                    <div class="col-md-3 payment-crypto-forms-section" data-merchant-id="{{$merchantForm->id}}" data-transaction-url="{{ route('backoffice.transaction.payment.form', ['id' => $merchantForm->id]) }}" style="cursor:pointer;">
                        <p class="activeLink provider-name">{{ $merchantForm->name ?? '' }}</p>
                        <p class="providers-section-dates d-none">Type: {{ \App\Enums\PaymentFormTypes::getName($merchantForm->type) }}</p>
                        <p class="providers-section-dates">Created: {{ $merchantForm->created_at }}
                            @if(in_array($merchantForm->type, \App\Enums\PaymentFormTypes::MERCHANT_PAYMENT_FORMS))
                                <br><strong>ID - {{ $merchantForm->cProfile->profile_id }}</strong>
                            @endif
                        </p>
                        <div class="providers-section-status">{{ \App\Enums\PaymentFormStatuses::getName($merchantForm->status) }}</div>
                        @if($currentAdmin->hasPermissionInAnyProject([\App\Enums\BUserPermissions::ADD_AND_EDIT_PAYMENT_FORMS]))
                            <button data-target="#paymentCryptoForm" type="button"
                                    class="editCryptoMerchantForm border-none updatePaymentForm"
                                    style="background: unset; cursor: pointer" data-toggle="modal"
                                    data-form-id="{{ $merchantForm->id }}"
                                    data-form-url="{{ route('backoffice.get.payment.form', ['paymentForm' => $merchantForm->id]) }}">
                                <img src="{{ config('cratos.urls.theme') }}images/edit_pencil.png" width="20"
                                     height="20" alt="">
                            </button>
                        @endif
                        <button class="showScript border-none" type="button" style="background: unset; cursor: pointer" data-toggle="modal" data-target="#showScriptCryptoForm{{ $merchantForm->id }}" title="{{ t('show_script_for_merchant') }}" >
                            <img src="{{ config('cratos.urls.theme') }}images/copy.png" width="20" height="auto" alt="{{ t('show_script_for_merchant') }}">
                        </button>
                    </div>
                    @include('backoffice.payment-form._show-script-crypto-form', ['paymentForm' => $merchantForm])
                @endforeach
            </div>

        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        window.AvailablePaymentTypes = {!! json_encode($availablePaymentTypes) !!};
        window.AvailableProjects = {!! json_encode($activeProjects) !!};
        window.AvailableKYC = {!! json_encode($availableKYCOptions) !!};
    </script>
    <script src="{{ asset('js/backoffice/payment-form/payment-form.js') }}"></script>
    <script>

        $('#status, #project_id, #paymentFormType, #cryptoMerchantStatus').on('change', function () {
           let filterForm = $('#filterForm').serialize();
           let filterCryptoForm = $('#filterCryptoForm').serialize();
           let param = '?' + filterForm + '&' + filterCryptoForm;
            window.location = '{{ route('backoffice.payment.form') }}' + param;
        })

        $('.payment-forms-section').on('click', function (e) {

            if ($(e.target).hasClass('editMerchantForm')
                || $(e.target).parent().hasClass('editMerchantForm')
            ) {
                return;
            }

            if ($(e.target).hasClass('showScript')
                || $(e.target).parent().hasClass('showScript')) {
                return;
            }
            $('#merchantFormsSection').children('div').each(function () {
                $(this).removeClass('red-border');
            });
            $(this).addClass('red-border');
        })

        $('.payment-crypto-forms-section').on('click', function (e) {

            if ($(e.target).hasClass('editCryptoMerchantForm')
                || $(e.target).parent().hasClass('editCryptoMerchantForm')
            ) {
                return;
            }

            if ($(e.target).hasClass('showScript')
                || $(e.target).parent().hasClass('showScript')) {
                return;
            }

            $('#merchantCryptoFormsSection').children('div').each(function () {
                $(this).removeClass('red-border');
            });
            $(this).addClass('red-border');
            let url = $(this).closest('.payment-crypto-forms-section').data('transaction-url');
            if(url){
                window.location.href = url;
            }

        })

        $('.copyScriptButton').click(function (e) {
            let formId = $(this).data('form-id');
            let textareaId = 'paymentFormScript' + formId;
            let tempInput = document.createElement('input');
            tempInput.setAttribute('id', 'tempScriptInput');
            tempInput.setAttribute('value', $('#' + textareaId).val());
            tempInput.setAttribute('style', 'margin-left:10000px');
            document.body.appendChild(tempInput)
            $('#tempScriptInput').select();
            document.execCommand('copy');
            $('#tempScriptInput').remove();
            $(this).parent().find('.copy-successful').slideDown();
            setTimeout(() => {
                $(this).parent().find('.copy-successful').slideUp();
            }, 1500)
        })

        @if($errors->any())
            @if($errors->has('update_payment'))
                $('#updatePaymentForm{{ $errors->first('update_payment') }}').click()
            @else
                $('#createPaymentFormButton').click()
            @endif

            @if($errors->has('update_crypto_payment'))
                $('#updatePaymentForm{{ $errors->first('update_crypto_payment') }}').click()
            @else
                $('#createCryptoPaymentFormButton').click()
            @endif
        @endif
    </script>
@endsection
