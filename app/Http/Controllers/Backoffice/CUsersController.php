<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\CProfileStatuses;
use App\Enums\OperationOperationType;
use App\Models\Cabinet\CProfile;
use App\Services\CProfileService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CUsersController extends Controller
{
         /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getCsv(CProfileService $cProfileService, Request $request)
    {
        $cProfileService->getCsvFile($request->only(['from', 'to', 'project']));
    }
}
