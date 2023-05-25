<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\ProjectStatuses;
use App\Enums\ReportStatuses;
use App\Enums\ReportTypes;
use App\Http\Controllers\Controller;
use App\Models\ReportRequestTemporary;
use App\Services\CProfileService;
use App\Services\OperationService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ReportController extends Controller
{
    public function index(ProjectService $projectService)
    {
        $projects = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);
        return view('backoffice.reports', compact('projects'));
    }

    public function getOperationsCsv(OperationService $operationService, Request $request)
    {
        $reportRequest = $operationService->getCsvFile($request->only(['from', 'to']));
        return response()->json([
            'reportRequestId' => $reportRequest->id,
            'report' => 'operations-report.csv'
        ]);
    }

    public function getClientsCsv(CProfileService $cProfileService, Request $request)
    {
        $reportRequest = $cProfileService->getCsvFile($request->only(['from', 'to']));
        return response()->json([
            'reportRequestId' => $reportRequest->id,
            'report' => 'client-report.csv'
        ]);
    }

    public function generateCsvForMerchantsOperations(Request $request, OperationService $operationService)
    {
        $reportRequest = $operationService->getCsvFileForMerchantsBackoffice($request->only(['from', 'to']));
        return response()->json([
            'reportRequestId' => $reportRequest->id,
            'report' => 'merchant-operation-report.csv'
        ]);
    }

    public function downloadOperationReportPdf(Request $request, OperationService $operationService)
    {
        $reportRequest = $operationService->getOperationReportPdf($request->except(['_token']));
        return response()->json([
            'reportRequestId' => $reportRequest->id,
            'report' => 'operation-report.pdf'
        ]);
    }


    public function checkStatus(Request $request)
    {
        $reportRequestId = $request->get('reportRequestId');
        $reportRequest = ReportRequestTemporary::find($reportRequestId);
        if(!$reportRequest) {
            return response()->json(['error' => t('report_not_found')], 403);
        }

        if($reportRequest->status == ReportStatuses::REPORT_COMPLETE) {
            $type = ReportTypes::REPORT_TYPES[$reportRequest->report_type];
            $link = storage_path("app/reports/{$reportRequest->id}_{$reportRequest->report_type}.{$type}");
            return response()->download($link);
        } elseif($reportRequest->status == ReportStatuses::REPORT_NEW) {
            try {
                Artisan::call('reports:generate-report ' . $reportRequest->id);
            } catch (\Exception $exception){}
            return response()->json(['status' => 'Pending', 'isPending' => true]);
        } else {
            return response()->json(['status' => 'Pending', 'isPending' => true]);
        }

    }

}

