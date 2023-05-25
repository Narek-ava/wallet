<?php


namespace App\Services\Wallester;
use App\DataObjects\OperationTransactionData;
use App\DataObjects\Payments\Wallester\WallesterCardData;
use App\DataObjects\Payments\Wallester\WallesterDeliveryAddress;
use App\DataObjects\Payments\Wallester\WallesterLimits;
use App\DataObjects\Payments\Wallester\WallesterPersonData;
use App\DataObjects\Payments\Wallester\WallesterSecure;
use App\DataObjects\Payments\Wallester\WallesterSecurity;
use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Enums\WallesterCardOrderPaymentMethods;
use App\Enums\WallesterCardStatuses;
use App\Enums\WallesterCardTypes;
use App\Exceptions\OperationException;
use App\Facades\EmailFacade;
use App\Models\Account;
use App\Models\BankAccountTemplate;
use App\Models\Cabinet\CProfile;
use App\Models\CardDeliveryAddress;
use App\Models\Operation;
use App\Models\Transaction;
use App\Models\WallesterAccountDetail;
use App\Operations\OrderCardByCrypto;
use App\Operations\OrderCardByWire;
use App\Services\BankAccountTemplateService;
use App\Services\CountryService;
use Illuminate\Support\Str;
use function C\c_user_update_login;
use App\Services\Wallester\Api as WallesterApi;
use Carbon\Carbon;
use Illuminate\Support\Collection;


class WallesterPaymentService
{
    const SESSION_KEY = 'wallester_order_card_';

    protected Api $wallesterApi;
    protected CountryService $countryService;

    public function __construct()
    {
        $this->wallesterApi = resolve(Api::class);
        $this->countryService = resolve(CountryService::class);
    }

    public function getSteps(int $currentStep = 1)
    {
        $currentIsComplete = false;
        for ($i = 1; $i <= 4; $i++) {
            if ($i === $currentStep) {
                $currentIsComplete = true;
                $steps['plastic']['step_' . $i] = 'step-current red-border';
                if ($i !== 4) {
                    $steps['virtual']['step_' . $i] = 'step-current red-border';
                }
            } elseif($currentIsComplete) {
                $steps['plastic']['step_' . $i] = 'step-next';
                if ($i !== 4) {
                    $steps['virtual']['step_' . $i] = 'step-next';
                }
            } else {
                $steps['plastic']['step_' . $i] = 'step-completed';
                if ($i !== 4) {
                    $steps['virtual']['step_' . $i] = 'step-completed';
                }
            }
        }

        return $steps ?? [];
    }

    public function orderCardFromWallester(CProfile $cProfile, array $currentOrderData)
    {
        $this->createPersonInWallester($cProfile);
        $cProfile->refresh();

        $purchases = [
            'contactless_purchases' => $currentOrderData['contactless_purchases'] ?? null,
            'atm_withdrawals' => $currentOrderData['atm_withdrawals'] ?? null,
            'internet_purchases' => $currentOrderData['internet_purchases'],
            'overall_limits_enabled' => $currentOrderData['overall_limits_enabled'],
        ];
        return $this->createAccountForWallester( $cProfile,'Wallester ' . $cProfile->getFullName(), $currentOrderData['type'], $currentOrderData['password'], $currentOrderData['paymentMethod'] ?? null, $purchases);
    }

    public function putDataIntoSession(string $key, array $data)
    {
        session()->put(self::SESSION_KEY . $key, $data);
    }

    public function getDataFromSession(string $key): array
    {
        return session()->get(self::SESSION_KEY . $key) ?? [];
    }

    public function deleteDataFromSession(string $key): void
    {
        session()->forget(self::SESSION_KEY . $key);
    }

    public function createPersonInWallester(CProfile $cProfile)
    {
        if (!$cProfile->wallester_person_id) {
            $country = $this->countryService->getCountry(['code' => $cProfile->country]);
            $wallesterPersonData = new WallesterPersonData([
                'external_id' => $cProfile->id,
                'first_name' => $cProfile->first_name,
                'last_name' => $cProfile->last_name,
                'birth_date' => $cProfile->date_of_birth,
                'email' => $cProfile->cUser->email,
                'phone' => $cProfile->cUser->phone,
                'personal_number_issuer' =>  $country ? strtoupper($country->code_ISO3) : null,
            ]);
            $this->wallesterApi->createPerson($wallesterPersonData);
            $person = $this->wallesterApi->getPersonByExternalId($cProfile->id);
            $cProfile->wallester_person_id = $person['person']['id'];
            $cProfile->save();
        }
    }

