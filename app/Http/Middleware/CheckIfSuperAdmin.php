<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfSuperAdmin
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
        if (!auth()->guard('bUser')->user()->is_super_admin) {
            return redirect()->route('backoffice.profiles', ['type' => 1]);
        }
        return $next($request);
    }
}
