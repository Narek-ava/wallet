@if($sumSubNextLevelName)
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body" id="compliance-websdk-container">
            </div>
        </div>
    </div>
@endif
<script>
    let API = '{{ config('cratos.urls.cabinet.api-v1') }}';
</script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://static.sumsub.com/idensic/static/sns-websdk-builder.js"></script>
<script src="/js/cabinet/compliance.js"></script>

<script>
    window.env = '{{ config('app.env')}}';
    $(document).ready(function () {
        launchWebSdk('{{$sumSubApiUrl}}', '{{$sumSubNextLevelName}}', '{{$token}}', '{{$profile->cUser->email}}', '{{$profile->cUser->phone}}', null, '{{$contextId}}')
    });
</script>
