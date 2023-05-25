<div class="tab-pane fade @if($currency === \App\Enums\Currency::CURRENCY_USD) show active @endif" id="{{strtolower($currency)}}" role="tabpanel" aria-labelledby="{{strtolower($currency)}}home">
    <p class="textBold" style="text-align:center;margin-top:20px;margin-bottom:0;">{{ t('ui_top_up_sepa') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('ui_withdraw_sepa') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SEPA}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SEPA.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top:20px;margin-bottom:0;">{{ t('ui_top_up_swift') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('ui_withdraw_swift') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_SWIFT}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_SWIFT.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>

    @if(config('cratos.enable_fiat_wallets'))
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('rate_top_up_fiat_wallet_by_wire') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_TOP_UP_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('rate_by_crypto_fiat_wallet') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_CRYPTO_FROM_FIAT_WALLET.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('rate_withdraw_fiat_wallet') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_WITHDRAW_FIAT_BY_WIRE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="textBold" style="text-align:center;margin-top: 20px;margin-bottom:0;">{{ t('top_up_fiat_by_crypto') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_BUY_FIAT_FROM_CRYPTO_WALLET.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
    @endif

    <p class="textBold" style="margin-top:20px;margin-bottom:0;">{{ t('compliance_rates_percents_bank_card') }}</p>
    <p class="textBold" style="text-align:center;margin-bottom:0;">{{ t('ui_incoming') }}</p>
    <div>
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_CARD}}][{{\App\Enums\Commissions::TYPE_INCOMING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_CARD.'.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
{{--    <p class="textBold" style="margin-top:20px;margin-bottom:0;">{{ t('ui_exchange') }}</p>--}}
{{--    <p class="textBold" style="text-align:center;margin-bottom:0;">{{ t('ui_outgoing') }}</p>--}}
    <div style="display: none">
        <div style="width:51px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_FLAT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="fixed_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('fixed_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:100px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_PERCENTAGE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="percent_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('percent_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:85px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MIN) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:88px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_MAX) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="max_commission[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('max_commission.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:78px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::RATE_AMOUNT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="min_amount[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('min_amount.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:151px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_PERCENT) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer_percent[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer_percent.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:125px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_TRANSFER_EUR) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_transfer[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_transfer.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
        <div style="width:101px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MINIMUM_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="refund_minimum_fee[{{ $currency }}][{{\App\Enums\CommissionType::TYPE_EXCHANGE}}][{{\App\Enums\Commissions::TYPE_OUTGOING}}]" value="{{ old('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
            @error('refund_minimum_fee.'. $currency .'.'.\App\Enums\CommissionType::TYPE_EXCHANGE.'.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
