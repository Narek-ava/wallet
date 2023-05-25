@section('styles')
    <style>
        .hide {
            display: none;
        }
    </style>
@endsection
<div id="{{ $captchaid }}"></div>
<p id="wait-{{ $captchaid }}" class="show">{{ t('loading_captcha') }}</p>
@define use Illuminate\Support\Facades\Config

@section('scripts')
    <script src="/js/tool_gt.js"></script>
    <script>
        var geetest = function(url) {
            var handlerEmbed = function(captchaObj) {

                var action = '{{ $action ?? 'submit' }}';

                if (action == 'submit') {
                    $("#{{ $captchaid }}").closest('form').submit(function(e) {
                        var validate = captchaObj.getValidate();
                        if (!validate) {
                            $('.captcha-fail').show();
                            e.preventDefault();
                        }
                    });
                } else {
                    $("#{{ $captchaid }}").closest('form').click(function(e) {
                        var validate = captchaObj.getValidate();
                        if (!validate) {
                            $('.captcha-fail').show();
                            e.preventDefault();
                        }
                    });
                }

                captchaObj.appendTo("#{{ $captchaid }}");
                captchaObj.onReady(function() {
                    $("#wait-{{ $captchaid}}")[0].className = "hide";
                    $('.captcha-fail').hide();
                });
                captchaObj.onSuccess(function() {
                    $("#wait-{{ $captchaid}}")[0].className = "hide";
                    $('.captcha-fail').hide();
                    var validate = captchaObj.getValidate();
                    $("#{{ $captchaid }}").closest('form').find('input[name=geetest_challenge]').val(validate.geetest_challenge);
                    $("#{{ $captchaid }}").closest('form').find('input[name=geetest_validate]').val(validate.geetest_validate);
                    $("#{{ $captchaid}}").closest('form').find('input[name=geetest_seccode]').val(validate.geetest_seccode);
                });
                if ('{{ $product }}' == 'popup') {
                    captchaObj.appendTo("#{{ $captchaid }}");
                }
            };
            $.ajax({
                url: url + "?t=" + (new Date()).getTime(),
                type: "get",
                dataType: "json",
                success: function(data) {
                    $('.captcha-fail').hide();
                    initGeetest({
                        gt: data.gt,
                        challenge: data.challenge,
                        width: '100%',
                        product: "{{ $product ?? Config::get('geetest.product', 'float') }}",
                        offline: !data.success,
                        new_captcha: data.new_captcha,
                        lang: '{{ Config::get('geetest.lang', 'en') }}',
                        http: '{{ Config::get('geetest.protocol', 'http') }}' + '://'
                    }, handlerEmbed);
                }
            });
        };
        (function() {
            geetest('{{ Config::get('geetest.url', '/geetest') }}');
        })();
    </script>
    @parent
@endsection
