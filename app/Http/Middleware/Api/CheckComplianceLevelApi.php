<?php

namespace App\Http\Middleware\Api;

use App\Enums\ComplianceLevel;
use App\Enums\CProfileStatuses;
use Closure;

class CheckComplianceLevelApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $cProfile = auth()->guard('api')->user()->cProfile;

        if ($cProfile->status === CProfileStatuses::STATUS_ACTIVE && $cProfile->compliance_level === ComplianceLevel::VERIFICATION_LEVEL_0) {
            return response()->json(['error' => t('ui_update_compliance_level')], 403);
        }

        return $next($request);
    }
}
