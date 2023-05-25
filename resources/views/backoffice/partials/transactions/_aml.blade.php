<!-- Modal -->
<div class="modal w-100" id="complianceModal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true"
     style="border-radius: 31px; padding: 17px">
    <div class="modal-dialog w-50 m-auto" role="document">
        <div class="modal-content">
            <form
                action="{{ route('backoffice.transaction.compliance.level.change', $operation->id) }}"
                method="post">
                @csrf

                <div class="modal-header">
                    <h6 class="modal-title"
                        id="exampleModalLabel">{{ t('compliance_request') }}</h6>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="exampleFormControlSelect1"
                                       class="font-weight-bold w-100">{{ t('request') }}</label>
                                <select name="compliance_level"
                                        class="w-75 grey-rounded-border">
                                    <option value="" hidden>{{ t('select') }}</option>
                                    @foreach($nextComplianceLevels as $level => $levelName)
                                        <option
                                            value="{{ $level }}">{{ ($cProfile->account_type == \App\Models\Cabinet\CProfile::TYPE_INDIVIDUAL ? 'Individual ' : 'Corporate ' ) . $levelName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="exampleFormControlSelect2"
                                       class="font-weight-bold">{{ t('current_compliance_level') }}</label>
                                <input type="text" name="current_level"
                                       value="{{ \App\Enums\ComplianceLevel::getName($cProfile->compliance_level) }}"
                                       class="form-control grey-rounded-border"
                                       id="exampleFormControlSelect2" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"
                            class="btn themeBtn mt-2 mb-2 mt-2 round-border float-left">
                        {{ t('request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
