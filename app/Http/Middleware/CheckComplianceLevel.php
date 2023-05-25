<?php

namespace App\Http\Middleware;

use App\Enums\ComplianceLevel;
use App\Enums\CProfileStatuses;
use Closure;

class CheckComplianceLevel
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
        $cProfile = auth()->guard('cUser')->user()->cProfile;

        if ($cProfile->status === CProfileStatuses::STATUS_ACTIVE && $cProfile->compliance_level === ComplianceLevel::VERIFICATION_LEVEL_0) {
            return redirect()->route('cabinet.compliance')->withInput()->withErrors(['level' => t('ui_update_compliance_level')]);
        }

        return $next($request);
    }
}
