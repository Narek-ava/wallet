<?php


namespace App\Http\Controllers\Cabinet;


use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TimezoneEnum;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\WebhookUrlUpdateRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\EmailVerification;
use App\Services\CProfileService;
use App\Services\CUserService;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CUserController extends Controller
{
    public function index()
    {
        // @todo use guard('cUser')?
        $profile = Auth::user()->cProfile;
        return view('cabinet.setting.settings', compact('profile'));
    }

    public function verifyEmail($token, $id, CUserService $cUserService, EmailVerificationService $emailVerificationService)
    {
        if ($emailVerificationService->verify($token)) {
            //  FROM CRATOS-342 till better age it means token was about conformation and valid

            // @todo вынести куда-то? ибо CodeDup
            return redirect()->route('cabinet.wallets.index');
        }

        // @todo использовать всё, что можно, из $emailVerificationService
        $emailVerification = EmailVerification::where('token', $token)->where('id', $id)->firstOrFail();
        $cUser = $emailVerification->cUser;
        if (!$cUser) {
            abort(404);
        }
        $existingUserWithSameEmail = $cUserService->findByEmail($emailVerification->new_email, $cUser->id);
        $errorMessage = $successMessage = '';
        if ($emailVerification->status != 0) {
            $errorMessage = t('ui_email_verification_already_verified');
        } else {
            if ($existingUserWithSameEmail->isEmpty()) {
                DB::transaction(function () use ($cUser, $emailVerification, $emailVerificationService) {
                    $oldEmail = $cUser->email;
                    $cUser->fill(['email' => $emailVerification->new_email])->save();
                    $emailVerificationService->_completeVerified($emailVerification);
                    EmailFacade::sendChangedEmailToOldEmail($cUser, $oldEmail);
                });
                $successMessage = t('ui_email_verification_success_message');
                \C\c_user_guard()->logout();
                request()->session()->invalidate();
            } else {
                $errorMessage = t('ui_email_verification_email_exists');
            }
        }

        return view('cabinet.thank-you', compact('errorMessage', 'successMessage'));
    }

    public function resendEmailVerification($id, EmailVerificationService $emailVerificationService)
    {
        $cUser = CUser::find($id);
        if ($cUser) {
            $emailVerificationService->emailVerify($cUser);
            $emailVerificationService->setEmailVerificationSentStatusToCache($cUser->cProfile->id);
            ActivityLogFacade::saveLog(LogMessage::C_PROFILE_EMAIL_SEND_CABINET, [],
                LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_EMAIL_SEND_CABINET, null, $cUser->id);
        }
        return redirect()->back();
    }

    public function updateWebhookUrl(WebhookUrlUpdateRequest $request, CProfileService $cProfileService, CProfile $profile)
    {
        $cProfileService->updateWebhookUrl($profile, $request->get('webhook_url'));

        return response()->json([
            'success' => t('webhook_url_change_success'),
            'secretKey' => $profile->getSecretKey()
        ]);
    }

    public function updateTimezone(Request $request)
    {
        $request->validate([
            'timezone' => Rule::in(TimezoneEnum::getAllTimezones()),
        ]);

        $cProfile = getCProfile();
        $cProfile->timezone = $request->timezone;
        $cProfile->save();

        return redirect()->back();
    }


}
