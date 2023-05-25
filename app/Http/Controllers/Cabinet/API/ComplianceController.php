<?php

namespace App\Http\Controllers\Cabinet\API;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Http\Controllers\Controller;
use App\Models\Cabinet\CProfile;
use App\Services\ActivityLogService;
use App\Services\ComplianceService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ComplianceController extends Controller
{
    public function updateComplianceLevelWithToken(string $token, ComplianceService $complianceService, ActivityLogService $activityLogService)
    {
        $data = $complianceService->getTokenFromCache($token);

        if (empty($data)) {
            throw new NotFoundHttpException();
        }

        /* @var CProfile $profile*/
        $profile = CProfile::findOrFail($data['profile_id']);
        $complianceProvider = $complianceService->getComplianceProvider($profile->cUser->project);

        $sumSubApiUrl = $complianceProvider->getApiUrl();
        $retryComplianceRequest = $profile->retryComplianceRequest();
        $lastRequestIfDeclined = $profile->lastRequestIfDeclined();
        $sumSubNextLevelName = $complianceProvider->getNextLevelName($profile->account_type, $profile->compliance_level, $retryComplianceRequest, $lastRequestIfDeclined);
        $token = $complianceProvider->getToken($profile->id, $sumSubNextLevelName);
        $nextLevelButtons = $complianceProvider->getNextLevelButtons($profile->compliance_level, $retryComplianceRequest, $lastRequestIfDeclined);
        $activityLogService->setAction(LogMessage::C_USER_COMPLIANCE_PAGE_INIT)
            ->setReplacements(['cProfileId' => $profile->id, 'name' => $profile->getFullName()])
            ->setResultType(LogResult::RESULT_NEUTRAL);
        if ($retryComplianceRequest) {
            $activityLogService->setContextId($retryComplianceRequest->context_id);
        }
        $activityLogService->setType(LogType::TYPE_C_PROFILE_COMPLIANCE_INIT)
            ->log();
        $contextId = $activityLogService->getContextId();


        return view('api-index', compact(
            'profile', 'lastRequestIfDeclined', 'retryComplianceRequest', 'contextId', 'nextLevelButtons',
            'token', 'sumSubApiUrl', 'sumSubNextLevelName'
        ));
    }

}
