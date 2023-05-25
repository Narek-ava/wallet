<?php

namespace App\Http\Controllers\Cabinet\Transaction;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\WireExchangeRequest;
use App\Http\Requests\UploadProofRequest;
use App\Models\Cabinet\CProfile;
use App\Models\RatesValues;
use App\Services\AccountService;
use App\Services\BankAccountTemplateService;
use App\Services\ExchangeRequestService;
use App\Services\RatesValueService;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    public function index(ExchangeRequestService $exchangeRequestService)
    {
        $profile = Auth::user()->cProfile;
        $pdfFiles = $exchangeRequestService->getPdfFiles();
        /* @var $profile CProfile */
        $templates = $profile->bankAccountTemplates()->pluck('name', 'id')->toArray();

        return view('cabinet.deposit.request', compact('profile', 'templates', 'pdfFiles'));
    }

    public function downloadPdf(string $filename)
    {
        return \Response::download(public_path('/storage/files/transfer/deposit/' . $filename));
    }

    public function getRateValue($by)
    {
        $key = 'incoming_' . strtolower($by) . '_rate';
        $rateValue = RatesValues::where([
            'key' => $key,
            'level' => Auth::user()->cProfile->compliance_level,
            'rates_category_id' => Auth::user()->cProfile->ratesCategory->id,
        ])->first();
        return $rateValue ? $rateValue->value : 0;
    }

    public function getRateMin($currency, $by)
    {
        $key = 'incoming_' . strtolower($by) . '_' . strtolower($currency) . '_min';
        $rateValue = RatesValues::where([
            'key' => $key,
            'level' => Auth::user()->cProfile->compliance_level,
            'rates_category_id' => Auth::user()->cProfile->ratesCategory->id,
        ])->first();
        return $rateValue ? $rateValue->value : 0;
    }

    public function store(WireExchangeRequest $request,
                          BankAccountTemplateService $bankAccountTemplateService,
                          AccountService $accountService,
                          ExchangeRequestService $exchangeRequestService,
                          RatesValueService $ratesValueService
    )
    {
        $validator = $exchangeRequestService->validateAmountField($ratesValueService, $request->amount);
        if ($validator) {
            return back()->withErrors($validator)->withInput();
        }
        $params = $request->all();
        $bankAccountTemplate = $bankAccountTemplateService->saveTemplate($params);
        $params['from_account'] = $accountService->getAccount($params, $params['currency_from']);
        $params['to_account'] = $accountService->getAccount(['wire_type' => AccountType::TYPE_CRYPTO], $params['currency_to']);
        $exchangeRequestId = $exchangeRequestService->createWire($params);

        return redirect()->route('deposit.show.exchange.request', ['exchangeRequestId' => $exchangeRequestId, 'bankAccountTemplateId' => $bankAccountTemplate->id]);

    }

    public function showExchangeRequest(
        ExchangeRequestService $exchangeRequestService,
        BankAccountTemplateService $bankAccountTemplateService,
        RatesValueService $ratesValueService,
        $exchangeRequestId,
        $bankAccountTemplateId
    )
    {
        $exchangeRequest = $exchangeRequestService->getExchangeRequest($exchangeRequestId);
        $bankAccountTemplate = $bankAccountTemplateService->getBankAccountTemplate($bankAccountTemplateId);
        $cProfile = Auth::user()->cProfile;
        $ratesValueRateValue = $ratesValueService->getRateValueRate($cProfile->rates_category_id, $cProfile->compliance_level, \App\Enums\WireType::NAMES[$bankAccountTemplate->type]);
        $ratesValueRateMonthLimitValue = $ratesValueService->getRateValueMonthLimit($cProfile->rates_category_id, $cProfile->compliance_level);
        $ratesValueRateLimitValue = $ratesValueService->getRateValueLimit($cProfile->rates_category_id, $cProfile->compliance_level, \App\Enums\WireType::NAMES[$bankAccountTemplate->type], \App\Enums\Currency::FIAT_CURRENCY_NAMES[$exchangeRequest->trans_currency]);
        return view('cabinet.deposit.exchange-request', compact(
            'exchangeRequest',
            'bankAccountTemplate',
            'ratesValueRateValue',
            'ratesValueRateMonthLimitValue',
            'ratesValueRateLimitValue')
        );
    }

    public function uploadProof(ExchangeRequestService $exchangeRequestService, UploadProofRequest $request, $fileName)
    {
        $exchangeRequestService->storeProofDocument($request->file('proof'), $fileName);
        return back()->with('success-message','File uploaded successful');
    }

    public function setStatus(ExchangeRequestService $exchangeRequestService, $id, $status)
    {
        $exchangeRequestService->updateStatus($id, $status);
        return back();
    }

    public function getTransactionsMonthLimit(RatesValueService $ratesValueService)
    {
        $cProfile = Auth::user()->cProfile;
        return $ratesValueService->getRateValueMonthLimit($cProfile->rates_category_id, $cProfile->compliance_level);
    }

}
