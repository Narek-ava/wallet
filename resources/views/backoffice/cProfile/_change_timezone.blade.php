<form autocomplete="off" class="form " role="form" method="post"
      action="{{route('backoffice.profile.updateTimezone', ['profileId' => $profile->id])}}">
    {{ csrf_field() }}
    {{ method_field('patch') }}
    <h2 class="mt-5">{{__('Change Timezone')}}</h2>
    <div class="row">
        <div class="form-group col-md-4">
            <label for="timezone">{{__('Timezone')}}</label>
            <select name="timezone" id="timezone" class="form-control grey-rounded-border" style="width: 100%">
                @foreach(\App\Enums\TimezoneEnum::getAllTimezones() as $timezone)
                    <option value="{{$timezone}}"
                            @if($timezone == $profile->timezone) selected @endif
                    >{{$timezone}}</option>
                @endforeach
            </select>
            @error('timezone')
            <div class="error text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3"
            type="submit">{{__('Save')}}
    </button>
</form>
