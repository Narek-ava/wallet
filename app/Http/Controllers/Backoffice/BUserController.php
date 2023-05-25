<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\AdminRoles;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TwoFAType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BUserRequest;
use App\Http\Requests\Backoffice\NewPasswordRequest;
use App\Http\Requests\Backoffice\TwoFaDisableRequest;
use App\Models\Backoffice\BUser;
use App\Services\BUserService;
use App\Services\TwoFAService;
use Illuminate\Http\Request;

class BUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $bUsers = BUser::getBUsersByStatus($request->get('status'));
        return view('backoffice.b-users.index', compact('bUsers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('backoffice.b-users.create');
    }

    /**  `
     *
     * Store a newly created resource in storage
     *
     * @param BUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(BUserRequest $request, BUserService $BUserService)
    {
        $BUserService->createUser($request->validated());

        session()->flash('success', t('b_user_successfully_created', ['email' => $request->email]));
        return redirect()->route('b-users.index');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param BUser $BUser
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(BUser $BUser)
    {
        return view('backoffice.b-users.edit', compact('BUser'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BUserRequest $request
     * @param BUser $BUser
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(BUserRequest $request, BUser $BUser)
    {
        $BUser->update($request->validated());

        session()->flash('success', t('b_user_successfully_updated'));
        return redirect()->route('b-users.index');
    }

    /**
     * Disable the specified resource status.
     *
     * @param BUser $BUser
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(BUser $BUser)
    {
        if ($BUser->is_super_admin) {
            session()->flash('error', t('b_user_admin_not_disabled'));
            return redirect()->route('b-users.index');
        }
        $BUser->status = AdminRoles::STATUS_DISABLED;
        $BUser->save();

        session()->flash('success', t('b_user_successfully_deleted'));
        return redirect()->route('b-users.index');
    }

    public function setNewPassword(string $token, BUserService $BUserService)
    {
        $BUser = $BUserService->getBUserByToken($token);
        if (!$BUser) {
            abort(403);
        }

        return view('backoffice.b-users.new_password', compact('token'));
    }

    public function storeNewPassword(string $token, NewPasswordRequest $request, BUserService $BUserService)
    {
        if (!$BUserService->setNewPassword($token, $request->password)) {
            abort(403);
        };

        return view('backoffice.b-users._success');
    }

    public function enableAdmin($adminId)
    {
        $BUser = BUser::find($adminId);
        if ($BUser->is_super_admin) {
            session()->flash('error', t('b_user_admin_not_enable'));
            return redirect()->route('b-users.index');
        }

        $BUser->status = AdminRoles::STATUS_ACTIVE;
        $BUser->save();

        session()->flash('success', t('b_user_successfully_enable'));
        return redirect()->route('b-users.index');
    }

    public function twoFactorAuth(TwoFAService $twoFAService)
    {
        $bUser = \C\b_user();
        $twoFactorAuthData = $twoFAService->generateTwoFactorAuth($bUser);
        return view('backoffice.b-users.two-factor', compact('twoFactorAuthData'));
    }

    public function twoFactorAuthGenerate(Request $request, TwoFAService $twoFAService)
    {
        $bUser = \C\b_user();
        $twoFactorAuthData = $twoFAService->generateTwoFactorAuth($bUser);
        return redirect()->route('b-users.twoFactor')->with(compact('twoFactorAuthData'));
    }

    public function twoFactorAuthDisable(TwoFaDisableRequest $request)
    {
        $bUserId = $request->bUserId;

        $bUser = BUser::find($bUserId);
        /** @var BUser $bUser */

        $bUser->two_fa_type = TwoFAType::NONE;
        $bUser->google2fa_secret = null;
        $bUser->save();

        ActivityLogFacade::saveLog(
            LogMessage::ADMIN_2FA_GOOGLE_WAS_DISABLED,
            [],
            LogResult::RESULT_SUCCESS,
            LogType::TYPE_ADMIN_2FA_GOOGLE_DISABLED,
            $bUser->id
        );

        return response()->json(['success' => true]);
    }

    public function twoFactorAuthConfirm(TwoFAService $twoFAService)
    {
        $confirmReturn = $twoFAService->confirmGoogleTwoFactorAuthAdmin();
        return response()->json($confirmReturn);
    }
}
