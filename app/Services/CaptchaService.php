<?php

namespace App\Services;

use Germey\Geetest\Geetest;
use Germey\Geetest\GeetestCaptcha;
use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;

class CaptchaService
{
    public function checkCaptcha(Request $request): bool
    {
        if (config('app.env') == 'local') {
            return true;
        }

        if (empty($request->geetest_challenge) || !is_string($request->geetest_challenge)) {
            return false;
        }

        $rules = ['geetest_challenge' => 'geetest'];
        $validator = validator()->make($request->all(), $rules);

        return !$validator->fails();
    }

}
