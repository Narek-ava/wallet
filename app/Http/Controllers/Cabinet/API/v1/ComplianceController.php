<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Services\ComplianceService;
use App\Services\PaymentFormsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ComplianceController extends Controller
{
    public function saveComplianceRequest(Request $request, ComplianceService $complianceService, PaymentFormsService $paymentFormsService)
    {
         if($request->has('paymentFormAttemptId')) {
            $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId)) ;
            $cProfile = $paymentFormAttempt->cProfile;
            if (!$cProfile) {
                return response()->json([
                    'success' => false,
                ], 404);
            }
            \C\c_user_login($cProfile->cUser);

        } else {
            $cProfile = Auth::user()->cProfile;
        }
        $isVideo = $request->get('type') === 'idCheck.onVideoIdentModeratorJoined';
        if (!$isVideo) {
            $request->validate([
                'payload.levelName' => ['required', 'string'],
                'payload.reviewStatus' => ['required', 'string'],
                'applicantId' => ['required', 'string'],
                'contextId' => ['required', 'string'],
            ]);
        }


        $newComplianceRequest = $complianceService->createComplianceRequest($cProfile, $request);

        return response()->json([
            'success' => $newComplianceRequest,
        ]);
    }

}
