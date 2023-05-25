<?php
namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockWalletRequest;
use App\Http\Requests\UnblockWalletsRequest;
use App\Services\BlockedWalletsService;

class WalletController extends Controller
{
    public function blockWallet(BlockWalletRequest $request, BlockedWalletsService $blockedWalletsService)
    {
        $blockedWalletsService->block($request);
        return redirect()->back();
    }

    public function unblockWallet(UnblockWalletsRequest $request, BlockedWalletsService $blockedWalletsService)
    {
        $blockedWalletsService->unblock($request);
        return redirect()->back();
    }
}
