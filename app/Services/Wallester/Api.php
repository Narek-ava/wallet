<?php


namespace App\Services\Wallester;

use App\DataObjects\Payments\Wallester\WallesterLimits;
use App\DataObjects\Payments\Wallester\WallesterSecure;
use App\DataObjects\Payments\Wallester\WallesterSecurity;
use App\Enums\Providers;
use App\Enums\WallesterCardStatuses;
use App\Models\PaymentProvider;
use App\Models\Project;
use App\Services\ProviderService;
use App\DataObjects\Payments\Wallester\WallesterCardData;
use App\DataObjects\Payments\Wallester\WallesterPersonData;
use App\Enums\WallesterCardTypes;
use GuzzleHttp\Client;

class Api
{
    const PROFILE_HIGH_RISK = 'High';
    const PROFILE_MEDIUM_RISK = 'Medium';
    const PROFILE_LOW_RISK = 'Low';

    const TYPE_NAMES_WALLESTER = [
        WallesterCardTypes::TYPE_PLASTIC => "ChipAndPin",
        WallesterCardTypes::TYPE_VIRTUAL => "Virtual"
    ];

    protected ?Project $project;
    protected ?PaymentProvider $cardIssuingProvider;
    private Client $client;

    /**
     * @param string $apiUrl
     */
    public function __construct(?Project $project = null)
    {
        $this->project = $project;

        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $this->cardIssuingProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_CARD_ISSUING, $project->id ?? null);

        $this->client = new Client([
            'base_uri' => $this->getConfigValue('appUrl'),
            'headers' => [
                "Content-Type" => 'application/json',
            ]
        ]);

