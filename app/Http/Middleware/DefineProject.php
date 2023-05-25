<?php

namespace App\Http\Middleware;

use App\Enums\ProjectStatuses;
use App\Services\ProjectService;
use App\Services\SettingService;
use Closure;
use Illuminate\Support\Facades\Route;

class DefineProject
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
        /* @var ProjectService $projectService */
        $projectService = resolve(ProjectService::class);

        $exceptionFromRoute = false;
        $checkAllProjects = false;
        try {
            $route = Route::getRoutes()->match($request);
        }catch (\Throwable $exception) {
            $exceptionFromRoute = true;
            $checkAllProjects = $request->ajax();
        }


        $status = null;
        if (!$checkAllProjects && !$exceptionFromRoute) {
            if ($route->getPrefix() !== 'backoffice') {
                $status = ProjectStatuses::STATUS_ACTIVE;
            }
        }

        $project = $projectService->getProjectByDomain($request->getHost(), $status);

        if (!$project) {
            if (!auth()->guest()) {
                if ($route->getPrefix() !== 'backoffice') {
                    abort(404);
                }
            } elseif (auth()->guard('bUser')->user() && !auth()->guard('bUser')->user()->is_super_admin) {
                abort(404);
            }
        }

        if ($project) {
            config()->set('projects.currentProject', $project);
            config()->set('app.name', $project->name);
            config()->set('mail.from.name', $project->name);

            foreach (config('mail.email_providers') as $key => $provider) {
                config()->set('mail.email_providers.' . $key . '.name', $project->name);
            }
        }

        if (!$checkAllProjects && !$exceptionFromRoute) {
            if ($route->getPrefix() !== '/backoffice' && $project) {
                config()->set('cratos.company_details',  (array) $project->companyDetails);
                config()->set('cratos.urls.terms_and_conditions',  $project->companyDetails->terms_and_conditions ?? '');
                config()->set('cratos.urls.aml_policy',  $project->companyDetails->aml_policy ?? '');
                config()->set('cratos.urls.privacy_policy',  $project->companyDetails->privacy_policy ?? '');
                config()->set('cratos.urls.frequently_asked_question',  $project->companyDetails->frequently_asked_question ?? '');
            }
        }

//        setPermissionsTeamId($project->id);
        return $next($request);
    }
}
