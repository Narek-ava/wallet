<?php

namespace App\Services\CardProviders;

use App\DataObjects\Payments\TrustPayment\FormData;
use App\DataObjects\Payments\TransactionData;
use App\Enums\Providers;
use App\Models\Project;
use App\Services\ProviderService;

class TrustPaymentService extends CardProviderService
{

    public function __construct(?Project $project = null, ?\App\Models\PaymentProvider $cardProvider = null)
    {
        parent::__construct($project, $cardProvider);

        if (!$this->cardProvider) {
            if (!$this->project) {
                $this->project = Project::getCurrentProject();
            }

            /* @var ProviderService $providerService */
            $providerService = resolve(ProviderService::class);

            $this->cardProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD, $this->project->id);
        }
    }

    public function getPaymentPageUrl(): string
    {
        // Setup API client
        $this->setupApiClient();

        // Create transaction
        $this->initCardTransaction();

        return $this->createPaymentPageUrl();
    }



    public function getPaymentFormData(): FormData
    {
       return new FormData([
            'currencyiso3a' => $this->_currency,
            'mainamount' => $this->_amount
        ]);
    }

    public function retrieveTransactionByReference($reference): TransactionData
    {
        $configData = $this->getConfigData();
        $requestData = $this->getRequestData($reference);

        $api = \Securetrading\api($configData);
        $response = $api->process($requestData);

        $dataArray = $response->toArray()['responses'][0]['records'][0];
        return new TransactionData([
            'firstName' => $dataArray['customerfirstname'] ?? '',
            'lastName' => $dataArray['customerlastname'] ?? '',
            'cardNumber' => $dataArray['maskedpan'] ?? '',
            'operationId' => $dataArray['orderreference'] ?? '',
            'paymentType' => $dataArray['paymenttypedescription'] ?? '',
            'currency' => $dataArray['currencyiso3a'] ?? '',
            'amount' => $dataArray['settlebaseamount'] / 100,
            'cardSecure' => $dataArray['enrolled'],
            'transactionDate' => $dataArray['transactionstartedtimestamp'],
            'is_successful' => !$dataArray['errorcode'],
            'error_message' => $dataArray['errormessage']
        ]);
    }

    // @todo card function
    public function checkTransactionDetails(TransactionData $transactionDataObject, float $expectedAmount, string $currency, string $userFirstName)
    {
        if ($transactionDataObject->amount < $expectedAmount) {
            return false;
        }

        if ($transactionDataObject->currency != $currency) {
            return false;
        }

        if ($transactionDataObject->firstName != $userFirstName) {
            return false;
        }

        return true;

    }

    protected function getConfigData(): array
    {
        return [
            'username' => $this->getConfigValue('username'),
            'password' => $this->getConfigValue('password'),
        ];
    }

    protected function getRequestData($transactionReference): array
    {
        return [
            'requesttypedescriptions' => ['TRANSACTIONQUERY'],
            'filter' => [
                'sitereference' => [['value' => $this->getConfigValue('sitereference')]],
                'transactionreference' => [['value' => $transactionReference]]
            ]
        ];
    }

    protected function getConfigValue(string $key)
    {
        $configKey = $this->getConfigFileName() . '.' . $this->cardProvider->api . '.' . $this->cardProvider->api_account;
        return config($configKey . '.' . $key);
    }

    protected function getConfigFileName(): string
    {
        return 'cardproviders';
    }


}
