@extends('backoffice.layouts.backoffice')

@section('title', t('title_clients_page'))

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">{{ t('title_clients_page') }}</h2>
            <div class="row">
                <div class="col-md-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        {{ t('backoffice_profile_page_header_body') }}
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>


    <div class="row mt-5">
        <h2>{{ t('collected_crypto_fees') }}</h2>
        <select name="project_id" data-url="{{ route('collected.fee') }}"
                class="mb-5 mt-0 ml-3" id="projectId" style="padding-right: 50px;">
            @foreach($activeProjects as $project)
                <option value="{{ $project->id }}" @if($project->id == $selectedProject->id) selected @endif> {{ $project->name }} </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-12">
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
    </div>

    @include('backoffice.collectedCryptoFee.total-collected-fees')

    <div class="row mt-5">
        <div class="col-md-1 textBold text-center">{{ t('collected_crypto_fees_number') }}</div>
        <div class="col-md-2 textBold">{{ t('collected_crypto_fees_wallet_id') }}</div>
        <div class="col-md-2 textBold">{{ t('collected_crypto_fees_client_account_name') }}</div>
        <div class="col-md-2 textBold">{{ t('collected_crypto_fees_amount') }}</div>
        <div class="col-md-1 textBold">{{ t('collected_crypto_fees_currency') }}</div>
        <div class="col-md-2 textBold">{{ t('collected_crypto_fees_is_collected') }}</div>
        <div class="col-md-2 textBold">{{ t('collected_crypto_fees_created_at') }}</div>
        <div class="col-md-12">
            @foreach($collectedCryptoFees as $collectedCryptoFee)
                <div class="row collectedTransactions">
                    <div class="col-md-1 text-center">{{ $loop->index + 1 }}</div>
                    <div class="col-md-2 breakWord">{{ $collectedCryptoFee->wallet_id }}</div>
                    <div class="col-md-2 breakWord">{{ $collectedCryptoFee->clientAccount->name ?? '' }}</div>
                    <div class="col-md-2">{{ $collectedCryptoFee->amount }}</div>
                    <div class="col-md-1">{{ $collectedCryptoFee->currency }}</div>
                    <div
                        class="col-md-2">{{ \App\Enums\CollectedCryptoFee::getName($collectedCryptoFee->is_collected) }}</div>
                    <div class="col-md-2">{{ $collectedCryptoFee->created_at->format('d-m-Y') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    {{ $collectedCryptoFees->appends(request()->query())->links() }}

@endsection

@section('scripts')
    <script src="/js/backoffice/collected-fees.js"></script>
@endsection
