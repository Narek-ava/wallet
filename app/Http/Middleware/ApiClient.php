<?php

namespace App\Http\Middleware;

use App\Services\CUserService;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('api-client');

        $apiClient = \App\Models\ApiClient::query()->where([
            'token' => $token
        ])->first();

        if (!$apiClient) {
            throw new HttpResponseException(response()->json(['error' => 'Unauthenticated.'], 401));
        }

        app(CUserService::class)->setTokensExpireTime($apiClient);

        return $next($request);
    }
}
