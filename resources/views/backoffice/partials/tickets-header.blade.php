<div class="row mb-4 pb-3">
    <div class="col-md-12">
        <h2 class="mb-3 mt-2 large-heading-section">{{ t('ui_tickets') }}</h2>
        <div class="row">
            <div class="col-lg-5 d-block d-md-flex subheader-title">
                <p>{{ t('backoffice_profile_page_header_body') }}</p>
            </div>
            @include('cabinet.partials.notification', ['notify' => getNotification(\Illuminate\Support\Facades\Auth::id()), 'admin' => true])
        </div>
    </div>
</div>

