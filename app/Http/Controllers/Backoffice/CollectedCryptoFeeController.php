<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\TransactionType;
use App\Exceptions\OperationException;
use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\WithdrawCollectedCryptoFee;
use App\Models\Operation;
use App\Models\Transaction;
use App\Models\Project;
use App\Services\CollectedCryptoFeeService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class CollectedCryptoFeeController extends Controller
{
    public function collectedCryptoFee(CollectedCryptoFeeService $collectedCryptoFeeService, Request $request, ProjectService $projectService)
    {
        $selectedProject = $request->project_id ? Project::find($request->project_id) : Project::first();

        $collectedCryptoFees = $collectedCryptoFeeService->getPaginatedCollectedCryptoFees($selectedProject->id);
        $totalCollected = $collectedCryptoFeeService->getTotalCollectedAmount($request->from, $request->to, null, $selectedProject->id);
        $readyForWithdrawal = $collectedCryptoFeeService->getTotalCollectedAmount($request->from, $request->to, \App\Enums\CollectedCryptoFee::IS_NOT_COLLECTED);
        $notCollectedCryptoFeeTransactions = $collectedCryptoFeeService->getNotCollectedCryptoFees($request->from, $request->to, $selectedProject->id);
        $feesForWithdraw = $collectedCryptoFeeService->getFeesForWithdraw($readyForWithdrawal->toArray(), $selectedProject->id);
        $activeProjects =  $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);

        return view('backoffice.collectedCryptoFee.collected-crypto-fee-table', compact(
            'collectedCryptoFees',
            'totalCollected', 'feesForWithdraw', 'selectedProject','activeProjects',
            'readyForWithdrawal', 'notCollectedCryptoFeeTransactions'));
    }

    public function withdrawCollectedFee(WithdrawCollectedCryptoFee $request, CollectedCryptoFeeService $collectedCryptoFeeService)
    {
        $project = Project::find($request->project_id);
        config()->set('projects.project', $project);
        $notCollectedOperations = $collectedCryptoFeeService->getTotalCollectedAmount(null, null, false, $request->project_id);
        if ($request->amount > $notCollectedOperations[$request->currency]) {
            session()->flash('error', t('unsuccessful_withdraw'));
            return redirect()->route('collected.fee');
        }

        try {
            $operation = $collectedCryptoFeeService->makeOperation($request->validated());
        } catch (\Throwable $exception) {
            session()->flash('error', $exception->getMessage());
            return redirect()->route('collected.fee');
        }

        $key = 'checkedTransactions' . $request->get('currency');
        $collectedCryptoFeeService->markTransactionsAsCollected($request->get($key), $operation);

        session()->flash('success', t('successful_withdraw'));
        return redirect()->route('collected.fee');
    }

    public function show(Operation $operation)
    {
        $cryptoTrx = $operation->getLastTransactionByType(TransactionType::CRYPTO_TRX);
        $credited = $cryptoTrx->trans_amount ?? '';
        $transactions = $operation->transactions()->where('type', TransactionType::CRYPTO_TRX)->paginate(10);
        $collectedTransactions = $operation->getCollectedTransactions()->paginate(10);
        return view('backoffice.collectedCryptoFee.show',
            compact('operation', 'credited', 'transactions', 'collectedTransactions'));
    }


    public function getTransactionDetails(Request $request)
    {
        $transaction = Transaction::query()->with(['fromAccount', 'toAccount', 'fromCommission', 'toCommission', 'operation'])->findOrFail($request->transaction_id);
        $fromType = $transaction->fromAccount->getAccountTypeName();
        $toType = $transaction->toAccount->getAccountTypeName();
        $trxType = TransactionType::getName($transaction->type);


        return response()->json([
            'transaction' => $transaction,
            'fromType' => $fromType,
            'toType' => $toType,
            'trxType' => $trxType,
            'toCryptoAccountDetail' => $transaction->toAccount->cryptoAccountDetail
        ]);
    }

    public function getFeesForNotCollectedTransactions(Request $request, CollectedCryptoFeeService $collectedCryptoFeeService)
    {
        return response()->json($collectedCryptoFeeService->getFeesByCurrency($request->amount, $request->currency));
    }
}
