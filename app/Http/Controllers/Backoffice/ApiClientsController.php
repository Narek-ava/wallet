<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiClientsRequest;
use App\Models\ApiClient;
use App\Services\ProjectService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApiClientsController extends Controller
{

    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_API_CLIENTS]), ['only' => ['index', 'edit']]);
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_API_CLIENTS]), ['except' => ['index', 'edit','generateToken']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(Request $request, ProjectService $projectService)
    {
        $activeProjects = $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        $bUser = auth()->user();
        $apiClients = ApiClient::query();
        $projectIds = $request->project_id ? [$request->project_id] : $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));

        if (!$bUser->is_super_admin) {
            $apiClients->whereIn('project_id', $projectIds);
        }

        $apiClients = $apiClients->latest()->get();

        return view('backoffice.api-clients.index', compact('apiClients', 'activeProjects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(ProjectService $projectService)
    {
        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.api-clients.create', compact('projectNames'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ApiClientsRequest  $request
     */
    public function store(ApiClientsRequest $request)
    {
        $apiClient = new ApiClient();
        $apiClient->fill([
            'name' => $request->name,
            'key' => $request->key,
            'token' => $request->apiToken,
            'status' => $request->status,
            'project_id' => $request->project_id,
            'access_token_expires_time' => $request->accessTokenExpiresTime,
            'refresh_token_expires_time' => $request->refreshTokenExpiresTime,
        ]);
        $apiClient->save();

        session()->flash('success', t('api_client_successfully_created'));
        return redirect()->route('api-clients.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ApiClient $apiClient
     * @return Application|Factory|View
     */
    public function edit(ApiClient $apiClient, ProjectService $projectService)
    {
        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.api-clients.edit', compact('apiClient', 'projectNames'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ApiClientsRequest  $request
     * @param  string  $api_client
     */
    public function update(string $api_client, ApiClientsRequest $request)
    {
        $apiClient = ApiClient::query()->findOrFail($api_client);

        $apiClient->update([
            'name' => $request->name,
            'key' => $request->key,
            'status' => $request->status,
            'token' => $request->apiToken,
            'project_id' => $request->project_id,
            'access_token_expires_time' => $request->accessTokenExpiresTime,
            'refresh_token_expires_time' => $request->refreshTokenExpiresTime,
        ]);
        $apiClient->refresh();

        session()->flash('success', t('api_client_successfully_updated'));
        return redirect()->route('api-clients.index');
    }

    /**
     * Generate unique token for api client
     */
    public function generateToken()
    {
        $token = Str::random(60);

        while (ApiClient::query()->whereToken($token)->exists()) {
            $token = Str::random(60);
        }

        return response()->json([
            'token' => $token
        ]);
    }
}
