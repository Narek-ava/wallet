<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\CProfileStatuses;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Enums\ProjectStatuses;
use App\Enums\Providers;
use App\Enums\PaymentProvider as PaymentProviderEnum;
use App\Enums\TransactionStatuses;
use App\Enums\TransactionType;
use App\Facades\EmailFacade;
use App\Facades\KrakenFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\ProviderOperationRequest;
use App\Http\Requests\Backoffice\WithdrawCardToPaymentRequest;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\ComplianceRequest;
use App\Models\Operation;
use App\Models\PaymentProvider;
use App\Models\Project;
use App\Operations\AmountCalculators\TopUpCardCalculator;
use App\Services\AccountService;
use App\Services\OperationService;
use App\Services\ProjectService;
use App\Services\ProviderService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Provider;
use TrustPayments\Sdk\Model\TransactionGroup;

class DashboardController extends Controller
{
    public function index(Request $request, ProviderService $providerService)
    {
        $providers = $providerService->getProvidersWithoutCurrencyQuery($request->get('providerType'), $request->get('status', PaymentProviderEnum::STATUS_ACTIVE))->get();
        return view('backoffice.dashboard', compact('providers'));
    }

    public function account(Request $request, Account $account, TransactionService $transactionService)
    {
        $showFeeTransactions = $request->get('transaction_group') == TransactionType::GROUP_FEE_TRX;

        $currentAccount = $showFeeTransactions ? $account->childAccount : $account;
        $currentAccount->updateBalance();

        $transactions = $transactionService->getAccountTransactionsByIdPagination($request, $currentAccount->id);

        $maxAmount = $currentAccount->balance;

        $account->updateBalance();

        return view('backoffice.providers.accounts.account', compact('account', 'transactions', 'maxAmount', 'showFeeTransactions', 'currentAccount'));
    }

    public function toProviderAccounts($providerType, $currency, AccountService $accountService)
    {
        return $accountService->toProviderAccounts($providerType, $currency);
    }

    public function withdraw(WithdrawCardToPaymentRequest $request,
                             AccountService $accountService,
                             OperationService $operationService,
                             TransactionService $transactionService)
    {
        $fromAccount = $accountService->getAccountById($request->from_account);
        $toAccount = $accountService->getAccountById($request->to_account);
        $operation = $operationService->createOperation(null,
            OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, $request->currency_amount,
            $request->from_currency, $request->from_currency, $fromAccount->id, $toAccount->id);
        $transactionService->createTransactions(TransactionType::BANK_TRX, $request->currency_amount,
            $fromAccount, $toAccount, $request->date, TransactionStatuses::SUCCESSFUL,
            null, $operation);
    }

    public function createProviderOperation(ProviderOperationRequest $request, OperationService $operationService)
    {
        $operationType = $request->transaction_type == TransactionType::PROVIDER_WITHDRAW_TRX ? OperationOperationType::TYPE_PROVIDER_WITHDRAW : OperationOperationType::TYPE_PROVIDER_TOP_UP;
        $operationService->makeProviderOperation($operationType, $request->all());
        return redirect()->back()->with(['success' => 'operation_added_successfully']);
    }

    public function showOperation(Operation $operation,Account $account = null)
    {
        $transactions = $operation->transactions()->whereNull('parent_id')->orderBy('transaction_id')->paginate(10);;
        return view('backoffice.providers.accounts.see-operation-details', compact('operation', 'account', 'transactions'));
    }

    public function getKrakenBalance(Request $request, ProjectService $projectService)
    {
        $project = $request->project_id ? Project::find($request->project_id) : Project::query()->where('status', ProjectStatuses::STATUS_ACTIVE)->first();
        config()->set('projects.project', $project);
        $response = KrakenFacade::balance();
        $balanceArray = $response['result'];

        $projectNames = $projectService->getProjectIdAndNames(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.kraken-balance', compact('balanceArray', 'project', 'projectNames'));
    }

}

