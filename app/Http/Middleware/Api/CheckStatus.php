<?php

namespace App\Http\Middleware\Api;

use App\Enums\CProfileStatuses;
use Closure;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $statuses
     * @return mixed
     */
    public function handle($request, Closure $next, string $statuses)
    {
        $statusesArray = explode('-', $statuses);
        if (!auth()->guard('api')->user() || !auth()->guard('api')->user()->cProfile) {
            return response()->json(['error' => t('error_unauthenticated')], 401);
        }
        $status = auth()->guard('api')->user()->cProfile->status;
        if (!in_array($status, $statusesArray)) {
            if (in_array($status, CProfileStatuses::NOT_ALLOWED_TO_ACCESS_SETTINGS_STATUSES)) {
                \C\c_user_api_guard()->logout();
                return response()->json(['email' => t('error_status_banned')], 403);
            }
            return response()->json(['error' => t('error_access_denied')], 403);
        }

        return $next($request);
    }
}
