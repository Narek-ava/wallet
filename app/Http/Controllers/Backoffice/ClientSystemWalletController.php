<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientSystemWalletRequest;
use App\Models\ClientSystemWallet;
use App\Models\CryptoAccountDetail;
use App\Services\BitGOAPIService;
use App\Services\ClientSystemWalletService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ClientSystemWalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::EDIT_CLIENT_WALLETS]), ['except' => ['index']]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, ProjectService $projectService, ClientSystemWalletService $clientSystemWalletService)
    {
        $bUser = auth()->guard('bUser')->user();

        if ($request->project_id) {
            $projectIds = [$request->project_id];
        }

        if (!$bUser->is_super_admin && empty($projectIds)) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
        }

        $clientWallets = $clientSystemWalletService->getClientSystemWalletsForProjects($projectIds ?? null);
        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.client-system-wallets.index', compact('clientWallets', 'projectNames'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('backoffice.client-system-wallets.create');
    }

    /**
     * @param ClientSystemWalletRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store($project_id, ClientSystemWalletRequest $request)
    {
        $clientSystemWallet = new ClientSystemWallet();
        $clientSystemWallet->fill([
            'wallet_id' => $request->walletId,
            'currency' => $request->currency,
            'project_id' => $project_id,
        ]);
        if ($request->passphrase) {
            $clientSystemWallet->passphrase = Crypt::encrypt($request->passphrase);
        }
        $clientSystemWallet->save();

        session()->flash('success', t('client_system_wallet_successfully_created'));
        return redirect()->route('projects.edit', $project_id);
    }


    /**
     * @param $client_wallet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($client_wallet)
    {
        $clientWallet = ClientSystemWallet::findOrFail($client_wallet);
        return view('backoffice.client-system-wallets.edit', compact('clientWallet'));
    }

    /**
     * @param ClientSystemWalletRequest $request
     * @param string $client_wallet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ClientSystemWalletRequest $request, string $client_wallet, BitGOAPIService $bitGOAPIService)
    {
        $clientWallet = ClientSystemWallet::findOrFail($client_wallet);

        $clientWallet->currency = $request->currency;

        $isWalletChanged = $request->walletId != $clientWallet->wallet_id;
        if ($isWalletChanged) {
            $clientWallet->wallet_id = $request->walletId;
            $url = route('webhook.bitgo.transfer', ['walletId' => $request->walletId, 'isWallet' => 1], true);
            if (config('app.env') != 'local') {
                sleep(1);
                $bitGOAPIService->addWalletWebhook($request->currency, $request->walletId, 'transfer', $url);
            }
        }

        if ($request->passphrase) {
            $clientWallet->passphrase = Crypt::encrypt($request->passphrase);
            if (!$isWalletChanged) {
                CryptoAccountDetail::query()
                    ->where('wallet_id', $clientWallet->wallet_id)
                    ->update([
                        'passphrase' => $clientWallet->passphrase
                    ]);
            }
        }
        $clientWallet->save();

        session()->flash('success', t('client_system_wallet_successfully_updated'));
        return redirect()->route('projects.edit', $clientWallet->project->id ?? null);
    }

    public function regenerateWalletWebhook(ClientSystemWallet $clientSystemWallet, BitGOAPIService $bitGOAPIService)
    {
        if (config('app.env') != 'local') {
            $webhooks = $bitGOAPIService->listWalletWebhooks($clientSystemWallet->currency, $clientSystemWallet->wallet_id);
            foreach ($webhooks['webhooks'] as $webhook) {
                $bitGOAPIService->removeWalletWebhook($clientSystemWallet->currency, $clientSystemWallet->wallet_id, [
                    'id' => $webhook['id'],
                    'type' => $webhook['type'],
                    'url' => $webhook['url'],
                ]);
                sleep(1);
            }
            $url = route('webhook.bitgo.transfer', ['walletId' => $clientSystemWallet->wallet_id, 'isWallet' => 1], true);
            $bitGOAPIService->addWalletWebhook($clientSystemWallet->currency, $clientSystemWallet->wallet_id, 'transfer', $url);
        }

        session()->flash('success', t('webhook_regenerate_success', ['currency' => $clientSystemWallet->currency]));
        return redirect()->route('client-wallets.index');
    }
}
