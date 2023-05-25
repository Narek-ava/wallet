<?php

namespace App\Facades;

use App\Enums\Commissions;
use App\Models\Commission;
use App\Services\CommissionsService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void createPaymentCommissions($account, $name, $request)
 * @method static void updatePaymentCommission($accountId, $request)
 * @method static void createCardCommissions($account, $name, $request)
 * @method static void updateCardCommission($accountId, $request)
 * @method static void createWalletCommissions($account, $name, $request)
 * @method static void updateWalletCommission($accountId, $request)
 * @method static void createLiquiditySepaCommissions($account, $name, $request)
 * @method static void updateLiquiditySepaCommission($accountId, $request)
 * @method static void createLiquidityBtcCommissions($account, $name, $request)
 * @method static void updateLiquidityBtcCommission($accountId, $request)
 * @method static void createRateTemplateCommission($rateTemplateId, $name, $data)
 * @method static void updateRateTemplateCommission($rateTemplateId, $data)
 * @method static float|int calculateCommissionAmount(Commission $commission, $amount)
 * @method static Commission updateBlockChainCommission($commission, $data)
 * @method static Commission updateCommission($commission, $data, $fromTo)
 * @method static Commission createExchangeCommission($fee, $currency, $operationId)
 * @method static Commission|null commissions($rateTemplateId, int $commissionType, string $currency, int $type = Commissions::TYPE_OUTGOING)
 * @method static mixed limits($rateTemplateId, $complianceLevel)
 *
 * @see CommissionsService
 */

class CommissionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CommissionFacade';
    }
}
