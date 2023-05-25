@extends('cabinet.layouts.cabinet')
@section('title', t('title_request_page'))

@section('content')
    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 mt-2 large-heading-section page-title">Request - Wire #48</h2>
            <div class="row">
                <div class="col-lg-5 d-block d-md-flex">
                     <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In placerat, urna quis ornare blandit,
                         magna sapien fermentum ex, sed lacinia nulla ipsum et ligula. Phasellus vel sapien nec massa dictum tincidunt.</p>
                </div>
                <div class="col-lg-7">
                    <div class="compliance common-shadow-theme">
                        <div class="info-label">
                            <i class="fa fa-exclamation" aria-hidden="true"></i>
                        </div>
                        <div class="col"><h2 class="mb-3">Compliance</h2></div>
                        <div class="row m-0">
                            <div class="col-lg-9">
                                <p class="font-weight-bold">To use the deposit, exchange and withdrawal
                                    of funds, complete the verification of your account by uploading
                                    documents and wait for its approval</p>
                            </div>
                            <div class="col-lg-3">
                                <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4"
                                        type="submit">Verify
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 pt-5 pb-5">
        <div class="col-md-12">
            <form method="POST" action="{{route('cabinet.request.store')}}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <h1 class="large-heading-section mb-5 ml-15">{{t('ui_cabinet_deposit_wire_transfer_request')}}</h1>
                        <div class="row">
                            <div class="form-group col-md-5 paddingLeft0">
                                <label for="currency_from">{{t('ui_cabinet_deposit_choose_a_currency')}}</label>
                                <select class="col-md-12 mb-2 mt-3"  name="currency_from"  id="currency_from">
                                    @foreach(\App\Enums\Currency::FIAT_CURRENCY_NAMES as $currency => $currencyName)
                                        <option value="{{$currency}}" data-symbol="{{\App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[$currency]}}">{{$currencyName}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-5">
                                <label for="currency_to">{{t('ui_cabinet_deposit_exchange_to')}}</label>
                                <select class="col-md-12 mb-2 mt-3"  name="currency_to" id="currency_to">
                                    <option value="" disabled hidden>{{t('ui_cabinet_default_select_option_text')}}</option>
                                    @foreach(\App\Enums\Currency::getList() as $currency => $currencyName)
                                        <option value="{{$currency}}">{{$currencyName}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-5 paddingLeft0">
                                <label for="amount">{{t('ui_cabinet_deposit_amount')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="number"
                                         name="amount" id="amount" value="{{old('amount')}}">
                                <p class="invalid-amount text-danger">{{t('ui_cabinet_deposit_amount_invalid_text')}}</p>
                                @error('amount')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group col-md-5">
                                <label for="currency_to">{{t('ui_cabinet_deposit_by')}}</label>
                                <select class="col-md-12 mb-2 mt-3"  name="wire_type" id="by">
                                    @foreach(\App\Enums\WireType::NAMES as $type => $typeName)
                                        <option value="{{$type}}">{{$typeName}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-1">
                                <a href="" data-storage="{{storage_path('pdf')}}" id="downloadPdfFile">
                                    <img src="{{ asset('/cratos.theme/images/pdf.png') }}" width="50" class="pdf-icon pdf-bottom">
                                </a>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <p>
                                <span class="textBold">{{t('ui_cabinet_deposit_time_to_fund')}}</span>
                                <span>{{t('ui_cabinet_deposit_time_to_fund_text')}}</span>
                            </p>
                            <p>
                                <span class="textBold">{{t('ui_cabinet_deposit_fee')}}</span>
                                <span>
                                    <span class="feePercent">
                                    </span>
                                    <span id="minimumInfo">
                                    </span>
                                </span>
                            </p>
                            <div class="custom-checkbox-container">
                                <label class="custom-checkbox">
                                    {{t('ui_cabinet_deposit_exchange_rate_agree_checkbox_text')}}
                                    <input name="confirm_exchange_rate_agreement" type="checkbox">
                                    <span class="checkmark"></span>
                                    @error('confirm_exchange_rate_agreement')
                                        <p class="textRed">{{ $message }}</p>
                                    @enderror
                                </label>
                            </div>
                            <div class="custom-checkbox-container">
                                <label class="custom-checkbox">
                                    {{t('ui_cabinet_deposit_undertake_agree_checkbox_text')}}
                                    <input name="confirm_undertake_agreement" type="checkbox">
                                    <span class="checkmark"></span>
                                    @error('confirm_undertake_agreement')
                                        <p class="textRed">{{ $message }}</p>
                                    @enderror
                                </label>
                            </div>
                            <div class="custom-checkbox-container">
                                <label class="custom-checkbox">
                                    {{t('ui_cabinet_deposit_terms_and_conditions_agree_checkbox_text')}}
                                    <input name="confirm_terms_and_conditions_agreement" type="checkbox">
                                    <span class="checkmark"></span>
                                    @error('confirm_terms_and_conditions_agreement')
                                        <p class="textRed">{{ $message }}</p>
                                    @enderror
                                </label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 paddingLeft0">
                                <p class="textBold priceText">
                                    {{t('ui_cabinet_deposit_you_send')}}
                                </p>
                                <p class="textBold priceRed">
                                     <span class="currency-symbol">
                                        $
                                    </span>
                                    <span class="totalAmount">

                                    </span>
                                </p>
                            </div>
                            <div class="form-group col-md-4 paddingLeft0">
                                <p class="textBold priceText">
                                    {{t('ui_cabinet_deposit_fee')}}
                                </p>
                                <p class="textBold priceRed">
                                    <span class="currency-symbol">
                                        $
                                    </span>
                                    <span class="totalFee">

                                    </span>
                                </p>
                            </div>
                            <div class="form-group col-md-4 paddingLeft0">
                                <p class="textBold priceText">
                                    {{t('ui_cabinet_deposit_you_will_receive')}}
                                </p>
                                <p class="textBold priceRed">
                                     <span class="currency-symbol">
                                        $
                                    </span>
                                    <span class="totalReceive">

                                    </span>
                                </p>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-7  bankDetails">

                        <h1 class="large-heading-section mb-5">{{t('ui_cabinet_bank_details')}}</h1>
                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="templateName">{{t('ui_cabinet_bank_details_name_of_template')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="templateName" id="templateName" placeholder="{{t('ui_cabinet_bank_details_name_of_template_placeholder')}}"
                                         value="{{old('templateName')}}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="chooseTemplate">{{t('ui_cabinet_bank_details_choose_from_template')}}</label>
                                <select class="col-md-12 mb-2 mt-3"  name="template_id" id="chooseTemplate">
                                    <option value="" >{{t('ui_cabinet_default_select_option_text')}}</option>
                                    @foreach($templates as $templateId => $templateName)
                                        <option value="{{$templateId}}">{{$templateName}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="templateName">{{t('ui_cabinet_bank_details_account_holder')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="holder" id="accountHolder"
                                         placeholder="{{t('ui_cabinet_bank_details_account_holder_placeholder')}}"
                                         value="">
                                @error('holder')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="account_number">{{t('ui_cabinet_bank_details_account_number')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="number" id="accountNumber" placeholder="{{t('ui_cabinet_bank_details_account_number_placeholder')}}"
                                         value="">
                                @error('number')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="bank_name">{{t('ui_cabinet_bank_details_bank_name')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="bank_name" id="bank_name"
                                         placeholder="{{t('ui_cabinet_bank_details_bank_name_placeholder')}}"
                                         value="">
                                @error('bank_name')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="bank_address">{{t('ui_cabinet_bank_details_bank_address')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="bank_address" id="bank_address"
                                         placeholder="{{t('ui_cabinet_bank_details_bank_address_placeholder')}}"
                                         value="">
                                @error('bank_address')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="bank_name">{{t('ui_cabinet_bank_country_choose_from_template')}}</label>
                                <select class="col-md-12 mb-2 mt-3"  name="country" id="chooseCountry">
                                    <option value="" >{{t('ui_cabinet_default_select_option_text')}}</option>
                                    @foreach(\App\Models\Country::getCountries(false) as $countryCode => $countryName)
                                        <option value="{{$countryCode}}">{{$countryName}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6 ">
                                <label for="iban">{{t('ui_cabinet_bank_details_iban')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="iban" id="iban"
                                         placeholder="{{t('ui_cabinet_bank_details_iban_placeholder')}}"
                                         value="">
                                @error('iban')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="form-group col-md-6 ">
                                <label for="swift">{{t('ui_cabinet_bank_details_swift')}}</label>
                                <input   autocomplete="off" class="mt-3 form-control"  type="text"
                                         name="swift" id="swift"
                                         placeholder="{{t('ui_cabinet_bank_details_swift_placeholder')}}"
                                         value="">
                                @error('swift')
                                    <p class="textRed">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                        <div class="row mt-5">
                            <p class="textBold textRed">{{t('ui_cabinet_bank_details_red_info_text')}}
                            </p>
                            <p class="textBold ">{{t('ui_cabinet_bank_details_info_text')}}
                            </p>
                        </div>
                        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4" type="submit">{{t('ui_cabinet_bank_details_create_btn')}}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js/cabinet/deposit.js"></script>
    <script>
        let feePercent = @json(\App\Models\ExchangeRequest::FEE);
        const pdfFiles = @json($pdfFiles);
        const selectedCurrencyElement = document.getElementById('currency_from');
        const selectedByElement = document.getElementById('by');

        selectedCurrencyElement.addEventListener('change', changeSelectListener);
        selectedByElement.addEventListener('change', changeSelectListener);
        selectedCurrencyElement.addEventListener('change', changeBySelect);

        callDefaultFunctions();

        function changeSelectListener() {
            const pdfFileType = selectedCurrencyElement.value + '_' + selectedByElement.value;
            const pdfFileHref = pdfFiles[pdfFileType];

            let pdfButton = document.getElementById('downloadPdfFile');
            let newPdfButton = pdfButton.cloneNode(true);
            if (pdfFileHref) {
                pdfButton.parentNode.replaceChild(newPdfButton, pdfButton);
                newPdfButton.setAttribute('href', 'download/' + pdfFileHref);
            } else {
                pdfButton.setAttribute('href', '');
                pdfButton.addEventListener("click", function(event){
                    event.preventDefault()
                });
            }
            callDefaultFunctions();
        }

        function callDefaultFunctions() {
            getMinimumColumnValue();
            rateValue();
            getCurrency();
            changeBySelect();
            pdfDefaultHref();
            getMontHLimitValue();
        }

        function setAjaxHeader() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }

        function getMinimumColumnValue() {
            setAjaxHeader();

            let thisCurrency = Array.apply(null, selectedCurrencyElement.options).find(opt => {
                return opt.value == selectedCurrencyElement.value;
            }).text;
            let thisBy = Array.apply(null, selectedByElement.options).find(opt => {
                return opt.value == selectedByElement.value;
            }).text;
            $.ajax({
                url: "get-rate-min/" + thisCurrency + '/' + thisBy,
            }).then(response => {
                rateMin = parseInt(response);
                $('#minFee').text(rateMin);
            });
        }

        function rateValue() {
            setAjaxHeader();
            let thisBy = Array.apply(null, selectedByElement.options).find(opt => {
                return opt.value == selectedByElement.value;
            }).text;
            $.ajax({
                url: "get-rate-value/" + thisBy,
            }).then(response => {
                feePercent = response;
                $('.feePercent').text(parseFloat(feePercent)+'%');
            });
        }

        function getCurrency() {
            let currencyValue = $('#currency_from option:selected').text();
            let currencyMinimumText = '(minimum ' + '<span id="minFee"></span>' + ' ' + currencyValue + ')';
            $('#minimumInfo').html(currencyMinimumText);
        }

        function changeBySelect() {
            if(selectedCurrencyElement.value == '{{ App\Enums\Currency::CURRENCY_USD }}') {
                selectedByElement.options[value='{{ App\Enums\WireType::TYPE_SEPA }}'].setAttribute('disabled', true);
                $('#by').val('{{ App\Enums\WireType::TYPE_SWIFT }}');
            } else {
                selectedByElement.options[value='{{ App\Enums\WireType::TYPE_SEPA }}'].removeAttribute('disabled');
            }
        }

        function pdfDefaultHref() {
            const pdfFileType = selectedCurrencyElement.value + '_' + selectedByElement.value;
            const pdfFileHref = pdfFiles[pdfFileType];
            document.getElementById('downloadPdfFile').setAttribute('href', 'download/' + pdfFileHref);
        }

        function getMontHLimitValue() {
            setAjaxHeader();
            $.ajax({
                url: "get-transactions-month-limit",
            }).then(response => {
                $('#amount').attr('max', response);
            });
        }
    </script>

@endsection
