<div>
    <div style="width:140px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INCOMING_FLAT) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}fixed_commission[{{ \App\Enums\Commissions::TYPE_INCOMING }}]" value="{{ old($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
        @error($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }} </p> @enderror
    </div>
    <div style="width:170px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INCOMING_PERCENTAGE) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}percent_commission[{{ \App\Enums\Commissions::TYPE_INCOMING }}]" value="{{ old($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
        @error($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p> @enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INCOMING_MIN) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}min_commission[{{ \App\Enums\Commissions::TYPE_INCOMING }}]" value="{{ old($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
        @error($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}  </p> @enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INCOMING_MAX) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}max_commission[{{ \App\Enums\Commissions::TYPE_INCOMING }}]" value="{{ old($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_INCOMING) }}">
        @error($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_INCOMING)<p class="text-danger">{{ $message }}</p> @enderror
    </div>
</div>
<div>
    <div style="width:140px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::OUTGOING_FLAT) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}fixed_commission[{{ \App\Enums\Commissions::TYPE_OUTGOING }}]" value="{{ old($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
        @error($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:170px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::OUTGOING_PERCENTAGE) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}percent_commission[{{ \App\Enums\Commissions::TYPE_OUTGOING }}]" value="{{ old($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
        @error($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::OUTGOING_MIN) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}min_commission[{{ \App\Enums\Commissions::TYPE_OUTGOING }}]" value="{{ old($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
        @error($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::OUTGOING_MAX) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}max_commission[{{ \App\Enums\Commissions::TYPE_OUTGOING }}]" value="{{ old($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_OUTGOING) }}">
        @error($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_OUTGOING)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    @isset($wallet)
        <div style="width:160px;display: inline-block;vertical-align: top;">
            <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::BLOCKCHAIN_FEE) }}</p>
            <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="blockchain_fee" value="{{ old('blockchain_fee') }}">
            @error('blockchain_fee')<p class="text-danger">{{ $message }}</p>@enderror
        </div>
    @endisset
</div>
<div>
    <div style="width:140px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INTERNAL_FLAT) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}fixed_commission[{{ \App\Enums\Commissions::TYPE_INTERNAL }}]" value="{{ old($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_INTERNAL) }}">
        @error($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_INTERNAL)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:170px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INTERNAL_PERCENTAGE) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}percent_commission[{{ \App\Enums\Commissions::TYPE_INTERNAL }}]" value="{{ old($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_INTERNAL) }}">
        @error($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_INTERNAL)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INTERNAL_MIN) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}min_commission[{{ \App\Enums\Commissions::TYPE_INTERNAL }}]" value="{{ old($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_INTERNAL) }}">
        @error($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_INTERNAL)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::INTERNAL_MAX) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}max_commission[{{ \App\Enums\Commissions::TYPE_INTERNAL }}]" value="{{ old($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_INTERNAL) }}">
        @error($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_INTERNAL)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
</div>
<div>
    <div style="width:140px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_FLAT) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}fixed_commission[{{ \App\Enums\Commissions::TYPE_REFUND }}]" value="{{ old($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_REFUND) }}">
        @error($prefix . 'fixed_commission.'.\App\Enums\Commissions::TYPE_REFUND)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:170px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_PERCENTAGE) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}percent_commission[{{ \App\Enums\Commissions::TYPE_REFUND }}]" value="{{ old($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_REFUND) }}">
        @error($prefix . 'percent_commission.'.\App\Enums\Commissions::TYPE_REFUND)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MIN) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}min_commission[{{ \App\Enums\Commissions::TYPE_REFUND }}]" value="{{ old($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_REFUND) }}">
        @error($prefix . 'min_commission.'.\App\Enums\Commissions::TYPE_REFUND)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
    <div style="width:160px;display: inline-block;vertical-align: top;">
        <p class="activeLink" style="font-size: 13px;">{{ \App\Enums\CommissionFieldNames::getName(\App\Enums\CommissionFieldNames::REFUND_MAX) }}</p>
        <input type="text" style="border: 1px solid #c1c1c1;border-radius: 10px;" name="{{ $prefix }}max_commission[{{ \App\Enums\Commissions::TYPE_REFUND }}]" value="{{ old($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_REFUND) }}">
        @error($prefix . 'max_commission.'.\App\Enums\Commissions::TYPE_REFUND)<p class="text-danger">{{ $message }}</p>@enderror
    </div>
</div>
