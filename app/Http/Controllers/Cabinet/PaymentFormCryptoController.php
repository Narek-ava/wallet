<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\OperationOperationType;
use App\Enums\PaymentFormStatuses;
use App\Enums\TransactionType;
use App\Facades\ActivityLogFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\CryptoToCryptoPayerDataRequest;
use App\Http\Requests\PaymentFormCryptoRequest;
use App\Http\Requests\PaymentFormRequest;
use App\Http\Requests\ValidateChangedAmountsRequest;
use App\Models\PaymentForm;
use App\Models\PaymentFormAttempt;
use App\Models\Project;
use App\Services\AccountService;
use App\Services\BitGOAPIService;
use App\Services\OperationService;
use App\Services\PaymentFormCryptoService;
use App\Services\PaymentFormsService;
use App\Services\ProjectService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentFormCryptoController extends Controller
{
    public function index(PaymentForm $paymentForm, PaymentFormCryptoService $paymentFormCryptoService)
    {
        if ($paymentForm->status !== PaymentFormStatuses::STATUS_ACTIVE) {
            abort(404);
        }
        $merchant = $paymentForm->cProfile;

        $project = $paymentForm->project ?? null;
        /* @var Project $project */

        $this->setProject($project);

        $allowed_crypto_currencies = $paymentForm->allowed_crypto_currencies;
        $cryptoCurrency = in_array(Currency::CURRENCY_BTC, $allowed_crypto_currencies) ? Currency::CURRENCY_BTC : reset($allowed_crypto_currencies);
        $minPaymentAmount = $paymentFormCryptoService->getCryptoPaymentMinAmount($paymentForm, $cryptoCurrency);
        $minPaymentAmountInEuro = KrakenFacade::getRateCryptoFiat($cryptoCurrency, Currency::CURRENCY_EUR, $minPaymentAmount);
        return view('cabinet.payment-forms.cryptoToCrypto.crypto-form', compact('merchant', 'paymentForm', 'minPaymentAmount', 'cryptoCurrency', 'minPaymentAmountInEuro', 'project'));
    }

    public function getMinCryptoPaymentAmount(Request $request, PaymentForm $paymentForm, PaymentFormCryptoService $paymentFormCryptoService)
    {
        if (!$request->cryptocurrency || !in_array($request->cryptocurrency, Currency::getList())) {
            return response()->json(['minAmount' => 0]);
        }

        $minAmount = $paymentFormCryptoService->getCryptoPaymentMinAmount($paymentForm, $request->cryptocurrency);
        $minPaymentAmountInEuro = KrakenFacade::getRateCryptoFiat($request->cryptocurrency, Currency::CURRENCY_EUR, $minAmount);

        return response()->json([
            'minAmount' => generalMoneyFormat($minAmount, $request->cryptocurrency),
            'minAmountInEuro' => $minPaymentAmountInEuro
        ]);
    }

    public function getChangedCryptoPaymentAmount(ValidateChangedAmountsRequest $request, PaymentForm $paymentForm, PaymentFormCryptoService $paymentFormCryptoService)
    {
        $minAmount = $paymentFormCryptoService->getCryptoPaymentMinAmount($paymentForm, $request->cryptocurrency);
        if ($request->fromCryptoToFiat !== 'false') {
            if ($minAmount > $request->amount) {
                return response()->json([
                    'amount' => t('payment_form_lower_amount', ['minAmount' => $minAmount, 'currency' => $request->cryptocurrency]),
                ], 403);
            }
            return response()->json([
                'amount' => $request->amount,
                'amountInEuro' => KrakenFacade::getRateCryptoFiat($request->cryptocurrency, Currency::CURRENCY_EUR, $request->amount)
            ]);
        }

        $rate = KrakenFacade::getRateCryptoFiat($request->cryptocurrency, Currency::CURRENCY_EUR, 1);
        $amount = $request->amount / $rate;
        $minPaymentAmountInEuro = KrakenFacade::getRateCryptoFiat($request->cryptocurrency, Currency::CURRENCY_EUR, $minAmount);

        if ($minPaymentAmountInEuro > $request->amount) {
            return response()->json([
                'amountInEuro' => t('payment_form_lower_amount', ['minAmount' => $minPaymentAmountInEuro, 'currency' => Currency::CURRENCY_EUR]),
            ], 403);
        }
        return response()->json([
            'amount' => generalMoneyFormat($amount, $request->cryptocurrency),
            'amountInEuro' => $request->amount
        ]);
    }

    public function saveInitialData(PaymentForm $paymentForm, PaymentFormCryptoService $paymentFormCryptoService, PaymentFormCryptoRequest $request)
    {
        $paymentFormAttempt = null;
        if (!empty($request->paymentFormAttemptId)) {
            $paymentFormAttempt = $paymentFormCryptoService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        }

        $response = $paymentFormCryptoService->saveInitialDataInAttempt($paymentForm, $request->validated(), $paymentFormAttempt);

        if (!empty($response['error'])) {
            return response()->json($response, 403);
        }

        $paymentFormAttempt = $response['paymentFormAttempt'];

        $calculatedFee = $paymentFormCryptoService->calculateFee($paymentForm, $paymentFormAttempt->amount);

        $amount = $paymentFormAttempt->amount;

        return response()->json([
            'paymentFormAttemptId' => encrypt($paymentFormAttempt->id),
            'total' =>  generalMoneyFormat($amount, $paymentFormAttempt->to_currency, true),
            'details' => $paymentForm->description,
            'fee' => generalMoneyFormat($calculatedFee['feeAmount'], $paymentFormAttempt->to_currency, true)  . ' (' . $calculatedFee['feePercent'] . '%)',
            'amount' => generalMoneyFormat($calculatedFee['leftAmount'], $paymentFormAttempt->to_currency, true),
        ]);
    }

    public function savePayerData(CryptoToCryptoPayerDataRequest $request, PaymentFormCryptoService $paymentFormCryptoService, BitGOAPIService $bitGOAPIService, OperationService $operationService)
    {
        $paymentFormAttempt = $paymentFormCryptoService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            throw (new HttpResponseException(response(t('something_went_wrong'), 403)));
        }

        $paymentFormCryptoService->savePayerData($paymentFormAttempt, $request->validated());

        $merchantWallet =  $paymentFormAttempt->paymentForm->cProfile->accounts()
            ->where('is_external', '!=', AccountType::ACCOUNT_EXTERNAL)
            ->where([
                'account_type' => AccountType::TYPE_CRYPTO,
                'currency' => $paymentFormAttempt->to_currency,
            ])
            ->whereHas('cryptoAccountDetail')->with('cryptoAccountDetail')->first();
        $cryptoAccountDetail = $merchantWallet->cryptoAccountDetail;

        $operation = $operationService->createOperation($merchantWallet->cProfile->id, OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF,
            $paymentFormAttempt->amount, $paymentFormAttempt->to_currency, $paymentFormAttempt->from_currency, null, $cryptoAccountDetail->account->id);

        $operation->payment_form_id = $paymentFormAttempt->payment_form_id;
        $paymentFormAttempt->operation_id = $operation->id;
        $paymentFormAttempt->save();

        $responseJSON = $bitGOAPIService->generateWalletAddress($cryptoAccountDetail->coin, $cryptoAccountDetail->wallet_id, 'New address for #' . $operation->operation_id . ' operation');
        $response = json_decode($responseJSON, true);
        $address = $response['address'] ?? "";
        if ($address) {
            $operation->address = $address;
        }
        $operation->save();

        $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $address;
        return response()->json([
            'qr' => $qr,
            'address' => $address,
            'cryptoCurrency' => $merchantWallet->currency,
            'amount' => $paymentFormAttempt->amount . ' ' . $paymentFormAttempt->to_currency,
            'currencyImage' => Currency::IMAGES[$merchantWallet->currency],
        ]);
    }

    public function checkPayment(Request $request, PaymentFormsService $paymentFormsService, PaymentFormCryptoService $paymentFormCryptoService)

    {
        $paymentFormAttempt = $paymentFormsService->getPaymentFormAttemptById(decrypt($request->paymentFormAttemptId));
        /* @var PaymentFormAttempt $paymentFormAttempt */

        if(!$paymentFormAttempt) {
            logger()->error('PaymentFormAttemptNotFound', ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)]);
            ActivityLogFacade::saveLog(LogMessage::PAYMENT_FORM_REQUEST_NOT_FOUND, ['paymentFormAttemptId' => decrypt($request->paymentFormAttemptId)], LogResult::RESULT_FAILURE, LogType::TYPE_PAYMENT_FORM_REQUEST_NOT_FOUND);
            return response()->json(['error' => t('try_again')], 403);
        }

        $success = $paymentFormCryptoService->checkPayment($paymentFormAttempt);

        $additionalData = [];
        if ($success) {
            $cryptoTransaction = $paymentFormAttempt->operation->getLastTransactionByType(TransactionType::CRYPTO_TRX);
            $credited = $cryptoTransaction->trans_amount ?? 0;

            $feeTransaction = $paymentFormAttempt->operation->getLastTransactionByType(TransactionType::SYSTEM_FEE);
            $feeAmount = $feeTransaction->trans_amount ?? 0;

            $amount = $paymentFormAttempt->operation->amount;

            $additionalData = [
                'amount' => generalMoneyFormat($amount, $paymentFormAttempt->to_currency, true),
                'fee' => generalMoneyFormat($feeAmount, $paymentFormAttempt->to_currency, true),
                'credited' => generalMoneyFormat($credited, $paymentFormAttempt->to_currency, true),
                'link' => $paymentFormAttempt->operation->getCryptoExplorerUrl(),
            ];
        }


        return response()->json(array_merge([
            'success' => $success,
        ], $additionalData));

    }

    private function setProject(Project $project)
    {
        config()->set('projects.currentProject', $project);
        config()->set('app.name', $project->name);
        config()->set('mail.from.name', $project->name);
        foreach (config('mail.email_providers') as $key => $provider) {
            config()->set('mail.email_providers.' . $key . '.name', $project->name);
        }
        config()->set('cratos.company_details',  (array) $project->companyDetails);
        config()->set('cratos.urls.terms_and_conditions',  $project->companyDetails->terms_and_conditions ?? '');
        config()->set('cratos.urls.aml_policy',  $project->companyDetails->aml_policy ?? '');
        config()->set('cratos.urls.privacy_policy',  $project->companyDetails->privacy_policy ?? '');
        config()->set('cratos.urls.frequently_asked_question',  $project->companyDetails->frequently_asked_question ?? '');
        URL::forceRootUrl($project->domainFullPath());
    }
}
