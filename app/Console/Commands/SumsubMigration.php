<?php


namespace App\Console\Commands;

use App\Models\Cabinet\CProfile;
use App\Models\ComplianceRequest;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use App\Services\SumSubService;

class SumsubMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sumsub:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate applicant data';

    protected $apiUrl;
    protected $appNewToken;
    protected $appOldToken;
    protected $secretNewKey;
    protected $secretOldKey;
    protected $fromClientId;
    protected $sumSubService;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SumSubService $subService)
    {
        $this->apiUrl = 'https://api.sumsub.com';
        $this->appOldToken = config('cratos.sum_sub.old_app_token');
        $this->appNewToken = config('cratos.sum_sub.app_token');
        $this->secretOldKey = config('cratos.sum_sub.old_secret_key');
        $this->secretNewKey = config('cratos.sum_sub.secret_key');
        $this->fromClientId = 'cubios.net_29038';
        $this->sumSubService = $subService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $this->sumsubMigrateRun();
    }

    protected function sumsubMigrateRun()
    {

        CProfile::query()
            ->where('account_type', CProfile::TYPE_INDIVIDUAL)
            ->whereHas('complianceRequest')
            ->where('is_sumsub_migrated', false)
            ->chunk(10, function ($profiles) {
                foreach ($profiles as $profile) {
                    try {
                        $this->migrate($profile);
                    } catch (\Exception $exception) {
                        logger()->error('Import failed' ,[
                            'c_profile_id' => $profile->id,
                            'message' => $exception->getMessage(),
                        ] );
                    }
                }
            });

        CProfile::query()
            ->where('account_type', CProfile::TYPE_CORPORATE)
            ->where('is_sumsub_migrated', false)
            ->whereHas('complianceRequest')
            ->chunk(10, function ($profiles) {
                foreach ($profiles as $profile) {
                    try {
                        $this->migrateCorporate($profile);
                    } catch (\Exception $exception) {
                        logger()->error('Import failed' ,[
                            'c_profile_id' => $profile->id,
                            'message' => $exception->getMessage(),
                        ] );
                    }
                }
            });
    }

    protected function migrate(CProfile $cProfile)
    {
        $profileId = $cProfile->id;
        $applicantData = $this->getApplicantInfoByExternalUserId($profileId);
        $shareTokenData = $this->getShareToken($applicantData['id'], $this->fromClientId);
        $importData = $this->importApplicant($shareTokenData['token'], $profileId, $applicantData['review']['reviewStatus'] == 'completed' ? 'true' : 'false');
        ComplianceRequest::where('c_profile_id', $profileId)->update(['applicant_id' => $importData['id']]);
        $cProfile->update(['is_sumsub_migrated' => true]);

    }

    protected function migrateCorporate(CProfile $cProfile)
    {
        $profileId = $cProfile->id;
        $applicantData = $this->getApplicantInfoByExternalUserId($profileId);
        $shareTokenData = $this->getShareToken($applicantData['id'], $this->fromClientId);
        $importData = $this->importApplicant($shareTokenData['token'], $profileId, $applicantData['review']['reviewStatus'] == 'completed' ? 'true' : 'false');
        ComplianceRequest::where('c_profile_id', $profileId)->update(['applicant_id' => $importData['id']]);
        $cProfile->update(['is_sumsub_migrated' => true]);
        if (!empty($applicantData['info']['companyInfo']['beneficiaries'])) {
            foreach ($applicantData['info']['companyInfo']['beneficiaries'] as $beneficiary) {
                $this->migrateByApplicantId($beneficiary['applicantId']);
            }
        }

    }

    protected function migrateByApplicantId($applicantId)
    {
        $applicantData = $this->getApplicantInfo($applicantId);
        $shareTokenData = $this->getShareToken($applicantData['id'], $this->fromClientId);
        $this->importApplicant($shareTokenData['token'], $applicantData['externalUserId'], $applicantData['review']['reviewStatus'] == 'completed' ? 'true' : 'false');
    }

    protected function getApplicantInfoByExternalUserId($profileId)
    {
        $this->sumSubService->setAppToken($this->appOldToken);
        $this->sumSubService->setSecretKey($this->secretOldKey);
        return $this->sumSubService->getApplicantInfoByExternalUserId($profileId);
    }

    protected function getApplicantInfo($applicantId)
    {
        $this->sumSubService->setAppToken($this->appOldToken);
        $this->sumSubService->setSecretKey($this->secretOldKey);
        return $this->sumSubService->getApplicantInfo($applicantId);
    }

    protected function getShareToken($applicantId, $fromClient)
    {
        $this->sumSubService->setAppToken($this->appOldToken);
        $this->sumSubService->setSecretKey($this->secretOldKey);
        return $this->sumSubService->getShareToken($applicantId, $fromClient);
    }

    protected function importApplicant($shareToken, $profileID, $trustReview)
    {
        $this->sumSubService->setAppToken($this->appNewToken);
        $this->sumSubService->setSecretKey($this->secretNewKey);
        return $this->sumSubService->importApplicant($shareToken, $profileID, $trustReview);
    }
}
