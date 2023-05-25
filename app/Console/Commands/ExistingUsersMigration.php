<?php


namespace App\Console\Commands;

use App\Models\Cabinet\CProfile;
use App\Models\ComplianceRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Services\SumSubService;

class ExistingUsersMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sumsub:exiting-user-migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate applicant id';

    protected $sumSubService;
    protected $existingUsersId = [
        '27b20dd4-b59f-469f-8c32-6ed7e26058d5',
        '9d5d32fe-e90e-48f4-b8fa-6d1cfdfe036b',
        'aa5c2018-724f-49f3-9ba8-7a1e381e3f58',
        '3f17495e-f9a8-4a6a-8872-27714ae428f6',
        '62937167-d0bb-4494-ac23-c0a8f9850fda',
        '66ee96b6-9324-45f3-848d-300c98854005',
        'db4b115f-e6e4-4a76-9d2c-3bf69b420e9c',
        'e6c22a05-a1f0-42ec-afbe-7877233550b6',
    ];


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SumSubService $subService)
    {
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
            foreach ($this->existingUsersId as $cProfileId) {
                $cProfile = CProfile::find($cProfileId);
                if($cProfile) {
                    $profileId = $cProfile->id;
                    try {
                        $applicantData = $this->sumSubService->getApplicantInfoByExternalUserId($profileId);
                        ComplianceRequest::where('c_profile_id', $profileId)->update(['applicant_id' => $applicantData['id']]);
                        $cProfile->update(['is_sumsub_migrated' => true]);
                    } catch (\Throwable $exception) {
                        logger()->error($exception->getMessage(), compact('profileId'));
                    }

                } else {
                    Log::alert('CProfile not exist: ' . $cProfileId);
                }
            }
    }
}
