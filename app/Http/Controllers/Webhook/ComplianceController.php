<?php

namespace App\Http\Controllers\Webhook;

use App\Facades\SumSubNotificationsFacade;
use App\Http\Controllers\Controller;
use App\Services\{ComplianceService, SumSubService};
use Illuminate\{Http\Request, Support\Facades\Log, Validation\UnauthorizedException};
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class ComplianceController extends Controller
{
    /**
     * Entry point to our ApplicantReviewed webhook handler
     *
     * @param \Illuminate\Http\Request $request
     * @param ComplianceService $complianceService
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleApplicantReviewed(Request $request, ComplianceService $complianceService)
    {
        SumSubNotificationsFacade::write($request->all());
        $complianceProvider = $complianceService->getComplianceProvider();

        //checking if secret key is valid
        if (($signature = $request->headers->get('x-payload-digest')) == null) {
            throw new BadRequestHttpException('Header not set');
        }
        $webhookSecretKey = $complianceProvider->getWebhookSecretKey();
        $known_signature = hash_hmac('sha1', $request->getContent(), $webhookSecretKey);

        if (!hash_equals($known_signature, $signature)) {

            throw new UnauthorizedException('Could not verify request signature ' . $signature);
        }
        $requestData = json_decode($request->getContent(), true);
        SumSubNotificationsFacade::write($requestData);

        $complianceService->validateApplicantReviewedWebhook($requestData);

    }

}
