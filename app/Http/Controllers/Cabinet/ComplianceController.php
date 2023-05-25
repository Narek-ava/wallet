<?php


namespace App\Http\Controllers\Cabinet;


use App\Enums\ComplianceLevel;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\RateTemplatesStatuses;
use App\Http\Controllers\Controller;
use App\Models\Cabinet\CProfile;
use App\Models\Operation;
use App\Models\Project;
use App\Services\ActivityLogService;
use App\Services\ComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{

    //@TODO middleware for checking access status
    public function index(ComplianceService $complianceService, ActivityLogService $activityLogService)
    {

        $profile = Auth::user()->cProfile;
        /* @var CProfile $profile*/

        $complianceProvider = $complianceService->getComplianceProviderAccount();
        if(!$complianceProvider){
            $complianceData = null;
        } else {
            $complianceData = $complianceService->getCProfileComplianceData($profile);
        }


        $rates = (new \App\Services\RatesService)->cProfileValues($profile);

        $rateTemplate = \auth()->user()->cProfile->rateTemplate()->with(['commissions' => function($qc){
            return $qc->where('is_active', RateTemplatesStatuses::STATUS_ACTIVE)->orderBy('commission_type')->orderBy('type')->orderBy('currency', 'desc');
        }, 'limits' => function($q){
            return $q->orderBy('level');
        }])->first();

        $bankCardRateTemplate = \auth()->user()->project->bankCardRate ?? (new \App\Services\RateTemplatesService())->getActiveBankCardRates();

        return view('cabinet.compliance.index', compact(
            'profile','complianceData', 'rates', 'rateTemplate', 'bankCardRateTemplate', 'complianceProvider'
        ));
    }

    public function requestComplianceLevelChange(Operation $operation, ComplianceService $complianceService, Request $request)
    {
        $cProfile = $operation->cProfile;
        $message = $complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
        return redirect()->route('cabinet.compliance')->with(['success' => $message]);
    }

}
