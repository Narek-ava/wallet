<?php


namespace App\Http\Controllers\Cabinet;



use App\Enums\AccountType;
use App\Enums\OperationOperationType;
use App\Enums\OperationStatuses;
use App\Facades\EmailFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransferRequest;
use App\Models\Account;
use App\Models\Operation;
use App\Services\ComplianceService;
use App\Services\OperationService;
use App\Services\PdfGeneratorService;
use App\Services\WireTransferSelectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FiatTopUpController extends Controller
{

    public function topUp($id)
    {
        $cprofile = getCProfile();
        $fiatAccount = $cprofile->getAccountById($id, AccountType::TYPE_FIAT);
        return view('cabinet.wallets.fiat_top_up', compact('fiatAccount'));
    }

    public function topUpByWire($id, WireTransferSelectionService $wireTransferSelectionService, Request $request)
    {
        $cprofile = getCProfile();
        $fiatAccount = $cprofile->getAccountById($id, AccountType::TYPE_FIAT);
        $wireFiatSelectionDTO = $wireTransferSelectionService->getWireFiatSelectionDTO($fiatAccount, true);
        $operationIds = $request->session()->pull('operationIds', []);
        $operationIds[] = $wireFiatSelectionDTO->currentOperationId;
        $request->session()->put('operationIds', $operationIds);
        $submitAction = route('cabinet.fiat.top_up.wire.create', $fiatAccount->id);

        return view('cabinet.wallets.fiat_top_up_wire', compact('fiatAccount', 'wireFiatSelectionDTO', 'submitAction'));
    }

    public function createOperation(TransferRequest $request, OperationService $operationService, PdfGeneratorService $pdfGeneratorService, ComplianceService $complianceService, $id)
    {

        $operationIds = $request->session()->pull('operationIds', []);
        $cProfile = getCProfile();

        if (in_array($request->operation_id, $operationIds)) {
            $fiatAccount = $cProfile->getAccountById($id, AccountType::TYPE_FIAT);
            $providerAccount = Account::getActiveAccountById($request->bank_detail);
            $provider_id = $providerAccount->provider->id;

            $cprofileId = $cProfile->id;
            $operation = $operationService->createOperation(
                $cprofileId, OperationOperationType::TYPE_FIAT_TOP_UP_BY_WIRE, $request->amount, $request->currency, $request->exchange_to,
                null, $fiatAccount->id, OperationStatuses::PENDING,
                $provider_id, null , $request->operation_id, $providerAccount->id, $cProfile->cUser->project->id
            );

            $operation->additional_data = json_encode([
                'payment_method' => $request->wire_type,
            ]);

            $operation->save();

            if ($operation->isLimitsVerified()) {
                EmailFacade::sendInvoicePaymentSepaOrSwift(auth()->user(), $operation->operation_id, OperationOperationType::getName($operation->operation_type), $operation->amount, $operation->from_currency, $operation->id);
            } else {
                //$complianceService->createNewRetryRequestN2(t('compliance_request'), $request, $operation, $cProfile);
                EmailFacade::sendVerificationRequestFromTheManager(auth()->user(), $operation);
            }
            Session::flash('showModalInfo', t('withdrawal_crypto_successful'));
            Session::flash('operationCreated', t('top_up_wire_operation_created_successfully'));

            return $pdfGeneratorService->getPdfDepositFile($operation, $providerAccount->id);
        } else {
            Session::flash('showModalInfo', t('withdrawal_crypto_fail'));
            return redirect()->route('cabinet.wallets.index', $id);
        }
    }

}
