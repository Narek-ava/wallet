<?php

namespace App\Services;

use App\Models\ReferralLink;
use App\Models\ReferralPartner;


class ReferralLinkService
{

    /**
     * @param $partnerId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function partnerReferralLinks($partnerId)
    {
        return ReferralLink::query()->where('partner_id', $partnerId)->get();
    }

    /**
     * @param $data
     * @return ReferralLink
     */
    public function storeReferralLink($data)
    {
        $referralLink = new ReferralLink();
        $referralLink->fill($data);
        $referralLink->save();
        return $referralLink;
    }

    /**
     * @param $id
     * @return \App\Models\BaseModel
     */
    public function getReferralLinkById($id)
    {
        return ReferralLink::find($id);
    }

    /**
     * @param $referral_link
     * @param $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function updateReferralLink($referral_link, $data)
    {
        $referralLink = ReferralLink::query()->findOrFail($referral_link);
        $referralLink->update($data);
        $referralLink->refresh();

        return $referralLink;
    }

}
