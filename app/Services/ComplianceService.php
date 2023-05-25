<?php


namespace App\Services;


use App\DataObjects\SumSubCorporateUserProfileData;
use App\Exceptions\OperationException;
use Carbon\Carbon;
use App\Enums\{ComplianceLevel,
    ComplianceProviders,
    ComplianceRequest as ComplianceRequestEnum,
    CProfileStatuses,
    Currency,
    Gender,
    LogMessage,
    LogResult,
    LogType};
use App\Facades\{ActivityLogFacade, EmailFacade, SumSubNotificationsFacade};
use App\Models\{Cabinet\CProfile, ComplianceRequest, Country, Operation, Project};
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\{Facades\Cache, Facades\Log, Str};


class ComplianceService
{

    const USER_IDENTIFICATION_TOKEN = 'user_identification_token_';

    /** @var EmailService */
    protected $notificationService;
    protected $notificationUserService;
    protected $complianceProvider;

    public function __construct(?Project $project = null)
    {
        $this->notificationService = new NotificationService();
        $this->notificationUserService = new NotificationUserService();
        $this->complianceProvider = $this->getComplianceProvider($project);
    }


    public function createComplianceRequest(CProfile $cProfile, Request $request)
    {
        if (in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES)) {
            $statusNames = ComplianceRequestEnum::SUM_SUB_STATUS_NAMES;
            $status = ComplianceRequestEnum::STATUS_PENDING; //@TODO check this part
            $reviewStatus = $request->payload['reviewStatus'] ?? null;

            if (!$cProfile->hasPendingComplianceRequest()) {
                if ($statusNames[$status] == $reviewStatus) {
                    $retryComplianceRequest = $cProfile->retryComplianceRequest();
                    if ($retryComplianceRequest) {
                        $retryComplianceRequest->status = $status;
                        $retryComplianceRequest->save();
                        $logMessage = LogMessage::C_USER_COMPLIANCE_REQUEST_DOCUMENTS_UPLOADED;
                    } else {
                        $nextLevelCompliance = $cProfile->compliance_level + 1;
                        if (!isset(ComplianceLevel::NAMES[$nextLevelCompliance])) {
                            throw new Exception('Invalid compliance level ' . $nextLevelCompliance);
                        }
                        //creating new compliance request if reviewStatus is pending
                        $this->saveComplianceRequest($cProfile, $nextLevelCompliance, $status, $request->applicantId, $request->contextId);
                        $logMessage = LogMessage::C_USER_COMPLIANCE_REQUEST_SUCCESS;
                    }
                    ActivityLogFacade::saveLog($logMessage,
                        ['cProfileId' => $cProfile->id, 'name' => $cProfile->getFullName()],
                        LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT);
                } elseif ($request->type == 'idCheck.onVideoIdentModeratorJoined' && $cProfile->compliance_level == 2) {
                    $this->saveComplianceRequest($cProfile, 3, $status, $request->applicantId, $request->contextId);
                }
            }
        }

