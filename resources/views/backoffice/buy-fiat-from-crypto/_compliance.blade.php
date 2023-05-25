@if(!$passCompliance)
    <div class="col-md-5">
        <h2 class="mt-5">Compliance</h2>
        <div class="row pink-border">
            <div class="col-md-7">
                <div class="">
                    <h5 class="d-block ml-4 mt-2">{{ t('compliance') }}</h5>
                    <p class="d-block ml-4 mt-2">
                        @if(config('cratos.compliance.expire_period') - ($operation->created_at->diffInDays(\Carbon\Carbon::now())) > 0)
                            {{ t('verification_required') }}
                        @else
                            {{ t('verification_not_successful') }}
                        @endif
                    </p>
                    <h5 class="d-block mt-3 ml-4 mt-2">{{ t('reason') }}</h5>
                    <p class="d-block ml-4 mt-2">{{ t('overlimit') }}</p>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn themeBtn mt-2 mb-2 ml-4 mt-2 round-border"
                            data-toggle="modal" data-target="#complianceModal">
                        {{ t('request') }}
                    </button>
                    @include('backoffice.partials.transactions._aml')
                </div>
            </div>
            <div class="col-md-4">
                <h5 class="d-block mt-2">{{ t('days_left') }}</h5>
                <p class="d-block text-danger">
                    @if(config('cratos.compliance.expire_period')- ($operation->created_at->diffInDays(\Carbon\Carbon::now())) > 0)
                        {{ config('cratos.compliance.expire_period') - ($operation->created_at->diffInDays(\Carbon\Carbon::now())) }}
                    @else
                        0
                    @endif
                </p>
            </div>
        </div>
    </div>
@endif
