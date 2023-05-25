<div class="col-md-12 pl-0">
    <h2 style="display: inline;margin-right: 25px;">{{ t('bank_details') }}</h2>
    <button class="btn mb-2" style="border-radius: 25px;background-color: #000;color: #fff" data-toggle="modal" id="addBankDetail" data-target="#bankDetail">Add</button>
</div>

<div class="col-md-12">
    <div class="row">
        @if($accounts->count())
            @foreach($accounts as $account)
                @php
                    $address = '';
                    if ($account->account_type == \App\Enums\AccountType::TYPE_WIRE_SWIFT) {
                        $address = $account->wire->swift;
                    } elseif ($account->account_type == \App\Enums\AccountType::TYPE_WIRE_SEPA) {
                        $address = $account->wire->iban;
                    }
                @endphp
                <div class="col-md-6 col-lg-3 pl-0 pr-0 pr-sm-4 d-flex">
                    <div data-account-id="{{ $account->id }}" class="bankDetailBlockBorderInactive bankDetailBlock w-100">
                        <h5 class="textBold breakWord">{{ \App\Enums\AccountType::getName($account->account_type) }} {{ $account->name }}</h5>
                        <div class="breakWord">{{ $address }} <br>
                            {{ $account->wire->account_beneficiary }}, {{ $account->wire->bank_name }}</div>
                        <p class="date-styles"></p>
                    </div>
                </div>
            @endforeach
        @else
            <h6 class="mt-3">{{ t('have_not_bank_template_yet') }}</h6>
        @endif
    </div>
</div>
