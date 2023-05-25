<?php


namespace App\Enums;


class WallesterCardBlockTypes extends Enum
{
    const BLOCKED_BY_CARDHOLDER = "BlockedByCardholder";
    const BLOCKED_BY_CARDHOLDER_VIA_PHONE = "BlockedByCardholderViaPhone";
    const BLOCKED_BY_CLIENT = "BlockedByClient";
    const BLOCKED_BY_ISSUER = "BlockedByIssuer";
    const COUNTERFEIT = "Counterfeit";
    const FRAUDULENT = "Fraudulent";
    const LOST = "Lost";
    const NOT_DELIVERED = "NotDelivered";
    const STOLEN = "Stolen";

}
