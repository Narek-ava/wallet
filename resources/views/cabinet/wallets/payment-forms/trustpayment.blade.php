<?php

use App\DataObjects\Payments\TrustPayment\FormData;
use App\Models\Operation;

/* @var FormData $formData */
/* @var Operation $operation */
/* @var string $siteReference */

?>
<link rel="stylesheet" href="{{ config('cratos.urls.theme') }}css/stylesheet.css?v={{ time() }}">
<style>
    body {
        background-color: white;
    }
</style>
<form method="POST" id="trustpayment_form" action="https://payments.securetrading.net/process/payments/choice">
    <input type="hidden" name="sitereference" value="{{ $siteReference }}">
    <input type="hidden" name="stprofile" value="default">
    <input type="hidden" name="stdefaultprofile" value="st_paymentcardonly">
    <input type="hidden" name="currencyiso3a" value="{{ $formData->currencyiso3a }}">
    <input type="hidden" name="mainamount" value="{{ $formData->mainamount }}">
    <input type="hidden" name="orderreference" value="{{ $operation->id }}">

    <!-- Set the required card holder name field !-->
    <input type="hidden" name="strequiredfields" value="nameoncard">
    <!-- Set the required card holder name fields !-->

    <input type="hidden" name="version" value="2">

    @if(config('app.env') != 'local' && !in_array($operation->operation_type, [\App\Enums\OperationOperationType::MERCHANT_PAYMENT, \App\Enums\OperationOperationType::TYPE_CARD_PF]))
        <input type=hidden name="ruleidentifier" value="STR-6">
        <input type=hidden name="successfulurlredirect"
               value="{{ route('cabinet.history', ['newCardOperationSuccess' => t('log_message_card_operation_successful_payment_response', ['operationId' => $operation->operation_id])]) }}">
    @endif
    <input type=hidden name="ruleidentifier" value="STR-8">
    <input type=hidden name="successfulurlnotification" value="{{ route('webhook.trust.payments.transfer') }}">
    {{--            <input type=hidden name="successfulurlnotification" value="https://dev.cratos.net/webhook/payments/trust-payment">--}}

    <button class="btn" type="submit" hidden id="trustPaymentFormBtn">Pay</button>
</form>


<script>
    window.onload = function () {
        document.getElementById('trustpayment_form').submit();
    }
</script>
