<div class="modal fade" id="timezoneInformationModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('ui_timezone_information') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="timezone-form" autocomplete="off" class="form dashboard-forms" role="form" method="post"
                  action="{{ route('cabinet.timezone.update') }}">
                {{ csrf_field() }}
                {{ method_field('patch') }}

                <div class="form-group col-md-12 text-left">
                    <p>
                        <label for="timezone">{{__('Select your timezone')}}</label>
                    </p>
                    <p>
                        <select name="timezone" id="timezone" class="form-control grey-rounded-border" style="width: 100%">
                            @foreach(\App\Enums\TimezoneEnum::getAllTimezones() as $timezone)
                                <option value="{{$timezone}}"
                                        @if($timezone == $profile->timezone) selected @endif
                                >{{$timezone}}</option>
                            @endforeach
                        </select>
                    </p>

                    <p class="error-text mt-3" data-error-target="timezone"></p>
                </div>

                <div class="modal-footer">
                    <button
                        class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3 disabled_el loader"
                        type="submit">{{__('Save')}}
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>

@section('scripts')
    @parent
    <script>
        $('#timezone').select2();
    </script>
@endsection
