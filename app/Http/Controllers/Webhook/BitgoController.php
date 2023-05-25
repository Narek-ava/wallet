<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\CryptoAccountDetail;
use App\Services\CryptoWebhookService;
use Illuminate\Http\Request;


class BitgoController extends Controller
{

    public function transfer(string $walletId, Request $request, CryptoWebhookService $cryptoWebhookService)
    {
        logger()->info('BitgoWebhookInfo', ['CryptoAccountDetailId' => $walletId, 'data' => $request->all()]);

        if ($request->has('isWallet')) {
            return $cryptoWebhookService->addToQueue(null, $request->all(), $walletId);
        }

        $cryptoAccountDetail = CryptoAccountDetail::find($walletId);
        if (!$cryptoAccountDetail) {
            logger()->error('BitgoWebhookNotFound', compact('walletId'));
            return false;
        }

        return $cryptoWebhookService->addToQueue($walletId, $request->all());

    }

}
