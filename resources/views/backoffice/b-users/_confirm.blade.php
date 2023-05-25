<div class="modal fade login-popup rounded-0" id="confirmModal{{$key}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <h5 class="modal-title">{{ !$bUser->status ? t('b_user_confirm_enable') : t('b_user_confirm_delete') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <form method="POST" action="{{ !$bUser->status ? route('b-users.enable', $bUser) : route('b-users.destroy', $bUser) }}">
                @if(!$bUser->status)
                    @method('GET')
                @else
                    @method('DELETE')
                @endif
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                {!! !$bUser->status ? t('b_user_confirm_enable_text') : t('b_user_confirm_delete_text') !!}
                            </div>
                            <div class="error-text-list">
                                @foreach($errors->all() as $message)
                                    <p class="error-text">{{ $message }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-lg btn-primary themeBtn register-buttons" type="submit">
                        {{ !$bUser->status ? t('b_user_enable') : t('b_user_delete') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
