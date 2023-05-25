@extends('cabinet.layouts.deposit-pdf')

@section('content')
    @php
        $profile = $operation->CProfile;
    @endphp
    <table style="width: 100%;border-collapse:separate;border-spacing: 0 1.5em;">
        <tr>
            <td>{{ t('from') }}:
                {!! (new \App\Services\SettingService)->getProjectAddress($profile->cUser->project_id ?? null) ?? '' !!}
            </td>
            <td>{{ t('to') }}:
                <span>
                @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                        {{ $profile->company_name }}
                        <br>
                        {{ t('ui_registry_code') }}: {{ $profile->registration_number ?? t('pdf_info_absent') }}
                        <br>
                        {{ t('ui_registered_at') }}: {{ $profile->legal_address ?? t('pdf_info_absent') }}
                    @else
                        {{ $profile->first_name . ' ' . $profile->last_name . ' - ID ' . $profile->profile_id }}
                        <br>
                        {{ t('address') }}: {{ $profile->address }}
                    @endif
                </span><br>
            </td>
        </tr>
        <tr>
            <td colspan="2"><h2>INVOICE #{{ $profile->profile_id . '-' . $operation->operation_id }}</h2></td>
        </tr>
        <tr>
            <td>{{ t('wire_transfer_account_beneficiary') }}</td>
            <td>{{ $providerAccount->wire->account_beneficiary }}</td>
        </tr>
        <tr>
            <td>{{ t('wire_transfer_beneficiary_address') }}</td>
            <td>{{ $providerAccount->wire->beneficiary_address }}</td>
        </tr>
        <tr>
            <td>{{ t('wire_transfer_eur_swift_bic') }}</td>
            <td>{{ $providerAccount->wire->swift }}</td>
        </tr>
        <tr>
            <td>{{ t('withdraw_wire_bank_name') }}</td>
            <td>{{ $providerAccount->wire->bank_name }}</td>
        </tr>
        <tr>
            <td>{{ t('withdraw_wire_bank_address') }}</td>
            <td>{{ $providerAccount->wire->bank_address }}</td>
        </tr>
        @if($providerAccount->account_type == \App\Enums\AccountType::TYPE_WIRE_SEPA)
            <tr>
                <td>Iban</td>
                <td>{{ $providerAccount->wire->iban }}</td>
            </tr>
        @endif
        @if($providerAccount->account_type == \App\Enums\AccountType::TYPE_WIRE_SWIFT)
            <tr>
                <td>{{ t('ui_cabinet_correspondent_bank') }}</td>
                <td>{{ $providerAccount->wire->correspondent_bank }}</td>
            </tr>
            <tr>
                <td>{{ t('ui_cabinet_correspondent_bank_swift') }}</td>
                <td>{{ $providerAccount->wire->correspondent_bank_swift }}</td>
            </tr>
            <tr>
                <td>{{ t('ui_cabinet_intermediary_bank') }}</td>
                <td>{{ $providerAccount->wire->intermediary_bank }}</td>
            </tr>
            <tr>
                <td>{{ t('ui_cabinet_intermediary_bank_swift') }}</td>
                <td>{{ $providerAccount->wire->intermediary_bank_swift }}</td>
            </tr>
        @endif
        <tr>
            <td>{{ t('ui_cabinet_payment_purpose') }}</td>
            <td>INVOICE #{{ $profile->profile_id . '-' . $operation->operation_id }}</td>
        </tr>
        <tr>
            <td colspan="2">
                {!! (new \App\Services\SettingService)->getProjectAddress($operation->cProfile->cUser->project_id) ?? '' !!}
            </td>
        </tr>
    </table>
@endsection
