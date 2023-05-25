<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\API\v1\ConfirmPhoneCodeRequest;
use App\Http\Requests\Cabinet\API\v1\ConfirmVerifyEmailCode;
use App\Http\Requests\Cabinet\API\v1\CUserRegisterSmsRequest;
use App\Http\Requests\Cabinet\API\v1\RegisterCUserAPIRequest;
use App\Http\Requests\Cabinet\API\v1\VerifyEmailRequest;
use App\Http\Requests\Cabinet\API\v1\VerifyPhoneRequest;
use App\Http\Resources\Cabinet\API\v1\CUserRegisterResource;
use App\Models\Cabinet\CUser;
use App\Models\Project;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Services\{CProfileService, CUserService, EmailVerificationService, SmsCodeService};
use Illuminate\Support\Str;


class CUserRegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register CUser Controller
    |--------------------------------------------------------------------------
    */

    use AuthenticatesUsers;

    /** @var CProfileService */
    protected $cProfileService;

    /** @var CUserService */
    protected $cUserService;

    public function __construct(
        CProfileService $cProfileService,
        CUserService    $cUserService
    )
    {
        $this->cProfileService = $cProfileService;
        $this->cUserService = $cUserService;
    }


    /**
     * @OA\Post(
     *     path="/api/verify/email/get/code",
     *     summary="Get email verification code",
     *     description="This API call is used to get email verification code.",
     *     tags={"001. Authentication"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     description="Email",
     *                     type="string",
     *                     example="test@example.com",
     *                 ),
     *                 required={"email"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code was sent successfully",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="success",
     *                     description="Success",
     *                     type="bool"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                 "success": true,
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ivalid properties",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                           property="error_already_sent_email",
     *                           type="string",
     *                         ),
     *                     ),
     *                     example={
     *                         "error": {
     *                              "error_already_sent_email":"Code was already sent. If you do not get it make a request to /api/verify/email/resend/code endpoint.",
     *                          },
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function sendEmailVerificationCode(VerifyEmailRequest $request, EmailVerificationService $emailVerificationService, CUserService $cUserService)
    {

        $response = $emailVerificationService->generateEmailConfirmCodeForApi($request->email);

        if (!$response['success']) {
            return response()->json([
                'error' => $response['error']
            ], 422);
        }

        return response()->json([
            'success' => true
        ]);

    }

    /**
     * @OA\Post(
     *     path="/api/verify/email",
     *     summary="Verify email address",
     *     description="This API call is used to verify email address. Max attempt count for every received code is 3. After that code will be expired.",
     *     tags={"001. Authentication"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     description="Email",
     *                     type="string",
     *                     example="test@example.com",
     *                 ),
     *                 @OA\Property(
     *                     property="code",
     *                     description="Code which has been sent to the email.",
     *                     type="string",
     *                     example="123456",
     *                 ),
     *                 required={"email", "code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email has been verified successfully.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="token",
     *                     description="Token to confirm that email was verified. Send it with registration data.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "token": "eyJpdiI6Ik1OZWxHdlZRdEo1ZU0za0JORlQ0alE9PSIsInZhbHVlIjoiVkpPVWpZbnZxRCtmQ0xMOHdnV0h3ZmJ5TFpFUlF4MHBSSTZKVjlDTy9kNlRQbXppaWVMUzFGMllJYmpOVmZjWFdxb1JmMGdWUHI1aXB3WDkwN093SzQ1eExqQmoyM1Rya21GY0l4ckZERjA9IiwibWFjIjoiOWJhZmY4YmE2ZGVhYTk3ODZjODA3YTRiZTMxMTU5ODdlOGE1YzI3NTVmZDEzNDI4OWE3ZTRlYTVlODc3MjkyZCJ9"
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ivalid properties. New code will be sent to email address.",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                           property="error",
     *                           type="object",
     *                           @OA\Property(
     *                               property="code",
     *                               type="string",
     *                              ),
     *                         ),
     *                     ),
     *                     example={
     *                         "error": {
     *                              "code":"Invalid authentication code. Try limit is reached. Make request to /api/verify/email/resend/code endpoint to get new confirmation code.",
     *                          },
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function verifyEmailCode(ConfirmVerifyEmailCode $request, CUserService $CUserService, EmailVerificationService $emailVerificationService)
    {
        $response = $emailVerificationService->verifyConfirmApi($request->email, $request->get('code'));

        if (!$response['success']) {
            return response()->json([
                'error' => [
                    'code' => $response['error'],
                ],
            ], 422);
        }

        $token = Str::random(60);

        $CUserService->putRegisterDataIntoCache($token, [
            'email' => $request->email,
        ]);

        return response()->json([
            'token' => encrypt($token)
        ]);

    }


    /**
     * @OA\Post(
     *     path="/api/verify/phone/get/code",
     *     summary="Get phone verification code",
     *     description="This API call is used to get phone verification code.",
     *     tags={"001. Authentication"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="phone_cc_part",
     *                     description="Phone number country code part. Send country code without (+). For example 1 for USA",
     *                     type="string",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     property="phone_no_part",
     *                     description="Phone number part",
     *                     type="string",
     *                     example="55555555",
     *                 ),
     *                 required={"phone_cc_part", "phone_no_part"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code was sent successfully",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="message",
     *                     description="success",
     *                     type="bool"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                 "success": true,
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ivalid properties",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                           property="phone_no_part",
     *                           type="string",
     *                         ),
     *                     ),
     *                     example={
     *                         "error": {
     *                              "phone_no_part":"Unable to send code to given phone number.",
     *                          },
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function sendPhoneVerificationCode(VerifyPhoneRequest $request, SmsCodeService $smsCodeService, CUserService $cUserService)
    {
        $phone = $request->phone_cc_part . $request->phone_no_part;

        if ($cUserService->getUserByPhone($phone)) {
            return response()->json([
                'error' => [
                    'phone_no_part' => t('ui_phone_already_exists')
                ]
            ], 422);
        }
        $response = $smsCodeService->generateConfirmForApi($phone);
        if (!$response['success']) {
            return response()->json([
                'error' => [
                    'phone_no_part' => $response['error']
                ]
            ], 422);
        }

        return response()->json([
            'success' => true
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/verify/phone",
     *     summary="Verify phone number",
     *     description="This API call is used to verify phone number. Max attempt count for every received code is 3. After that code will be expired.",
     *     tags={"001. Authentication"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="phone_cc_part",
     *                     description="Phone number country code part. Send country code without (+). For example 1 for USA",
     *                     type="string",
     *                     example="1",
     *                 ),
     *                 @OA\Property(
     *                     property="phone_no_part",
     *                     description="Phone number part",
     *                     type="string",
     *                     example="55555555",
     *                 ),
     *                 @OA\Property(
     *                     property="code",
     *                     description="Code was sent to the phone",
     *                     type="string",
     *                     example="123456",
     *                 ),
     *                 required={"phone_cc_part", "phone_no_part", "code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Phone was verified.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="token",
     *                     description="Token to confirm that phone was verified. Send it with registration data.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "token": "eyJpdiI6Ik1OZWxHdlZRdEo1ZU0za0JORlQ0alE9PSIsInZhbHVlIjoiVkpPVWpZbnZxRCtmQ0xMOHdnV0h3ZmJ5TFpFUlF4MHBSSTZKVjlDTy9kNlRQbXppaWVMUzFGMllJYmpOVmZjWFdxb1JmMGdWUHI1aXB3WDkwN093SzQ1eExqQmoyM1Rya21GY0l4ckZERjA9IiwibWFjIjoiOWJhZmY4YmE2ZGVhYTk3ODZjODA3YTRiZTMxMTU5ODdlOGE1YzI3NTVmZDEzNDI4OWE3ZTRlYTVlODc3MjkyZCJ9"
     *              }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ivalid properties.",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                           property="error",
     *                           type="object",
     *                           @OA\Property(
     *                               property="code",
     *                               type="string",
     *                              ),
     *                         ),
     *                     ),
     *                     example={
     *                         "error": {
     *                              "code":"Invalid authentication code. Try limit is reached. Make request to /api/verify/phone/resend/code endpoint to get new confirmation code.",
     *                          },
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function verifyPhoneCode(ConfirmPhoneCodeRequest $request, CUserService $CUserService, SmsCodeService $smsCodeService)
    {
        $phone = $request->phone_cc_part . $request->phone_no_part;

        if ($CUserService->getUserByPhone($phone)) {
            return response()->json([
                'error' => [
                    'phone_no_part' => t('ui_phone_already_exists')
                ]
            ], 422);
        }

        $response = $smsCodeService->verifyConfirmApi($phone, $request->get('code'));
        if (!$response['success']) {
            return response()->json([
                'error' => [
                    'code' => $response['error'],
                ],
            ], 422);
        }


        $token = Str::random(60);

        $CUserService->putRegisterDataIntoCache($token, [
            'phone' => $phone,
        ]);

        return response()->json([
            'token' => encrypt($token)
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/registration",
     *     summary="Register user",
     *     description="This API call is used to register user. First of all you should do API call to /api/verify/email/get/code endpoint, which will provide you code that should be used in /api/verify/email endpoint to get token, that should be used in this endpoint as email_verification_token parameter.
                 After that you should do API call to /api/verify/phone/get/code endpoint, which will provide you code that should be used in /api/verify/phone endpoint to get token, that should be used in this endpoint as phone_verification_token parameter",
     *     tags={"001. Authentication"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="password",
     *                     description="Password",
     *                     type="string",
     *                     example="Secret*1"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_verification_token",
     *                     description="Token from phone verification",
     *                     type="string",
     *                     example="eyJpdiI6Ik1OZWxHdlZRdEo1ZU0za0JORlQ0alE9PSIsInZhbHVlIjoiVkpPVWpZbnZxRCtmQ0xMOHdnV0h3ZmJ5TFpFUlF4MHBSSTZKVjlDTy9kNlRQbXppaWVMUzFGMllJYmpOVmZjWFdxb1JmMGdWUHI1aXB3WDkwN093SzQ1eExqQmoyM1Rya21GY0l4ckZERjA9IiwibWFjIjoiOWJhZmY4YmE2ZGVhYTk3ODZjODA3YTRiZTMxMTU5ODdlOGE1YzI3NTVmZDEzNDI4OWE3ZTRlYTVlODc3MjkyZCJ9",
     *                 ),
     *                 @OA\Property(
     *                     property="email_verification_token",
     *                     description="Token from email verification",
     *                     type="string",
     *                     example="eyJpdiI6Ik1OZWxHdlZRdEo1ZU0za0JORlQ0alE9PSIsInZhbHVlIjoiVkpPVWpZbnZxRCtmQ0xMOHdnV0h3ZmJ5TFpFUlF4MHBSSTZKVjlDTy9kNlRQbXppaWVMUzFGMllJYmpOVmZjWFdxb1JmMGdWUHI1aXB3WDkwN093SzQ1eExqQmoyM1Rya21GY0l4ckZERjA9IiwibWFjIjoiOWJhZmY4YmE2ZGVhYTk3ODZjODA3YTRiZTMxMTU5ODdlOGE1YzI3NTVmZDEzNDI4OWE3ZTRlYTVlODc3MjkyZCJ9",
     *                 ),
     *                 @OA\Property(
     *                     description="Account type. INDIVIDUAL=1; CORPORATE=2",
     *                     property="account_type",
     *                     type="integer",
     *                     example="1",
     *                     enum={"1", "2"}
     *                 ),
     *                 @OA\Property(
     *                     description="User registration refferal token",
     *                     property="ref",
     *                     type="string",
     *                     example="67038f9e-610e-4675-b872-4e3f011814d9",
     *                 ),
     *                 required={"password", "phone_verification_token", "email_verification_token", "account_type"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration completed succesfully.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="email",
     *                     description="Email.",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                     property="phone",
     *                     description="Phone.",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                     property="accessToken",
     *                     description="Auth(access) Token.",
     *                     type="string"
     *                 ),
     *              @OA\Property(
     *                     property="refreshToken",
     *                     description="Refresh Token.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "email": "tes98787t@cratos.com",
     *                  "phone": "1555789555",
     *                  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGcincskNJsdiJ9.eyJhdWQiOiIxIiwianRpIjoiZTBjOGUwMTJhYjhkNGY3ZWE3OGQwYzgyMzVmMTI2ODY2Y2ExNzM4ZGMzNjhkMWI3MTgwNDBmYzMzMDZhNDc2MDI1NTAzNDQ0ZTU1Mzk2MWEiLCJpYXQiOjE2NDMxODg1OTQsIm5iZiI6MTY0MzE4ODU5NCwiZXhwIjoxNjc0NzI0NTk0LCJzdWIiOiJlYmE1ZDE1Zi03NDA1LTQyZTQtYmM0My1kYTg4YTU3ZTlhOTgiLCJzY29wZXMiOltdfQ.yUiiYKodmndPD9VN7pbdYskb_bbEpkjhne-FIHdkYlcZH-Pyj3wIAyz62D_YcucTAyQX5FSJ2iAO5m8TjdrvhnG8ehHwogFyd9R5rHZx79fsoK2x09-Afx5ZOHdvgPeylWXVqy-ZdzDGh82cSslYuuvBfta7qWwJKoupWB6FhH16FRienEjjlsxMr3mBIY3Cyvfk7hChYAmrI9GagCEClWjy81i-fKJMdFArd066uT05-UVHXQq9FIvjYvUvzMT18vMTdWyYaSpVzNBj5Zv-dSqhoDAfSXcNXsbBR_TvL0bAUAlWQV5gOXXsLf1avv5kI6zOhU6D_kHFM7gIIZtaZrtZRxs598TAsa8qlaRkRedudikNvgtExPXI0WaLyO4iXyaq6QRl7OWNk-yeF4gGR-UOkiik4tqTHdYEeuXpX4V4_0jnwXIn63XrSxX9K03klcy6qd5emhi2yCVCnJrDlh2VCul7AT2LXdL1yjTnNug9HmSYLIhk26WX7M8EdvXE9Ol6FADgwr9ey5lBwa1_TpvOWkgvo32oPnwP4lsqMJifqpi0krRpMzVAYrpx9d1vvOTh2vbEiXN3z2XklK1W4kbWiEWZcqnsx3qRUTwULrLJFhWDqH4a3Pa3qkVn1VAu15bgN6u4myU3_Eu_A_Ev48LeJ0xlo7EpScujYQr7wtc",
     *                  "refreshToken": "def502000897362d48a611ef9a0434e9002e681dfe3bf1ff7fbc4c54eadfcf2f20d3659b4cc8f7212242cda1539dbf3fad9e17772a2d12450197fbdec44424b62206786c2d34478dcd06032f5f14c9905972ce8567db375c7ab2363bdb8a222579d84a7726b83f2db39fba1bdf04910339aa88fe2a8eefdf405c7a0032dc0cd7d7f0ddb4e52274cbaf668cefaad1c6c23901e0bf9e7dc321f7900a02f1c3562f3385361a944710a30e4641e6726112ef2c623dcb5e28c0fe13e27f6f637afe242aee38c25c93e1f488bfec1d358a4b7c07cab01450917f6169930ad8ed999aaabdc36f886989c528db0f6d1aef6c0e3e2c85dc337819640aa69ced338053679bd499bced8a0bb893daf0d29432802cb35b59df8376c65d84fb65643d8aa7253f0ce3579de8435097612952d70675a7d835e2db9540d15f91f68f2e8a8a9d7cf30d905c72c88f9c83f21edfa7bfdf40e2e7cf34200bdaa616593b05209d396bdbc1788946bf11d310dc03953cdc2f8cb32583747f2168427288078fb970163eb00c2355d0e0b7"
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=412,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "error": "Invalid token."
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="error",
     *                 description="Fail to generate access token.",
     *                 type="string"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Ivalid properties",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="object",
     *                         description="Error messages",
     *                         @OA\Property(
     *                           property="email",
     *                           type="string",
     *                         ),
     *                     ),
     *                     example={
     *                         "error": {
     *                              "phone_no_part":"Phone number field is required.",
     *                          },
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function create(RegisterCUserAPIRequest $request, CUserService $CUserService)
    {
        $data = $request->all();
        try {
            $phoneVerificationToken = decrypt($request->phone_verification_token);
            $emailVerificationToken = decrypt($request->email_verification_token);
        } catch (\Exception $exception) {
            return response()->json([
                'error' => [
                    'phone_verification_token' => t('invalid_token')
                ]
            ], 422);
        }

        $phoneVerification = $CUserService->getRegisterDataFromCache($phoneVerificationToken);
        $emailVerification = $CUserService->getRegisterDataFromCache($emailVerificationToken);

        if (empty($phoneVerification)) {
            return response()->json([
                'phone_no_part' => 'Not verified phone number.'
            ], 422);
        }
        if (empty($emailVerification)) {
            return response()->json([
                'email' => 'Not verified email.'
            ], 422);
        }

        $phone = $phoneVerification['phone'];

        $email = $emailVerification['email'];

        $exists = CUser::where('phone', $phone)->orWhere('email', $email)->exists();
        if ($exists) {
            return response()->json([
                'error' => [
                    'email' => t('error_duplicate_credentials')
                ]
            ], 422);
        }
        $project = Project::getCurrentProject();

        //
        $registerData = [
            'expires_at' => now()->add(\C\REGISTER_SESSION_DATA_TTL),
            'account_type' => $data['account_type'],
            'email' => $email,
            'phone' => $phone,
            'project_id' => $project->id ?? null,
            'password_encrypted' => bcrypt($data['password'])
        ];

        $cUser = $this->cUserService->createCUser($registerData);
        $cUser->email_verified_at = now();
        $cUser->save();

        $cUser->refresh();


        if(!empty($data['ref'])) {
            $this->cUserService->putRegisterDataIntoCache($email, ['ref' => $data['ref']]);
        }

        $this->cProfileService->createFromCUser($cUser, ['account_type' => $registerData['account_type']]);


        $getTokenDetails = $CUserService->getAccessAndRefreshTokens([
            'email' => $email,
            'password' =>  $data['password'],
            'userAgent' =>  $request->header('User-Agent', 'Incognito'),
        ], CUserService::GRANT_TYPE_PASSWORD);

        if(!$getTokenDetails) {
            return response()->json([
                'error' => t('api_error')
            ], 412);
        }
        $cUser->tokenDetails = $getTokenDetails;

        $CUserService->deleteRegisterDataFromCache($phoneVerificationToken);
        $CUserService->deleteRegisterDataFromCache($emailVerificationToken);

        return response()->json(new CUserRegisterResource($cUser));
    }


}
