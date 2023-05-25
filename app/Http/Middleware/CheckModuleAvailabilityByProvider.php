<?php

namespace App\Http\Middleware;

use App\Enums\Providers;
use App\Models\Project;
use App\Services\ProviderService;
use Closure;

class CheckModuleAvailabilityByProvider
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, int $type)
    {
        $project = Project::getCurrentProject();
        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);

        $providerExists = $providerService->checkProjectProviderExistsByType($project->id, $type);
        if (!$providerExists) {
            abort(404);
        }

        return $next($request);
    }
}