        $this->jwtTokenEncoder = new JwtTokenEncoder($this->cardIssuingProvider);
    }

    public function getConfigValue(string $key): ?string
    {
        $configKey = 'cardissuing.' . $this->cardIssuingProvider->api . '.' . $this->cardIssuingProvider->api_account . '.' . $key;
        return config($configKey);
    }

    public function sendRequest(string $url, string $method = 'GET', $requestParams = [])
    {
        if ($method === 'GET' || $method === 'DELETE') {
            $token = $this->jwtTokenEncoder->createToken();
            $requestParams['body'] = '';
        } else {
            $body = json_encode($requestParams['body'] ?? []);
            $token = $this->jwtTokenEncoder->createToken($body, true);
            $requestParams['body'] = $body;
        }

        $requestParams = array_merge($requestParams, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'X-Audit-Source-Type' => "SelfService",
                'X-Audit-User-Id' => $this->getConfigValue('audience'),
                'X-Product-Code' => 'CONNECTEECD',
            ]
        ]);


        return json_decode($this->client->request($method, $url, $requestParams)->getBody()->getContents(), true);

    }

    public function getPersonDataFromDTO(WallesterPersonData $wallesterPersonData): array
    {
        return [
            'first_name' => $wallesterPersonData->first_name,
            'last_name' => $wallesterPersonData->last_name,
            'birth_date' => $wallesterPersonData->birth_date,
            'email' => $wallesterPersonData->email,
            'mobile' => '+' . $wallesterPersonData->phone,
            'is_represented_by_someone_else' => false,
            'is_politically_exposed_person' => false,
            'is_beneficial_owner' => true,
            'external_id' => $wallesterPersonData->external_id,
            'risk_profile' => self::PROFILE_LOW_RISK,
            'personal_number_issuer' => $wallesterPersonData->personal_number_issuer //country code in ISO-3 format
        ];
    }

    public function getCardDataFromDto(WallesterCardData $wallesterCardData)
    {
        return json_decode(json_encode($wallesterCardData), true);
    }


    public function ping()
    {
        return $this->sendRequest('v1/test/ping', 'POST', ['body' => [
            'message' => 'ping'
        ]]);
    }

    public function createPerson(WallesterPersonData $wallesterPersonData)
    {
        $personData = $this->getPersonDataFromDTO($wallesterPersonData);

        return $this->sendRequest('/v1/persons', "POST", [
            'body' => [
                'person' => $personData
            ]
        ]);
    }

    public function updatePerson(string $person_id, WallesterPersonData $wallesterPersonData)
    {
        $personData = $this->getPersonDataFromDTO($wallesterPersonData);

        return $this->sendRequest("v1/persons/{$person_id}", "PUT", [
            'body' => [
                'person' => $personData
            ]
        ]);
    }

     public function vIban(string $account_id)
    {
        return $this->sendRequest("/v1/viban", "POST", [
            'body' => [
                'account_id' => $account_id
            ]
        ]);
    }

    public function getPerson(string $person_id)
    {
        return $this->sendRequest("v1/persons/{$person_id}");
    }

    public function getPersonByExternalId(string $external_id)
    {
        return $this->sendRequest("/v1/persons-by-external-id/{$external_id}");
    }

    public function activatePerson(string $person_id)
    {
        return $this->sendRequest("/v1/persons/{$person_id}/activate", 'PATCH');
    }

    public function activateCard(string $card_id)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/activate", 'PATCH');
    }

    public function deactivatePerson(string $person_id)
    {
        return $this->sendRequest("/v1/persons/{$person_id}", 'DELETE');
    }

    public function getPersonAccounts(string $person_id)
    {
        return $this->sendRequest("/v1/persons/{$person_id}/accounts");
    }

    public function getPersonCards(string $person_id)
    {
        return $this->sendRequest("/v1/persons/{$person_id}/cards");
    }

    public function createAccount(string $person_id, string $external_id, string $currency)
    {
        return $this->sendRequest("/v1/accounts", 'POST', [
            'body' => [
                'person_id' => $person_id,
                'external_id' => $external_id,
                'currency_code' => $currency,
            ]
        ]);
    }

    public function getAccountByExternalId(string $external_id)
    {
        return $this->sendRequest("/v1/accounts-by-external-id/{$external_id}");
    }

    public function getAccount(string $account_id)
    {
//        return $this->sendRequest("/v1/accounts/{$account_id}/reopen", 'PATCH');
        return $this->sendRequest("/v1/accounts/{$account_id}");
    }


    public function createAccountPayment(string $from_account_id, string $to_account_id, float $amount, string $description)
    {
        return $this->sendRequest("/v1/account-payments", 'PATCH', [
            'body' => [
                'amount' => $amount,
                'description' => $description,
                'from_account_id' => $from_account_id,
                'to_account_id' => $to_account_id,
            ]
        ]);
    }

    public function adjustAccountBalance(string $account_id, float $amount, string $description, bool $allow_negative_balance = false)
    {
        return $this->sendRequest("/v1/accounts/{$account_id}/balance", 'PATCH', [
            'body' => [
                'amount' => $amount,
                'description' => $description,
                'allow_negative_balance' => $allow_negative_balance,
            ]
        ]);
    }

    public function getAccountCards(string $account_id, int $from_record = 0, int $records_count = 10)
    {
        return $this->sendRequest("/v1/accounts/{$account_id}/cards", 'GET', [
            'query' => [
                'from_record' => $from_record,
                'records_count' => $records_count,
            ]
        ]);
    }

    /**
     * @param string $card_id
     * @param int $from_record
     * @param int $records_count
     * @param array $params
     * @return mixed
     */
    public function getCardTransactions(string $card_id, int $from_record = 0, int $records_count = 10, array $params)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/transactions", 'GET', [
            'query' => array_merge([
                    'from_record' => $from_record,
                    'records_count' => $records_count,
                ], $params)
        ]);
    }

    public function getCardLimits(string $card_id)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/limits");
    }

    public function getDefaultLimits()
    {
        return $this->sendRequest("/v1/product-settings/account-limits");
    }

    public function getCardDefaultLimits()
    {
        return $this->sendRequest("/v1/product-settings/card-limits");
    }

    public function getCardDefaultLimitsCached()
    {
//        $cacheKey = 'wallester_card_default_limits';
//        $limits = Cache::get($cacheKey);
//        if (empty($limits)) {
            try {
                $limits = $this->getCardDefaultLimits();
//                Cache::put($cacheKey, $limits, 3600);
            } catch (\Throwable $exception) {
                throw $exception;
            }
//        }
        return $limits;
    }

    public function getAllowedCardDeliveryCountries()
    {
        return $this->sendRequest("/v1/product-settings/allowed-card-delivery-country-codes");
    }

    public function getCardByExternalId(string $external_id)
    {
        return $this->sendRequest("/v1/cards-by-external-id/{$external_id}");

    }

    public function orderCard(WallesterCardData $cardData)
    {

        $cardDataArray = $this->getCardDataFromDto($cardData);
        $cardDataArray['3d_secure_settings'] = $cardDataArray['secure_3d_settings'];
        if ($cardData->type == Api::TYPE_NAMES_WALLESTER[WallesterCardTypes::TYPE_PLASTIC]) {
            $cardDataArray['delivery_address']['country_code'] = strtoupper($cardDataArray['delivery_address']['country_code']);
        }
        unset($cardDataArray['secure_3d_settings']);
        return $this->sendRequest("/v1/cards", 'POST', [
            'body' => $cardDataArray
        ]);
    }

    public function getEncrypted3dsPassword($card_id)
    {
        $response = $this->sendRequest("v1/cards/{$card_id}/encrypted-3ds-password", 'POST', [
            'body' => [
                'public_key' =>
                    base64_encode(file_get_contents(storage_path('wallester_encryption_public.key')))
            ]
        ]);

        $encryptedPassword = $response['encrypted_3ds_password'];

        return $this->jwtTokenEncoder->decodeRSA($encryptedPassword, 'Card3DSecurePassword');
    }

    public function getEncryptedCardNumber($card_id)
    {
        $response = $this->sendRequest("v1/cards/{$card_id}/encrypted-card-number", 'POST', [
            'body' => [
                'public_key' =>
                    base64_encode(file_get_contents(storage_path('wallester_encryption_public.key')))
            ]
        ]);

        $encryptedCardNumber = $response['encrypted_card_number'];

        return $this->jwtTokenEncoder->decodeRSA($encryptedCardNumber, 'CardNumber');
    }


    public function getEncryptedCVV($card_id)
    {
        $response = $this->sendRequest("v1/cards/{$card_id}/encrypted-cvv2", 'POST', [
            'body' => [
                'public_key' =>
                    base64_encode(file_get_contents(storage_path('wallester_encryption_public.key')))
            ]
        ]);

        $encryptedCVV = $response['encrypted_cvv2'];

        return $this->jwtTokenEncoder->decodeRSA($encryptedCVV, 'CVV2');
    }

    public function getEncryptedPIN($card_id)
    {
        $response = $this->sendRequest("v1/cards/{$card_id}/encrypted-pin", 'POST', [
            'body' => [
                'public_key' =>
                    base64_encode(file_get_contents(storage_path('wallester_encryption_public.key')))
            ]
        ]);

        $encryptedPIN = $response['encrypted_pin'];

        return $this->jwtTokenEncoder->decodeRSA($encryptedPIN, 'PIN');
    }

    public function updateCardLimits(string $card_id, WallesterLimits $limits)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/limits", 'PATCH', [
            'body' => [
                'limits' => json_decode(json_encode($limits), true)
            ]
        ]);
    }


    public function blockCard(string $card_id, string $cardBlockType = null)
    {

        return $this->sendRequest("/v1/cards/{$card_id}/block", 'PATCH', [
            'body' => [
                'block_type' => $cardBlockType
            ]
        ]);
    }

    public function unblockCard(string $card_id)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/unblock", 'PATCH');
    }

    public function updatePinCode(string $card_id, string $pin)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/pin", 'PATCH', [
            'body' => [
                'encrypted_pin' => $pin
            ]
        ]);
    }


    public function updateCardStatusTest(string $card_id, int $status, string $cardBlockType = null)
    {
        $statusArray = array_flip(WallesterCardStatuses::STATUSES_FROM_RESPONSE);
        $body['status'] = $statusArray[$status];
        if ($cardBlockType) {
            $body['block_type'] = $cardBlockType;
        }

        return $this->sendRequest("/v1/test/cards/{$card_id}/status", 'PATCH', [
            'body' => $body
        ]);
    }


    public function updateSecurity(string $card_id, WallesterSecurity $wallesterSecurity)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/security", 'PATCH', [
            'body' => json_decode(json_encode($wallesterSecurity), true)

        ]);
    }

    public function update3DSPassword(string $card_id, WallesterSecure $wallesterSecure)
    {
        return $this->sendRequest("/v1/cards/{$card_id}/3d-secure", 'PATCH', [
            'body' => json_decode(json_encode($wallesterSecure), true)
        ]);
    }


}