    public function createAccountForWallester(CProfile $cProfile, string $name, int $cardType, string $cardPassword, ?int $paymentMethod, array $purchases)
    {
        $account = new Account();
        $account->fill([
            'name' => $name,
            'status' => AccountStatuses::STATUS_ACTIVE,
            'c_profile_id' => $cProfile->id,
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
            'account_type' => AccountType::TYPE_CARD,
            'currency' => Currency::CURRENCY_EUR,
            'is_external' => false,
        ]);
        $account->save();
        $account->refresh();

        $this->createWallesterAccountDetails(null, $cardType, $account->id, null, $cProfile->getFullName(), null, WallesterCardStatuses::STATUS_PENDING_PAYMENT, null, $cardPassword, $paymentMethod, $purchases);

        return $account;
    }

    public function orderCard(CProfile $cProfile, string $externalAccountId, string $externalCardId, array $currentOrderData, string $cardName): WallesterAccountDetail
    {
//        $account = $cProfile->accounts()->whereHas('wallesterAccountDetail', function ($q) {
//            return $q->whereNotNull('wallester_account_id');
//        })->first();
//
//        if ($account) {
//            $accountInWallester = $this->wallesterApi->getAccount($account->wallesterAccountDetail->wallester_account_id);
//        } else {
        //        }

        $accountExists = false;
        try {
            $accountInWallester = $this->wallesterApi->getAccountByExternalId($externalAccountId);
            $accountExists = true;

            $existingAccount = Account::findOrFail($externalAccountId);
            $existingWallesterAccountDetail = $existingAccount->wallesterAccountDetail;
            if (!$existingWallesterAccountDetail->wallester_account_id) {
                $existingWallesterAccountDetail->wallester_account_id = $accountInWallester['account']['id'];
                $existingWallesterAccountDetail->save();
            }
        } catch (\Exception $exception) {
            $accountInWallester = $this->wallesterApi->createAccount($cProfile->wallester_person_id, $externalAccountId, Currency::CURRENCY_EUR);
        }

        if ($accountExists) {
            $cards = $this->wallesterApi->getAccountCards($accountInWallester['account']['id']);
            if ($cards['total_records_number'] !== 0){
                throw new OperationException('Card already exists');
            }
        }

        try {
            $this->wallesterApi->vIban($accountInWallester['account']['id']);
        } catch (\Throwable $exception) {
        }


        $accountInWallester = $this->wallesterApi->getAccount($accountInWallester['account']['id']);
        /* @var WallesterAccountDetail $wallesterAccountDetail */
        $wallesterAccountDetail = WallesterAccountDetail::find($externalCardId);
        $wallesterAccountDetail->wallester_account_id = $accountInWallester['account']['id'];

        $wallesterAccountDetail->save();

        $isCardTypePlastic = $currentOrderData['type'] == WallesterCardTypes::TYPE_PLASTIC;

        $wallesterLimits = new WallesterLimits($currentOrderData['limits']);
        if ($isCardTypePlastic) {
            $wallesterDelivery = new WallesterDeliveryAddress($currentOrderData['delivery']);
        }
        $wallesterSecure = new WallesterSecure([
            'type' => "SMSOTPAndStaticPassword",
            'password' => $currentOrderData['password'],
            'mobile' => '+' . $cProfile->cUser->phone
        ]);
        $wallesterSecurity = new WallesterSecurity([
            'contactless_enabled' => $currentOrderData['contactless_purchases'] ?? null,
            'withdrawal_enable' => $currentOrderData['atm_withdrawals'] ?? null,
            'internet_purchase_enabled' => $currentOrderData['internet_purchases'],
            'overall_limits_enabled' => $currentOrderData['overall_limits_enabled'],
        ]);

        $dtoParams = [
            'account_id' => $accountInWallester['account']['id'],
            'person_id' => $cProfile->wallester_person_id,
            'external_id' => $externalCardId,
            'type' => Api::TYPE_NAMES_WALLESTER[$currentOrderData['type']],
            'name' => $cardName,
            'security' => $wallesterSecurity,
            'secure_3d_settings' => $wallesterSecure,
            'limits' => $wallesterLimits
        ];

        if ($isCardTypePlastic) {
            $dtoParams['delivery_address'] = $wallesterDelivery ?? null;
        }

        $cardData = new WallesterCardData($dtoParams);
        $card = $this->wallesterApi->orderCard($cardData);

        $wallesterAccountDetail->wallester_card_id = $card['card']['id'];
        $wallesterAccountDetail->card_mask = $card['card']['masked_card_number'];
        $wallesterAccountDetail->status = WallesterCardStatuses::STATUSES_FROM_RESPONSE[$card['card']['status']];
        $wallesterAccountDetail->save();

        $purchases = [
            'internet_purchases' => $currentOrderData['internet_purchases'],
            'overall_limits_enabled' => $currentOrderData['overall_limits_enabled'],
        ];
        if ($isCardTypePlastic) {
            $purchases['contactless_purchases'] = $currentOrderData['contactless_purchases'];
            $purchases['atm_withdrawals'] = $currentOrderData['atm_withdrawals'];
        }

        if ($isCardTypePlastic) {
            $country = $this->countryService->getCountry(['code_iso3' => strtoupper($currentOrderData['delivery']['country_code'])]);
            $this->createCardDeliveryAddress($wallesterAccountDetail, $country->code, $currentOrderData['delivery']['first_name'], $currentOrderData['delivery']['last_name'], $currentOrderData['delivery']['address1'], $currentOrderData['delivery']['address2'] ?? null, $currentOrderData['delivery']['postal_code'], $currentOrderData['delivery']['city']);
        }
        $this->createBankDetails($accountInWallester['account']['top_up_details'] ?? [], $wallesterAccountDetail->card_mask, $accountInWallester['account']['viban'] ?? null, $cProfile, $wallesterAccountDetail->id);

        EmailFacade::sendWallesterCardOrderSuccessMessage($cProfile->cUser, $wallesterAccountDetail->id);
        return $wallesterAccountDetail;
    }

