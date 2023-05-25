@if(!$passCompliance && ($currentAdmin->isAllowed([\App\Enums\BUserPermissions::CHANGE_OPERATION_STATUS], $operation->cProfile->cUser->project_id)))
    <div class="col-md-3 ml-2">
        <div class="row pink-border">
            <div class="col-md-7">
                    <h6 class="d-block mt-2">{{ t('compliance') }}</h6>
                    <p class="d-block mt-2">
                        @if(config('cratos.compliance.expire_period') - ($operation->created_at->diffInDays(\Carbon\Carbon::now())) > 0)
                            {{ t('verification_required') }}
                        @else
                            {{ t('verification_not_successful') }}
                        @endif
                    </p>
                    <h6 class="d-block mt-3 mt-2">{{ t('reason') }}</h6>
                    <p class="d-block mt-2">{{ t('overlimit') }}</p>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn themeBtn mt-2 mb-2 mt-2 round-border"
                            data-toggle="modal" data-target="#complianceModal">
                        {{ t('request') }}
                    </button>
                    @include('backoffice.partials.transactions._aml')
            </div>
            <div class="col-md-4">
                <h6 class="d-block mt-2">{{ t('days_left') }}</h6>
                <p class="d-block text-danger">
                    @if(config('cratos.compliance.expire_period') - ($operation->created_at->diffInDays(\Carbon\Carbon::now())) > 0)
                        {{ config('cratos.compliance.expire_period') - ($operation->created_at->diffInDays(\Carbon\Carbon::now())) }}
                    @else
                        0
                    @endif
                </p>
            </div>
        </div>
    </div>
@endif
