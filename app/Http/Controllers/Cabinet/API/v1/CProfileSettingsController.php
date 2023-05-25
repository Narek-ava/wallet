<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Enums\CompanyOwners;
use App\Enums\CProfileStatuses;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\v1\CProfileUpdateCorporateRequest;
use App\Http\Requests\API\v1\CProfileUpdateRequest;
use App\Http\Resources\Cabinet\API\v1\CProfileResource;
use App\Models\Cabinet\CProfile;
use App\Services\BitGOAPIService;
use App\Services\CProfileService;
use App\Services\WalletService;
use App\Http\Requests\Common\{
    CUserUpdateEmailRequest,
    CUserUpdatePasswordRequest};
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\{Auth, Hash};
use App\Http\Requests\TwoFactorAuthRequest;
use App\Services\TwoFAService;
use Illuminate\Http\Request;

class CProfileSettingsController extends Controller
{

    /**
     * @OA\Patch(
     *     path="/api/profile/update",
     *     summary="Update user profile",
     *     description="This API call is used to update user profile information.",
     *     tags={"007. User profile settings"},
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
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="User phone",
     *                     property="phone",
     *                     type="string",
     *                     example="19065554433",
     *                 ),
     *                 @OA\Property(
     *                     description="User first name",
     *                     property="first_name",
     *                     type="string",
     *                     example="John",
     *                 ),
     *                 @OA\Property(
     *                     description="User last name",
     *                     property="last_name",
     *                     type="string",
     *                     example="Johnson",
     *                 ),
     *                 @OA\Property(
     *                     description="User country code",
     *                     property="country",
     *                     type="string",
     *                     example="us",
     *                 ),
     *                 @OA\Property(
     *                     description="User city",
     *                     property="city",
     *                     type="string",
     *                     example="New York",
     *                 ),
     *                 @OA\Property(
     *                     description="User citizenship",
     *                     property="citizenship",
     *                     type="string",
     *                     example="United States",
     *                 ),
     *                 @OA\Property(
     *                     description="User zip code",
     *                     property="zip_code",
     *                     type="string",
     *                     example="0001",
     *                 ),
     *                 @OA\Property(
     *                     description="User address",
     *                     property="address",
     *                     type="string",
     *                     example="7022 Pierce Ave",
     *                 ),
     *                 @OA\Property(
     *                     description="User birthday",
     *                     property="date_of_birth",
     *                     type="string",
     *                     example="1990-01-01",
     *                 ),
     *                 @OA\Property(
     *                     description="Gender. Male=1; Female=2",
     *                     property="gender",
     *                     type="integer",
     *                     example="1",
     *                 ),
     *                @OA\Property(
     *                     description="ID/Passport number",
     *                     property="passport",
     *                     type="string",
     *                     example="AD0102030405",
     *                 ),
     *                 required={"phone", "first_name", "last_name", "country", "city", "citizenship", "zip_code", "address", "date_of_birth", "gender", "passport"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Incorrect client type.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="errors",
     *                     description="Error message.",
     *                     type="object",
     *                     @OA\Property(
     *                          property="incorrect_user_type",
     *                          description="Incorrect client type.",
     *                          type="string"
     *                 ),
     *                 ),
     *              @OA\Examples(example="result", value={
     *                 "errors" : {"incorrect_user_type": "Incorrect client type."}
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal information update.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="success",
     *                     description="Personal information updated successfully.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "success": "Personal information updated successfully.",
     *               }, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function update(CProfileUpdateRequest $request, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {
        $cUser = Auth::user();
        $profile = $cUser->cProfile;
        if ($profile->account_type !== CProfile::TYPE_INDIVIDUAL) {
            return response()->json([
               "errors" => ['incorrect_user_type' =>  t('ui_personal_user_type_incorrect')]
            ], 422);
        }

        if ($profile->compliance_level >=  \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
            $profileFields = $request->only([ 'first_name', 'last_name', 'country', 'city', 'citizenship',
                'zip_code', 'address', 'date_of_birth']);
        } else {
            $profileFields = $request->only([ 'first_name', 'last_name', 'country']);
        }
        $phone = $request->get('phone');

        if (!in_array($profile->status, CProfileStatuses::ALLOWED_TO_CHANGE_SETTINGS_STATUSES)) {

            if ($profile->compliance_level >= \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
                $cUser->fill(['phone' => $phone]);
            }

            $profile->fill($profileFields);
            $changedAttributes = array_merge($profile->getDirty(), $cUser->getDirty());
            if ($changedAttributes) {
                $fullName = $profile->getFullName();
                EmailFacade::sendSettingUpdateToManager($profile, $changedAttributes, $fullName);
            }
            $successMessage = t('ui_personal_information_update_successfully');
        } else {
            //allow user to update cProfile details if status == STATUS_NEW
            if ($profile->compliance_level >=  \App\Enums\ComplianceLevel::VERIFICATION_LEVEL_1) {
                $cUser->update([
                    'phone' => $phone
                ]);
            }

            $profile->update($profileFields + ['status' => CProfileStatuses::STATUS_ACTIVE]); //Update user status to allow user add compliance
            $walletService->addNewWallet($bitGOAPIService, Currency::getDefaultWalletCoin($cUser->project_id), $profile);
            $successMessage = t('ui_personal_information_update_successfully');
        }

        return response()->json([
            'success' => $successMessage,
        ]);
    }


    /**
     * @OA\Patch(
     *     path="/api/corporate/profile/update",
     *     summary="Update corporate user profile",
     *     description="This API call is used to update corporate user profile information.",
     *     tags={"007. User profile settings"},
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
     *             required=true,
     *             @OA\JsonContent(
     *                  type="object",
     *                 @OA\Property(
     *                     description="Company email",
     *                     property="company_email",
     *                     type="string",
     *                     example="company_email@cratos.com",
     *                 ),
     *                 @OA\Property(
     *                     description="Company name",
     *                     property="company_name",
     *                     type="string",
     *                     example="Company",
     *                 ),
     *                 @OA\Property(
     *                     description="Company phone",
     *                     property="company_phone",
     *                     type="string",
     *                     example="15555555",
     *                 ),
     *                @OA\Property(
     *                     description="Contact phone",
     *                     property="contact_phone",
     *                     type="string",
     *                     example="19065554433",
     *                 ),
     *                @OA\Property(
     *                     description="Registration number",
     *                     property="registration_number",
     *                     type="string",
     *                     example="2345345324",
     *                 ),
     *                @OA\Property(
     *                     description="User country code",
     *                     property="country",
     *                     type="string",
     *                     example="us",
     *                 ),
     *               @OA\Property(
     *                     description="Legal address",
     *                     property="legal_address",
     *                     type="string",
     *                     example="P.O. Box 283 8562 Fusce Rd. Frederick Nebraska",
     *                 ),
     *               @OA\Property(
     *                     description="Trading address",
     *                     property="trading_address",
     *                     type="string",
     *                     example="P.O. Box 283 8562 Fusce Rd. Frederick Nebraska",
     *                 ),
     *              @OA\Property(
     *                     description="Contact email",
     *                     property="contact_email",
     *                     type="string",
     *                     example="contact_email@cratos.com",
     *               ),
     *               @OA\Property(
     *                  property="beneficial_owners",
     *                  type="object",
     *                  example={"Mavrodia Sergey", "Garrett Darby", "Andrea Lindner"},
     *                  description="Beneficial owner's names",
     *                 ),
     *               @OA\Property(
     *                     description="Ceo's full names",
     *                     property="ceos",
     *                     type="object",
     *                     example={"Mavrodia Sergey", "Garrett Darby", "Andrea Lindner"},
     *                 ),
     *               @OA\Property(
     *                     description="Shareholders",
     *                     property="shareholders",
     *                     type="object",
     *                     example={"Samira Piper", "Ivy Hawes", "Arthur Millington"},
     *               ),
     *               @OA\Property(
     *                     description="Interface language country code",
     *                     property="interface_language",
     *                     type="string",
     *                     example="us",
     *                 ),
     *                 @OA\Property(
     *                     description="Registration date",
     *                     property="registration_date",
     *                     type="string",
     *                     example="2020-01-01",
     *                 ),
     *                 required={"company_email", "company_name", "company_phone", "contact_phone", "registration_number", "country", "legal_address", "trading_address", "beneficial_owner", "contact_email", "ceos", "shareholders","interface_language", "registration_date"},
     *             )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Incorrect client type.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="errors",
     *                     description="Error message",
     *                     type="object",
     *                     @OA\Property(
     *                          property="incorrect_user_type",
     *                          description="Incorrect client type.",
     *                          type="string"
     *                 ),
     *                 ),
     *              @OA\Examples(example="result", value={
     *                 "errors" : {"incorrect_user_type": "Incorrect client type."}
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal information updated.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="success",
     *                     description="Thank you. We will review the changes and update your account information.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "success": "Thank you. We will review the changes and update your account information.",
     *               }, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function updateCorporate(CProfileUpdateCorporateRequest $request, CProfileService $CProfileService, BitGOAPIService $bitGOAPIService, WalletService $walletService)
    {
        $cUser = Auth::user();
        $profile = $cUser->cProfile;

        if ($profile->account_type !== CProfile::TYPE_CORPORATE) {
            return response()->json([
                "errors" => ['incorrect_user_type' => t('ui_personal_user_type_incorrect')]
            ], 422);
        }

        $profileFields = $request->only([
            'company_email', 'company_name', 'company_phone', 'registration_number',
            'country', 'legal_address', 'trading_address', 'contact_email', 'interface_language', 'currency_rate', 'registration_date'
        ]);
        $phone = $request->get('contact_phone');

        if (!in_array($profile->status, CProfileStatuses::ALLOWED_TO_CHANGE_SETTINGS_STATUSES)) {
            $profile->fill($profileFields);
            $changedAttributes = $profile->getDirty();

            if ($request->beneficial_owners != $profile->getBeneficialOwnersForProfile()) {
                $changedAttributes['beneficial_owners'] = $request->beneficial_owners;
            }
            if ($request->ceos != $profile->getCeosForProfile()) {
                $changedAttributes['ceos'] = $request->ceos;
            }
            if ($request->shareholders != $profile->getShareholdersForProfile()) {
                $changedAttributes['shareholders'] = $request->shareholders;
            }

            if ($phone !== $cUser->phone) {
                $cUser->phone = $phone;
                $changedAttributes['phone'] = $cUser->phone;
            }
            if ($changedAttributes) {
                $fullName = $profile->getFullName();
                EmailFacade::sendSettingUpdateToManager($profile, $changedAttributes, $fullName);
            }
            $successMessage = t('ui_settings_update_message_to_client');
        } else {
            $profile->fill($profileFields + ['status' => CProfileStatuses::STATUS_ACTIVE]); //Update user status to allow user add compliance
            $changedAttributes = $profile->getDirty();
            $successMessage = t('ui_personal_information_update_successfully');
            if ($phone !== $cUser->phone) {
                $cUser->phone = $phone;
                $changedAttributes['phone'] = $cUser->phone;
            }
            $profile->update();
            $profile->refresh();
            $walletService->addNewWallet($bitGOAPIService, Currency::getDefaultWalletCoin($profile->cUser->project_id), $profile);
            $cUser->save();
        }

        return response()->json([
            'success' => $successMessage,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/password/update",
     *     summary="User password update",
     *     description="This API call is used to update user password.",
     *     tags={"007. User profile settings"},
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
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="New password",
     *                     property="password",
     *                     type="string",
     *                     example="new_secret",
     *                 ),
     *                 @OA\Property(
     *                     description="Password confirmation",
     *                     property="password_confirmation",
     *                     type="string",
     *                     example="new_secret",
     *                 ),
     *                 @OA\Property(
     *                     description="Old password",
     *                     property="old_password",
     *                     type="string",
     *                     example="secret",
     *                 ),
     *                 required={"password", "password_confirmation", "old_password"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Change password.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="success",
     *                     description="Change password.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "success": "Your password has been changed successfully.",
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Password updated errors",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                          property="errors",
     *                          description="Updated errors",
     *                          type="object",
     *                          @OA\Property(
     *                              property="password",
     *                              description="New password",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="password_confirmation",
     *                              description="Confirmation password",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="old_password",
     *                              description="Old password",
     *                              type="string"
     *                          ),
     *                     ),
     *                      example={
     *                         "errors": {"password" : "The password confirmation does not match." , "password_confirmation": "Password must be minimum 8 characters long, contain digits, capital letters and symbols such as $#@!%^&*(),.+-/*", "old_password": "The current password is incorrect."},
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function updatePassword(CUserUpdatePasswordRequest $request)
    {
        $user = Auth::user();
        $user->fill(['password' => Hash::make($request->password)])->save();

        return response()->json([
            'success' => t('ui_password_reset_finish_text'),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/email/update",
     *     summary="User email update",
     *     description="This API call is used to update user email. An email is sent to the user for confirmation.",
     *     tags={"007. User profile settings"},
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
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="New email",
     *                     property="email",
     *                     type="string",
     *                     example="new_email@cratos.com",
     *                 ),
     *                 @OA\Property(
     *                     description="Email confirmation",
     *                     property="email_confirmation",
     *                     type="string",
     *                     example="new_email@cratos.com",
     *                 ),
     *                 @OA\Property(
     *                     description="Old email",
     *                     property="old_email",
     *                     type="string",
     *                     example="email@cratos.com",
     *                 ),
     *                 required={"email", "email_confirmation", "old_email"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sent verification email.",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="success",
     *                     description="Sent verification email.",
     *                     type="string"
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "success": "An email has been sent to you for confirmation.",
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Email updated errors",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                          property="errors",
     *                          description="Updated errors",
     *                          type="object",
     *                          @OA\Property(
     *                              property="email",
     *                              description="New email",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="email_confirmation",
     *                              description="Confirmation email",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="old_email",
     *                              description="Old email",
     *                              type="string"
     *                          ),
     *                     ),
     *                      example={
     *                         "errors": {"email" : "The email confirmation does not match." , "email_confirmation": "The email confirmation must be a valid email address.", "old_email": "Invalid email address."},
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function updateEmail(CUserUpdateEmailRequest $request, EmailVerificationService $emailVerificationService)
    {

        $user = \C\api_c_user();
        $emailVerificationService->generateToChange($user, $request->email);
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_EMAIL_CHANGE_CABINET, ['email' => $user->email, 'newEmail' => $request->email],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_EMAIL_CHANGE_CABINET, null, $user->id);
        $user->token()->revoke();
        return response()->json([
            'success' => t('ui_email_change_confirmation'),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/profile/info",
     *     summary="Get user profile information",
     *     description="This API call is used to get user profile information",
     *     tags={"007. User profile settings"},
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
     *         description="User information sent successfully",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                      @OA\Property(
     *                          property="companyName",
     *                          description="Company name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="companyEmail",
     *                          description="Company email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="companyPhone",
     *                           description="Company phone",
     *                           type="string"
     *                       ),
     *                       @OA\Property(
     *                           property="registrationDate",
     *                           description="Company registration date",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="legalForm",
     *                           description="Company legal form",
     *                           type="string"
     *                       ),
     *                      @OA\Property(
     *                           property="registrationNumber",
     *                           description="Company registration number",
     *                           type="string"
     *                       ),
     *                      @OA\Property(
     *                           property="country",
     *                           description="Company country",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="legalAddress",
     *                          description="Company legal address",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="tradingAddress",
     *                          description="Company trading address",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                            property="ceosNames",
     *                            description="Names of company ceos.",
     *                            type="object"
     *                        ),
     *                      @OA\Property(
     *                           property="beneficialOwners",
     *                           description="Names of company beneficial owners.",
     *                           type="object"
     *                       ),
     *                      @OA\Property(
     *                           property="shareholders",
     *                           description="Names of company shareholders.",
     *                           type="object"
     *                       ),
     *                      @OA\Property(
     *                          property="contactEmail",
     *                          description="Company contact email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="interfaceLanguage",
     *                          description="Application interface language",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          description="Login email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="phone",
     *                          description="Registration phone",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="twoFactorAuthentication",
     *                          description="Two factor authentication type",
     *                          type="string"
     *                      ),
     *                     @OA\Property(
     *                          property="status",
     *                          description="Profile status ( New, Pending Verification, Ready for Compliance, Active, Banned, Suspended)",
     *                          type="string"
     *                      ),
     *                  ),
     *                 @OA\Schema(
     *                       @OA\Property(
     *                          property="firstName",
     *                          description="User first name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="lastName",
     *                          description="User first name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="dateOfBirth",
     *                           description="User date of birth",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          description="User email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="phone",
     *                           description="User phone",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="country",
     *                           description="User country code",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                           property="city",
     *                           description="User city",
     *                           type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="citizenship",
     *                          description="User citizenship",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="zipCode",
     *                          description="User zipCode",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="address",
     *                          description="User address",
     *                          type="string"
     *                      ),
     *                     @OA\Property(
     *                          property="twoFactorAuthentication",
     *                          description="Two factor authentication type",
     *                          type="string"
     *                      ),
     *                     @OA\Property(
     *                          property="status",
     *                          description="Profile status ( New, Pending Verification, Ready for Compliance, Active, Banned, Suspended)",
     *                          type="string"
     *                      ),
     *     )
     *             },
     *             @OA\Examples(example="corporate", value={
     *                  "companyName": "Google",
     *                  "companyEmail": "info@gmail.com",
     *                  "companyPhone": "10000000000",
     *                  "registrationDate": "2020-01-01",
     *                  "legalForm": "Limited Liability Partnership (LLP)",
     *                  "registrationNumber": "10000000000",
     *                  "country": "Bulgaria",
     *                  "industryType": "Construction",
     *                  "legalAddress": "P.O. Box 000 00 1825 Broadcast Drive",
     *                  "tradingAddress": "P.O. Box 000 00 1825 Broadcast Drive",
     *                  "ceosNames": {
     *                      "John Smith J.", "Max Snow K."
     *                  },
     *                  "beneficialOwners": {
     *                      "John Smith", "Max Snow"
     *                  },
     *                  "shareholders": {
     *                      "John Smith S.", "Max Snow M."
     *                  },
     *                  "contactEmail": "info@gmail.com",
     *                  "interfaceLanguage": "us",
     *                  "email": "info@google.com",
     *                  "phone": "19154567889",
     *                  "twoFactorAuthentication": "Google",
     *                  "status": "Active",
     *             }, summary="An result corporate user."),
     *             @OA\Examples(example="individual", value={
     *                 "firstName": "John",
     *                 "lastName": "Smith",
     *                 "dateOfBirth": "1990-01-01",
     *                 "email": "info@google.com",
     *                 "phone": "10000000000",
     *                 "country": "Armenia",
     *                 "city": "Yerevan",
     *                 "citizenship": "Armenia",
     *                 "zipCode": "0000",
     *                 "address": "Street 00",
     *                 "twoFactorAuthentication": "None",
     *                 "status": "Active",
     *             }, summary="An result individual user."),
     *         )
     *     ),
     * )
     */
    public function getProfileInfo()
    {
        return response()->json(new CProfileResource(getCProfile()));
    }

