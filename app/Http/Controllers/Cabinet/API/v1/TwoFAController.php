<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TwoFactorAuthRequest;
use App\Models\Cabinet\CUser;
use App\Services\TwoFAService;
use Illuminate\Http\Request;

class TwoFAController extends Controller
{
    /** @var TwoFAService */
    protected $twoFAService;

    public function __construct (TwoFAService $twoFAService)
    {
        $this->twoFAService = $twoFAService;
    }

    public function badCode()
    {
        return response()->json([
            'success' => false,
            'error' => t('error_2fa_wrong_code'),
        ]);
    }

    public function twoFASet(CUser $cUser)
    {
        return response()->json([
            'success' => true,
            'two_fa_type' => $cUser->two_fa_type,
        ]);
    }

    public function emailEnable(Request $request)
    {
        $response = $this->twoFAService->enableEmailTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }

    public function emailEnableConfirm(TwoFactorAuthRequest $request)
    {
        $response =  $this->twoFAService->confirmEmailTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }


    public function emailDisable(Request $request)
    {
        $response = $this->twoFAService->disableEmailTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }

    public function emailDisableConfirm(TwoFactorAuthRequest $request)
    {
        $response = $this->twoFAService->confirmDisableEmailTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }


    /** Подтверждение включения Google 2FA */
    public function googleConfirm(TwoFactorAuthRequest $request)
    {
        $response = $this->twoFAService->confirmGoogleTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }

    public function googleDisable(TwoFactorAuthRequest $request)
    {
        $response = $this->twoFAService->disableGoogleTwoFactorAuth(\C\c_user());
        return response()->json($response);
    }

    public function googleRegister(Request $request)
    {
        $googleTwoFa = $this->twoFAService->enableGoogleTwoFactorAuth(\C\c_user());
        return response()->json($googleTwoFa);
    }

    public function twoFaOperationConfirm(TwoFAService $twoFAService, Request $request)
    {
        $verified = $twoFAService->verify($request->get('2fa-confirm-code'), auth()->user(), false, $request->generateAnyway);
        if ($verified) {
            $twoFAService->saveSession();
        }
        return response()->json([
            'isValid' => intval($verified),
        ]);
    }

    public function twoFaOperationInit(TwoFAService $twoFAService, ?bool $generateAnyway = false)
    {
        $cUser = auth()->user();
        /* @var CUser $cUser */
        $twoFAService->generateIfNeeded($cUser, $generateAnyway);
        return response()->json([
            'success' => 1
        ]);
    }
}
