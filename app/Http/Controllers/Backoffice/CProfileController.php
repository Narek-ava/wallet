<?php

namespace App\Http\Controllers\Backoffice;

use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Facades\KrakenFacade;
use App\Enums\{BUserPermissions,
    CompanyOwners,
    ComplianceLevel,
    CProfileStatuses,
    Currency,
    LogMessage,
    LogResult,
    LogType,
    OperationStatuses,
    ProjectStatuses,
    Providers,
    RateTemplatesStatuses,
    TimezoneEnum};
use App\Http\Requests\Backoffice\{CProfileCorporateStoreRequest, CProfileStoreRequest};
use App\Http\Requests\Common\{CProfileUpdateComplianceOfficerRequest, CProfileUpdateCorporateRequest, CProfileUpdateRequest, CUserUpdateEmailRequest};
use App\Models\{Cabinet\CProfile, Country, CryptoAccountDetail, Account};
use App\Services\{AccountService,
    ComplianceService,
    CProfileService,
    CUserService,
    EmailService,
    Wallester\Api,
    Wallester\WallesterPaymentService,
    EmailVerificationService,
    NotificationUserService,
    OperationService,
    ProjectService,
    ProviderService,
    SumSubService,
    RateTemplatesService,
    };
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CProfileController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @param CProfileService $cProfileService
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, CProfileService $cProfileService, ProjectService $projectService)
    {
        $sort = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('sortDirection', 'desc');
        $status = $request->input('status');
        $compliance_level = $request->input('compliance_level');
        $type = $request->input('type');
        $lastLoginFrom = $request->input('lastLoginFrom');
        $lastLoginTo = $request->input('lastLoginTo');
        $balanceFrom = $request->input('balanceFrom');
        $balanceTo = $request->input('balanceTo');
        $managerId = $request->input('manager_id');
        $projectId = $request->input('project_id');
        $export = $request->input('export');
        $q = $request->input('q');
        $ref = $request->input('ref');

        $profiles = $cProfileService->search(compact('sort','managerId', 'projectId','compliance_level', 'lastLoginFrom','lastLoginTo', 'balanceFrom', 'balanceTo', 'sortDirection', 'status', 'type', 'q', 'export', 'ref'));
        $statusList = CProfileStatuses::getList();
        $complianceLevelList = ComplianceLevel::getList();

        $activeProjects =  $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        if ($export) {
            [$callback, $headers] = $cProfileService->exportCsv($profiles, $statusList, $complianceLevelList, $type);
            return response()->stream($callback, 200, $headers);
        }

        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice/cProfile/index', compact('profiles', 'compliance_level', 'managerId', 'statusList','complianceLevelList', 'sort', 'sortDirection', 'status', 'type', 'q',
            'lastLoginTo', 'lastLoginFrom', 'balanceFrom', 'balanceTo', 'activeProjects', 'projectId', 'projectNames'
        ));
    }

    /**
     * @param Request $request
     * @param $profileId
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View
     */
    public function view(Request $request,
                         $profileId,
                         CProfileService $cProfileService,
                         RateTemplatesService $rateTemplatesService,
                         OperationService $operationService,
                         ProviderService $providerService,
                         ProjectService $projectService,
                         AccountService $accountService,
                        ComplianceService $complianceService)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $lastApprovedComplianceRequest = $profile->lastApprovedComplianceRequest();
        $retryComplianceRequest = $profile->retryComplianceRequest();
        $lastRequestIfDeclined = $profile->lastRequestIfDeclined();

        $userLogFrom = $request->input('userLogFrom');
        $userLogTo = $request->input('userLogTo');
        $userLogExport = $request->input('userLogExport');
        $userLogType = $request->input('userLogType');
        $cUserId = $profile->cUser->id;
        $userLogs = $cProfileService->searchLogs(compact('cUserId','userLogFrom', 'userLogTo', 'userLogType', 'userLogExport'));

        $managerLogFrom = $request->input('managerLogFrom');
        $managerLogTo = $request->input('managerLogTo');
        $managerLogExport = $request->input('managerLogExport');
        $managerLogType = $request->input('managerLogType');
        $cUserId = $profile->cUser->id;
        $managerLogs = $cProfileService->searchLogs(['cUserId' => $cUserId, 'userLogFrom' => $managerLogFrom, 'userLogTo' => $managerLogTo,
            'userLogType' => $managerLogType, 'userLogExport' => $managerLogExport, 'managerLog' => true]);
        if ($userLogExport || $managerLogExport) {
            [$callback, $headers] = $cProfileService->exportLogsCsv($profile, $userLogExport? $userLogs : $managerLogs);
            return response()->stream($callback, 200, $headers);

        }
        $renewMinDate = date('Y-m-d', strtotime(config('cratos.sum_sub.additional_time_for_doc_upload')));
        $ratesOptions = $rateTemplatesService->getActiveRateTemplatesOptions($profile->account_type, $profile);

        $operationsPending = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::PENDING, $profile->id);
        $operationsSuccessful = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::SUCCESSFUL, $profile->id);
        $operationsDeclined = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::DECLINED, $profile->id);
        $operationsReturned = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::RETURNED, $profile->id);

        $rateTemplate = $profile->rateTemplate()->with(['commissions' => function($qc){
            return $qc->where('is_active', RateTemplatesStatuses::STATUS_ACTIVE)->orderBy('commission_type')->orderBy('type')->orderBy('currency', 'desc');
        }, 'limits' => function($q){
            return $q->orderBy('level');
        }])->first();
        $bankCardRateTemplate = $profile->bankCardRate ?? (new \App\Services\RateTemplatesService())->getActiveBankCardRates();

        $accounts = $accountService->getUserBankAccountsByCProfileId($profile->id);
        $accountsCrypto = $accountService->getUserCryptoAccountsByCProfileId($profile->id);

        $paginate = config('cratos.pagination.tickets');

        $queryOpenTickets = $profile->cUser->openTickets();
        $queryClosedTickets = $profile->cUser->closedTickets();
        if ($request->sInput) {
            if ($request->id) {
                $queryOpenTickets->where('ticket_id', $request->sInput);
                $queryClosedTickets->where('ticket_id', $request->sInput);
            } else {
                $queryOpenTickets->where('subject', 'like', '%'.$request->sInput.'%');
                $queryClosedTickets->where('subject', 'like', '%'.$request->sInput.'%');
            }
        }
        $openTickets = $queryOpenTickets->paginate($paginate);
        $closedTickets = $queryClosedTickets->paginate($paginate);

        $project = $profile->cUser->project;
        $cardIssuingProviderExists = $providerService->checkProjectProviderExistsByType($project->id, Providers::PROVIDER_CARD_ISSUING);
        $wallesterLimits = null;
        $steps = null;
        if ($cardIssuingProviderExists) {
            $wallesterApi = resolve(Api::class);
            $wallesterPaymentService = resolve(WallesterPaymentService::class);
            $wallesterLimits = $wallesterApi->getDefaultLimits();
            $steps = $wallesterPaymentService->getSteps();

        }
        $cards = $profile->wallesterAccountDetail;

        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);
        $complianceProvider = $complianceService->getComplianceProvider($profile->cUser->project);


        $fiatWallets = $profile->getFiatWallets();

        return view('backoffice/cProfile/view', compact(
            'profile', 'lastRequestIfDeclined', 'retryComplianceRequest', 'retryComplianceRequest',
            'lastApprovedComplianceRequest',
            'userLogs', 'userLogFrom','userLogTo', 'userLogType', 'userLogExport', 'renewMinDate',
            'managerLogs', 'managerLogFrom','managerLogTo', 'managerLogType', 'managerLogExport', 'ratesOptions',
            'operationsPending', 'operationsSuccessful', 'operationsReturned', 'rateTemplate', 'cardIssuingProviderExists',
            'accounts', 'accountsCrypto', 'openTickets', 'closedTickets', 'operationsDeclined', 'steps', 'cards', 'wallesterLimits', 'projectNames', 'complianceProvider', 'bankCardRateTemplate', 'fiatWallets'
        ));
    }


    /**
     * @param CProfileUpdateRequest $request
     * @param $profileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CProfileUpdateRequest $request, $profileId, NotificationUserService $notificationUserService)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $profile->cUser->fill($request->only(['phone'/*, 'email'*/]));
        $citizenship = Country::getCountryNameByCode($request->citizenship);
        $profile->fill($request->only([
                'account_type', 'first_name', 'last_name', 'country',
                'city', 'zip_code', 'address', 'gender', 'passport'
            ]) + [
                'date_of_birth' => date('Y-m-d', strtotime($request->year . '-' . $request->month . '-' . $request->day)),
                'citizenship' => $citizenship,
            ]
        );

        $dirtyAttributes = array_merge($profile->getDirty(), $profile->cUser->getDirty());
        $profile->cUser->save();
        $profile->save();
        EmailFacade::sendSuccessUpdatePersonalInformationMessage($profile, $dirtyAttributes);
        ActivityLogFacade::saveLog(LogMessage::USER_PERSONAL_INFORMATION_UPDATED_BACKOFFICE, ['name' => $profile->getFullName()], LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_INFORMATION_CHANGE_BACKOFFICE, null, $profile->cUser->id) ;
        // @note не лучше ли вместо date() (выше и в других подобных местох) использовать Carbon

        return back()->with('success', __('Successfully Saved!'));
    }
    /**
     * @param CProfileUpdateCorporateRequest $request
     * @param $profileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCorporate(CProfileUpdateCorporateRequest $request, $profileId, CProfileService $CProfileService)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $updateData = $request->only([
                'company_email', 'company_name', 'company_phone', 'registration_number',
                'country', 'legal_address', 'trading_address',
                'contact_email', 'interface_language', 'currency_rate',
            ]) + ['registration_date' => date('Y-m-d', strtotime($request->year.'-'.$request->month.'-'.$request->day))];
        $CProfileService->updateProfile($profile, $updateData, $request->beneficial_owners, $request->ceos, $request->shareholders);
        if ($request->webhook_url) {
            $profile->setSecretKey();
        }

        if (!empty($request->contact_phone)) {
            $cUser = $profile->cUser;
            $cUser->phone = $request->contact_phone;
            $cUser->save();
        }
        return back()->with('success', __('Successfully Saved!'));
    }

    public function updateIsMerchant(Request $request, CProfileService $cProfileService)
    {
        return $cProfileService->updateIsMerchant($request->get('isMerchant'), $request->get('profileId'));
    }

    /**
     * @param CProfileUpdateComplianceOfficerRequest $request
     * @param $profileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateComplianceOfficer(CProfileUpdateComplianceOfficerRequest $request, $profileId)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $profile->update(['compliance_officer_id' => $request->compliance_officer_id]);
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE, ['email' => $profile->cUser->email],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_COMPLIANCE_OFFICER_CHANGE_BACKOFFICE, null, $profile->cUser->id);
        return back()->with('success', __('Successfully Saved!'));
    }

    /**
     * @param CProfileUpdateComplianceOfficerRequest $request
     * @param $profileId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateManager(CProfileUpdateComplianceOfficerRequest $request, $profileId)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $profile->update(['manager_id' => $request->manager_id]);
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_MANAGER_CHANGE_BACKOFFICE, ['email' => $profile->cUser->email],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_MANAGER_CHANGE_BACKOFFICE, null, $profile->cUser->id);
        return back()->with('success', __('Successfully Saved!'));
    }

    /**
     * @param CUserUpdateEmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmail(CUserUpdateEmailRequest $request, $profileId, CUserService $cUserService, EmailVerificationService $emailVerificationService)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $user = $profile->cUser;
        $email = $user->email;
        $emailVerificationService->generateToChange($user, $request->email);
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_EMAIL_CHANGE_BACKOFFICE, ['email' => $email, 'newEmail' => $request->email],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_EMAIL_CHANGE_BACKOFFICE, null, $profile->cUser->id);
        return back()->with('success', t('ui_verification_successfully_sent'));

    }

    /**
     * Change CProfile status
     * @param Request $request
     * @param $profileId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function changeStatus(Request $request, $profileId)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        /* @var CProfile $profile*/

        $allowedStatuses = CProfileStatuses::getList();
        $rules = [
            'status_change_text' => 'required|string|max:500',
            'status' => 'required|between:' . implode(',', array_keys($allowedStatuses)),
        ];
        $messages = [
            'status_change_text.required' => t('ui_error_status_change_text_required'),
            'status_change_text.*' => t('ui_error_status_change_text_string_max_500'),
        ];
        $input = $this->validate($request, $rules, $messages);
        $oldStatusName = CProfileStatuses::getName($profile->status);
        $oldStatus = $profile->status;
        $profile->update(['status' => $input['status'], 'status_change_text' => $input['status_change_text']]);
        $newStatus = $allowedStatuses[$input['status']];
        ActivityLogFacade::saveLog(LogMessage::C_PROFILE_STATUS_CHANGE, ['email' => $profile->cUser->email, 'oldStatus' => $oldStatusName, 'newStatus' => $newStatus],
            LogResult::RESULT_SUCCESS, LogType::TYPE_C_PROFILE_STATUS_CHANGE, null, $profile->cUser->id);

        EmailFacade::sendStatusAccount($profile->cUser, $input['status_change_text'], $input['status'], $oldStatus);

        return back()->with('success', t('ui_bo_user_status_successfully_changed'));

    }


    /**
     * @param CProfileStoreRequest $request
     * @param CProfileService $cProfileService
     * @param CUserService $cUserService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CProfileStoreRequest $request, CProfileService $cProfileService, CUserService $cUserService)
    {
        $cUser = $cUserService->create($request, true);
        $cProfileService->createFromCUser($cUser, $request->only([
            'first_name', 'last_name', 'country', 'manager_id', 'compliance_officer_id'
        ]) + ['account_type'=> CProfile::TYPE_INDIVIDUAL]);
        return back()->with('success', __('Successfully Saved!'));
    }

    /**
     * @param CProfileCorporateStoreRequest $request
     * @param CProfileService $cProfileService
     * @param CUserService $cUserService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCorporate(CProfileCorporateStoreRequest $request, CProfileService $cProfileService, CUserService $cUserService)
    {
        $cUser = $cUserService->create($request, true);
        $cProfileService->createFromCUser($cUser, $request->only([
               'company_name', 'country', 'manager_id', 'compliance_officer_id'
        ]) + ['account_type'=> CProfile::TYPE_CORPORATE]);

        return back()->with('success', __('Successfully Saved!'));
    }


    /**
     * @TODO remove function
     * Sendig test request for changing user compliance status
     * @param string $profileId
     * @param int $success
     */
    public function sendTestCompletedCompliance(string $profileId, int $success, ComplianceService $complianceService)
    {
        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $pendingComplianceRequest = $profile->pendingComplianceRequest();
        $complianceProvider = $complianceService->getComplianceProvider($profile->cUser->project);

        if ($pendingComplianceRequest) {
            $result = $complianceProvider->testCompleted($pendingComplianceRequest->applicant_id, $success);
            if (!empty($result['ok'])) {
                return back()->with('success', 'Compliance request status successfully changed!');
            }
        }
        return back()->with('error','User dont have pending compliance request');

    }
    public function sendTestCompletedCard(int $success, ComplianceService $complianceService)
    {
        if (session()->get('applicantId')) {
            $complianceProvider = $complianceService->getComplianceProviderByApplicant(session()->get('applicantId'));
            $result = $complianceProvider->testCompleted(session()->get('applicantId'), $success);
            if (!empty($result['ok'])) {
                return back()->with('success', 'Compliance request status successfully changed!');
            }
        }
        return back()->with('error','User dont have pending compliance request');

    }

    /**
     * @param string $walletId
     * @param Request $request
     * @param OperationService $operationService
     * @return Factory|View
     */
    public function viewWallet(string $walletId, Request $request, OperationService $operationService)
    {
        $cryptoAccountDetail = CryptoAccountDetail::with(['account' => function($q) {
            $q->with('cProfile');
        }])->find($walletId);
        $cProfile = $cryptoAccountDetail->account->cProfile;
        $operations = $cryptoAccountDetail->operations();
        $filteredOperationsPending = $operationService->getClientOperationsPaginationWithFilter($request, $cryptoAccountDetail->account_id, OperationStatuses::PENDING, $cProfile);
        $filteredOperationsSuccessful = $operationService->getClientOperationsPaginationWithFilter($request, $cryptoAccountDetail->account_id, OperationStatuses::SUCCESSFUL, $cProfile);
        $filteredOperationsDeclined = $operationService->getClientOperationsPaginationWithFilter($request, $cryptoAccountDetail->account_id, OperationStatuses::DECLINED, $cProfile);
        $filteredOperationsReturned = $operationService->getClientOperationsPaginationWithFilter($request, $cryptoAccountDetail->account_id, OperationStatuses::RETURNED, $cProfile);

        config()->set('projects.project', $cProfile->cUser->project);
        $rateForUSD = KrakenFacade::getRateCryptoFiat($cryptoAccountDetail->coin, Currency::CURRENCY_USD, $cryptoAccountDetail->account->getAvailableBalance());
        $rateForEUR = KrakenFacade::getRateCryptoFiat($cryptoAccountDetail->coin, Currency::CURRENCY_EUR, $cryptoAccountDetail->account->getAvailableBalance());

        return view('backoffice/cProfile/wallets/show')->with([
            'profile' => $cProfile,
            'cryptoAccountDetail' => $cryptoAccountDetail,
            'operations' => $operations,
            'filteredOperationsPending' => $filteredOperationsPending,
            'filteredOperationsSuccessful' => $filteredOperationsSuccessful,
            'filteredOperationsDeclined' => $filteredOperationsDeclined,
            'filteredOperationsReturned' => $filteredOperationsReturned,
            'rateForUSD' => $rateForUSD,
            'rateForEUR' => $rateForEUR
        ]);
    }

    /**
     * @param string $fiatAccountId
     * @param Request $request
     * @param OperationService $operationService
     * @return Factory|View
     */
    public function viewFiatWallet(string $fiatAccountId, Request $request, OperationService $operationService)
    {
        /** @var Account $fiatAccount */
        $fiatAccount = Account::findOrFail($fiatAccountId);
        $cProfile = $fiatAccount->cProfile;
        $operations = $fiatAccount->operations();
        $filteredOperationsPending = $operationService->getClientOperationsPaginationWithFilter($request, $fiatAccountId, OperationStatuses::PENDING, $cProfile);
        $filteredOperationsSuccessful = $operationService->getClientOperationsPaginationWithFilter($request, $fiatAccountId, OperationStatuses::SUCCESSFUL, $cProfile);
        $filteredOperationsDeclined = $operationService->getClientOperationsPaginationWithFilter($request, $fiatAccountId, OperationStatuses::DECLINED, $cProfile);
        $filteredOperationsReturned = $operationService->getClientOperationsPaginationWithFilter($request, $fiatAccountId, OperationStatuses::RETURNED, $cProfile);

        return view('backoffice/cProfile/fiat-wallets/show')->with([
            'profile' => $cProfile,
            'fiatAccount' => $fiatAccount,
            'operations' => $operations,
            'filteredOperationsPending' => $filteredOperationsPending,
            'filteredOperationsSuccessful' => $filteredOperationsSuccessful,
            'filteredOperationsDeclined' => $filteredOperationsDeclined,
            'filteredOperationsReturned' => $filteredOperationsReturned,
        ]);
    }

    public function updateTimezone(Request $request, $profileId)
    {
        $request->validate([
           'timezone' => Rule::in(TimezoneEnum::getAllTimezones()),
        ]);

        $profile = CProfile::where('id', $profileId)->firstOrFail();
        $profile->timezone = $request->timezone;
        $profile->save();

        return redirect()->back();
    }
}
