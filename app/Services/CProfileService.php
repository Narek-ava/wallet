<?php

namespace App\Services;

use App\DataObjects\SumSubCorporateUserProfileData;
use App\Enums\AccountType;
use App\Enums\CProfileStatuses;
use App\Enums\Enum;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Enums\ReportStatuses;
use App\Enums\ReportTypes;
use App\Enums\ProjectStatuses;
use App\Enums\TimezoneEnum;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Models\Account;
use App\Models\CompanyOwners;
use App\Models\Country;
use App\Models\Limit;
use App\Models\Log;
use App\Models\Project;
use App\Models\RateTemplate;
use App\Models\ReferralLink;
use App\Models\ReferralPartner;
use App\Models\ReportRequestTemporary;
use Carbon\Carbon;
use App\Models\Cabinet\{
    CProfile, CUser
};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CProfileService
{
    /**
     * Create CProfile
     *
     * @param CUser $cUser
     * @param array $cProfileData
     * @return CProfile
     */
    public function createFromCUser(CUser $cUser, array $cProfileData = []): CProfile
    {
        //! @todo transaction
        $cProfile = new CProfile();
        $cProfile->id = Str::uuid();
        $cProfile->fill($cProfileData);
        $cProfile->rate_template_id = (new RateTemplatesService)->getDefaultRateTemplateId($cProfileData['account_type']);

        $referralLinkTemplate = null;
        $refToken = $this->getRefToken($cUser->email);
        if($refToken) {
            $referralLink = ReferralLink::find($refToken);
            if ($referralLink) {
                $cProfile->ref = $referralLink->id;
                if(Carbon::now()->between($referralLink->activation_date,$referralLink->deactivation_date)) {
                    $referralLinkTemplate =  $cProfileData['account_type'] == CProfile::TYPE_INDIVIDUAL ? $referralLink->individual_rate_templates_id : $referralLink->corporate_rate_templates_id;
                }
            }
            Cookie::queue(Cookie::forget('ref'));
            (new CUserService)->deleteRegisterDataFromCache($cUser->email);
        }
        $project = $cUser->project;

        if ($referralLinkTemplate) {
            $cProfile->rate_template_id = $referralLinkTemplate;
        } elseif ($project) {
            $cProfile->rate_template_id = $cProfileData['account_type'] == CProfile::TYPE_CORPORATE ? $project->corporateRate->id : $project->individualRate->id;
        }

        $cProfile->rates_category_id = (new RatesService)->getRatesCategoryForAccountType($cProfileData['account_type']);

        $cProfile->timezone = TimezoneEnum::TIMEZONE_DEFAULT;

        $cProfile->save();
        $cProfile->refresh();;

        /** @todo через реляции? */
        $cUser->c_profile_id = $cProfile->id;
        $cUser->save();

        return $cProfile;
    }


    /**
     * @param array $params
     * @return mixed
     */
    public function search(array $params)
    {
        $query = CProfile::query();
        if (!isset($params['sort'])) {
            $params['sort'] = 'updated_at';
        }
        if (!isset($params['sortDirection'])) {
            $params['sortDirection'] = 'desc';
        }
        if ($params['sort'] === 'email') {
            $query = $query
                ->join('c_users', 'c_profiles.id', '=', 'c_users.c_profile_id')
                ->orderBy('c_users.email', $params['sortDirection']);
        } else {
            $query = $query->orderBy($params['sort'], $params['sortDirection']);
        }

        if (!empty($params['status']) || $params['status'] === '0') {
            $query->where('status', intval($params['status']));
        }

        // @todo using ?? | ?: statement
        if (!empty($params['compliance_level']) || $params['compliance_level'] === '0') {
            $query->where('compliance_level', intval($params['compliance_level']));
        }
        if (!empty($params['type'])) {
            $query->where('account_type', intval($params['type']));
        }
        if (!empty($params['managerId'])) {
            $query->where('manager_id', $params['managerId']);
        }

        $bUser = auth()->guard('bUser')->user();
         if (!empty($params['projectId'])) {
             $projectId = $params['projectId'];
             $query->whereHas('cUser', function ($q) use ($projectId) {
                 return $q->where('project_id', $projectId);
             });
        } else if (!$bUser->is_super_admin){
             $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
             $query->whereHas('cUser', function ($q) use ($projectIds) {
                 return $q->whereIn('project_id', $projectIds);
             });
         }

        if (!empty($params['lastLoginFrom']) && !empty($params['lastLoginTo'])) {
            $query->whereBetween('last_login', [date('Y-m-d', strtotime($params['lastLoginFrom'])), date('Y-m-d', strtotime($params['lastLoginTo'])) . ' 23:59:59']);
        } elseif (!empty($params['lastLoginFrom'])) {
            $query->whereDate('last_login', '>=', date('Y-m-d', strtotime($params['lastLoginFrom'])));
        } elseif (!empty($params['lastLoginTo'])) {
            $query->whereDate('last_login', '<=', date('Y-m-d', strtotime($params['lastLoginTo'])) . ' 23:59:59');
        }
        //    print_r($query->toSql()); die;
        if (isset($params['q'])) {
            $query->where(function ($query) use ($params) {
                $query->where('first_name', 'like', '%' . $params['q'] . '%')
                    ->orWhere('last_name', 'like', '%' . $params['q'] . '%')
                    ->orWhere('company_name', 'like', '%' . $params['q'] . '%')
                    ->orWhere('company_email', 'like', '%' . $params['q'] . '%')
                    ->orWhere('profile_id', $params['q'])
                    ->orWhereHas('cUser', function ($q) use ($params) {
                        $q->where('phone', 'like', '%' . $params['q'] . '%');
                        $q->orWhere('email', 'like', '%' . $params['q'] . '%');
                    });
            });
        }
        if (!empty($params['ref'])) {
            $ref = $params['ref'];
            $query->whereHas('referral', function ($q) use ($ref) {
                $q->where('name', 'like', '%' . $ref . '%');
            });
        }
        return isset($params['export']) ? $query->get() : $query->paginate(10);
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function searchLogs(array $params)
    {
        $query = Log::query()->where(['c_user_id' => $params['cUserId']])->orderBy('created_at', 'desc');
        if (empty($params['managerLog'])) {
            $query->whereNull('b_user_id');
            $logTypes = LogType::USER_LOG_TYPES;
            $pageName = Enum::USER_PAGE_NAME;
        } else {
            $logTypes = LogType::MANAGER_LOG_TYPES;
            $pageName = Enum::MANAGER_PAGE_NAME;
        }
        if (!empty($params['userLogFrom']) && !empty($params['userLogTo'])) {
            $query->whereBetween('created_at', [date('Y-m-d', strtotime($params['userLogFrom'])), date('Y-m-d', strtotime($params['userLogTo'])) . ' 23:59:59']);
        } elseif (!empty($params['userLogFrom'])) {
            $query->whereDate('created_at', '>=', date('Y-m-d', strtotime($params['userLogFrom'])));
        } elseif (!empty($params['userLogTo'])) {
            $query->whereDate('created_at', '<=', date('Y-m-d', strtotime($params['userLogTo'])) . ' 23:59:59');
        }

        if (!empty($params['userLogType'])) {
            $query->where('type', intval($params['userLogType']));
        }else{
            $query->whereIn('type', $logTypes);
        }
        return isset($params['userLogExport']) ? $query->get() : ($query->paginate(10, ['*'], $pageName));
    }

    /**
     * @param $profiles
     * @param $statusList
     * @param $complianceLevelList
     * @param $type
     * @return array
     */
    public function exportCsv($profiles, $statusList, $complianceLevelList, $type)
    {
        $fileName = 'CProfiles.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = ['ID'];
        if ($type == CProfile::TYPE_INDIVIDUAL) {
            $columns[] = 'First Name';
            $columns[] = 'Last Name';
        }
        if ($type == CProfile::TYPE_CORPORATE) {
            $columns[] = 'Company Name';
            $columns[] = 'Company Email';
        }
        $columns = array_merge($columns, ['Email', 'Verification', 'Manager', 'Total Balance', 'Last Login', 'Status', 'Referral Of User']);

        $callback = function () use ($profiles, $columns, $statusList, $complianceLevelList, $type) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($profiles as $profile) {
                $row = [$profile->profile_id];
                if ($type == CProfile::TYPE_INDIVIDUAL) {
                    $row[] = $profile->first_name;
                    $row[] = $profile->last_name;
                } else {
                    $row[] = $profile->company_name;
                    $row[] = $profile->company_email;
                }
                $row = array_merge($row, [
                    $profile->email ?? $profile->cUser->email ?? '',
                    !empty($complianceLevelList[$profile->compliance_level]) ? $complianceLevelList[$profile->compliance_level] : '',
                    $profile->manager ? $profile->manager->email : '-',
                    '-',
                    $profile->last_login,
                    $statusList[$profile->status],
                    '-',
                ]);

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return [$callback, $headers];
    }



    /**
     * Logs CSV Export
     * @param $logs
     * @return array
     */
    public function exportLogsCsv(CProfile $profile, $logs)
    {
        $fileName = $profile->getFullName().' Activity.csv';

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = ['DATE', 'IP', 'ACTION'];

        $callback = function () use ($logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($logs as $log) {
                $row = [$log->created_at->format('Y-m-d H:i:s'), $log->ip, t($log->action, $log->getReplacementsArray())];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return [$callback, $headers];
    }

    public function getById($id, ?string $projectId = null)
    {
        $query = CProfile::query()->where('profile_id', $id);
        if (!$projectId) {
            $query->whereHas('cUser', function ($q) use($projectId) {
                return $q->where('project_id', $projectId);
            });
        }
        return $query->first();
    }

    public function getProfilesDropdown()
    {
        return CProfile::orderBy('profile_id')->get();
    }

    public function getProfilesTyped($accountType)
    {
        return CProfile::where('account_type', $accountType)->get();
    }

    public function changeProfileRateTemplate($rateTemplateId, $profileId)
    {
        $profile = CProfile::find($profileId);
        $rateTemplate = RateTemplate::find($rateTemplateId);
        if ($profile && $rateTemplate) {
            $profile->update(['rate_template_id' => $rateTemplateId]);
            EmailFacade::sendChangingRateForUser($profile->cUser);
            return true;
        }
        return false;
    }

    public function updateIsMerchant($isMerchant, $profileId)
    {
        $profile = CProfile::find($profileId);
        if (!$profile || $profile->account_type != CProfile::TYPE_CORPORATE || $profile->paymentFormsActive->isNotEmpty()) {
            return false;
        }
        $profile->is_merchant = $isMerchant === 'true';
        $profile->save();
        return response()->json([
            'isMerchant' => $profile->is_merchant
        ]);
    }

    public function updateWebhookUrl(CProfile $profile, string $webhookUrl)
    {
        if ($profile->is_merchant) {
            $profile->webhook_url = $webhookUrl;
            $profile->save();
            $profile->setSecretKey();
        }
    }

    public function changeDefaultRateTemplates($rateTemplateIndividualId, $rateTemplateCorporateId)
    {
        CProfile::where('account_type', CProfile::TYPE_INDIVIDUAL)->update(['rate_template_id' => $rateTemplateIndividualId]);
        CProfile::where('account_type', CProfile::TYPE_CORPORATE)->update(['rate_template_id' => $rateTemplateCorporateId]);

    }

    public function getCProfileByProfileId($profileId)
    {
        return CProfile::where('profile_id', $profileId)->first();
    }

    public function getProfileById($id)
    {
        return CProfile::query()->findOrFail($id);
    }

    private function getClientsForExcel($params): Builder
    {
        $queryBuilder = CProfile::with(['operations', 'manager']);

        $bUser = auth()->guard('bUser')->user();
        $projectIds = [];
        if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
        }

        if (!empty($params['project'])) {
            $projectIds = $bUser->is_super_admin ? [$params['project']] : (in_array($params['project'], $projectIds) ? [$params['project']] : $projectIds);
        }

        if (!empty($projectIds)) {
            $queryBuilder->where(function ($query) use ($projectIds) {
                return $query->whereHas('cUser', function ($qw) use ($projectIds) {
                    $qw->whereHas('project', function ($qv) use ($projectIds) {
                        $qv->whereIn('id', $projectIds)->where('status', ProjectStatuses::STATUS_ACTIVE);
                    });
                });
            });
        } else {
            $queryBuilder->whereHas('cUser');
        }

        if (!empty($params['from'])) {
            $queryBuilder->where('created_at', '>=', $params['from'] . ' 00:00:00');
        }
        if (!empty($params['to'])) {
            $queryBuilder->where('created_at', '<=', $params['to'] . ' 23:59:59');
        }

        return $queryBuilder->orderBy('profile_id');
    }

    private function getCsvHeaders()
    {
        return ['Profile id', 'Email', 'Phone', 'Account type', 'Project', 'Full name', 'Referral', 'Country', 'Company name',
            'Company email', 'Company phone', 'Compliance level', 'Status', 'Manager email', 'Date of birth',
            'City', 'Zip code', 'Address', 'Legal address', 'Trading address', 'Registration number',
            'Ceos full name', 'Shareholders', 'Registration date', 'Rate template name', 'Operation ID',
            'Date', 'Bank name', 'Type', 'Amount'];
    }

    private function getCsvProfileRow($profile)
    {
        return [
            'profile_id' => $profile->profile_id,
            'email' => $profile->cUser->email,
            'phone' => $profile->cUser->phone,
            'account_type' => t(CProfile::TYPES_LIST[$profile->account_type]),
            'project' => $profile->cUser->project->name ?? '',
            'name' => $profile->account_type == CProfile::TYPE_INDIVIDUAL ? $profile->getFullName() : '',
            'referral' => $profile->getReferralName(),
            'country' => Country::getCountryNameByCode($profile->country),
            'company_name' => $profile->company_name,
            'company_email' => $profile->company_email,
            'company_phone' => $profile->company_phone,
            'compliance_level' => $profile->compliance_level,
            'status' => CProfileStatuses::getName($profile->status),
            'manager_email' => $profile->getManager()->email ?? '-',
            'date_of_birth' => $profile->date_of_birth,
            'city' => $profile->city,
            'zip_code' => $profile->zip_code,
            'address' => $profile->address,
            'legal_address' => $profile->legal_address,
            'trading_address' => $profile->trading_address,
            'registration_number' => $profile->registration_number,
            'ceo_full_name' => implode('; ', $profile->getCeosForProfile()) ,
            'shareholders' => implode('; ', $profile->getShareholdersForProfile()) ,
            'registration_date' => $profile->registration_date,
            'rate_template_name' => $profile->rateTemplate->name,
        ];
    }

    private function getCsvProfileOperationRow($operation)
    {
        $row = array_fill(0, 25, null);
        $rowOperations = [
            'operation_id' => $operation->operation_id,
            'operation_date' => $operation->created_at->format('d-m-Y'),
            'client_bank_name' => $operation->fromAccount->name ?? null,
            'operation_type' => OperationOperationType::getName($operation->operation_type),
            'incoming_amount' => $operation->amount,
        ];
        return array_merge($row, $rowOperations);
    }

    public function getCsvFile($params)
    {
        $reportRequestModel = new ReportRequestTemporary();
        /** @var ReportRequestTemporary  $reportRequestModel */

        $reportRequestModel->status = ReportStatuses::REPORT_NEW;
        $reportRequestModel->parameters = json_encode($params);
        $reportRequestModel->report_type = ReportTypes::REPORT_CLIENTS;
        $reportRequestModel->save();

        return $reportRequestModel;
    }

    public function generateCsvReport($params, $reportRequestId, $report_type)
    {
        $getClients = $this->getClientsForExcel($params);
        $file = fopen('php://temp', "w");
        fputcsv($file, $this->getCsvHeaders(), ',',' ');
        $getClients->chunk(config('cratos.chunk.report'), function ($profiles) use ($file) {
            foreach ($profiles as $profile) {
                fputcsv($file,  $this->getCsvProfileRow($profile), ',', ' ');
                if ($profile->operations->isNotEmpty()) {
                    foreach ($profile->operations as $operation) {
                        fputcsv($file,  $this->getCsvProfileOperationRow($operation), ',', ' ');
                    }
                }
            }
        });
        rewind($file);
        $output = stream_get_contents($file);
        fclose($file);

        if (!file_exists(storage_path('reports'))) {
            mkdir(storage_path('reports'));
        }

        $link = "reports/{$reportRequestId}_{$report_type}.csv";

        Storage::put($link, $output);

        return ReportStatuses::REPORT_COMPLETE;
    }

    public function changeDefaultRateTemplate($oldRateTemplateId, $rateTemplateTypeClient)
    {
        $defaultRateTemplateId = (new RateTemplatesService())->getDefaultRateTemplateId($rateTemplateTypeClient);
        if ($defaultRateTemplateId) {
            CProfile::where('rate_template_id', $oldRateTemplateId)->chunk(50, function ($profiles) use ($defaultRateTemplateId){
                foreach ($profiles as $profile) {
                    if ($profile->update(['rate_template_id' => $defaultRateTemplateId])){
                        EmailFacade::sendDefaultRateTemplateChangedClient($profile->cUser);
                        ActivityLogFacade::saveLog(
                            LogMessage::USER_RATE_TEMPLATE_WAS_CHANGED,
                            [],
                            LogResult::RESULT_SUCCESS,
                            LogType::TYPE_RATE_TEMPLATE_CHANGED,
                            null,
                            $profile->cUser->id
                        );
                    }
                }
            });
        }
    }

    public function getActiveMerchants(?string $projectId = null)
    {
        $query = CProfile::query()
            ->select(['id', 'company_name', 'profile_id'])
            ->where([
                'status' => CProfileStatuses::STATUS_ACTIVE,
                'account_type' => CProfile::TYPE_CORPORATE,
                'is_merchant' => true,
            ]);

        if ($projectId) {
            $query->whereHas('cUser', function ($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            });
        }
        return $query->get();
    }

    public function updateProfile(CProfile $profile, array $updateData, ?array $beneficialOwners = null, ?array $ceos = null, ?array $shareholders = null)
    {
        $profile->update($updateData);
        if ($profile->account_type == CProfile::TYPE_CORPORATE) {
            if (!empty($beneficialOwners))
                $this->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_BENEFICIAL_OWNER, $beneficialOwners, $profile);
            if (!empty($ceos)) {
                $this->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_CEO, $ceos, $profile);
            }
            if (!empty($shareholders)) {
                $this->updateCorporateProfileCompanyOwners(\App\Enums\CompanyOwners::TYPE_SHAREHOLDERS, $shareholders, $profile);
            }
        }
    }

    public function updateCorporateProfileCompanyOwners(int $type, array $companyOwners, CProfile $cProfile)
    {
        $cProfile->companyOwners()->where('type', $type)->delete();

        foreach ($companyOwners as $owner) {
            sleep(1);
            $companyOwner = new CompanyOwners();
            $companyOwner->fill([
                'c_profile_id' => $cProfile->id,
                'type' => $type,
                'name' => $owner,
            ]);
            $companyOwner->save();
        }
    }

    public function getLimits(CProfile $cProfile)
    {
        $limits = Limit::where('rate_template_id', $cProfile->rate_template_id)
            ->where('level', $cProfile->compliance_level)
            ->first();

        return $limits;
    }

    /**
     * @param $email
     * @return array|mixed|string|null
     */
    protected function getRefToken($email = null)
    {
        if(Cookie::has('ref'))   {
            return Cookie::get('ref');
        }

        $cUserService = app(CUserService::class);
        if($cUserService->hasRegisterDataFromCache($email)) {
            $getData = $cUserService->getRegisterDataFromCache($email);
            return $getData['ref'] ?? null;
        }
        return null;
    }
}
