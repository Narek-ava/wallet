<?php

namespace App\Http\Controllers\Cabinet\History;

use App\Enums\Commissions;
use App\Enums\CommissionType;
use App\Enums\OperationOperationType;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationHistoryRequest;
use App\Models\Commission;
use App\Models\Limit;
use App\Models\Operation;
use App\Services\CommissionsService;
use App\Services\OperationService;
use App\Services\PdfGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function index(Request $request,  OperationService $operationService, CommissionsService $commissionsService)
    {
        if ($request->has('newCardOperationSuccess')) {
            session()->flash('newCardOperationSuccess', $request->get('newCardOperationSuccess'));
            return redirect()->route('cabinet.history');
        }
        $operations = $operationService->getClientOperationsPaginationWithFilter($request);
        $limits = Limit::where('rate_template_id', auth()->user()->cProfile->rate_template_id)
            ->where('level', auth()->user()->cProfile->compliance_level)
            ->first();
        $cryptoAccountDetails = auth()->user()->cProfile->cryptoAccountDetail;
        $profile = auth()->user()->cProfile;

        return view('cabinet.history.index', compact('operations', 'limits', 'cryptoAccountDetails', 'profile'));
    }

    public function downloadTransactionReportPdf(Operation $operation, PdfGeneratorService $pdfGeneratorService)
    {
        return $pdfGeneratorService->getTransactionReportPdf($operation);
    }

    public function downloadHistoryReportPdf(Request $request, OperationService $operationService, PdfGeneratorService $pdfGeneratorService)
    {
        $operations = $operationService->getClientOperationsHistoryWithFilter($request);
        $profile = auth()->user()->cProfile;

        return $pdfGeneratorService->getHistoryReportPdf($operations, $profile, $request->get('from', null), $request->get('to', Carbon::now()->format('d.m.Y')));

    }

    public function showMerchantOperationsCsvFilterPage()
    {
        return view('cabinet.partials.generate-report');
    }

    public function generateCsvForMerchantOperations(Request $request, OperationService $operationService)
    {
        $cProfile = auth()->user()->cProfile;
        $operationTypes = [OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF];
        $operationsQuery = $operationService->getFilteredMerchantPaymentOperations($request, $operationTypes, $cProfile);
        $operationService->getCsvFileForMerchants($operationsQuery);
    }
}
