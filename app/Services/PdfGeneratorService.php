<?php
namespace App\Services;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfGeneratorService
{
    /**
     * @param $operation
     * @param $providerAccountId
     * @return string
     */
    public function savePdfDepositFile($operation, $providerAccountId)
    {
        $providerAccount = Account::find($providerAccountId);
        $pdf = \PDF::loadView('cabinet.partials.deposit-pdf', ['operation' => $operation, 'providerAccount' => $providerAccount]);
        header('Set-Cookie: fileLoading=true');
        $output = $pdf->output();

        if(!file_exists(storage_path('pdf'))) {
            mkdir(storage_path('pdf'));
        }
        $link = 'pdf/' . $providerAccountId . '_' . $operation->id . '.pdf';

        Storage::put($link, $output);

        return storage_path('app/' . $link);
    }

    /**
     * @param $operation
     * @param $providerAccountId
     * @return string
     */
    public function saveTransactionReportPdfFile($operation)
    {
        $pdf = \PDF::loadView('cabinet.partials.transaction-report-pdf', ['operation' => $operation]);
        $output = $pdf->output();

        if (!file_exists(storage_path('pdf'))) {
            mkdir(storage_path('pdf'));
        }
        if(!file_exists(storage_path('pdf/operations'))) {
            mkdir(storage_path('pdf/operations'));
        }
        $link = 'pdf/operations/operation_' . $operation->id . '.pdf';

        Storage::put($link, $output);

        return storage_path('app/' . $link);
    }

    public function getPdfDepositFile($operation, $providerAccountId)
    {
        $providerAccount = Account::find($providerAccountId);
        $pdf = \PDF::loadView('cabinet.partials.deposit-pdf', ['operation' => $operation, 'providerAccount' => $providerAccount]);
        header('Set-Cookie: fileLoading=true');
        return $pdf->download($operation->id.'.pdf');
    }

    public function getTransactionReportPdf($operation)
    {
        $pdf = \PDF::loadView('cabinet.partials.transaction-report-pdf', ['operation' => $operation]);
        return $pdf->download($operation->id.'.pdf');
    }

     public function getHistoryReportPdf($operations, $profile, $from, $to, $bUser = false)
    {
        $to = $to ?? Carbon::now()->format('d.m.Y');
        if ($from) {
            $from = Carbon::parse($from)->format('d.m.Y');
        } else {
            $from = $operations->isEmpty() ? Carbon::now()->subMonth()->format('d.m.Y') : Carbon::parse($operations->sortBy('created_at')->first()->created_at)->format('d.m.Y');
        }

        $pdf = \PDF::loadView('cabinet.history.partials.history-list-pdf', ['operations' => $operations, 'profile' => $profile, 'from' => $from, 'to' => $to, 'bUser' => $bUser]);
        $pdf->setPaper('A4', 'landscape');

        $file = $from . '-' . $to;

        return $pdf->download( $file . '.pdf');
    }

    public function generateOperationReportPdf($operations, $params, $reportRequestId, $report_type)
    {
        $to = !empty($params['to']) ? $params['to'] : Carbon::now()->format('d.m.Y');
        if (!empty($params['from'])) {
            $from = Carbon::parse($params['from'])->format('d.m.Y');
        } else {
            $from = $operations->isEmpty() ? Carbon::now()->subMonth()->format('d.m.Y') : Carbon::parse($operations->sortBy('created_at')->first()->created_at)->format('d.m.Y');
        }

        $pdf = \PDF::loadView('cabinet.history.partials.history-list-pdf', ['operations' => $operations, 'profile' => null, 'from' => $from, 'to' => $to, 'bUser' => true]);
        $pdf->setPaper('A4', 'landscape');
        $output = $pdf->output();

        if (!file_exists(storage_path('reports'))) {
            mkdir(storage_path('reports'));
        }

        $link = "reports/{$reportRequestId}_{$report_type}.pdf";

        Storage::put($link, $output);
    }


}
