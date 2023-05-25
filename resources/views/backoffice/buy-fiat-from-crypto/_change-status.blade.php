<!-- Modal -->
<div class="modal fade" id="changeOperationStatus" tabindex="-1" role="dialog" aria-labelledby="changeOperationStatusLabel" aria-hidden="true">
    <div class="modal-dialog  w-25" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('backoffice.withdraw.transaction.change.status', $operation->id) }}" method="post">
                    @csrf
                    <div class="form-group">
                        <input type="hidden" value="{{ \App\Enums\OperationStatuses::RETURNED }}" class="returnedStatus"/>
                        <label for="operation-status" class="font-weight-bold">{{ t('change_status') }}</label>
                        <select id="operation-status"
                                class="form-control grey-rounded-border transaction-type w-50"
                                name="status">
                            <option value="" hidden>{{ t('select') }}...</option>
                            @foreach(App\Enums\OperationStatuses::NAMES as $key => $operationStatus)
                                <option value="{{ $key }}" @if($key == $operation->status) selected @endif>{{  t($operationStatus) }}</option>
                            @endforeach
                        </select>

                        <label for="operation-substatus" class="font-weight-bold operation-substatus" @if($operation->status != \App\Enums\OperationStatuses::RETURNED) hidden @endif >{{ t('change_substatus') }}</label>
                        <select id="operation-substatus"
                                class="form-control grey-rounded-border w-50 operation-substatus" @if($operation->status != \App\Enums\OperationStatuses::RETURNED) hidden @endif
                                name="substatus">
                            <option value="" hidden>{{ t('select') }}...</option>
                            <option value=""></option>
                        @foreach(App\Enums\OperationSubStatuses::NAMES as $key => $operationSubstatus)
                                <option value="{{ $key }}" @if($key == $operation->substatus) selected @endif>{{  t($operationSubstatus) }}</option>
                            @endforeach
                        </select>
                        @error('substatus')
                        <div class="error text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="exampleFormControlTextarea1" class="font-weight-bold">{{ t('comment') }}</label>
                        <textarea placeholder=" Add comment" class="form-control" id="exampleFormControlTextarea1" rows="2"
                                  name="comment" style="border: 1px solid grey; border-radius: 10px;"></textarea>
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 round-border float-left"
                                type="submit">{{ t('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
