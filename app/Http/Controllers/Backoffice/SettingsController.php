<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Services\ProviderService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function settings(ProviderService $providerService)
    {
        $providers = $providerService->getProvidersActive();
        return view('backoffice.settings.settings', compact('providers'));
    }
}
