<?php

namespace App\Http\Controllers\Backoffice;

use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class KrakenController extends Controller
{
    public function getTicker(Request $request)
    {
        config()->set('projects.project', Project::find($request->projectId));
        return KrakenFacade::ticker($request->crypto, $request->fiat);
    }
}
