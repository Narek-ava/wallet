<html>
<head>
    <style>
        @page {
            margin: 150px 10px;
        }
        #header {
            position: fixed;
            left: 0px;
            top: -150px;
            right: 0px;
            height: 150px;
        }
        #footer {
            position: fixed;
            left: 0px;
            bottom: -150px;
            right: 0px;
        }
        .page_break {
            page-break-before: always;
        }
        table {
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
            font-size: 10px;
        }
        .container {
            padding: 20px 50px;
        }
        .main {
            margin: 50px 15px 0px 10px;
        }
        .button-logo {
            left: -28px;
            width: 200px;
            position: absolute;
            bottom: 185px;
            -webkit-transform: rotate(57deg);
            -moz-transform: rotate(57deg);
            -o-transform: rotate(57deg);
            -ms-transform: rotate(57deg);
            transform: rotate(57deg);
        }
        .report-logo {
            left: 50px;
            top: 50px;
            position: absolute;
            width: 150px
        }
        .footer-text {
            position: absolute;
            font-size: 10px;
            bottom: 100px;
            left: 50px;
            z-index: 1000;
        }
        .header-logo {
            width: 220px;
            position: absolute;
            left: 1040px;
            top: -170px;
            -webkit-transform: rotate(57deg);
            -moz-transform: rotate(57deg);
            -o-transform: rotate(57deg);
            -ms-transform: rotate(57deg);
            transform: rotate(57deg);
        }
        .info-border {
            font-size: 10px;
            max-width: 200px;
            border: 1px solid black;
            padding: 5px 5px 10px 5px;
        }
    </style>
<body>
<div id="header">
    @if(config('cratos.history_list_details.images.img-1'))
        <img src="{{ asset(config('cratos.history_list_details.images.img-1')) }}" alt="report-logo"
             class="report-logo">
    @endif
    @if(config('cratos.history_list_details.images.img-2'))
        <img src="{{ asset(config('cratos.history_list_details.images.img-2')) }}" class="header-logo"
             alt="header-logo">
    @endif
</div>
<div id="footer">
    @if(config('cratos.history_list_details.images.img-2'))
        <img class="button-logo" src="{{ asset(config('cratos.history_list_details.images.img-2')) }}" alt="header-logo">
    @endif
    <span class="footer-text">
         <b>{{config('cratos.company_details.name')}},</b>   <br>
    {{ config('cratos.company_details.address') }},<br>
    {{config('cratos.company_details.city')}}, {{ config('cratos.company_details.zip_code') }}, {{config('cratos.company_details.country')}}
    </span>
</div>
<div id="content">

    <div class="container">
        <div class="main">
            <h1 style="text-align: left; font-size: 20px">Operational Report</h1>
            <h2 style="font-size: 12px">Period {{ $from }} – {{ Carbon\Carbon::create($to)->format('d.m.Y') }}</h2>

            <br>
            @if(!$bUser)
                <div class="info-border">
                    @if($profile->account_type == \App\Models\Cabinet\CProfile::TYPE_CORPORATE)
                        <b>{{ $profile->company_name ?? "" }} (Client ID – {{ $profile->profile_id }}),</b>   <br>
                        {{ !empty($profile->getBeneficialOwnersForProfile()) ? implode(', ', $profile->getBeneficialOwnersForProfile()) : '' }}. <br>
                        @if(!empty($profile->legal_address))
                            <span style="max-width: 80px">{{  $profile->legal_address }}</span>
                        @endif

                        @if(!empty($profile->address))
                            {{ $profile->address }}, <br>
                        @endif

                        @if(!empty($profile->zip_code))
                            {{$profile->zip_code}},
                        @endif

                        @if(!empty($profile->city))
                            {{$profile->city}},
                        @endif

                        @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                            @if( $profile->country == $countryKey)
                                {{$country}}
                            @endif
                        @endforeach
                    @else
                        {{  $profile->first_name . ' ' . $profile->last_name }}   {{ ' (Client ID – ' . $profile->profile_id .'),'}}
                        <br>

                        @if(!empty($profile->address))
                            {{ $profile->address }}, <br>
                        @endif

                        @if(!empty($profile->zip_code))
                            {{ $profile->zip_code }},
                        @endif

                        @if(!empty($profile->city))
                            {{ $profile->city }},
                        @endif

                        @foreach(\App\Models\Country::getCountries(false) as $countryKey => $country)
                            @if( $profile->country == $countryKey)
                                {{ $country }}
                            @endif
                        @endforeach
                    @endif
                </div>
            @endif

        </div>

        @if($operations->isNotEmpty())
            <div class="page_break"></div>
            <br>
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Wallet Number</th>
                    <th>Type</th>
                    <th>Received Amount</th>
                    <th>Amount in EUR</th>
                    <th>Currency</th>
                    <th>Exchange Rate</th>
                    <th>Commission</th>
                    <th>Send to Client</th>
                </tr>
                </thead>
                <tbody>
                @foreach($operations as $operation)
                    <tr>
                        <td>{{ $operation->operation_id }}</td>
                        <td>{{ $operation->created_at->timezone($profile->timezone)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            @if(in_array($operation->operation_type, \App\Enums\OperationOperationType::TOP_UP_OPERATIONS))
                                {{ $operation->fromAccount->cryptoAccountDetail->address ?? '-' }}
                            @elseif(in_array($operation->operation_type, \App\Enums\OperationOperationType::WITHDRAW_OPERATIONS))
                                {{ $operation->toAccount->cryptoAccountDetail->address ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $operation->getOperationType()   }}
                        </td>
                        <td> {{ $operation->received_amount }}</td>
                        <td>
                            {{ $operation->amount_in_euro ?? null }}
                        </td>
                        <td>
                            {{ $operation->toAccount->currency }}
                        </td>
                        <td>{{ $operation->exchange_rate ? $operation->exchange_rate  : '-' }}</td>
                        <td>
                            @if(in_array($operation->operation_type, \App\Enums\OperationOperationType::WITHDRAW_OPERATIONS))
                                {{ $operation->withdrawalFeeForReport ?? "0% / 0" }}
                            @elseif($operation->operation_type ==  \App\Enums\OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF)
                                {{ $operation->cryptoToCryptoFeeForReport }}
                            @elseif($operation->operation_type ==  \App\Enums\OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE)
                                {{ $operation->topUpFiatFeeForReport ?? "0% / 0" }}
                            @else
                                0% / 0
                            @endif
                        </td>
                        <td>
                            {{ $operation->credited }}
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            <br>
            <span style="font-size: 10px"> {{ config('cratos.company_details.name') }}, Registry code {{config('cratos.company_details.registry')}}, Registered at {{config('cratos.company_details.city')}}, {{ config('cratos.company_details.address') }}, {{ config('cratos.company_details.zip_code') }}. {{ config('cratos.company_details.name') }} provides a virtual currency services, Start of validity 12.10.2020,
        Issuer of licence: Politsei- ja Piirivalveamet </span>
        @else
            <h2>No Data</h2>
        @endif

    </div>

</div>
</body>
</html>
