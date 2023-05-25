<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\PaymentFormTypes;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\CreatePaymentCryptoFormRequest;
use App\Http\Requests\Backoffice\CreatePaymentFormRequest;
use App\Http\Requests\Backoffice\UpdateCryptoPaymentFormRequest;
use App\Http\Requests\Backoffice\UpdatePaymentFormRequest;
use App\Http\Resources\Backoffice\PaymentFormResource;
use App\Models\Cabinet\CProfile;
use App\Models\PaymentForm;
use App\Operations\AmountCalculators\CryptoToCryptoCalculator;
use App\Models\Project;
use App\Services\CommissionsService;
use App\Services\ComplianceService;
use App\Services\CProfileService;
use App\Services\OperationService;
use App\Services\PaymentFormsService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\RateTemplatesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class PaymentFormController extends Controller
{
    public function index(Request $request, PaymentFormsService $paymentFormsService, RateTemplatesService $rateTemplatesService, ProjectService $projectService)
    {
        $activeProjects = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);
        $currentProject = Project::getCurrentProject();

        $merchantForms = $paymentFormsService->getPaymentForms($request->get('status'), $request->get('paymentFormType'), $request->project_id);

        $merchantCryptoForms = $paymentFormsService->getPaymentForms($request->get('status_crypto'), PaymentFormTypes::TYPE_CRYPTO_TO_CRYPTO_FORM, $request->project_id);
        $availableIndividualRates = $rateTemplatesService->getActiveRatesByAccountType(CProfile::TYPE_INDIVIDUAL);

        $availablePaymentTypes = PaymentFormTypes::getList();
        $availableKYCOptions = $paymentFormsService->getKYCTypes();

        return view('backoffice.payment-form.payment-form', compact('merchantForms',
            'availableIndividualRates', 'availablePaymentTypes',
            'availableKYCOptions', 'merchantCryptoForms', 'activeProjects', 'currentProject'));
    }

    public function create(CreatePaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $projectId = $request->paymentFormProject;

        $cardProvider = \App\Models\PaymentProvider::find($request->paymentFormCardProvider);
        $cardProjects = $cardProvider->projects()->pluck('projects.id')->toArray();

        $walletProvider = \App\Models\PaymentProvider::find($request->paymentFormWalletProvider);
        $walletProjects = $walletProvider->projects()->pluck('projects.id')->toArray();

        $liquidityProvider = \App\Models\PaymentProvider::find($request->paymentFormLiquidityProvider);
        $liquidityProjects = $liquidityProvider->projects()->pluck('projects.id')->toArray();


        if (in_array($request->paymentFormType, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rate = \App\Models\RateTemplate::find($request->paymentFormRate);
            if (!$rate) {
                return response()->json([
                    'errors' => [
                        'paymentFormRate' => t('is_not_from_selected_project', ['name' => 'Rate'])
                    ]
                ], 422);
            }
        } else {
            $merchant = CProfile::query()
                ->where([
                    'id' => $request->paymentFormMerchant,
                ])->whereHas('cUser', function ($q) use ($projectId) {
                    return $q->where('project_id', $projectId);
                })->first();
            if (!$merchant) {
                return response()->json([
                    'errors' => [
                        'paymentFormMerchant' => t('is_not_from_selected_project', ['name' => 'Merchant'])
                    ]
                ], 422);
            }

        }

        $projects = array_intersect($cardProjects, $walletProjects, $liquidityProjects);

        if (in_array($projectId, $projects)) {
            $paymentFormsService->createPaymentForm($request->validated());

            session()->flash('success',  t('payment_form_create_success'));
            return response()->json(['success' => true]);
        }

        return response()->json([
            'errors' => [
                'paymentFormCardProvider' => t('is_not_from_selected_project', ['name' => 'Card Provider']),
                'paymentFormWalletProvider' => t('is_not_from_selected_project', ['name' => 'Wallet Provider']),
                'paymentFormLiquidityProvider' => t('is_not_from_selected_project', ['name' => 'Liquidity Provider']),
            ]
        ], 422);
    }

    public function getPaymentForm(PaymentForm $paymentForm,  ProviderService $providerService)
    {
        $cardProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD, $paymentForm->project_id);
        $walletProviders = $providerService->getProvidersActive(Providers::PROVIDER_WALLET, $paymentForm->project_id);
        $liquidityProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY, $paymentForm->project_id);
        return View::make('backoffice.payment-form._create-payment-form', compact('paymentForm', 'cardProviders', 'liquidityProviders', 'walletProviders'));
    }

    public function update(PaymentForm $paymentForm, UpdatePaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $projectId = $request->paymentFormProject;

        $cardProvider = \App\Models\PaymentProvider::find($request->paymentFormCardProvider);
        $cardProjects = $cardProvider->projects()->pluck('projects.id')->toArray();

        $walletProvider = \App\Models\PaymentProvider::find($request->paymentFormWalletProvider);
        $walletProjects = $walletProvider->projects()->pluck('projects.id')->toArray();

        $liquidityProvider = \App\Models\PaymentProvider::find($request->paymentFormLiquidityProvider);
        $liquidityProjects = $liquidityProvider->projects()->pluck('projects.id')->toArray();


        if (in_array($request->paymentFormType, PaymentFormTypes::CLIENT_PAYMENT_FORMS)) {
            $rate = \App\Models\RateTemplate::find($request->paymentFormRate);
            if (!$rate) {
                return response()->json([
                    'errors' => [
                        'paymentFormRate' => t('is_not_from_selected_project', ['name' => 'Rate'])
                    ]
                ], 422);
            }
        } else {
            $merchant = CProfile::query()
                ->where([
                    'id' => $request->paymentFormMerchant,
                ])->whereHas('cUser', function ($q) use ($projectId) {
                    return $q->where('project_id', $projectId);
                })->first();
            if (!$merchant) {
                return response()->json([
                    'errors' => [
                        'paymentFormMerchant' => t('is_not_from_selected_project', ['name' => 'Merchant'])
                    ]
                ], 422);
            }

        }

        $projects = array_intersect($cardProjects, $walletProjects, $liquidityProjects);

        if (in_array($projectId, $projects)) {
            $response = $paymentFormsService->updatePaymentForm($paymentForm, $request->validated());

            if (!empty($response['error'])) {
                return response()->json($response['error'], 422);
            }

            session()->flash('success',  t('payment_form_update_success'));
            return response()->json(['success' => true]);
        }

        return response()->json([
            'errors' => [
                'paymentFormCardProvider' => t('is_not_from_selected_project', ['name' => 'Card Provider']),
                'paymentFormWalletProvider' => t('is_not_from_selected_project', ['name' => 'Wallet Provider']),
                'paymentFormLiquidityProvider' => t('is_not_from_selected_project', ['name' => 'Liquidity Provider']),
            ]
        ], 422);

    }


    public function getForm(PaymentForm $paymentForm): JsonResponse
    {
        return response()->json(new PaymentFormResource($paymentForm));
    }

    public function createCryptoForm(CreatePaymentCryptoFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $projectId = $request->paymentFormProject;

        $cardProvider = \App\Models\PaymentProvider::find($request->paymentFormCardProvider);
        $cardProjects = $cardProvider->projects()->pluck('projects.id')->toArray();

        $walletProvider = \App\Models\PaymentProvider::find($request->paymentFormWalletProvider);
        $walletProjects = $walletProvider->projects()->pluck('projects.id')->toArray();

        $liquidityProvider = \App\Models\PaymentProvider::find($request->paymentFormLiquidityProvider);
        $liquidityProjects = $liquidityProvider->projects()->pluck('projects.id')->toArray();

        $merchant = CProfile::query()
            ->where([
                'id' => $request->paymentFormMerchant,
            ])->whereHas('cUser', function ($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })->first();
        if (!$merchant) {
            return response()->json([
                'errors' => [
                    'paymentFormMerchant' => t('is_not_from_selected_project', ['name' => 'Merchant'])
                ]
            ], 422);
        }
        $projects = array_intersect($cardProjects, $walletProjects, $liquidityProjects);

        if (in_array($projectId, $projects)) {
            $paymentFormsService->createPaymentFormCrypto($request->validated());

            session()->flash('success',  t('payment_form_create_success'));
            return response()->json(['success' => true]);
        }

        return response()->json([
            'errors' => [
                'paymentFormCardProvider' => t('is_not_from_selected_project', ['name' => 'Card Provider']),
                'paymentFormWalletProvider' => t('is_not_from_selected_project', ['name' => 'Wallet Provider']),
                'paymentFormLiquidityProvider' => t('is_not_from_selected_project', ['name' => 'Liquidity Provider']),
            ]
        ], 422);

    }

    public function updateCryptoForm(PaymentForm $paymentForm, UpdateCryptoPaymentFormRequest $request, PaymentFormsService $paymentFormsService)
    {
        $projectId = $request->paymentFormProject;

        $walletProvider = \App\Models\PaymentProvider::find($request->paymentFormWalletProvider);
        $walletProjects = $walletProvider->projects()->pluck('projects.id')->toArray();

         $merchant = CProfile::query()
            ->where([
                'id' => $request->paymentFormMerchant,
            ])->whereHas('cUser', function ($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })->first();
        if (!$merchant) {
            return response()->json([
                'errors' => [
                    'paymentFormMerchant' => t('is_not_from_selected_project', ['name' => 'Merchant'])
                ]
            ], 422);
        }

        if (in_array($projectId, $walletProjects)) {
            $response = $paymentFormsService->updateCryptoPaymentForm($paymentForm, $request->validated());

            if (!empty($response['error'])) {
                return response()->json($response['error'], 422);
            }

            session()->flash('success', t('payment_form_update_success'));
            return response()->json(['success' => true]);
        }

        return response()->json([
            'errors' => [
                'paymentFormCardProvider' => t('is_not_from_selected_project', ['name' => 'Card Provider']),
                'paymentFormWalletProvider' => t('is_not_from_selected_project', ['name' => 'Wallet Provider']),
                'paymentFormLiquidityProvider' => t('is_not_from_selected_project', ['name' => 'Liquidity Provider']),
            ]
        ], 422);

    }

    /**
     * @param $id
     * @param Request $request
     * @param OperationService $operationService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */
    public function showPaymentFormTransactions($id, Request $request,OperationService $operationService, ProjectService $projectService)
    {

        if($request->has('payment_form_id')) {
            return $operationService->generateCryptoToCryptoMerchantReport($request->all());
        }
        $operationsPending = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::PENDING, null, $id);
        $operationsSuccessful = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::SUCCESSFUL, null, $id);
        $operationsDeclined = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::DECLINED, null, $id);
        $operationsReturned = $operationService->getOperationsByFilterPaginate($request, OperationStatuses::RETURNED, null, $id);

        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.transactions.index', array_merge(['paymentFormId' => $id] , compact(
            'operationsPending',
            'operationsSuccessful',
            'operationsDeclined',
            'operationsReturned',
            'projectNames'
        )));
    }

    public function showCryptoToCryptoOperation($id, CommissionsService $commissionsService, OperationService $operationService, PaymentFormsService $paymentFormsService)
    {
        $operation = $operationService->getOperationById($id);

        $allowedMaxAmount = $operation->calculateOperationMaxAmount();
        $cProfile = $operation->cProfile;
        $accounts = $cProfile->accounts;
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);
        $nextComplianceLevels = (new ComplianceService())->getNextComplianceLevels($cProfile);
        $operationIds = $cProfile->operations()->pluck('id');

        if ($operationIds) {
            $receivedAmountForCurrentMonth = $operationService->getCurrentMonthOperationsAmountSum($cProfile);
        } else {
            $receivedAmountForCurrentMonth = 0;
        }

        //get limits of transaction
        $complianceLevel = $paymentFormsService->getComplianceLevel($cProfile);
        $limits = $commissionsService->limits($cProfile->rate_template_id, $complianceLevel);

        if (!$limits) {
            // @todo correct error view
            return response()->json([
                'message' => 'failed'
            ]);
        } else {
            $availableAmountForMonth = $limits->monthly_amount_max - $receivedAmountForCurrentMonth;
            if ($availableAmountForMonth < 0) {
                $availableAmountForMonth = 0;
            }
        }

        $passCompliance = $operation->isLimitsVerified($complianceLevel);

        $fromWallet = $operation->fromAccount->cryptoAccountDetail ?? null;
        $pendingCryptoTransaction = $operation->pendingCrypto();

        $txTransactionLink = $operation->getCryptoExplorerUrl();
        $steps = $operation->stepInfo();

        $isCryptoToCryptoPF = $operation->operation_type == OperationOperationType::TYPE_CRYPTO_TO_CRYPTO_PF;
        $payerDetails = $operation->merchantOperationsInformation;
        $operationCalculator = new CryptoToCryptoCalculator($operation);

        return view('backoffice.topup-crypto-pf.show')->with([
            'allowedMaxAmount' => $allowedMaxAmount,
            'operation' => $operation,
            'accounts' => $accounts,
            'transactions' => $transactions,
            'cProfile' => $cProfile,
            'nextComplianceLevels' => $nextComplianceLevels,
            'passCompliance' => $passCompliance,
            'availableMonthlyAmount' => $availableAmountForMonth ?? '-',
            'limits' => $limits,
            'fromWallet' => $fromWallet,
            'pendingCryptoTransaction' => $pendingCryptoTransaction,
            'link' => $txTransactionLink,
            'steps' => $steps,
            'isCryptoToCryptoPF' => $isCryptoToCryptoPF,
            'payerDetails' => $payerDetails,
            'operationCalculator' => $operationCalculator,
        ]);
    }


    public function getData(Request $request, CProfileService $CProfileService, RateTemplatesService $rateTemplatesService, ProviderService $providerService)
    {
        $project = Project::findOrFail($request->project);


        $merchants = $CProfileService->getActiveMerchants($project->id);
        $rates = $rateTemplatesService->getActiveRatesByAccountType(CProfile::TYPE_INDIVIDUAL);


        $cardProviders = $providerService->getProvidersActive(Providers::PROVIDER_CARD, $project->id);
        $walletProviders = $providerService->getProvidersActive(Providers::PROVIDER_WALLET, $project->id);
        $liquidityProviders = $providerService->getProvidersActive(Providers::PROVIDER_LIQUIDITY, $project->id);

        return response()->json([
            'merchants' => $merchants,
            'rates' => $rates,
            'cardProviders' => $cardProviders,
            'liquidityProviders' => $liquidityProviders,
            'walletProviders' => $walletProviders,
            'selectedProviders' => []
        ]);
    }


}