    public function createBankDetails(array $topUpDetails, string $templateName, ?string $vIban = null, CProfile $cProfile, ?string $wallesterAccountDetailId)
    {
        /* @var BankAccountTemplateService $bankAccountTemplateService */
        $bankAccountTemplateService = resolve(BankAccountTemplateService::class);
        if ($vIban || isset($topUpDetails['iban'])) {
            $params = [
                'iban' => $vIban ?? $topUpDetails['iban'],
                'wire_type' => AccountType::TYPE_WIRE_SEPA,
                'currency_from' => Currency::CURRENCY_EUR,
                'country' => 'lt',
                'templateName' => $templateName,
                'c_profile_id' => $cProfile->id,
                'holder' => $topUpDetails['receiver_name'] ?? $cProfile->getFullName(),
                'number' => $topUpDetails['registration_number'] ?? '-',
                'bank_name' => $topUpDetails['bank_name'] ?? '-',
                'bank_address' => $topUpDetails['bank_address'] ?? '-',
                'swift' => $topUpDetails['swift_code'] ?? '-',
                'wallester_account_detail_id' => $wallesterAccountDetailId,
            ];


            $bankAccountTemplateService->saveTemplate($params);
        }
    }

    public function createWallesterAccountDetails(?string $id, int $cardType,string $accountId, $wallesterCardId, string $cardName, $wallesterAccountId, int $status, ?string $cardMask, string $password, ?int $paymentMethod, array $purchases): WallesterAccountDetail
    {
        $wallesterAccountDetails = new WallesterAccountDetail();
        if ($id) {
           $wallesterAccountDetails->id = $id;
        }
        $wallesterAccountDetails->fill([
            'account_id' => $accountId,
            'name' => $cardName,
            'wallester_account_id' => $wallesterAccountId ?? null,
            'wallester_card_id' => $wallesterCardId ?? null,
            'card_type' => $cardType,
            'status' => $status,
            'contactless_purchases' => $purchases['contactless_purchases'] ?? null,
            'atm_withdrawals' => $purchases['atm_withdrawals'] ?? null,
            'internet_purchases' => $purchases['internet_purchases'],
            'overall_limits_enabled' => $purchases['overall_limits_enabled'],
            'password_3ds' => $password,
            'payment_method' => $paymentMethod ?? null,
            'card_mask' => $cardMask ?? null
        ]);
        $wallesterAccountDetails->save();

        return $wallesterAccountDetails;
    }

