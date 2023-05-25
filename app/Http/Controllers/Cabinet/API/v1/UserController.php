<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Cabinet\CUser;
use App\Services\EmailVerificationService;

class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/verify/email",
     *     summary="Send email verification message to user email",
     *     description="This API call is used to send verification message to user email. ",
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
     *         response=200,
     *         description="Email sent successfully",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                  property="success",
     *                  type="string",
     *                  description="The verification message has been sent to the email successfully."
     *              ),
     *             @OA\Examples(example="result", value={
     *                "success": "Email verification message was sent to email successfully."
     *             }, summary="An result object."),
     *         )
     *     ),
     *
     * )
     */
    public function sendEmailVerification(EmailVerificationService $emailVerificationService)
    {
        /* @var CUser $cUser */
        $cUser = auth()->user();

        $emailVerificationService->emailVerify($cUser);

        return response()->json([
            'success' => t('verification_email_send'),
        ]);
    }

}
