<?php


namespace App\Services;


use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Models\Project;
use Illuminate\Database\Query\Expression;

class ProjectService
{

    public function getProjectsByStatus(int $status)
    {
        return Project::query()->where([
            'status' => $status
        ])->get();
    }

    public function getActiveProjectByDomain(string $domain)
    {
        return Project::query()->where([
            'status' => ProjectStatuses::STATUS_ACTIVE,
            'domain' => $domain
        ])->first();
    }

    public function getProjectByDomain(string $domain, ?int $status)
    {
        $query = Project::query()->where([
            'domain' => $domain
        ]);

        if ($status) {
            $query->where('status' ,$status);
        }

        return $query->first();
    }

    /**
     * @param int|null $status
     * @param array|null $projectIds
     * @return array
     */
    public function getProjectIdAndNames(?int $status = null, ?array $projectIds = [])
    {
        $projectsQuery = Project::query();
        if ($status) {
            $projectsQuery->where('status', $status);
        }

        if (!empty($projectIds)) {
            $projectsQuery->whereIn('id', $projectIds);
        }
        return $projectsQuery->pluck('name', 'id')->toArray();
    }

    public function getCardApiSettings(?Project $project)
    {
        if ($project) {
            $providerService = resolve(ProviderService::class);
            /* @var ProviderService $providerService */

            $defaultCardProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD, $project->id);
        }

        return [
            'api' => $defaultCardProvider->api ?? null,
            'api_account' => $defaultCardProvider->api_account ?? null
        ];
    }

    public function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

}
