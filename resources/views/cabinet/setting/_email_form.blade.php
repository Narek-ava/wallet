<div class="modal fade" id="emailModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ t('profile_wallets_email') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form id="updateEmail" autocomplete="off" class="form " role="form" method="post" action="{{ route('cabinet.settings.updateEmail') }}">
                {{ csrf_field() }}
                {{ method_field('patch') }}

                <div class="modal-body">
                    <div class="errors-alert alert alert-danger alert-dismissible fade show" style="display: none"
                        role="alert">
                        <div class="errors"></div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{__('Current email')}}</label>
                            <input name="old_email" type="email" class="form-control disabled_el" disabled
                                required>
                            @error('old_email')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{__('New email')}}</label>
                            <input name="email" type="email" class="form-control disabled_el" disabled required>
                            @error('email')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label for="inputEmail">{{__('Confirm new email')}}</label>
                            <input   name="email_confirmation"  type="email" class="form-control disabled_el" disabled required>
                            @error('email_confirmation')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-lg btn-primary themeBtn register-buttons mb-4 mb-md-0 mr-3 disabled_el loader" disabled
                            type="submit">{{__('Save')}}
                    </button>

                    <button class="change_btn btn btn-lg btn-primary themeBtnDark register-buttons mb-4 mb-md-0"
                            type="button">{{__('Change')}}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
