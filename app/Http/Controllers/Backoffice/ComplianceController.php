<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ComplianceLevel;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\TwoFAType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Requests\Backoffice\ComplianceImagesDeleteRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\ComplianceRequest;
use App\Models\Operation;
use App\Services\ActivityLogService;
use App\Services\ComplianceService;
use App\Services\CUserService;
use App\Services\EmailService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Profiler\Profile;
use \App\Enums\ComplianceRequest as ComplianceRequestStatus;

class ComplianceController extends Controller
{

    /** @var ComplianceService */
    protected $complianceService;


    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Returns applicant sumsub documents info.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function applicantDocs(string $applicantId)
    {
        $nameWithIds = [];
        $complianceProvider = $this->complianceService->getComplianceProviderByApplicant($applicantId);
        $info = $complianceProvider->getRequiredDocs($applicantId);
        $applicantData = $complianceProvider->getApplicantInfo($applicantId);
        $inspectionId = $applicantData['inspectionId'];
        foreach ($info as $documentName => $documentData) {
            if (!empty($documentData['imageIds'])) {
                $nameWithIds[] = [
                    'name' =>str_replace('_', ' ', ucfirst(strtolower($documentName))),
                    'images' => implode(',', $documentData['imageIds'])
                ];
            }
        }

        if (!$nameWithIds) {
            return response()->json(['docs' => null]);
        }
        return response()->json(['docs' => $nameWithIds, 'inspectionId' => $inspectionId]);
    }

    /**
     * delete Compliance images
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function requestDocumentsDelete(ComplianceImagesDeleteRequest $request)
    {
        $complianceRequest = ComplianceRequest::where('id', $request->complianceRequestId)->firstOrFail();
        $profile = $complianceRequest->cProfile;
         if ($profile->retryComplianceRequest()) {
            return back()->with('warning', t('ui_compliance_documents_retry_already_requested'));
        }
        $documentIds = explode(',', $request->docIds);
        $complianceProvider = $this->complianceService->getComplianceProviderByApplicant($complianceRequest->applicant_id);

        foreach ($documentIds as $id) {
            try {
                $complianceProvider->deleteImage($request->inspectionId, $id);
            } catch (\Exception $exception) {
                logger()->error('complianceProviderDeleteImage', ['error' => $exception->getMessage()]);
            }
        }
        ActivityLogFacade::saveLog(LogMessage::COMPLIANCE_REQUEST_DOCUMENTS_RETRY, ['imageIds' => $request->docIds, 'complianceRequestId' => $complianceRequest->id],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_RETRY, $complianceRequest->context_id, $profile->cUser->id);

        $this->complianceService->createNewRetryRequest($request->requiredDocsMessage, $complianceRequest,false,null, $request->docsMessageRequestDescription);
        // todo verify 7
        EmailFacade::sendUpdatingDocuments($complianceRequest->cProfile->cUser, $complianceRequest->applicant_id);
        return back()->with('success', t('ui_compliance_documents_requested_success'));
    }

    /**
     * Renew successful compliance request date
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function renew(string $profileId, Request $request)
    {
        $minRenewTime = strtotime(config('cratos.sum_sub.additional_time_for_doc_upload'));
        $renewDate = $request->renewDate;
        if (!$renewDate || !isValidDate($renewDate) || strtotime($renewDate) < $minRenewTime) {
            return back()->with('warning', t('ui_compliance_renew_date_is_not_valid'));
        }
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $lastApprovedRequest = $profile->lastApprovedComplianceRequest();
        if (!$lastApprovedRequest) {
            return back()->with('warning', t('ui_user_didnt_have_approved_request'));
        }
        $this->complianceService->renewDate($lastApprovedRequest, $renewDate, $profile->cUser->id);

        return back()->with('success', t('ui_compliance_renew_date_updated', ['renewDate' => $renewDate]));
    }

    public function resetClientPassword(CUserService $CUserService, $id)
    {
        $user = $CUserService->findById($id);
        if ($user) {
            EmailFacade::sendPasswordRecovery($user);
        }
        return redirect()->back()->with('success', t('backoffice_bassford_reset_sended'));
    }

    public function resetClient2FA(CUserService $CUserService, $id)
    {
        $user = $CUserService->findById($id);
        if ($user) {
            $user->update(['two_fa_type' => TwoFAType::NONE, 'google2fa_secret' => null]);
        }
        EmailFacade::sendUnlink2FA($user);
        return redirect()->back()->with('success', '2FA data was reseted!');
    }

    public function requestComplianceLevelChange(Request $request, $operationId)
    {
        $operation = Operation::findOrFail($operationId);
        $cProfile = $operation->cProfile;

        $message = $this->complianceService->createNewRetryRequestN2('', $request, $operation, $cProfile);

        return redirect()->back()->with(['success' => $message]);
    }

    public function cancelComplianceRequest(Request $request)
    {
        $pendingRequest = ComplianceRequest::findOrFail($request->complianceId);
        if ($pendingRequest) {
            $pendingRequest->status = ComplianceRequestStatus::STATUS_CANCELED;
            $pendingRequest->save();
            $replacements['id'] = $pendingRequest->id;
            $replacements['complianceLevel'] = ComplianceLevel::getName($pendingRequest->compliance_level);

            if ($pendingRequest->operation) {
                $message = LogMessage::COMPLIANCE_REQUEST_CANCELED_WITH_OPERATION_NUMBER;
                $replacements['operationNumber'] = $pendingRequest->operation->operation_id;
            }else{
                $message = LogMessage::COMPLIANCE_REQUEST_CANCELED;
            }
            ActivityLogFacade::saveLog($message,
                $replacements,LogResult::RESULT_SUCCESS,
                LogType::TYPE_COMPLIANCE_REQUEST_CANCELED, null,
                $pendingRequest->cProfile->cUser->id
            );
            return redirect()->back()->with('success', t('compliance_request_canceled_success'));
        }
        return redirect()->back()->with('warning', t('user_didnt_have_pending_request'));
    }
}
