<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReferralLinksRequest;
use App\Http\Requests\ReferralPartnersRequest;
use App\Http\Resources\ReferralLinkResource;
use App\Models\ReferralPartner;
use App\Services\AccountService;
use App\Services\ProviderService;
use App\Services\RateTemplatesService;
use App\Services\ReferralLinkService;
use App\Services\ReferralPartnerService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ReferralLinksController extends Controller
{

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ReferralPartnerService $referralPartnerService, ProviderService $providerService, AccountService $accountService, ReferralLinkService $referralLinkService)
    {
        $referralPartners = $referralPartnerService->getReferralPartners();
        $activeReferralPartnersFirstId = null;
        if ($referralPartners->count()) {
            $activeReferralPartnersFirstId = $referralPartners->first()->id;
        }

        $partnerId = old('referral_partner_id') ?? $activeReferralPartnersFirstId;
        if (session()->has('payment_provider_id')) {
            $partnerId = session()->get('referral_partner_id') ?? $partnerId;
        }

        $referralLinks = $referralLinkService->partnerReferralLinks($partnerId);

        return view('backoffice.referral-links.referral-partner', compact('referralPartners', 'partnerId', 'referralLinks',));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createLink($partner_id, RateTemplatesService $rateTemplatesService)
    {
        $rates = $rateTemplatesService->getRateTemplatesServiceActive();
        return view('backoffice.referral-links.create', compact('rates', 'partner_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReferralLinksRequest $request, ReferralLinkService $referralLinkService)
    {
        $link = $referralLinkService->storeReferralLink($request->validated());

        session()->flash('success', t('referral_partner_successfully_created'));
        return redirect()->route('referral-links.edit', $link);
    }


    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id, ReferralLinkService $referralLinkService, RateTemplatesService $rateTemplatesService)
    {
        $referralLink = $referralLinkService->getReferralLinkById($id);
        $rates = $rateTemplatesService->getRateTemplatesServiceActive();

        return view('backoffice.referral-links.edit', compact('referralLink', 'rates'));
    }

    /**
     * @param $referral_partner
     * @param ReferralPartnersRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($referral_link, ReferralLinksRequest $request, ReferralLinkService $referralLinkService)
    {
        $referralLinkService->updateReferralLink($referral_link, $request->validated());
        session()->flash('success', t('referral_partner_successfully_updated'));
        return redirect()->route('referral-links.index');
    }


    /**
     * @param ReferralPartnersRequest $request
     * @param ReferralPartnerService $referralLinkService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePartner(ReferralPartnersRequest $request, ReferralPartnerService $referralLinkService)
    {
        $referralLinkService->storeReferralPartner($request->validated());
        session()->flash('success', t('referral_partner_successfully_created'));
        return redirect()->route('referral-links.index');
    }

    /**
     * @param ReferralPartnersRequest $request
     * @param ReferralPartnerService $referralLinkService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePartner(ReferralPartnersRequest $request, ReferralPartnerService $referralLinkService)
    {
        $referralLinkService->updateReferralPartner($request->validated());
        session()->flash('success', t('referral_partner_successfully_updated'));
        return redirect()->route('referral-links.index');
    }

    /**
     * @param $id
     * @param ReferralLinkService $referralLinkService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLinksByPartner($id, ReferralLinkService $referralLinkService)
    {
        return response()->json(ReferralLinkResource::collection($referralLinkService->partnerReferralLinks($id)));
    }

    /**
     * @param ReferralPartnerService $referralPartnerService
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getPartners(ReferralPartnerService $referralPartnerService)
    {
        return $referralPartnerService->getReferralPartners();
    }

    public function getPartner($id, ReferralPartnerService $referralPartnerService)
    {
        return $referralPartnerService->getReferralPartnerById($id);
    }
}
