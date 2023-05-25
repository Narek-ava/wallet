<?php

namespace App\Services;

use App\Models\ReferralPartner;


class ReferralPartnerService
{
    public function getReferralPartnerById($referralPartnerId)
    {
        return ReferralPartner::find($referralPartnerId);
    }

    public function getReferralPartners()
    {
        return ReferralPartner::query()->latest()->get();
    }

    public function storeReferralPartner($data)
    {
        $referralPartner = new ReferralPartner();
        $referralPartner->fill($data);
        $referralPartner->save();
    }

    /**
     * @param $data
     * @return void
     */
    public function updateReferralPartner($data)
    {
        $referralPartner = ReferralPartner::query()->findOrFail($data['partner_id']);
        $referralPartner->update($data);
        $referralPartner->refresh();
    }

    public function existToken($token)
    {
        return ReferralPartner::query()->whereToken($token)->exists();
    }

}
