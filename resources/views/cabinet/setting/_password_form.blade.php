<div class="modal fade" id="passwordModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('profile_wallets_password') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="password-update-form" autocomplete="off" class="form " role="form" method="post" action="{{ route('cabinet.settings.updatePassword') }}">
                {{ csrf_field() }}
                {{ method_field('patch') }}

                <div class="modal-body">
                    <div class="errors-alert alert alert-danger alert-dismissible fade show" style="display: none" role="alert">
                        <div class="errors"></div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="old_password">{{__('Current password')}}</label>
                            <input id="old_password" disabled type="password" name="old_password" class="form-control disabled_el" required>
                            @error('old_password')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="password">{{__('New password')}}</label>
                            <input id="password" disabled type="password" name="password" class="form-control disabled_el" required>
                            @error('password')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="form-group col-md-3">
                            <label for="password_confirmation">{{__('Confirm password')}}</label>
                            <input id="password_confirmation" disabled type="password" name="password_confirmation" class="form-control disabled_el" required>
                            @error('password_confirmation')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3 disabled_el loader" disabled type="submit">{{__('Save')}}
                    </button>

                    <button class="btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0 change_btn" type="submit">{{__('Change')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