    /**
     * @OA\Get(
     *     path="/api/2fa/google/enable",
     *     summary="Enable Google 2FA authentication",
     *     description="This API call is used to enable Google 2FA authentication. After must call /2fa/google/enable/confirm endpoint for finishin enable 2FA. The user should not have any other authentication connected, if has, at first  disconnect them.",
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
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="errors",
     *                     description="Error message",
     *                     type="object",
     *                     @OA\Property(
     *                          property="error",
     *                          description="Already enabled 2FA authentication.",
     *                          type="string"
     *                 ),
     *                 ),
     *              @OA\Examples(example="result", value={
     *                      "errors" : {"2fa_error": "Already enabled Google two-factor authentication. /Already enabled other type two-factor authentication. At first disable that one, than enable Google."}
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful enable Google 2FA authentication",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="Successfully enable Google 2FA authentication"
     *                     ),
     *                  @OA\Property(
     *                         property="qrImage",
     *                         type="string",
     *                         description="Google 2FA authentication QR image"
     *                     ),
     *                  @OA\Property(
     *                         property="secret",
     *                         type="string",
     *                         description="Google 2FA authentication secret key"
     *                     ),
     *                      example={
     *                         "success": "true", "secret": "secret", "qrImage": "svg"
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function enableGoogleTwoFactorAuthentication(Request $request, TwoFAService $twoFAService)
    {
        $googleTwoFa = $twoFAService->enableGoogleTwoFactorAuth(\C\api_c_user(), true);
        if(isset($googleTwoFa['errors'])) {
            return response()->json($googleTwoFa, 401);
        }
        return response()->json($googleTwoFa);
    }

    /**
     * @OA\Get(
     *     path="/api/2fa/email/enable",
     *     summary="Enable Email 2FA authentication",
     *     description="This API call is used to enable Email 2FA authentication. After must call /2fa/email/enable/confirm endpoint for finishin enable 2FA. The user should not have any other authentication connected, if has, at first  disconnect them.",
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
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                     property="errors",
     *                     description="Error message",
     *                     type="object",
     *                     @OA\Property(
     *                          property="2fa_error",
     *                          description="Already enabled 2FA authentication.",
     *                          type="string"
     *                 ),
     *                 ),
     *              @OA\Examples(example="result", value={
     *                  "errors" : {"2fa_error": "Already enabled Email two-factor authentication. /Already enabled other type two-factor authentication. At first disable that one, than enable Email."}
     *               }, summary="An result object."),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful enable Email 2FA authentication",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="Successfully enable Email 2FA authentication"
     *                     ),
     *                 @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Sent verification email"
     *                     ),
     *                      example={
     *                         "success": "true", "message": "Code was sent to your email."
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function enableEmailTwoFactorAuthentication(Request $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->enableEmailTwoFactorAuth(\C\api_c_user(), true);
        if(isset($response['errors'])) {
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/2fa/google/enable/confirm",
     *     summary="Confirm Google two factor authentication with verification code after enable 2fa",
     *     description="This API call is used when user  enabled Google two-factor authentication and must confirm with a verification code.",
     *      tags={"017. Two-factor authentication"},
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
     *                     description="Verification code. Client get it from Google authenticator application.",
     *                     example="000000"
     *                 ),
     *                 required={"code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                  "success" : "false", "errors" : {"error_2fa_wrong_code": "Invalid code."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                      property="success",
     *                      description="Failed to verify code",
     *                      type="bool"
     *             ),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="error_2fa_wrong_code",
     *                      description="Invalid code",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully confirm",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "success": "true",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Confirm verification code",
     *                 type="bool"
     *             ),
     *         )
     *     )
     * )
     */
    public function confirmGoogleTwoFactorAuthentication(TwoFactorAuthRequest $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->confirmGoogleTwoFactorAuth(\C\api_c_user(), true);
        if (isset($response['errors'])) {
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/2fa/email/enable/confirm",
     *     summary="Confirm Email two factor authentication with verification code after enable 2fa",
     *     description="This API call is used when user  enabled Email two-factor authentication and must confirm with a verification code.",
     *      tags={"017. Two-factor authentication"},
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
     *                     description="Verification code. The code is sent to the client's email.",
     *                     example="000000"
     *                 ),
     *                 required={"code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *              "success": "false", "errors" : {"error_2fa_wrong_code": "Invalid code."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                      property="success",
     *                      description="Failed to verify code",
     *                      type="bool"
     *             ),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="error_2fa_wrong_code",
     *                      description="Invalid code",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully confirm",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "success": "true",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Confirm verification code",
     *                 type="bool"
     *             ),
     *         )
     *     )
     * )
     */
    public function confirmEmailTwoFactorAuthentication(TwoFactorAuthRequest $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->confirmEmailTwoFactorAuth(\C\api_c_user(), true);
        if (isset($response['errors'])) {
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/2fa/google/disable",
     *     summary="Disable Google two factor authentication.",
     *     description="This API call is used when user disable Google two-factor authentication and must confirm with a verification code.",
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
     *                     description="Verification code. Client get it from Google authenticator application.",
     *                     example="000000"
     *                 ),
     *                 required={"code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                "success" : "false", "errors": {"error_2fa_wrong_code": "Invalid code."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                      property="success",
     *                      description="Failed to verify code",
     *                      type="bool"
     *             ),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="error_2fa_wrong_code",
     *                      description="Invalid code",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully disable",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "success": "true",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Disable Google 2fa authentication",
     *                 type="bool"
     *             ),
     *         )
     *     )
     * )
     */
    public function disableGoogleTwoFactorAuthentication(TwoFactorAuthRequest $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->disableGoogleTwoFactorAuth(\C\api_c_user(), true);
        if (isset($response['errors'])) {
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/2fa/email/disable",
     *     summary="Disable Email 2FA authentication",
     *     description="This API call is used to disable Email 2FA authentication. After user must confirm with verification code(code will sent in email), call /2fa/email/disable/confirm endpoint.",
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
     *     @OA\Response(
     *         response="200",
     *         description="Disable Email 2FA authentication",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="Disable Email 2FA authentication"
     *                     ),
     *                      example={
     *                         "success": "true",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function disableEmailTwoFactorAuthentication(Request $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->disableEmailTwoFactorAuth(\C\api_c_user(), true);
        return response()->json($response);
    }

    /**
     * @OA\Post(
     *     path="/api/2fa/email/disable/confirm",
     *     summary="Confirm Email two factor authentication with verification code after disable 2fa",
     *     description="This API call is used when user  disable Email two-factor authentication and must confirm with a verification code.",
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
     *                     type="integer",
     *                     description="Verification code. The code is sent to the client's email.",
     *                     example="000000"
     *                 ),
     *                 required={"code"},
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "success": "false", "errors": {"error_2fa_wrong_code": "Invalid code."}
     *
     *     }, summary="An result object."),
     *             @OA\Property(
     *                      property="success",
     *                      description="Failed to verify code",
     *                      type="bool"
     *             ),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="error_2fa_wrong_code",
     *                      description="Invalid code",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully confirm disable verification code",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *          "success": "true",
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="success",
     *                 description="Disable verification code",
     *                 type="bool"
     *             ),
     *         )
     *     )
     * )
     */
    public function confirmDisableEmailTwoFactorAuthentication(TwoFactorAuthRequest $request, TwoFAService $twoFAService)
    {
        $response = $twoFAService->confirmDisableEmailTwoFactorAuth(\C\api_c_user(), true);
        if(isset($response['errors'])) {
            return response()->json($response, 403);
        }
        return response()->json($response);
    }

    /**
     * @OA\Get(
     *     path="/api/2fa/create",
     *     summary="Create 2FA authentication ID",
     *     description="This API call is used to get the 2FA authentication code ID. After that use, this ID for getting a 2FA token to call /API/2fa/verify endpoint.",
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
     *     @OA\Response(
     *         response=403,
     *         description="Error message",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={
     *                 "errors": {"invalid_status": "Invalid status."}
     *
     *     }, summary="An result object."),
     *            @OA\Property(
     *                 property="errors",
     *                 description="Error message",
     *                 type="object",
     *                 @OA\Property(
     *                      property="invalid_status",
     *                      description="Invalid status",
     *                      type="string"
     *             ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Get 2FA authentication ID",
     *         @OA\JsonContent(
     *            @OA\Examples(example="result", value={ "twoFaRequired": "true", "twoFAId": "93044fd1-6ff1-4945-8532-e1b5c8450848" }, summary="An result object."),
     *            @OA\Property(
     *                 property="twoFaRequired",
     *                 description="Two-factor authentication enabled",
     *                 type="bool"
     *             ),
     *            @OA\Property(
     *                 property="twoFAId",
     *                 description="2FA code ID for generate 2FA token.",
     *                 type="string"
     *            ),
     *         )
     *     ),
     * )
     */
    public function createTwoFaCode(TwoFAService $twoFAService)
    {
        $newCode = $twoFAService->createTwoFACode(\C\api_c_user());
        if (empty($newCode)) {
            return response()->json(["errors" => ['invalid_status' => t('invalid_status')]], 403);
        }
        return response()->json(['twoFaRequired' => true, 'twoFAId' => $newCode->id]);
    }

    /**
     * @OA\Get(
     *     path="/api/available/currencies",
     *     summary="Get available currencies collection",
     *     description="This api call is used to get the collection of the available currencies",
     *     tags={"007. User profile settings"},
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
     *         description="Success.",
     *         @OA\JsonContent(
     *                  @OA\Property(
     *                      property="fiat",
     *                      type="array",
     *                      description="Available fiat.",
     *                      @OA\Items(
     *                          type="string",
     *                      ),
     *              ),
     *              @OA\Property(
     *                      property="crypto",
     *                      type="array",
     *                      description="Available crypto.",
     *                      @OA\Items(
     *                          type="string",
     *                      ),
     *              ),
     *             @OA\Examples(example="result", value={
     *                 "fiat": { "USD", "EUR", "GBR"},  "crypto": { "BTC", "LTC", "BCH"},
     *             }, summary="An result object."),
     *         )
     *     ),
     * )
     */
    public function getAvailableCurrency()
    {
        return [
            'fiat' => array_values(Currency::FIAT_CURRENCY_NAMES),
            'crypto' => array_values(Currency::getList()),
        ];
    }

}
