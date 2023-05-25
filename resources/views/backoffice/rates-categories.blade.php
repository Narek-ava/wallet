@extends('backoffice.layouts.backoffice')
@section('title', t('settings'))

@section('content')

    <div class="row mb-5 pb-5">
        <div class="col-md-12">
            <h2 class="mb-3 large-heading-section">Settings</h2>
            <div class="row">
                <div class="col-lg-5 d-flex justify-content-between">
                    <div class="balance mb-4">
                        Platform is operated by {{ config('cratos.company_details.name') }} Registry code {{config('cratos.company_details.registry')}}, registered at {{config('cratos.company_details.address')}}, {{ config('cratos.company_details.city') }},  {{config('cratos.company_details.zip_code')}}.
                    </div>
                </div>
                @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
            </div>
        </div>
    </div>
    <div class="row mb-5 pb-5">
    <div class="col-md-2">
            <h2 class="mb-3 large-heading-section" style="margin-bottom: 0 !important;margin-top: 14px !important;">
                {{ t('ui_rates_list_header') }}
            </h2>
        </div>
        <div class="col-md-5">
            <a href="{{ route('rates.create') }}">
                <button class="btn btn-lg btn-primary themeBtnDark mr-3 mt-3"
                        type="button">
                    {{ t('ui_rates_create_button')  }}
                </button>
            </a>
        </div>
    </div>
    <div class="row mt-5 pt-5 pb-5">
        <div class="col-md-8">
            <div class="row">
                @foreach($categories as $category)
                    <div class="col-md-6">
                        <div class="common-shadow-theme rates-category-list mb-12">
                            <div class="d-block rates-category-list-item">
                                @if($category->isActive())
                                    @if(! $category->default_for_account_type )
                                        <button type="button" class="close" aria-label="Close"
                                                data-category-id="{{ $category->id }}"
                                        >
                                            <img src="{{ config('cratos.urls.theme') }}images/close.png" alt="">
                                        </button>
                                    @endif
                                    <h3><a href="{{ route('rates.show', $category->id) }}">
                                            {{ $category->title }}
                                        </a></h3>
                                @else
                                    <h3 style="color: silver;">
                                        {{ $category->title }}
                                    </h3>
                                @endif
                                <div class="price-1">
                                    {{ t('ui_rates_created_label') }}
                                    {{ C\time($category->created_at) }}
                                </div>
                                <div class="price-2">
                                    {{ t('ui_rates_updated_label') }}
                                    {{ C\time($category->updated_at) }}
                                </div>
                            </div>
                        </div>
                        <br><br>
                    </div>
                @endforeach
                    {!! $categories->appends(request()->query())->links() !!}
            </div>
        </div>
    </div>
@endsection

