<form autocomplete="off" class="form " role="form" method="post"
      action="{{route('backoffice.profile.changeStatus', ['profileId' => $profile->id])}}">
    {{ csrf_field() }}
    <div class="row mb-5">
        <div class="col-md-12">
            <h5 class="mb-4">{{t('ui_bo_user_status_change_section_name')}}</h5>
        </div>
        <div class="d-flex justify-content-start">
            @foreach($profile->getAllowedToChangeStatuses() as $statusKey => $statusName)
                <button
                    class="btn btn-lg btn-primary btn-status-change themeBtnDark mr-3" data-status="{{$statusKey}}" type="button">{{$statusName}}</button>
            @endforeach
        </div>
        <div class="col-md-7 mt-5">
            <div class="d-block mt-4">
                <p>{{t('ui_bo_user_status_change_title')}}
                     </p>
                <p>{{t('ui_bo_user_status_change_text')}}</p>
            </div>
        </div>
        <div class="col-md-5 mt-5">
            <label for="inputEmail" class="d-block">{{t('ui_bo_user_status_change_textarea_label')}}</label>
            <input type="hidden" name="status" value="" id="changed-status">
            <input type="hidden"  value="{{t('ui_bo_change_status_confirm_msg')}}" id="change-status-confirm-msg">
            <textarea required name="status_change_text" class="form-control">{{old('status_change_text')}}</textarea>
            @error('status_change_text')
            <div class="error text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</form>
