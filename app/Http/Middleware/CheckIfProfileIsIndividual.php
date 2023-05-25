<?php

namespace App\Http\Middleware;

use App\Models\Cabinet\CProfile;
use Closure;

class CheckIfProfileIsIndividual
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
        if (getCProfile()->account_type === CProfile::TYPE_CORPORATE) {
            return redirect()->route('cabinet.wallets.index');
        }
        return $next($request);
    }
}
