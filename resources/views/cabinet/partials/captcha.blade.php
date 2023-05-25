<div class="d-flex">
    <div class="form-group mt-3 captcha-image">
        {!! captcha_img() !!}
    </div>
    <button class="border-none regenerateCaptchaButton" type="button"
            style="background: unset; cursor: pointer"
            title="{{ t('captcha_regenerate_code') }}">
        <img src="{{ config('cratos.urls.theme') }}images/regenerate_icon.png" width="30"
             height="auto" alt="">
    </button>
</div>
<input class="{{ $inputClass ?? '' }}" name="{{ $inputName ?? 'captcha'}}" type="text" required placeholder="{{ $placeholder ?? '' }}">