        return false;
    }

    public function saveComplianceRequest(CProfile $cProfile, int $level, int $status, string $applicantId, ?string $contextId = null): ComplianceRequest
    {
        $complianceRequest = new ComplianceRequest([
            'id' => Str::uuid(),
            'c_profile_id' => $cProfile->id,
            'compliance_level' => $level,
            'status' => $status,
            'applicant_id' => $applicantId,
            'context_id' => $contextId ?? ActivityLogFacade::getContextId(),
        ]);

        $complianceRequest->save();
        return $complianceRequest;
    }

    /**
     * Create success compliance request and update users verificationLevel
     * @param CProfile $cProfile
     * @param int $complianceLevel
     * @param string $applicantId
     * @return ComplianceRequest
     */
    public function complianceLevelManualAssign(CProfile $cProfile, int $complianceLevel, string $applicantId): ComplianceRequest
    {
        if (!isset(ComplianceLevel::NAMES[$complianceLevel])) {
            throw new $complianceLevel('Invalid compliance level ' . $complianceLevel);
        }
        $complianceRequest = $this->saveComplianceRequest($cProfile, $complianceLevel, ComplianceRequestEnum::STATUS_APPROVED, $applicantId);
        $cProfile->compliance_level = $complianceRequest->compliance_level;
        if ($cProfile->status == CProfileStatuses::STATUS_READY_FOR_COMPLIANCE) {
            $cProfile->status = CProfileStatuses::STATUS_ACTIVE;
            ActivityLogFacade::saveLog(LogMessage::C_PROFILE_STATUS_CHANGE,
                ['email' => $cProfile->cUser->email, 'oldStatus' => CProfileStatuses::STATUS_READY_FOR_COMPLIANCE, 'newStatus' => CProfileStatuses::STATUS_ACTIVE],
                LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_STATUS_CHANGE);
        }
        $cProfile->save();


        $logMessage = LogMessage::C_USER_COMPLIANCE_LEVEL_MANUAL_CHANGE;
        ActivityLogFacade::saveLog($logMessage,
            ['clientId' => $cProfile->id, 'level' => $complianceLevel],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_LEVEL_MANUAL_CHANGE);
        return $complianceRequest;
    }

    /**
     * @param array $requestData
     * @return bool
     * @throws GuzzleException
     */
    public function validateApplicantReviewedWebhook(array $requestData)
    {
        $complianceRequest = ComplianceRequest::findByApplicantId($requestData['applicantId']);
        $this->complianceProvider =  $this->getComplianceProviderByApplicant($requestData['applicantId']);
        $cProfile = CProfile::query()->findOrFail($requestData['externalUserId']);
        /* @var CProfile $cProfile */
        $cUser = $cProfile->cUser;
        $levelNames = $this->complianceProvider->getAvailableLevelNames($cProfile->account_type);

        $applicantData = $this->complianceProvider->getApplicantInfo($requestData['applicantId']);
        $review = $applicantData['review'] ?? null;
        if (!isset($review['reviewResult'])) {
            SumSubNotificationsFacade::write($requestData, 'ComplianceReviewNotFound');
            return false;
        }

        SumSubNotificationsFacade::write(compact('review'), 'ComplianceApplicantReview');

        $isReviewSuccessful = $review['reviewResult']['reviewAnswer'] == ComplianceRequestEnum::REVIEW_ANSWER_GREEN;
        $isCompleted = $review['reviewStatus'] == 'completed';
        if (empty($review['levelName'])) {
            SumSubNotificationsFacade::write(compact('review'), 'ComplianceLevelMissing');
            return false;
        }
        $receivedLevel = array_search($review['levelName'], $levelNames);
        if ($receivedLevel === false) {
            SumSubNotificationsFacade::write(compact('review', 'levelNames'), 'ComplianceLevelNotFound');
            return false;
        }

        if (!$complianceRequest || ($complianceRequest->compliance_level < $receivedLevel)) {
            SumSubNotificationsFacade::write([
                'receivedLevel' => $receivedLevel,
                'complianceRequest' => $complianceRequest ? $complianceRequest->toArray() : null
            ], 'ComplianceRequestNeedNew');

            $complianceRequest = $this->saveComplianceRequest($cProfile, $receivedLevel, ComplianceRequestEnum::STATUS_PENDING, $requestData['applicantId']);
        } else {
            SumSubNotificationsFacade::write($complianceRequest->toArray(),  'ComplianceRequestExists');
        }

        $complianceRequest->message = !empty($requestData['reviewResult']['moderationComment']) ?
            $requestData['reviewResult']['moderationComment'] :
            $this->complianceProvider->getModerationMessage($requestData['applicantId']);

        $complianceRequestLogResult = LogResult::RESULT_FAILURE;

        if ($isReviewSuccessful) {
            if ($isCompleted) {
                SumSubNotificationsFacade::write($complianceRequest->toArray(), 'ComplianceRequestIsSuccessful');

                if ($complianceRequest->status !== ComplianceRequestEnum::STATUS_APPROVED) {

                    SumSubNotificationsFacade::write([], 'HandleSuccessfulCompliance');
                    $this->handleSuccessfulCompliance($cProfile, $complianceRequest);
                    $complianceRequestLogResult = LogResult::RESULT_SUCCESS;
                } elseif ($cProfile->hasPendingComplianceRequest()) {
                    $pendingComplianceRequest = $cProfile->getPendingComplianceRequest();
                    $pendingComplianceRequest->status = ComplianceRequestEnum::STATUS_APPROVED;
                    $pendingComplianceRequest->save();
                    $complianceRequestLogResult = LogResult::RESULT_SUCCESS;
                    SumSubNotificationsFacade::write([], 'ComplianceUpdatePendingStatus');
                } else {
                    SumSubNotificationsFacade::write([], 'ComplianceSkipSuccessful');
                }
            } else {
                SumSubNotificationsFacade::write([], 'ComplianceNotCompletedYet');
            }
            $this->synchronizeClientData($applicantData, $cProfile);

        } else {
            $allDeclined = false;
            if ($complianceRequest->status == ComplianceRequestEnum::STATUS_APPROVED) {
                EmailFacade::sendAdditionalVerificationRequest($cUser, $complianceRequest->applicant_id);
                SumSubNotificationsFacade::write($complianceRequest->toArray(),  'ComplianceCreateNewRetryRequest');
                $this->createNewRetryRequest($complianceRequest->message, $complianceRequest, false, LogMessage::C_USER_COMPLIANCE_REQUEST_RETRY_SUMSUB);

            } elseif ($complianceRequest->status == ComplianceRequestEnum::STATUS_PENDING) {
                $complianceRequest->status = ComplianceRequestEnum::STATUS_DECLINED;
                $complianceRequest->save();
                SumSubNotificationsFacade::write($complianceRequest->toArray(), 'ComplianceDeclineRequest');
                $allDeclined = $this->checkBlockCompliance($cProfile, $complianceRequest);
            }

            $complianceRequestLogResult = LogResult::RESULT_FAILURE;
            if (!$allDeclined) { //Don't send request fail mail if already user suspended and suspend mail is already sent
                if ($complianceRequest->operation && ($complianceRequest->id == $complianceRequest->operation->compliance_request_id)) {
                    EmailFacade::sendUnsuccessfulConfirmationVerificationFromTheManager($cUser, $complianceRequest->operation);
                } else {
                    EmailFacade::sendUnsuccessfulVerification($cUser, $complianceRequest->message);
                }
            }
            ActivityLogFacade::saveLog(LogMessage::C_USER_COMPLIANCE_REQUEST_FAIL_MAIL,
                ['complianceRequestId' => $complianceRequest->id, 'name' => $cProfile->getFullName()], LogResult::RESULT_SUCCESS,
                LogType::TYPE_COMPLIANCE_FAIL_MAIL, $complianceRequest->context_id, $cUser->id);
        }
        ActivityLogFacade::saveLog(LogMessage::COMPLIANCE_REQUEST_STATUS_CHANGE,
            ['newStatus' => ComplianceRequestEnum::getName($complianceRequest->status)], $complianceRequestLogResult,
            LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_STATUS_CHANGE, $complianceRequest->context_id, $cProfile->cUser->id);

    }

    public function synchronizeClientData(array $applicantData, CProfile $cProfile)
    {
        /* @var CProfileService $cProfileService */
        $cProfileService = resolve(CProfileService::class);

        $cProfileDataArray = $this->getUpdateDataForCProfile($cProfile, $applicantData);
        $beneficialOwners = null;
        $ceo = null;
        $shareholders = null;
        if ($cProfile->account_type == CProfile::TYPE_CORPORATE) {
            if (!empty($applicantData['info']['companyInfo']['beneficiaries'])) {
                $companyOwners = $this->getCompanyOwnersFromSumsub($applicantData['info']['companyInfo']['beneficiaries']);
                $ceo = $companyOwners['director'] ?? null;
                $beneficialOwners = $companyOwners['ubo'] ?? null;
                $shareholders = $companyOwners['shareholder'] ?? null;
            }
        } elseif (isset($applicantData['memberOf'])) {
            foreach ($applicantData['memberOf'] as $companyData) {
                if (isset($companyData['applicantId'])) {
                    $this->updateCompanyOwners($companyData['applicantId']);
                }
            }
        }
        $cProfileService->updateProfile($cProfile, $cProfileDataArray, $beneficialOwners, $ceo, $shareholders);
    }

    public function getAddress(?array $addressInfo)
    {
        if (!empty($addressInfo['formattedAddress'])) {
            return $addressInfo['formattedAddress'];
        }

        $address = '';
        if (!empty($addressInfo['flatNumber'])) {
            $address .= ' Flat - ' . $addressInfo['flatNumber'];
        }
        if (!empty($addressInfo['subStreetEn'])) {
            $address .= ' SubStreet - ' . $addressInfo['subStreetEn'];
        }
        if (!empty($addressInfo['streetEn'])) {
            $address .= ' Street - ' . $addressInfo['streetEn'];
        }
        if (!empty($addressInfo['stateEn'])) {
            $address .= ' State - ' . $addressInfo['stateEn'];
        }
        if (!empty($addressInfo['buildingNumber'])) {
            $address .= ' Building - ' . $addressInfo['buildingNumber'];
        }
        if (!empty($addressInfo['townEn'])) {
            $address .= ' Town - ' . $addressInfo['townEn'];
        }

        return $address;
    }

    public function updateCompanyOwners(string $applicantId)
    {
        $complianceService =  $this->getComplianceProviderByApplicant($applicantId);

        /* @var CProfileService $cProfileService */
        $cProfileService = resolve(CProfileService::class);

        $companyData = $complianceService->getApplicantInfo($applicantId);
        $company = CProfile::find($companyData['externalUserId']);

        if ($company && !empty($companyData['info']['companyInfo']['beneficiaries'])) {
            $companyOwners = $this->getCompanyOwnersFromSumsub($companyData['info']['companyInfo']['beneficiaries']);
            $companyCeos = $companyOwners['director'];
            $companyBeneficialOwners = $companyOwners['ubo'];
            $companyShareholders = $companyOwners['shareholder'];
            $cProfileService->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_BENEFICIAL_OWNER, $companyBeneficialOwners, $company);
            $cProfileService->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_SHAREHOLDERS, $companyShareholders, $company);
            $cProfileService->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_CEO, $companyCeos, $company);
        }
    }

    public function getUpdateDataForCProfile(CProfile $cProfile, array $responseArray): array
    {
        $responseInfoArray = $responseArray['info'];
        if ($cProfile->account_type == CProfile::TYPE_INDIVIDUAL) {
            $updateData = [
                'date_of_birth' => $responseInfoArray['dob'] ?? null,
                'address' => isset($responseInfoArray['addresses']) ? $this->getAddress($responseInfoArray['addresses'][0]) : null,
                'zip_code' => $responseInfoArray['addresses'][0]['postCode'] ?? null,
                'city' => $responseInfoArray['addresses'][0]['townEn'] ?? null
            ];
            if (!empty($responseInfoArray['firstNameEn'])) {
                $updateData['first_name'] = $responseInfoArray['firstNameEn'];
            }
            if (!empty($responseInfoArray['lastNameEn'])) {
                $updateData['last_name'] = $responseInfoArray['lastNameEn'];
            }
            if (!empty($responseInfoArray['country'])) {
                /* @var CountryService $countryService */
                $countryService = resolve(CountryService::class);
                $country = $countryService->getCountry(['code_iso3' => strtolower($responseInfoArray['country'])]);
                $updateData['country'] = isset($country) ? $country->code : $cProfile->country;
                $updateData['citizenship'] = Country::getCountryNameByCode($updateData['country']);
            }

            if (!empty($responseInfoArray['gender'])) {
                $updateData['gender'] = Gender::TYPE_GENDER[$responseInfoArray['gender']] ?? null;
            }

            if (!empty($responseInfoArray['idDocs'])) {
                $updateData['passport'] = $this->getIdDocNumber($responseInfoArray['idDocs']) ?? null;
            }

        } else {
            $companyInfo = $responseInfoArray['companyInfo'];
            $updateData = [
                'registration_number' => $companyInfo['registrationNumber'] ?? null,
                'legal_address' => $companyInfo['legalAddress'] ?? null,
                'trading_address' => $companyInfo['registrationLocation'] ?? null,
                'registration_date' => isset($companyInfo['incorporatedOn']) ? Carbon::createFromFormat('Y-m-d H:i:s', $companyInfo['incorporatedOn'])->format('Y-m-d') : null,
                'company_email' => $companyInfo['email'] ?? null,
                'contact_email' => $companyInfo['email'] ?? null,
                'company_phone' => isset($companyInfo['phone']) ? str_replace([' ', '+'], '', $companyInfo['phone']) : null,
            ];
            if (!empty($companyInfo['companyName'])) {
                $updateData['company_name'] = $companyInfo['companyName'];
            }
        }
        return $updateData;
    }

    public function getCompanyOwners(?array $companyOwnersInfo): array
    {
        $companyOwners = [];
        foreach ($companyOwnersInfo as $beneficialOwner) {
            $firstName = ''; $lastName = '';
            foreach ($beneficialOwner as $key => $value) {
                if (strpos($key, 'surname') !== false) {
                    $lastName = $value['value'];
                }else if (strpos($key, 'name') !== false) {
                    $firstName = $value['value'];
                }
            }
            $companyOwners[] = $lastName . ' ' . $firstName;
        }

        return $companyOwners;
    }

    public function getCompanyOwnersFromSumsub(array $companyOwners): array
    {

        $owners = [];
        foreach ($companyOwners as $companyOwner) {
            if($companyOwner['positions']) {
                $name = $this->complianceProvider->getApplicantName($companyOwner['applicantId']);
                $owners[$companyOwner['positions'][0]] [$companyOwner['applicantId']] = $name;
            }
        }

        return $owners;
    }

    public function handleSuccessfulCompliance(CProfile $profile, ComplianceRequest $complianceRequest)
    {
        $cUser = $profile->cUser;
        $oldComplianceLevel = $profile->compliance_level;
        $profile->compliance_level = $complianceRequest->compliance_level;
        // todo verify 1 $profile->compliance_level = $complianceRequest->compliance_level;
        if ($profile->status == CProfileStatuses::STATUS_READY_FOR_COMPLIANCE) {
            $profile->status = CProfileStatuses::STATUS_ACTIVE;
            ActivityLogFacade::saveLog(LogMessage::C_PROFILE_STATUS_CHANGE,
                ['email' => $profile->cUser->email, 'oldStatus' => CProfileStatuses::STATUS_READY_FOR_COMPLIANCE, 'newStatus' => CProfileStatuses::STATUS_ACTIVE],
                LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_STATUS_CHANGE, $complianceRequest->context_id);
        }
        $profile->save();

        ActivityLogFacade::saveLog(
            $oldComplianceLevel == $profile->compliance_level ? LogMessage::COMPLIANCE_REQUESTED_DOCS_UPLOADED : LogMessage::COMPLIANCE_LEVEL_UP,
            [
                'oldComplianceLevel' => ComplianceLevel::getName($oldComplianceLevel),
                'newComplianceLevel' => ComplianceLevel::getName($profile->compliance_level)
            ], LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_LEVEL_UP, $complianceRequest->context_id, $profile->cUser->id);
        $complianceRequest->status = ComplianceRequestEnum::STATUS_APPROVED;
        $complianceRequest->save();
        if(!empty($cUser->paymentForm) && $complianceRequest->compliance_level == ComplianceLevel::VERIFICATION_LEVEL_1) {
            EmailFacade::sendSuccessVerificationRegistrationConfirmEmail($cUser);
        } else {
            EmailFacade::sendSuccessfulVerification($cUser);
        }

        // todo verify 5 check $complianceRequest->id operation  (compliance_request_id)
        if ($complianceRequest->operation && ($complianceRequest->id == $complianceRequest->operation->compliance_request_id)) {
            EmailFacade::sendVerificationConfirmationFromTheManager($cUser, $complianceRequest->operation);
        }

        ActivityLogFacade::saveLog(LogMessage::C_USER_COMPLIANCE_REQUEST_SUCCESS_MAIL,
            ['complianceRequestId' => $complianceRequest->id, 'name' => $profile->getFullName()], LogResult::RESULT_SUCCESS,
            LogType::TYPE_COMPLIANCE_SUCCESS_MAIL,
            $complianceRequest->context_id, $cUser->id);
    }

    /**
     * create new retry Compliance Request
     * @param string $requiredDocsMessage
     * @param string $docsMessageRequestDescription
     * @param ComplianceRequest $complianceRequest
     * @param bool $autoRetry
     * @param string|null $action
     */
    public function createNewRetryRequest(string $requiredDocsMessage, ComplianceRequest $complianceRequest, bool $autoRetry = false, ?string $action = null, ?string $docsMessageRequestDescription = null)
    {
        $retryComplianceRequest = new ComplianceRequest();
        $retryComplianceRequest->fill([
            'id' => Str::uuid(),
            'c_profile_id' => $complianceRequest->c_profile_id,
            'compliance_level' => $complianceRequest->compliance_level,
            'applicant_id' => $complianceRequest->applicant_id,
            'context_id' => $complianceRequest->context_id,
            'status' => ComplianceRequestEnum::STATUS_RETRY,
            'message' => $requiredDocsMessage,
            'description' => $docsMessageRequestDescription,
        ]);
        if (!$action) {
            $action = !$autoRetry ? LogMessage::C_USER_COMPLIANCE_REQUEST_RETRY : LogMessage::COMPLIANCE_DOCUMENTS_AUTO_DELETE;
        }
        $retryComplianceRequest->save();
        $cProfile = $retryComplianceRequest->cProfile;
        ActivityLogFacade::saveLog($action,
            ['cProfileId' => $cProfile->id, 'name' => $cProfile->getFullName()],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT,
            $retryComplianceRequest->context_id, $cProfile->cUser->id);
    }

    /**
     * Check applicant previous request, if last 5 are declined then suspend user
     * @param CProfile $profile
     * @param ComplianceRequest $complianceRequest
     * @return bool
     */
    public function checkBlockCompliance(CProfile $profile, ComplianceRequest $complianceRequest)
    {
        $allDeclined = false;
        if ($profile->compliance_level == ComplianceLevel::VERIFICATION_LEVEL_0) {
            $allowedMaxRequestsCount = config('cratos.sum_sub.allowed_requests_count');
            $statuses = ComplianceRequest::findApplicantRequestStatuses(
                $complianceRequest->applicant_id, ComplianceRequestEnum::STATUS_DECLINED,
                $allowedMaxRequestsCount,
                'updated_at', 'desc');
            if ($allowedMaxRequestsCount == count($statuses)) {
                $allDeclined = true;
            }

            Log::info('statuses', [$statuses, $allDeclined]);

            if ($allDeclined) {
                $cUser = $profile->cUser;
                $oldStatusName = $profile->status;
                $profile->status = CProfileStatuses::STATUS_SUSPENDED;
                $profile->save();
                $replacements = ['email' => $cUser->email, 'oldStatus' => $oldStatusName, 'newStatus' => $profile->status];
                ActivityLogFacade::saveLog(LogMessage::C_PROFILE_STATUS_CHANGE, $replacements, LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_STATUS_CHANGE, $complianceRequest->context_id, $cUser->id);
                EmailFacade::sendInfoEmail($cUser, $profile, 'mail_client_account_suspended_same_fail_reason_subject', 'mail_client_account_suspended_same_fail_reason_title');
            }
        }
        return $allDeclined;
    }

    /**
     * Auto deletes documents from sumsub
     */
    public function autoDeleteDocuments()
    {
        $complianceRequests = ComplianceRequest::groupBy('c_profile_id')->where('status', ComplianceRequestEnum::STATUS_APPROVED)->orderBy('updated_at', 'desc')
           ->get();
        $intervalTime = [];
        foreach ($complianceRequests as $complianceRequest) {
            $interval = $complianceRequest->cProfile->cUser->project->complianceProvider()->pivot->renewal_interval ?? null;
            if (!$interval) {
                continue;
            }
            $intervalTime[$complianceRequest->cProfile->id] = $interval;
        }

        $complianceRequests = ComplianceRequest::query();

        foreach ($intervalTime as $key => $value) {
            $date = Carbon::now()->subMonths($value)->startOfDay();
            $complianceRequests->orWhere(function ($q) use ($key, $date) {
                $q->where('c_profile_id', $key)->whereBetween('updated_at', [$date, $date->copy()->endOfDay()]);
            });
        }

        $complianceRequests = $complianceRequests->groupBy('c_profile_id')->get();
        echo 'found total ' . count($complianceRequests) . "\n";
        foreach ($complianceRequests as $complianceRequest) {
            $profile = $complianceRequest->cProfile;
            $complianceService = $this->getComplianceProvider($profile->cUser->project);
            if ($profile->compliance_level == $complianceRequest->compliance_level) {

                $cUser = $profile->cUser;
                $requiredDocNames = $profile->account_type == CProfile::TYPE_INDIVIDUAL ?
                    $complianceService->getIndividualDocNamesList() : $complianceService->getCorporateDocNamesList();
                $ids = $names = [];
                $info = $complianceService->getRequiredDocs($complianceRequest->applicant_id);
                $applicantData = $complianceService->getApplicantInfo($complianceRequest->applicant_id);
                $inspectionId = $applicantData['inspectionId'];
                foreach ($info as $documentName => $documentData) {
                    if (isset($documentData['imageIds']) && in_array($documentName, $requiredDocNames)) {
                        $names[] = str_replace('_', ' ', ucfirst(strtolower($documentName)));
                        foreach ($documentData['imageIds'] as $id) {
                            $ids[] = $id;
                            $complianceService->deleteImage($inspectionId, $id);
                        }
                    }
                }
                if (!$ids) {
                    ActivityLogFacade::saveLog(LogMessage::COMPLIANCE_REQUEST_DOCUMENTS_RETRY,
                        ['info' => $info, 'applicantData' => $applicantData, 'complianceRequestId' => $complianceRequest->id],
                        LogResult::RESULT_FAILURE, LogType::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY,
                        $complianceRequest->context_id, $cUser->id);
                } else {
                    $documentNames = implode(', ', $names);
                    $this->createNewRetryRequest(t('ui_documents_automatic_request_message', ['documentNames' => $documentNames]), $complianceRequest, true);
                    ActivityLogFacade::saveLog(LogMessage::COMPLIANCE_DOCUMENTS_AUTO_DELETE, ['imageIds' => implode(', ', $ids), 'documentNames' => $documentNames, 'complianceRequestId' => $complianceRequest->id],
                        LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY, $complianceRequest->context_id, $cUser->id);
                    EmailFacade::sendDocsAutoDeleteEmail($cUser, $profile, $documentNames);
                }
                echo 'done for ' . $profile->getFullName() . "\n";
            }
        }
        return true;
    }


    /**
     * Notify users before suspending account
     * @return bool
     */
    public function notifyBeforeSuspend()
    {
        $additionalTime = config('cratos.sum_sub.additional_time_for_doc_upload');
        $notifyTime = config('cratos.sum_sub.notify_time_before_making_user_suspended');
        $complianceDate = date('Y-m-d', strtotime($additionalTime . ' ' . $notifyTime));

        $complianceRequests = ComplianceRequest::groupBy('c_profile_id')->whereIn('status', [ComplianceRequestEnum::STATUS_PENDING, ComplianceRequestEnum::STATUS_RETRY])->orderBy('updated_at', 'desc')
            ->whereBetween('updated_at', [$complianceDate, $complianceDate . ' 23:59:59'])
            ->get();
        echo 'found total ' . count($complianceRequests) . "\n";

        foreach ($complianceRequests as $complianceRequest) {
            $profile = $complianceRequest->cProfile;
            if ($profile->compliance_level == $complianceRequest->compliance_level) {
                $cUser = $profile->cUser;
                EmailFacade::sendNotifyBeforeSuspendingUserEmail($cUser, $profile);
                ActivityLogFacade::saveLog(LogMessage::NOTIFY_USER_BEFORE_SUSPEND,
                    ['complianceRequestId' => $complianceRequest->id], LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY, $complianceRequest->context_id, $cUser->id);
                echo 'done for ' . $profile->getFullName() . "\n";
            }
        }
        return true;
    }

    /**
     * Susspend user account
     * @return bool
     */
    public function suspendUser()
    {
        $additionalTime = config('cratos.sum_sub.additional_time_for_doc_upload');
        $complianceDate = date('Y-m-d', strtotime($additionalTime));


        $complianceRequests = ComplianceRequest::groupBy('c_profile_id')->whereIn('status', [ComplianceRequestEnum::STATUS_PENDING, ComplianceRequestEnum::STATUS_RETRY])->orderBy('updated_at', 'desc')
            ->whereBetween('updated_at', [$complianceDate, $complianceDate . ' 23:59:59'])
            ->get();
        echo 'found total ' . count($complianceRequests) . "\n";

        foreach ($complianceRequests as $complianceRequest) {
            $profile = $complianceRequest->cProfile;
            $complianceRequestLogResult = $complianceRequest->status;
            if ($profile->compliance_level == $complianceRequest->compliance_level) {
                $cUser = $profile->cUser;

                $complianceRequest->status = ComplianceRequestEnum::STATUS_DECLINED;
                $complianceRequest->save();
                ActivityLogFacade::saveLog(LogMessage::COMPLIANCE_REQUEST_STATUS_CHANGE, ['newStatus' => ComplianceRequestEnum::getName($complianceRequest->status)], $complianceRequestLogResult, LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_STATUS_CHANGE, $complianceRequest->context_id, $cUser->id);

                $profile->status = CProfileStatuses::STATUS_SUSPENDED;
                $profile->save();
                EmailFacade::sendSuspendUserEmail($cUser, $profile);
                ActivityLogFacade::saveLog(LogMessage::SUSPEND_USER,
                    ['complianceRequestId' => $complianceRequest->id], LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_DOCUMENTS_AUTO_RETRY, $complianceRequest->context_id, $cUser->id);
                echo 'done for ' . $profile->getFullName() . "\n";

            }
        }
        return true;
    }


    /**
     * @param ComplianceRequest $complianceRequest
     * @param string $renewDate
     * @param string $userId
     * @return bool
     */
    public function renewDate(ComplianceRequest $complianceRequest, string $renewDate, string $userId): bool
    {
        $complianceRequest->updated_at = $renewDate;
        $complianceRequest->save();
        ActivityLogFacade::saveLog(LogMessage::RENEW_COMPLIANCE_REQUEST_DATE,
            ['complianceRequestId' => $complianceRequest->id, 'renewDate' => $renewDate],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_DATE_RENEW,
            $complianceRequest->context_id, $userId);

        return true;
    }

    /**
     * create new retry Compliance Request
     * @param string $requestMessage
     * @param Request $request
     * @param bool $autoRetry
     * @param bool $operation
     * @param CProfile $cProfile
     * @param string|null $action
     */

    public function createNewRetryRequestN2(string $requestMessage, $request, Operation $operation, CProfile $cProfile)
    {
        $nextComplianceLevel = (!$request->compliance_level || $request->compliance_level == $cProfile->compliance_level) ?
            $operation->nextComplianceLevel() : $request->compliance_level;

        $complianceRequest = ComplianceRequest::where('c_profile_id', $cProfile->id)
            ->where('compliance_level', $nextComplianceLevel)
            ->where('status', ComplianceRequestEnum::STATUS_RETRY)->first();

        $lastApprovedComplianceRequest = $cProfile->lastApprovedComplianceRequest();
        if (!$lastApprovedComplianceRequest) {
            if ($cProfile->compliance_level === ComplianceLevel::VERIFICATION_LEVEL_0) {
                EmailFacade::sendUpdateLevelRequest($cProfile->cUser, $operation);
                return t('level_increase_require_message_send_to_client');
            } else {
                return t('cant_create_retry_request');
            }
        }

        if ($complianceRequest) {
            $message = t('compliance_request_already_created');
        } else {
            $complianceRequest = new ComplianceRequest();
            $complianceRequest->fill([
                'id' => Str::uuid(),
                'c_profile_id' => $cProfile->id,
                'compliance_level' => $nextComplianceLevel,
                'applicant_id' => $lastApprovedComplianceRequest->applicant_id,
                'context_id' => $operation->id,
                'status' => ComplianceRequestEnum::STATUS_RETRY,
                'message' => $requestMessage,
                'description' => $request->docsMessageRequestDescription ?
                    $request->docsMessageRequestDescription : t('need_to_increase_compliance_level', ['number' => $operation->operation_id]),
            ]);
            $action = LogMessage::C_USER_COMPLIANCE_REQUEST_RETRY;

            $complianceRequest->save();

            //update operation
            $operation->compliance_request_id = $complianceRequest->id;
            $operation->save();

            $cProfile = $complianceRequest->cProfile;
            ActivityLogFacade::saveLog($action,
                ['cProfileId' => $cProfile->id, 'name' => $cProfile->getFullName()],
                LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_REQUEST_SUBMIT,
                $complianceRequest->context_id, $cProfile->cUser->id);

            // todo verify 4
            EmailFacade::sendVerificationRequestFromTheManager($cProfile->cUser, $operation);

            ActivityLogFacade::saveLog(LogMessage::C_USER_COMPLIANCE_REQUEST_SUCCESS_MAIL,
                ['complianceRequestId' => $complianceRequest->id, 'name' => $cProfile->getFullName()], LogResult::RESULT_SUCCESS,
                LogType::TYPE_COMPLIANCE_SUCCESS_MAIL,
                $complianceRequest->context_id, $cProfile->cUser->id);

            $message = t('compliance_request_successfully_created');
        }
        return $message;
    }


    public function getNextComplianceLevels(CProfile $cProfile): array
    {
        $nextComplianceLevels = ComplianceLevel::getList();

        for ($i = 0; $i <= $cProfile->compliance_level; $i++) {
            unset($nextComplianceLevels[$i]);
        }

        return $nextComplianceLevels;
    }

    /**
     * @param CProfile $cProfile
     * @return array
     */
    public function getCProfileComplianceData(CProfile $cProfile): array
    {
        $complianceService = $this->getComplianceProvider($cProfile->cUser->project);

        /* @var ActivityLogService $activityLogService */
        $activityLogService = resolve(ActivityLogService::class);

        $sumSubApiUrl = $complianceService->getApiUrl();
        $retryComplianceRequest = $cProfile->retryComplianceRequest();
        $lastRequestIfDeclined = $cProfile->lastRequestIfDeclined();
        $sumSubNextLevelName = $complianceService->getNextLevelName($cProfile->account_type, $cProfile->compliance_level, $retryComplianceRequest, $lastRequestIfDeclined);
        $token = $complianceService->getToken($cProfile->id, $sumSubNextLevelName);
        $nextLevelButtons = $complianceService->getNextLevelButtons($cProfile->compliance_level, $retryComplianceRequest, $lastRequestIfDeclined);
        $activityLogService->setAction(LogMessage::C_USER_COMPLIANCE_PAGE_INIT)
            ->setReplacements(['cProfileId' => $cProfile->id, 'name' => $cProfile->getFullName()])
            ->setResultType(LogResult::RESULT_NEUTRAL);
        if ($retryComplianceRequest) {
            $activityLogService->setContextId($retryComplianceRequest->context_id);
        }
        $activityLogService->setType(LogType::TYPE_C_PROFILE_COMPLIANCE_INIT)
            ->log();
        $contextId = $activityLogService->getContextId();

        $complianceProviderService= app(ComplianceProviderService::class);
        $complianceApi = $complianceProviderService->getProvidersActive($cProfile->cUser->project->id ?? null)->first();
        $complianceProvider = $complianceApi->api ?? null;

        return compact('lastRequestIfDeclined', 'retryComplianceRequest', 'contextId', 'nextLevelButtons', 'token', 'sumSubApiUrl', 'sumSubNextLevelName', 'complianceProvider');

    }

    public function putTokenIntoCache(string $key, array $data)
    {
        Cache::put(self::USER_IDENTIFICATION_TOKEN . $key, $data, 1800);
    }

    public function getTokenFromCache(string $key)
    {
        return Cache::get(self::USER_IDENTIFICATION_TOKEN . $key) ?? [];
    }

    protected function getIdDocNumber(array $idDocs)
    {
        foreach ($idDocs as $idDoc) {
            if (!empty($idDoc['number']) && in_array($idDoc['idDocType'], ['ID_CARD', 'PASSPORT'])) {
                return $idDoc['number'];
            }
        }
    }

    public function getComplianceProvider($project = null)
    {
        try {
            if ($project) {
                $currentProject = $project;
            } else {
                $currentProject = Project::getCurrentProject();
            }

            $complianceProviderService= app(ComplianceProviderService::class);
            $complianceProvider =  $complianceProviderService->getProvidersActive($currentProject->id ?? null)->first();

            if($complianceProvider) {
                $configs = config('compliance_providers.providers.' . $complianceProvider->api. '.' . $complianceProvider->api_account);
                $service = config('compliance_providers.serviceObject.' . $complianceProvider->api);
                return new $service($configs);
            }
        } catch (Exception $e){
            Log::error('getComplianceProvider: ' . $e->getMessage());
        }

        return  app(SumSubService::class);
    }

    public function getComplianceProviderAccount()
    {
        /** @var Project $currentProject */
        $currentProject = Project::getCurrentProject();
        return app(ComplianceProviderService::class)->getProviderForProject($currentProject->id) ?? null;
    }

    public function getComplianceProviderByApplicant(string $applicantId)
    {
        $project = ComplianceRequest::findByApplicantId($applicantId)->cProfile->cUser->project ?? null;
        return $this->getComplianceProvider($project);
    }
}