    public function createCardDeliveryAddress(WallesterAccountDetail $wallesterAccountDetails, string $countryCode, string $firstName, string $lastName, string $address_1, ?string $address_2, string $postalCode, string $city)
    {
        $cardDeliveryAddress = new CardDeliveryAddress();
        $cardDeliveryAddress->fill([
            'wallester_account_detail_id' => $wallesterAccountDetails->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'address_1' => $address_1,
            'address_2' => $address_2,
            'postal_code' => $postalCode,
            'city' => $city,
            'country_code' => $countryCode
        ]);
        $cardDeliveryAddress->save();

        return $cardDeliveryAddress;
    }

    public function getProfileCards()
    {
        $cProfile = getCProfile();

        if ($cProfile->wallester_person_id) {
            return [];
        }

        $cProfile->accounts()->whereHas('wallesterAccountDetail');
    }


    /**
     * @param array $dataArray
     * @param CProfile $cProfile
     * @return void
     */
    public function createCardByManager(array $dataArray, CProfile $cProfile)
    {

        $countryService = resolve(CountryService::class);

        if($dataArray['type'] == WallesterCardTypes::TYPE_PLASTIC) {
            $country = $countryService->getCountry(['code' => $dataArray['country_code']]);
            $dataArray['country_code'] = strtoupper($country->code_ISO3);
        }
        unset($dataArray['cProfileId']);

        $this->orderCardFromWallester($cProfile, $dataArray);
    }

    public function createPerson(CProfile $cProfile)
    {
        $apiService = resolve(Api::class);
        $personData = new WallesterPersonData([
            'first_name' => $cProfile->first_name,
            'last_name' => $cProfile->last_name,
            'birth_date' => $cProfile->date_of_birth,
            'email' => $cProfile->cUser->email,
            'phone' => $cProfile->cUser->phone,
            'external_id' => $cProfile->id,
            'personal_number_issuer' => $cProfile->country,
        ]);
        return $apiService->createPerson($personData);
    }

    public function getCardTransactions(string $cardId, array $requestArray)
    {
        $wallesterApi = resolve(WallesterApi::class);
        /** @var WallesterApi $wallesterApi*/

        if (!empty($requestArray['to_date'])) {
            $requestArray['to_date'] = Carbon::parse($requestArray['to_date'])->toRfc3339String();
        }

        if (!empty($requestArray['from_date'])) {
            $requestArray['from_date'] = Carbon::parse($requestArray['from_date'])->toRfc3339String();
        }

        $cardTransactions =  $wallesterApi->getCardTransactions($cardId, $requestArray['from_record'] ?? 0, $requestArray['records_count'] ?? 10, $requestArray);

        $transactions = new Collection($cardTransactions['transactions']);

        if(!empty($requestArray['merchant_name'])) {
            $merchant_name = $requestArray['merchant_name'];
            $transactions = $transactions->filter(function ($item) use ($merchant_name) {
                return false !== stristr($item['merchant_name'], $merchant_name);
            });
        }

        if(!empty($requestArray['type'])) {
            $type = $requestArray['type'];
            $transactions = $transactions->filter(function ($item) use ($type) {
                return false !== stristr($item['group'], $type);
            });
        }

        $pagination = view('backoffice.cProfile.cards._paginate', ['count' => $cardTransactions['total_records_number'] ?? 0, 'cardId' => $cardId]);
        $cardTransactions['transactions'] = $transactions;
        $cardTransactions['pagination'] = $pagination;

        return $cardTransactions;
    }

    public function executePayment(int $paymentMethod, Operation $operation)
    {
        $operationTransactionData = new OperationTransactionData([
            'date' => date('Y-m-d'),
            'transaction_type' => TransactionType::CRYPTO_TRX,
            'from_type' => Providers::CLIENT,
            'to_type' => Providers::PROVIDER_LIQUIDITY,
            'from_currency' => $operation->from_currency,
            'from_account' => $operation->from_account,
            'currency_amount' => $operation->amount
        ]);

        $projectId = $operation->cProfile->cUser->project_id ?? null;

        switch ($paymentMethod) {
            case WallesterCardOrderPaymentMethods::CRYPTOCURRENCY:
                $toAccount = Account::getProviderAccount($operation->from_currency, Providers::PROVIDER_LIQUIDITY, null, null, $projectId);
                $operationTransactionData->to_account = $toAccount->id;
                $orderCard = new OrderCardByCrypto($operation, $operationTransactionData);
                break;
            case WallesterCardOrderPaymentMethods::BANK_CARD:

                die;
                break;
            default:
                die;
        }
        try {
            $orderCard->execute();
        } catch (\Throwable $exception) {
            throw $exception;
        }

    }


