<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WhalexSandboxController extends Controller
{
    public function index()
    {
        return view('backoffice.whalex-sandbox.whalex_sandbox');
    }
}
