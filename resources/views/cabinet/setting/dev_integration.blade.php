<div class="col-md-6 col-lg-5 ml-lg-5 mr-lg-5 personal-info-value">
    <h5>{{ t('webhook_settings') }}</h5>
    <hr>
    <br>
    @if($profile->is_merchant)
        <form method="post" id="updateWebhookUrl" action="{{ route('update.webhook.url', ['profile' => $profile]) }}"
              data-token="{{ csrf_token() }}">

            <div class="row">
                <div class="form-group col-md-6">
                    <label for="webhook_url">{{ t('ui_webhook_url') }}</label>
                    <input autocomplete="off" class="form-control disabled_el" @if(!$profile->is_merchant) disabled=""
                           @endif type="text" id="webhook_url" name="webhook_url" value="{{ $profile->webhook_url }}">
                </div>
            </div>

            <p class="error-text" hidden>{{ t('invalid_url') }}</p>
            <button type="submit" class="btn btn-primary themeBtn">Submit</button>
        </form>

        <div class="row mt-4 secretKeyContainer" @if(!$profile->webhook_url) hidden @endif>
            <div class="form-group col-md-6">
                <label for="webhook_url">Secret Key</label>
                <input class="copy-text-value w-75" id="textSecretKey"
                       style="position: absolute; top: -1500px; left: -1500px;" type="text"
                       value="{{ $profile->getSecretKey() }}">
                <button id="SecretKey" class="btn btn-light" onclick="copyText(this.id)">
                    <span class="secret-key-btn">{{ $profile->getSecretKey() }}</span>
                    <i class="fa fa-copy" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    @endif
</div>

<div class="col-md-6 col-lg-6 personal-info-value" id="accordion">
    <h5>{{ t('webhook_example_request') }}</h5>

    <div class="card border-none">
        <div class="card-header" id="headingOne">
            <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    {{ \App\Enums\OperationOperationType::getName(\App\Enums\OperationOperationType::TYPE_CARD_PF) }}
                </button>
            </h5>
        </div>


        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
<pre class="card-default" style="padding: 10px 20px;">{
    <span class="textColorRed">{{ t('webhook_id') }}</span>:<span class="textColorLavander">"59ls2ed3-f5a7-98ta-9ecc-4146770946c5"</span>,
    <span class="textColorRed">{{ t('webhook_operation_id') }}</span>:<span class="textColorLavander">"592a3ed3-f5a7-98c2-9ecc-4141730746c9"</span>,
    <span class="textColorRed">{{ t('webhook_operation_number') }}</span>:<span class="textColorLavander">35</span>,
    <span class="textColorRed">{{ t('webhook_operation_type') }}</span>: <span class="textColorLavander">"Merchant Payment"</span>,
    <span class="textColorRed">{{ t('webhook_operation_status') }}</span>: <span class="textColorLavander">"SUCCESSFUL"</span>,
    <span class="textColorRed">{{ t('webhook_from_currency') }}</span>: <span class="textColorLavander">"EUR"</span>,
    <span class="textColorRed">{{ t('webhook_amount') }}</span>: <span class="textColorLavander">10.00</span>,
    <span class="textColorRed">{{ t('webhook_to_currency') }}</span>: <span class="textColorLavander">"BTC"</span>,
    <span class="textColorRed">{{ t('webhook_card_number_mask') }}</span>: <span class="textColorLavander">"400000######1000"</span>,
    <span class="textColorRed">{{ t('webhook_transaction_reference') }}</span>: <span class="textColorLavander">"51-6-1346654"</span>,
    <span class="textColorRed">{{ t('webhook_blockchain_fee') }}</span>: <span class="textColorLavander">0.0002</span>,
    <span class="textColorRed">{{ t('webhook_top_up_fee') }}</span>: <span class="textColorLavander">0.001</span>,
    <span class="textColorRed">{{ t('webhook_exchange_rate') }}</span>: <span class="textColorLavander">153.74</span>,
    <span class="textColorRed">{{ t('webhook_credited') }}</span>: <span class="textColorLavander">"0.06107027 BTC"</span>,
    <span class="textColorRed">{{ t('webhook_date') }}</span>: <span class="textColorLavander">"2022-01-01 21:00:00"</span>,
    <span class="textColorRed">{{ t('webhook_payer_name') }}</span>: <span class="textColorLavander">"John Doe"</span>,
    <span class="textColorRed">{{ t('webhook_payer_phone') }}</span>: <span class="textColorLavander">"19026682819"</span>,
    <span class="textColorRed">{{ t('webhook_email') }}</span>: <span class="textColorLavander">"jdoe@gmail.com"</span>,
}  </pre>
            </div>
        </div>

    </div>

    <div class="card border-none">
        <div class="card-header" id="headingTwo">
            <h5 class="mb-0">
                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    {{ \App\Enums\OperationOperationType::getName(\App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF) }}
                </button>
            </h5>
        </div>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
            <div class="card-body">
                <pre class="card-default" style="padding: 10px 20px;">{
    <span class="textColorRed">{{ t('webhook_id') }}</span>:<span class="textColorLavander">"59ls2ed3-f5a7-98ta-9ecc-4146770946c5"</span>,
    <span class="textColorRed">{{ t('webhook_operation_id') }}</span>:<span class="textColorLavander">"592a3ed3-f5a7-98c2-9ecc-4141730746c9"</span>,
    <span class="textColorRed">{{ t('webhook_operation_number') }}</span>:<span class="textColorLavander">35</span>,
    <span class="textColorRed">{{ t('webhook_operation_type') }}</span>: <span class="textColorLavander">"Crypto to crypto (PF)"</span>,
    <span class="textColorRed">{{ t('webhook_operation_status') }}</span>: <span class="textColorLavander">"SUCCESSFUL"</span>,
    <span class="textColorRed">{{ t('webhook_from_currency') }}</span>: <span class="textColorLavander">"LTC"</span>,
    <span class="textColorRed">{{ t('webhook_amount') }}</span>: <span class="textColorLavander">0.07</span>,
    <span class="textColorRed">{{ t('webhook_to_currency') }}</span>: <span class="textColorLavander">"LTC"</span>,
    <span class="textColorRed">{{ t('webhook_top_up_fee') }}</span>: <span class="textColorLavander">0.01</span>,
    <span class="textColorRed">{{ t('webhook_credited') }}</span>: <span class="textColorLavander">"0.06993000 LTC"</span>,
    <span class="textColorRed">{{ t('webhook_date') }}</span>: <span class="textColorLavander">"2022-01-01 21:00:00"</span>,
    <span class="textColorRed">{{ t('webhook_payer_name') }}</span>: <span class="textColorLavander">"John Doe"</span>,
    <span class="textColorRed">{{ t('webhook_payer_phone') }}</span>: <span class="textColorLavander">"19026682819"</span>,
    <span class="textColorRed">{{ t('webhook_email') }}</span>: <span class="textColorLavander">"jdoe@gmail.com"</span>,
}  </pre>
            </div>
        </div>
    </div>
</div>

<div class="col-md-6 col-lg-12">
    <a class="webhook-params-details link-default ml-lg-5 text-left cursor-pointer"> See Details
        <i class="fa fa-angle-down" aria-hidden="true"></i> </a>
    <div class="webhookParamsDetailsContainer mt-5" style="display: none; padding: 10px 20px;">
        @include('cabinet.setting.webhook_verify')
        @include('cabinet.setting.webhook_request_field_description')
    </div>
</div>
