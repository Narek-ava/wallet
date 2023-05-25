<?php

namespace App\Http\Middleware;

use App\Enums\ComplianceLevel;
use App\Enums\CProfileStatuses;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckEnableFiatWallets
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
        if (!config('cratos.enable_fiat_wallets')) {
            throw new HttpResponseException(response()->json(['error' => 'Forbidden.'], 401));
        }

        return $next($request);
    }
}
