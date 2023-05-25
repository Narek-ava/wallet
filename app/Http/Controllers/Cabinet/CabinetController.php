<?php


namespace App\Http\Controllers\Cabinet;


use App\Http\Controllers\Controller;
use App\Services\OperationService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;

class CabinetController extends Controller
{

    public function download($operationId,
                             PdfGeneratorService $pdfGeneratorService,
                             OperationService $operationService)
    {
        $operation = $operationService->getOperationById($operationId);
        if ($operation) {
            $providerAccount = $operation->providerAccount->wire ? $operation->provider_account_id :
                (isset($operation->providerAccount->parentAccount->wire) ? $operation->providerAccount->parentAccount->id : null);

            if ($providerAccount) {
                return $pdfGeneratorService->getPdfDepositFile($operation, $providerAccount);
            }
        }
        return 'No file';
    }

    public function dashboard()
    {
        return redirect()->route('cabinet.wallets.index');
    }

    public function exchange()
    {
        return view('cabinet.exchange');
    }

    public function deposit()
    {
        return view('cabinet.deposit');
    }

    public function verifyNotification(Request $request)
    {
        verifyNotification($request->id);
        return redirect()->back();
    }

    public function getNotification()
    {
        return getNotificationPartial();
    }

}
