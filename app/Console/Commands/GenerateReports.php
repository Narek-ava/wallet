<?php

namespace App\Console\Commands;


use App\Enums\OperationOperationType;
use App\Enums\ReportStatuses;
use App\Enums\ReportTypes;
use App\Models\ReportRequestTemporary;
use App\Services\CProfileService;
use App\Services\OperationService;
use Illuminate\Console\Command;

class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-report {request_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checking report request, if have a new request, generate report';

    public int $request_id;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $operationService = app(OperationService::class);
        $cProfileService = app(CProfileService::class);
        $reportRequest = ReportRequestTemporary::query()->where(['status' => ReportStatuses::REPORT_NEW, 'id' => $this->argument('request_id')])->first();

        if(!$reportRequest) {
            return true;
        }

        $reportRequest->update([
            'status' => ReportStatuses::REPORT_PENDING
        ]);
        switch ($reportRequest->report_type){
            case ReportTypes::REPORT_OPERATIONS:
                $operationService->generateCsvReport($reportRequest->params, $reportRequest->id, $reportRequest->report_type);
                break;
            case ReportTypes::REPORT_CLIENTS:
                $cProfileService->generateCsvReport($reportRequest->params, $reportRequest->id, $reportRequest->report_type);
                break;
            case ReportTypes::REPORT_MERCHANT:
                $operationTypes = [OperationOperationType::TYPE_CARD_PF, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF];
                $operationsQuery = $operationService->getFilteredMerchantOperations($reportRequest->params, $operationTypes);
                $operationService->generateCsvFileForMerchantsBackoffice($operationsQuery, $reportRequest->id, $reportRequest->report_type);
                break;
            case ReportTypes::REPORT_OPERATIONS_PDF:
                $operationService->generateOperationReportPdf($reportRequest->params, $reportRequest->id, $reportRequest->report_type);
                break;
        }
        $reportRequest->update([
            'status' => ReportStatuses::REPORT_COMPLETE
        ]);
    }
}
