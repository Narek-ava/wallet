<?php

namespace App\Http\Controllers\Webhook;

use App\Exceptions\OperationException;
use App\Http\Controllers\Controller;
use App\Services\TopUpCardService;
use Illuminate\Http\Request;

class TrustPaymentController extends Controller
{
    public function cardTransfer(Request $request)
    {
        logger()->error('TrustPaymentWebhookInfo', ['data' => $request->all()]);

        $topUpCardService = resolve(TopUpCardService::class);
        /* @var TopUpCardService $topUpCardService */
        try {
            return $topUpCardService->handleTransaction($request->get('transactionreference'));
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            $trace = $exception->getTraceAsString();
            logger()->error('TopUpCardError', compact('message', 'trace'));
            return false;
        }
    }

}
