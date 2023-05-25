<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CratosSandboxController extends Controller
{
    public function index()
    {
        return view('backoffice.cratos-sandbox.cratos_sandbox');
    }
}
