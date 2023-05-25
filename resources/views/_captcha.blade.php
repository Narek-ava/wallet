@if (config('services.recaptcha.no_hide'))
    <div style="border: green 2px solid">
        <h4>recaptcha.sitekey</h4>
        <p><b>{{ config('services.recaptcha.sitekey') }}</b></p>
        <h4>action</h4>
        <p><b>{{ $action }}</b></p>
        <label class="form-check-label" for="recaptcha">
            recaptcha.value
        </label>
        <input class="form-check-input" type="text" name="recaptcha" id="recaptcha">
    </div>
@else
    <input type="hidden" name="recaptcha" id="recaptcha">
@endif


<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.sitekey') }}"></script>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('{{ config('services.recaptcha.sitekey') }}', {action: '{{ $action }}'}).then(function (token) {
            if (token) {
                document.getElementById('recaptcha').value = token;
            }
        });
    });
</script>
