<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Enums\CProfileStatuses;
use App\Http\Requests\TokenRequest;
use App\Http\Requests\TwoFactorVerifyRequest;
use App\Http\Resources\Cabinet\API\v1\CUserResource;
use App\Models\Cabinet\CUser;
use App\Services\CUserService;
use App\Services\TwoFAService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
 use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;

class CUserLoginController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Login CUser Controller
    |--------------------------------------------------------------------------
    | This controller handles authenticating CUsers for API
    */

    use AuthenticatesUsers;
    use ConvertsPsrResponses;

    /** @var TwoFAService */
    protected $twoFAService;

    public function __construct(TwoFAService $twoFAService)
    {
        $this->middleware('guest')->except('verifyTwoFA');
        $this->twoFAService = $twoFAService;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     description="This API call is used to get user auth Bearer token and Refresh token. If user enabled 2FA authentication, this endpoint return 2FA code Id, use it for calling /api/2fa/verify endpoint, that endpoint returned 2FA token, and try again login with 2FA token.",
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
     *                     type="string",
     *                     example="test@cratos.com"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="secret"
     *                 ),
     *                 @OA\Property(
     *                     description="Two-factor authentication token. It used for get access token. For get, must call /api/2fa/verify endpoint.",
     *                     property="twoFaToken",
     *                     type="string",
     *                     example="MLJq3uSJEOlCQFJTb4nM5nMPXJeExI9l40zxu7F9WA2aRM2IJxVIeVjMNfhr"
     *                 ),
     *                 required={"email", "password"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={ "errors" : {
     *          "email_password_mismatch": "Incorrect email or password." }
     *
     *     }, summary="An result object."),
     *             @OA\Property(
     *                 property="errros",
     *                 type="object",
     *                 @OA\Property(
     *                      property="email_password_mismatch",
     *                      description="Incorrect email or password.",
     *                      type="string"
     *                ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={ "errors": {
     *          "status_banned": "Your account is banned." }
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="status_banned",
     *                      description="Your account is banned.",
     *                      type="string"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=412,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "errors": { "invalid_token": "Invalid token."}
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="invalid_token",
     *                      description="Fail to generate access token",
     *                      type="string"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "twoFaRequired": "true", "twoFAId": "null", "tokens" : {
     *                  "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMTQ5ODQ3YjBlNGJkNTQ1MDAxZGJlZTAwYTZjMWEwNjZmOWU5NTY3MWQ0YmMwZTk1ZmJmMDY5MWQzYTE3MWQ4MTZhZTIzYjY4NWUzY2I3MDIiLCJpYXQiOjE2NDMwOTk4MzQsIm5iZiI6MTY0MzA5OTgzNCwiZXhwIjoxNjc0NjM1ODM0LCJzdWIiOiI1YjA1ZTgxMC1jZGExLTRhMzYtYjE0Ni1mODA5OGM5NGJkMzYiLCJzY29wZXMiOltdfQ.LoNc40NN5S4EaNraNS8Qy9FeADehW5qOXfuZYDC5LGw1bCjfeI6z5VXUwGpRvef2ktLILMA3mvSgomDuhACOfqTZ-D8Cd2QGyeoa2b2qo_lDyGjxX6QNlMrrBkkevufdsv0OhN08YI8S6E4MZe5IIVJ2iHJy0KxIWeS5h9cnGU3lYohn1C27wmPxHMP5QTkzRT9iZi_ZCOVP6TOW_iTrkQWoa3bgiDgAB7gjGwiRrSvQbDcgJaBKoZqm0m-fO0PZt71ndoNMdk-JAxJFyJaT_yDfLOmWivZJTvSjCjJgswx35Z33C173jwhMveCGvqF_9W8w2pO1kKq3Q2G2fBR2ZpkaSol2w56BT2BHGWiGaIBl5RrcdikNuy0vfyrJt2qRf-L-9meUiOQv1zp6cFnoDDpF88Rp-Rq_sSruJYEVw5TapZMgg70Ea5YIv4GU6HGmBljmtRp8huSZJC0RHJVllQCEOjw4SznmjKuj2mQ-WjXT28Q9c2rV_zbURvDIYCG5Zdp4e9VHw6O1X8fiy_7j6Cv_cfyfZczk-hUvSA6nCsc-Vc2UXYQhl1YFWI8G1GDsduauU7or8byLOHLA25gPM1A2nYPwELeXNyx8LS_uWojMUxan-m8ARNkUg9yZgi5QI5gkD6mu9g_nHbv6pUqqrV6dDclUKX91gG7dxB6WNQ0",
     *                  "refreshToken": "def502000e397f57366a3f0bd17b428c2303f42f63f3ef8722f704b5e750a999a9c00a854b972937fde1ca69c43aec40f9dc225b81d392a32ac92155b374f643a3aabe3db4e4535c536a0bacb5cebf8a2164da8149870778b6b9cbe889b5658520c2c1cdfa4a6223e175da9c45d44b509ea2bfef14ba2a7d18aa683f5d5d241fd96e215e3c94218cef83c771bedb0cdd58e37a6f17fdfc4ed38d085637de54e79ebecd28fa7ee2634c002c4d184cae915f97df9685d4895b3c4efed3de519504c06627ba73eb00b7ad41b24715e5423eca472782d6582bb6ea5a8d135d6c0c73ecee88163eefca146acf23e24ca72a4a3c5227086203bcf682c27cd1bd793000306b338b062945e0b16bf21ff7a21c06e629982ef7d5049fb41276879f0b624b6c12b666c4a0e5dfbfd31ba71cc00e33221fd449d757dc67f0cdf059d002deccd9ead5fc23886c8b8fb063b33eca72123786828c4e8812f5f21fb6aa38cd0959162dedcd908aab3285fdf98aa7f787d6af080f8f9285428b6a14da985d9244ae6b9adc30260c",
     *          }
     *     }, summary="An result object."),
     *           @OA\Property(
     *                 property="twoFaRequired",
     *                 description="Two-factor authentication enabled",
     *                 type="bool"
     *             ),
     *            @OA\Property(
     *                 property="twoFAId",
     *                 description="2FA code ID for generate 2FA token.",
     *                 type="string"
     *            ),
     *             @OA\Property(
     *                 property="tokens",
     *                 description="Tokens",
     *                 type="object",
     *                         @OA\Property(
     *                              property="accessToken",
     *                              description="Bearer(access) auth token",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="refreshToken",
     *                              description="Refresh token to get new access token.",
     *                              type="string"
     *                          ),
     *             ),
     *         )
     *     )
     * )
     */
    public function login(Request $request, CUserService $cUserService) {

        if (! \C\c_user_guard()->attempt(['email' => $request->email, 'password' => $request->password])) {
            $error = ['email_password_mismatch' => t('error_password_mismatch')];
            return response()->json(['errors' => $error], 401);
        }

        $cUser = \C\c_user();
        $status = $cUser->cProfile->status;

        // @todo status is status value in status-list of statuses
        if ($status == CProfileStatuses::STATUS_BANNED) {
            $error = ['status_banned' => t('error_status_banned')];
            return response()->json(['errors' => $error], 403);
        }

        if (!empty($cUser->two_fa_type)) {
            if($request->has('twoFaToken')) {
                if(!$this->twoFAService->verifyToken($request->twoFaToken, $cUser)) {
                    return response()->json(['errors' => ['2fa_wrong_token' => t('error_2fa_wrong_token')]], 403);
                }
            } else {
                \C\c_user_guard()->logout();
                $twoFaCode = $this->twoFAService->createTwoFACode($cUser);
                if (empty($twoFaCode)) {
                    return response()->json(["errors" => ['invalid_status' => t('invalid_status')]], 403);
                }
                $cUser->twoFAId = $twoFaCode->id;
                return response()->json(new CUserResource($cUser));
            }
        }

        $getTokenDetails = $cUserService->getAccessAndRefreshTokens([
            'email' => $request->email,
            'password' =>  $request->password,
            'userAgent' =>  $request->header('User-Agent', 'Incognito'),
        ], CUserService::GRANT_TYPE_PASSWORD);

        if(!$getTokenDetails) {
            return response()->json([
                'errors' => ['invalid_token' => t('api_error')]
            ], 412);
        }
        $cUser->tokenDetails = $getTokenDetails;

        return response()->json(new CUserResource($cUser), 201);
    }

    /**
     * @OA\Post(
     *     path="/api/tokens/update",
     *     summary="Update user access token",
     *     description="This API call is used to generate new auth Bearer(access) token and Refresh Token with Refresh Token",
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
     *                     property="refreshToken",
     *                     type="string",
     *                     example="def502000e397f57366a3f0bd17b428c2303f42f63f3ef8722f704b5e750a999a9c00a854b972937fde1ca69c43aec40f9dc225b81d392a32ac92155b374f643a3aabe3db4e4535c536a0bacb5cebf8a2164da8149870778b6b9cbe889b5658520c2c1cdfa4a6223e175da9c45d44b509ea2bfef14ba2a7d18aa683f5d5d241fd96e215e3c94218cef83c771bedb0cdd58e37a6f17fdfc4ed38d085637de54e79ebecd28fa7ee2634c002c4d184cae915f97df9685d4895b3c4efed3de519504c06627ba73eb00b7ad41b24715e5423eca472782d6582bb6ea5a8d135d6c0c73ecee88163eefca146acf23e24ca72a4a3c5227086203bcf682c27cd1bd793000306b338b062945e0b16bf21ff7a21c06e629982ef7d5049fb41276879f0b624b6c12b666c4a0e5dfbfd31ba71cc00e33221fd449d757dc67f0cdf059d002deccd9ead5fc23886c8b8fb063b33eca72123786828c4e8812f5f21fb6aa38cd0959162dedcd908aab3285fdf98aa7f787d6af080f8f9285428b6a14da985d9244ae6b9adc30260c"
     *                 ),
     *                 required={"refreshToken"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=412,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "errors": {"invalid_token" :  "Invalid token."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="invalid_token",
     *                      description="Fail to generate access token",
     *                      type="string"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMTQ5ODQ3YjBlNGJkNTQ1MDAxZGJlZTAwYTZjMWEwNjZmOWU5NTY3MWQ0YmMwZTk1ZmJmMDY5MWQzYTE3MWQ4MTZhZTIzYjY4NWUzY2I3MDIiLCJpYXQiOjE2NDMwOTk4MzQsIm5iZiI6MTY0MzA5OTgzNCwiZXhwIjoxNjc0NjM1ODM0LCJzdWIiOiI1YjA1ZTgxMC1jZGExLTRhMzYtYjE0Ni1mODA5OGM5NGJkMzYiLCJzY29wZXMiOltdfQ.LoNc40NN5S4EaNraNS8Qy9FeADehW5qOXfuZYDC5LGw1bCjfeI6z5VXUwGpRvef2ktLILMA3mvSgomDuhACOfqTZ-D8Cd2QGyeoa2b2qo_lDyGjxX6QNlMrrBkkevufdsv0OhN08YI8S6E4MZe5IIVJ2iHJy0KxIWeS5h9cnGU3lYohn1C27wmPxHMP5QTkzRT9iZi_ZCOVP6TOW_iTrkQWoa3bgiDgAB7gjGwiRrSvQbDcgJaBKoZqm0m-fO0PZt71ndoNMdk-JAxJFyJaT_yDfLOmWivZJTvSjCjJgswx35Z33C173jwhMveCGvqF_9W8w2pO1kKq3Q2G2fBR2ZpkaSol2w56BT2BHGWiGaIBl5RrcdikNuy0vfyrJt2qRf-L-9meUiOQv1zp6cFnoDDpF88Rp-Rq_sSruJYEVw5TapZMgg70Ea5YIv4GU6HGmBljmtRp8huSZJC0RHJVllQCEOjw4SznmjKuj2mQ-WjXT28Q9c2rV_zbURvDIYCG5Zdp4e9VHw6O1X8fiy_7j6Cv_cfyfZczk-hUvSA6nCsc-Vc2UXYQhl1YFWI8G1GDsduauU7or8byLOHLA25gPM1A2nYPwELeXNyx8LS_uWojMUxan-m8ARNkUg9yZgi5QI5gkD6mu9g_nHbv6pUqqrV6dDclUKX91gG7dxB6WNQ0",
     *          "refreshToken": "def502000e397f57366a3f0bd17b428c2303f42f63f3ef8722f704b5e750a999a9c00a854b972937fde1ca69c43aec40f9dc225b81d392a32ac92155b374f643a3aabe3db4e4535c536a0bacb5cebf8a2164da8149870778b6b9cbe889b5658520c2c1cdfa4a6223e175da9c45d44b509ea2bfef14ba2a7d18aa683f5d5d241fd96e215e3c94218cef83c771bedb0cdd58e37a6f17fdfc4ed38d085637de54e79ebecd28fa7ee2634c002c4d184cae915f97df9685d4895b3c4efed3de519504c06627ba73eb00b7ad41b24715e5423eca472782d6582bb6ea5a8d135d6c0c73ecee88163eefca146acf23e24ca72a4a3c5227086203bcf682c27cd1bd793000306b338b062945e0b16bf21ff7a21c06e629982ef7d5049fb41276879f0b624b6c12b666c4a0e5dfbfd31ba71cc00e33221fd449d757dc67f0cdf059d002deccd9ead5fc23886c8b8fb063b33eca72123786828c4e8812f5f21fb6aa38cd0959162dedcd908aab3285fdf98aa7f787d6af080f8f9285428b6a14da985d9244ae6b9adc30260c",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="accessToken",
     *                 description="Bearer(access) auth token",
     *                 type="string"
     *             ),
     *            @OA\Property(
     *                 property="refreshToken",
     *                 description="Refresh token to get new access token.",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     */
    public function updateTokens(TokenRequest $request, CUserService $cUserService) {

        $getTokenDetails = $cUserService->getAccessAndRefreshTokens([
            'refresh_token' => $request->refreshToken,
            'userAgent' =>  $request->header('User-Agent', 'Incognito'),
        ], CUserService::GRANT_TYPE_REFRESH_TOKEN);

        if(!$getTokenDetails) {
            return response()->json([
                'errors' => ['invalid_token' => t('api_error')]
            ],401);
        }
        $cUser = new CUser();
        $cUser->tokenDetails = $getTokenDetails;

        return response()->json(new CUserResource($cUser));
    }

    public function deleteUserTokens(Request $request, CUserService $cUserService) {
        $request->user()
            ->tokens
            ->each(function ($token, $key) use ($cUserService) {
                $cUserService->revokeAccessAndRefreshTokens($token->id);
            });

        return response()->json([
            'message' => 'Successfully deleted'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/logout",
     *     summary="Logout user",
     *     description="This API call is used to logout user and revoke access token.",
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
     *     @OA\Response(
     *         response="200",
     *         description="Successful logout",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Successfully logged out"
     *                     ),
     *                      example={
     *                         "message": "Successfully logged out"
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function logOutApi(Request $request)
    {
        $request->user()->token()->revoke();
        return [ 'message' => t('logout')];
    }

    /**
     * @OA\Post(
     *     path="/api/2fa/verify",
     *     summary="Verify two factor authentication",
     *     description="This API call is used for verify 2FA code ID( for example after endpoint /api/login , if user has 2FA, user get 2FA code ID or after calling /api/2fa/create endpoint). This endpoint return 2FA token.",
     *     tags={"017. Two-factor authentication"},
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
     *                     property="code",
     *                     type="string",
     *                     description="Verification code. If enabled Google 2FA get it from Google authenticator application, if enabled Email 2FA, is sent to email.",
     *                     example="000000"
     *                 ),
     *                @OA\Property(
     *                     property="twoFaId",
     *                     type="string",
     *                     description="Two-factor authentication code identificator. It is got by calling /api/login or after calling /api/2fa/create endpoint.",
     *                     example="93044fd1-6ff1-4945-8532-e1b5c8450848"
     *                 ),
     *                 required={"code", "twoFaId"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "success": "false", "errors": {"error_2fa_wrong_code" : "Invalid code."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Action failed",
     *                 type="boolean"
     *            ),
     *            @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="error_2fa_wrong_code",
     *                      description="Invalid code",
     *                      type="string"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "success": "false", "errors": {"2fa_code_error" : "Not found 2FA code."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Action failed",
     *                 type="boolean"
     *            ),
     *            @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="2fa_code_error",
     *                      description="Not found 2FA code",
     *                      type="string"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *               "success": "true" , "token": "MLJq3uSJEOlCQFJTb4nM5nMPXJeExI9l40zxu7F9WA2aRM2IJxVIeVjMNfhr",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Seccessfully generate token.",
     *                 type="bool"
     *             ),
     *            @OA\Property(
     *                 property="token",
     *                 description="2FA token.",
     *                 type="string"
     *             ),
     *         )
     *     )
     * )
     */
    public function verifyTwoFA(TwoFactorVerifyRequest $request)
    {
        $response = $this->twoFAService->generateTwoFAToken($request->twoFaId, \C\twoFACode());
        return response()->json($response['response'], $response['status_code']);
    }

}
