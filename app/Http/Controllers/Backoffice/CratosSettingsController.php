<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\CratosSettingRequest;
use App\Services\ProjectService;
use App\Services\SettingService;
use Illuminate\Http\Request;

class CratosSettingsController extends Controller
{
    public function index(Request $request, SettingService $settingService)
    {
        $settings = $settingService->getSettingsPaginate($request->project_id);
        return view('backoffice.settings.cratos.index', compact('settings'));
    }

    public function add(ProjectService $projectService)
    {
        $projectNames = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        return view('backoffice.settings.cratos.add', compact('projectNames'));
    }

    public function create(CratosSettingRequest $request, SettingService $settingService)
    {
        $settingService->createSetting($request->only(['key', 'content', 'project_id']));
        return redirect()->route('cratos.settings');
    }

    public function edit($id, SettingService $settingService, ProjectService $projectService)
    {
        $projectNames = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        $setting = $settingService->findById($id);
        if ($setting) {
            return view('backoffice.settings.cratos.add', compact('setting', 'projectNames'));
        }
        return redirect()->back();
    }

    public function put(CratosSettingRequest $request, SettingService $settingService)
    {
        $updated = $settingService->updateSetting($request->only(['key', 'content', 'project_id']));
        if ($updated) {
            return redirect()->route('cratos.settings');
        } else {
            return redirect()->back()->withMessage('Setting with this message is not valid!');
        }
    }
}
