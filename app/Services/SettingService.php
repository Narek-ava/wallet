<?php
namespace App\Services;

use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Str;

class SettingService
{
    public function createSetting($data)
    {
        return Setting::create([
            'id' => Str::uuid()->toString(),
            'key' => $data['key'],
            'content' => $data['content'] ?? '',
            'project_id' => $data['project_id'] ?? null,
            'project_company_details' => $data['project_company_details'] ?? null,
        ]);
    }

    public function findById($id)
    {
        return Setting::find($id);
    }

    public function getSettingByKey($key, ?string $projectId = null)
    {
        $query = Setting::where('key', $key);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return $query->first();
    }

    public function getProjectAddress(?string $projectId = null)
    {
        if ($projectId) {
            $project = Project::find($projectId);
        }
        if (!$project) {
            $project = Project::getCurrentProject();
        }
        $companyDetails = $project->companyDetails;
        $address = '';

        if($companyDetails) {
            if(!empty($companyDetails->name)) {
                $address.= $companyDetails->name . ',<br>';
            }
            if(!empty($companyDetails->companyAddress)) {
                $address.= $companyDetails->companyAddress . ',<br>';
            }
            if(!empty($companyDetails->city)) {
                $address.= $companyDetails->city .',';
            }
            if(!empty($companyDetails->zip_code)) {
                $address.= $companyDetails->zip_code . ',';
            }
            if(!empty($companyDetails->country)) {
                $address.= $companyDetails->country . ',<br>';
            }

        }

        return $address;
    }

    public function getSettingContentByKey($key)
    {
        $setting = Setting::where('key', $key)->first();
        if ($setting) {
            return $setting->content;
        }
        return false;
    }

    public function getSettingsPaginate(?string $projectId = null)
    {
        $query = Setting::query();

        $bUser = auth()->guard('bUser')->user();

        if (!$bUser->is_super_admin) {
            if (!$projectId) {
                $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
                $query->whereIn('project_id', $projectIds);
            } else {
                $query->where('project_id', $projectId);
            }
        }
        return $query->orderBy('created_at', 'desc')->paginate(config('cratos.pagination.settings'));
    }

    public function updateSetting($data)
    {
        $setting = $this->getSettingByKey($data['key']);
        if ($setting) {
            $setting->update([
                'content' => $data['content'],
                'project_id' => $data['project_id']
            ]);
            return true;
        }
        return false;
    }
}
