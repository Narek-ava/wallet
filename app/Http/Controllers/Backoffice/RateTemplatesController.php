<?php
namespace App\Http\Controllers\Backoffice;

use App\Enums\RateTemplatesStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankCardRateTemplateRequest;
use App\Http\Requests\CreateRateTemplateRequest;
use App\Services\CommissionsService;
use App\Services\CProfileService;
use App\Services\LimitsService;
use App\Services\RateTemplateCountriesService;
use App\Services\RateTemplatesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RateTemplatesController extends Controller
{
    public function index(RateTemplatesService $rateTemplatesService, Request $request)
    {
        if ($request->get('part') == 'all') {
            $rateTemplates = $rateTemplatesService->getRateTemplatesServiceAll();
        } else {
            $rateTemplates = $rateTemplatesService->getRateTemplatesServiceActivePaginate();
        }
        $bankCardRateTemplates = $rateTemplatesService->getBankCardRateTemplatesServiceActive();
        return view('backoffice.rate-templates.index', compact('rateTemplates', 'bankCardRateTemplates'));
    }

    public function store(CreateRateTemplateRequest $request,
                          RateTemplatesService $rateTemplatesService,
                          CommissionsService $commissionsService,
                          LimitsService $limitsService,
                          RateTemplateCountriesService $rateTemplateCountriesService)
    {
        if ($request->makeCopy) {
            $request->offsetUnset('name');
            $request->offsetUnset('is_default');
            $request->offsetSet('name', $request->copyName);
            $request->offsetSet('status', RateTemplatesStatuses::STATUS_ACTIVE);
        }
        $rateTemplateData = $request->only(['name', 'status', 'is_default', 'type_client', 'opening', 'maintenance', 'account_closure', 'referral_remuneration']);
        $rateTemplateId = $rateTemplatesService->store($rateTemplateData, $request->countries);
        $rateTemplateCountriesService->createCountries($rateTemplateId, $request->countries);
        $commissionsService->createRateTemplateCommission($rateTemplateId, 'name',  $request->only(['percent_commission', 'fixed_commission', 'min_commission', 'max_commission', 'min_amount', 'refund_transfer_percent', 'refund_transfer', 'refund_minimum_fee', 'blockchain_fee']));
        $limitsService->createClientRateLimits($rateTemplateId, $request->all(['transaction_amount_max', 'monthly_amount_max']));
        return redirect()->back()->with('success', t('rate_template_add_success'));
    }

    public function getUserProfileIdsArray($accountType, CProfileService $cProfileService)
    {
        return $cProfileService->getProfilesTyped($accountType);
    }

    public function getRateTemplate($id, RateTemplatesService $rateTemplatesService)
    {
        return $rateTemplatesService->getRateTemplateById($id);
    }

    public function getRateTemplateCountries($id, RateTemplatesService $rateTemplatesService)
    {
        return $rateTemplatesService->getRateTemplateCountriesData($id);
    }

    public function putRateTemplate(CreateRateTemplateRequest $request,
                                    RateTemplatesService $rateTemplatesService,
                                    RateTemplateCountriesService $rateTemplateCountriesService,
                                    LimitsService $limitsService,
                                    CommissionsService $commissionsService,
                                    CProfileService $cProfileService)
    {
        try {
            $rateTemplate = $rateTemplatesService->getRateTemplateById($request->rate_template_id);
            if ($rateTemplate->is_default && $request->status == RateTemplatesStatuses::STATUS_DISABLED) {
                $customValidator = Validator::make([], []);
                $customValidator->getMessageBag()->add('status', t('ui_rate_template_status_change'));
                return redirect()->back()->withErrors($customValidator)->withInput();
            }
            $rateTemplateData = $request->only(['name', 'status', 'is_default', 'type_client', 'opening', 'maintenance', 'account_closure', 'referral_remuneration']);
            if($request->is_default != null) {
                $rateTemplatesService->changeDefaultRateTemplate($rateTemplate->type_client);
                $rateTemplateData['is_default'] = true;
            }
            if($rateTemplate->update($rateTemplateData) && $request->status == RateTemplatesStatuses::STATUS_DISABLED) {
                $cProfileService->changeDefaultRateTemplate($rateTemplate->id, $rateTemplate->type_client);
            }
            $rateTemplateCountriesService->createCountries($rateTemplate->id, $request->countries);
            $commissionsService->updateRateTemplateCommission($rateTemplate->id,  $request->only(['percent_commission', 'fixed_commission', 'min_commission', 'max_commission', 'min_amount', 'refund_transfer_percent', 'refund_transfer', 'refund_minimum_fee', 'blockchain_fee']));
            $limitsService->updateRateTemplateLimits($rateTemplate->id, $request->all(['transaction_amount_max', 'monthly_amount_max']));
            return redirect()->back()->with('success', t('rate_template_add_success'));
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', $e->getMessage());
        }
    }

    public function getRateTemplatesPart($part, RateTemplatesService $rateTemplatesService)
    {
        if ($part === 'all') {
            return $rateTemplatesService->getRateTemplatesServiceAll();
        } else {
            return $rateTemplatesService->getRateTemplatesServiceActive();
        }
    }

    public function changeProfileRateTemplate(Request $request, RateTemplatesService $rateTemplatesService, CProfileService $cProfileService)
    {
        return $cProfileService->changeProfileRateTemplate($request->rateTemplateId, $request->profileId);
    }

    public function storeBankCardRateTemplate(BankCardRateTemplateRequest $request, RateTemplatesService $rateTemplatesService)
    {
        $requestData = $request->validated();
        $rateTemplatesService->storeBankCardRateTemplate($requestData);
        return redirect()->back()->with('success', t('wallester_bank_card_add_successfully'));
    }

    public function getBankCardRateTemplate($id, RateTemplatesService $rateTemplatesService)
    {
        return  response()->json(['template' => $rateTemplatesService->getBankCardRateTemplateById($id)]) ;
    }

    public function updateBankCardRateTemplate(BankCardRateTemplateRequest $request, RateTemplatesService $rateTemplatesService)
    {
        $requestData = $request->validated();
        $rateTemplatesService->updateBankCardRateTemplate($requestData);
        return redirect()->back()->with('success', t('wallester_bank_card_add_successfully'));
    }

}