    public function approveClientToLiqProviderTransaction(Transaction $transaction)
    {
        $transaction->markAsSuccessful();
        $operation = $transaction->operation;

        try {
            $transactionData = new OperationTransactionData([
                'transaction_type' => TransactionType::EXCHANGE_TRX,
                'from_type' => Providers::PROVIDER_LIQUIDITY,
                'to_type' => Providers::PROVIDER_LIQUIDITY,
                'from_currency' => $transaction->operation->from_currency,
                'from_account' => $transaction->toAccount->id,
                'to_account' => $operation->toAccount->id,
                'currency_amount' => $transaction->trans_amount
            ]);

            $orderCardByCrypto = new OrderCardByCrypto($operation, $transactionData);
            $orderCardByCrypto->execute();

            return true;
        } catch (\Throwable $exception) {
            throw $exception;
        }

    }

    public function addTransactionForCardOrderByWire(string $operationId, array $operationData)
    {
        try {
            $operation = Operation::findOrFail($operationId);

            $operationDTO = new OperationTransactionData([
                'date' => date('Y-m-d'),
                'transaction_type' => $operationData['transaction_type'],
                'from_type' => $operationData['from_type'],
                'to_type' => $operationData['to_type'],
                'from_currency' => $operationData['from_currency'],
                'from_account' => $operationData['from_account'],
                'to_account' => $operationData['to_account'],
                'currency_amount' => $operationData['currency_amount'],
            ]);

            $orderCard = new OrderCardByWire($operation, $operationDTO);
            $orderCard->execute();
        } catch (\Throwable $exception) {
            throw $exception;
        }



    }

    public function markWireOperationAsSuccessful(Operation $operation): bool
    {
        if (!$operation->additional_data) {
            return false;
        }

        $additionalData = json_decode($operation->additional_data, true);
        $walAccountDetailId = $additionalData['wallester_account_detail_id'];
        $wallesterAccountDetail = WallesterAccountDetail::find($walAccountDetailId);

        if ($wallesterAccountDetail->wallester_card_id && $wallesterAccountDetail->wallester_account_id) {
            return true;
        }

        if (!$wallesterAccountDetail) {
            return false;
        }
        $wallesterAccountDetail->operation_id = $operation->id;

        $wallesterAccountDetail->save();

        $currentOrderData = $additionalData['wallester_card_info'];

        try {
            $this->orderCard($operation->cProfile, $wallesterAccountDetail->account->id, $wallesterAccountDetail->id, $currentOrderData, $operation->cProfile->getFullName());
            $wallesterAccountDetail->is_paid = true;
            $wallesterAccountDetail->save();
        } catch (\Exception $exception) {
            logger()->error('OrderCardWireStatusUpdateError', [
                'operationId' => $operation->operation_id,
                'accountId' => $wallesterAccountDetail->account_id,
                'message' => $exception->getMessage(),
            ]);
            throw new OperationException($exception->getMessage());

        }

        return true;
    }

    public function getCardDetailsForCopy(WallesterAccountDetail $card, ?BankAccountTemplate $bankAccountTemplate)
    {
        return t('recipient')
            . "\r\n" . $card->account->cProfile->getFullName()
            . "\r\n". "\r\n" . t('passport_id')
            . "\r\n". ($card->account->cProfile->passport ?? '-')
            . "\r\n". "\r\n" . t('resident_address')
            . "\r\n" . ($card->account->cProfile->address ?? '-')
            . "\r\n". "\r\n" . t('topup_iban')
            . "\r\n". ($bankAccountTemplate->IBAN ?? '-')
            . "\r\n". "\r\n" . t('swift_bic')
            . "\r\n". ($bankAccountTemplate->SWIFT ?? '-')
            . "\r\n". "\r\n" . t('wallester_bank_name')
            . "\r\n". ($bankAccountTemplate->bank_name ?? '-')
            . "\r\n". "\r\n" . t('wallester_bank_address')
            . "\r\n". ($bankAccountTemplate->bank_address ?? '-');
    }
}
