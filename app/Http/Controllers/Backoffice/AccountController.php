<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function getAccount($accountId, AccountService $accountService)
    {
        $account = $accountService->getAccountById($accountId);
        $wireAccountType = $account->accountClientPolicy->toArray();
        return array_merge($account->toArray(), ['wireAccountType' => $wireAccountType]);
    }

    public function changeAccountStatus(Request $request, AccountService $accountService)
    {
        $accountService->changeAccountStatus($request->accountId, $request->status);

        return response()->json([
            'message' => t('provider_status_changed'),
        ]);
    }
}
