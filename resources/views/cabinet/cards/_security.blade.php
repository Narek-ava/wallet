<!-- Modal -->
<div class="modal fade" id="securityModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content modal-content-centered">
            <div class="modal-header">
                <button type="button" class="close closeSuccessModal"data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="row pl-5 pr-5 w-100">
                <div class="d-flex flex-column" style="width: 90%">
                    <h3 class="modal-title text-left">{{ t('security') }}</h3>
                    <p>{{ t('manage_your_limits') }}</p>
                </div>
            </div>
            <div class="row text-left row text-left pl-5 pr-5">
                <div class="">
                    <form method="post" id="updateSecurityDetails" action="{{ route('wallester.update.card.security', ['id' => $card->id]) }}" class="col-12 p-0">
                        @csrf
                        <div id="security" class="mt-5">
                            <h2 class="wallesterOrderBlocks">{{ t('security') }}</h2>
                            <div class="d-flex">
                                @if($card->card_type == \App\Enums\WallesterCardTypes::TYPE_PLASTIC)
                                    <div class=" activeLink">
                                        <label for="contactless_purchases">{{ t('cards_conditions_contactless_purchases') }}</label>
                                        <select class="mt-3 col-10" name="contactless_purchases" id="contactless_purchases"
                                                style="padding-right: 20px;">
                                            @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                <option value="{{ $value }}" @if($card->contactless_purchases) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('contactless_purchases')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                    <div class="activeLink">
                                        <label for="atm_withdrawals">{{ t('cards_conditions_atm_withdrawals') }}</label>
                                        <select class="mt-3 col-10" name="atm_withdrawals" id="atm_withdrawals"
                                                style="padding-right: 20px;">
                                            @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                                <option value="{{ $value }}"  @if($card->atm_withdrawals) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('atm_withdrawals')
                                    <div class="error text-danger">{{ $message }}</div>
                                    @enderror
                                @endif
                                <div class="activeLink">
                                    <label for="internet_purchases">{{ t('cards_conditions_purchases') }}</label>
                                    <select class="mt-3 col-10" name="internet_purchases" id="internet_purchases"
                                            style="padding-right: 20px;">
                                        @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                            <option value="{{ $value }}" @if($card->internet_purchases) selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('internet_purchases')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                                <div class="activeLink">
                                    <label for="overall_limits_enabled">{{ t('cards_conditions_overall_limits_enabled') }}</label>
                                    <select class="mt-3 col-10" name="overall_limits_enabled" id="overall_limits_enabled"
                                            style="padding-right: 20px;">
                                        @foreach(\App\Models\WallesterAccountDetail::SECURITY_YES_OR_NO as $value => $name)
                                            <option value="{{ $value }}" @if($card->overall_limits_enabled) selected @endif>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('overall_limits_enabled')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="password" class="mt-5">
                            <div class="d-flex justify-start" style="max-height:40px">
                                <h2 class="wallesterOrderBlocks">{{ t('wallester_card_3ds_password') }}</h2>
                                <button id="remind3dsPassword" class="btn btn-lg btn-primary themeBtn register-buttons ml-3" type="button">
                                    {{ t('remind') }}
                                </button>
                            </div>
                            <div class="d-flex mt-5">
                                <p class=" activeLink">
                                    {{ t('wallester_card_3ds_password_input') }}
                                    <input class="wallesterInputs"  id="wallester3dsPasswordInput" name="password" type="password" value="{{ $card->password_3ds }}">
                                </p>
                                <p class="activeLink">
                                    {{ t('wallester_card_3ds_confirm_password_input') }}
                                    <input class="wallesterInputs" name="password_confirmation" id="wallester3dsPasswordInputConfirm" type="password" value="{{ $card->password_3ds }}">
                                </p>
                            </div>
                            @error('password')
                            <div class="error text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <div class="w-100 ml-3 d-flex justify-content-start">
                    <button id="securityModalBtn" class="btn btn-lg btn-primary themeBtn register-buttons" type="button">
                        {{ t('save') }}
                    </button>
                </div>
            </div>

            <form action="{{ route('wallester.remind.3ds.password', ['id' => $card->id]) }}" method="POST" id="getEncrypted3dsPassword">
                @csrf
            </form>
        </div>

    </div>
</div>
