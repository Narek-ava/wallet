@extends('cabinet.layouts.cabinet')
@section('title', t('title_deposit_page'))

@section('content')


    <div class="fiat 15">
        <div class="top_page flex jcsb">
            <div class="left">
                <h1>{{ t('ui_cabinet_menu_deposit') }}</h1>
                {{ t('ui_15_minutes_message') }}
            </div>
            <div class="right">
                <div class="confirmation confirmation_ok flex aife">
                    <div class="left">
                        <strong>{{ t('ui_confirmation') }}</strong>
                        <p>{{ t('ui_request_for_deposit') }}</p>
                    </div>
                    <a class="btn btn-active 2_7" onclick="print_str(&#39;27&#39;,&#39;content&#39;);">Verify</a>
                </div>
            </div>
        </div>
        <div class="listItem after fww">
            <div class="inner">
                <label class="ic_ ic_select">
                    Choose a balance
                    <select name="choose_a_balance" onchange="" id="name_currency">
                        <option value="6" data-cur="USD">USD 0.00 </option>
                        <option value="7" data-cur="EUR">EUR 0.00 </option>
                        <option value="8" data-cur="GBP">GBP 0.00 </option>
                    </select>
                </label>
            </div>
            <div class="inner">
                <label>
                    {{ t('ui_deposit_amount') }}
                    <input type="text" id="dep_value" class="amOOunt">
                    <span class="error-text"></span>
                </label>
            </div>
            <div class="inner width">
                <div class="buttons flex fww">
                    <div class="button flex">
                        <button type="button" name="button" onclick="send_pay_trustpayments_com($(&#39;#name_currency option:selected&#39;).data(&#39;cur&#39;));" class="btn btn-active" disabled="">{{ t('compliance_rates_percents_bank_card') }}</button>
                        <div class="checkbox">
                            <input type="checkbox">
                            <a href="https://cratos.net/terms-and-conditions/">{{ t('ui_terms_and_conditions') }}</a>
                        </div>
                    </div>
                    <div class="button flex">
                        <button type="button" name="button" onclick="open_wire_transfer($(&#39;#dep_value&#39;).val()); $(&#39;#name_currency option:selected&#39;).val(); return false;" class="btn btn-active" disabled="">{{ t('ui_wire_transfer') }}</button>
                        <div class="checkbox">
                            <input type="checkbox">
                            <a href="https://cratos.net/terms-and-conditions/">{{ t('ui_terms_and_conditions') }}</a>
                        </div>
                    </div>
                    <p class="info-text width">
                        <span>{{ t('ui_vulture_text') }} <a href="mailto:operations@cratos.net">operations@cratos.net</a></span>
                        <span>Operated by {{ config('cratos.company_details.name') }} Registry code {{config('cratos.company_details.registry')}}.</span>
                    </p>
                </div>
            </div>
            <form method="POST" action="https://payments.securetrading.net/process/payments/choice" id="trustpayments_com">
                <input type="hidden" name="sitereference" value="vultureou79830">
                <input type="hidden" name="stprofile" value="default">
                <input type="hidden" name="currencyiso3a" value="">
                <input type="hidden" name="mainamount" value="">
                <input type="hidden" name="version" value="2">
                <input type="hidden" name="user_id" value="1">
                <input type="hidden" name="acc_histopy_id" value="">
                <input type="hidden" name="orderreference" value="">
                <input type="hidden" name="declinedurlredirect" value="https://exchange.cratos.net/php/trustpayments.com_declined.php">
                <input type="hidden" name="declinedurlnotification" value="https://exchange.cratos.net/php/crm_brana.php?form=pay_trustpayments_com_declined">
                <input type="hidden" name="successfulurlredirect" value="https://exchange.cratos.net/php/trustpayments.com_successful.php">
                <input type="hidden" name="successfulurlnotification" value="https://exchange.cratos.net/php/crm_brana.php?form=pay_trustpayments_com_successful">
                <input type="hidden" name="ruleidentifier" value="STR-6">
                <input type="hidden" name="ruleidentifier" value="STR-7">
                <input type="hidden" name="ruleidentifier" value="STR-8">
                <input type="hidden" name="ruleidentifier" value="STR-9">
                <input type="hidden" name="stdefaultprofile" value="st_cardonly">
                <input type="submit" value="Pay" style="display:none;">
            </form>
        </div><h2>Deposit via cryptos</h2><div class="listItem listItemCrypto aife after fww">
            <div class="flex width">
                <div class="item">
                    <div class="inner">
                        <label class="ic_ ic_select">
                            Choose a balance
                            <select name="choose_a_balance" onchange="">
                                <option value="1">BTC 0.00000</option>
{{--                                <option value="2">ETH 0.00000 </option>--}}
                                <option value="3">XRP 0.00000 </option>
                                <option value="4">LTC 0.00000 </option>
                                <option value="5">BCH 0.00000 </option>
                            </select>
                        </label>
                    </div>
                    <div class="inner">
                        <label>
                            Your crypto wallet address
                            <input type="text" class="input_clear password">
                            <span class="error-text"></span>
                        </label>
                    </div>
                </div>
                <div class="item">
                    <div class="inner">
                        <label>
                            Deposit amount
                            <input type="text" class="amOOunt">
                            <span class="error-text"></span>
                        </label>
                    </div>
                    <div class="inner">
                        <label class="label_qr">
                            Our crypto wallet address
                            <input type="text" class="input_clear input_qr" value="3Mv3Bm4pqFvBDhxcDYcsXvYrXp4hsVbGJu" readonly="1">
                            <span class="error-text"></span>
                        </label>
                    </div>
                </div>
                <div class="item">
                    <div class="inner">
                        <div class="qr">
                            <img src="./deposit_files/qr.png">
                        </div>
                    </div>
                </div>
            </div>
            <div class="width">
                <div class="buttons flex fww">
                    <div class="button flex">
                        <button type="button" name="button" onclick="" class="btn btn-active" disabled="">Deposit amount</button>
                        <div class="checkbox">
                            <input type="checkbox">
                            <a href="https://cratos.net/terms-and-conditions/">Terms and Conditions</a>
                        </div>
                    </div>
                    <p class="info-text width">
                        <span>{{ config('cratos.company_details.name') }}, Registry code {{config('cratos.company_details.registry')}}. {{ config('cratos.company_details.address') }}, {{config('cratos.company_details.city')}},  {{config('cratos.company_details.zip_code')}}, {{config('cratos.company_details.country')}}. <a href="mailto:operations@cratos.net">operations@cratos.net</a></span>
                        <span>Operated by {{ config('cratos.company_details.name') }} Registry code {{config('cratos.company_details.registry')}}.</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="histo">
            <h2>History of deposits</h2>
            <div class="list_transaction" id="list_transaction"><p>You have no deposits yet</p></div>
        </div>
        <div class=""></div>
    </div>


@endsection
