 <form autocomplete="off" class="form " role="form" method="post"
      action="{{route('backoffice.profile.updateEmail', ['profileId' => $profile->id])}}">
    {{ csrf_field() }}
    {{ method_field('patch') }}
    <h2 class="mt-5">{{__('Change Email')}}</h2>
    <div class="row">
        <div class="form-group col-md-4">
            <label for="inputEmail">{{__('Current email')}}</label>
            <input name="old_email" value="{{old('old_email')}}" type="email" id="inputEmail" class="form-control disabled_el" disabled
                   required>
            @error('old_email')
            <div class="error text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label for="inputEmail">{{__('New email')}}</label>
            <input name="email" type="email"  value="{{old('email')}}"  id="inputEmail" class="form-control disabled_el" disabled required>
            @error('email')
            <div class="error text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label for="inputEmail">{{__('Confirm new email')}}</label>
            <input   name="email_confirmation"  value="{{old('email_confirmation')}}"  type="email" id="inputEmail" class="form-control disabled_el" disabled required>
            @error('email_confirmation')
            <div class="error text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
            type="submit">{{__('Save')}}
    </button>
    <button class="change_btn btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0"
            type="button">{{__('Change')}}
    </button>
</form>
