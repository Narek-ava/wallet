<?php

namespace App\Services;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Exceptions\Chainalysis\RegisterWithdrawalAttemptFail;
use App\Facades\KrakenFacade;
use App\Models\Account;
use App\Models\Operation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use phpDocumentor\Reflection\Types\Self_;
use Throwable;
use function GuzzleHttp\Promise\exception_for;

/**
 * @property PendingRequest $client
 * @property PendingRequest $request
 */
class ChainalysisService
{
    public const RISK_LOW = 'LOW';
    public const RISK_MEDIUM = 'MEDIUM';
    public const RISK_HIGH = 'HIGH';
    public const RISK_SEVERE = 'SEVERE';

    public function __construct()
    {
        $client = resolve(Http::class);

        $this->client = $client::withHeaders([
            'Token' => config('cratos.chainalysis_api_key'),
            'Content-Type' => 'application/json',
        ]);

    }

    public function registerTransfer(
        string $userId,
        string $network,
        string $asset,
        string $txId,
        string $outputAddress,
        string $description
    )
    {
        //transferReference tx_id + ':' + outputAddress
        //description 'sent' or 'received'
        //network Currency::FULL_NAMES[] (example: 'Bitcoin');
        //asset (example: 'BTC')
        $response = $this->client->post(config('cratos.chainalysis_api_url') . '/v2/users/' . $userId . '/transfers', [
            'network' => $network,
            'asset' => $asset,
            'transferReference' => $txId . ':' . $outputAddress,
            'direction' => $description
        ]);

        return json_decode($response->body(), true);

    }

    public function registerWithdrawalAttempts(
        string $userId,
        string $network,
        string $asset,
        string $address,
        string $attemptIdentifier,
        ?int   $assetAmount,
        string $attemptTimestamp
    )
    {
        $response = $this->client->post(config('cratos.chainalysis_api_url') . '/v2/users/' . $userId . '/withdrawal-attempts', [
            'network' => $network,
            'asset' => $asset,
            'address' => $address,
            'attemptIdentifier' => $attemptIdentifier,
            'assetAmount' => $assetAmount,
            'attemptTimestamp' => $attemptTimestamp
        ]);
        return $response;

    }

    public function getTransferByExternalId(string $externalId)
    {

            $response = $this->client->get(config('cratos.chainalysis_api_url') . '/v2/transfers/' . $externalId);

        return json_decode($response->body(), true);

    }

    public function getTransferExposuresByExternalId(string $externalId)
    {

            $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/transfers/' . $externalId . '/exposures');



        return json_decode($response->body(), true);

    }

    public function getTransferAlertsByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/transfers/' . $externalId . '/alerts');

        return json_decode($response->body(), true);
    }

    public function getTransferNetworkIdentificationsByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/transfers/' . $externalId . '/network-identifications');

        return json_decode($response->body(), true);
    }

    public function getWithdrawalAttemptsByExternalId(string $externalId)
    {

        $response = $this->client->get(config('cratos.chainalysis_api_url') . '/v2/withdrawal-attempts/' . $externalId);

        return json_decode($response->body(), true);
    }

    public function getWithdrawalAttemptsExposuresByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/withdrawal-attempts/' . $externalId . '/exposures');

        return json_decode($response->body(), true);

    }

    public function getWithdrawalAttemptsAlertsByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/withdrawal-attempts/' . $externalId . '/alerts');

        return json_decode($response->body(), true);

    }

    public function getWithdrawalAttemptsHighRiskAddressesByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/withdrawal-attempts/' . $externalId . '/high-risk-addresses');

        return json_decode($response->body(), true);

    }

    public function getWithdrawalAttemptsNetworkIdentificationsByExternalId(string $externalId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v2/withdrawal-attempts/' . $externalId . '/network-identifications');

        return json_decode($response->body(), true);

    }

    public function registerUsersReceivedTransfers(
        string $userId,
        string $network,
        string $asset,
        string $txId,
        string $outputAddress,
        string $assetAmount,
        string $transferTimestamp
    )
    {

        $response =  $this->client->post(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/transfers/received', [
            'network' => $network,
            'asset' => $asset,
            'transferReference' => $txId . ':' . $outputAddress,
            'assetAmount' => $assetAmount,
            'transferTimestamp' => $transferTimestamp
        ]);

        return json_decode($response->body(), true);

    }

    public function getUsersReceivedTransfers(string $userId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/transfers/received');

        return json_decode($response->body(), true);

    }

    public function registerUsersSentTransfers(
        string $userId,
        string $network,
        string $asset,
        string $txId,
        string $outputAddress,
        string $assetAmount,
        string $transferTimestamp
    )
    {

        $response =  $this->client->post(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/transfers/sent', [
            'network' => $network,
            'asset' => $asset,
            'transferReference' => $txId . ':' . $outputAddress,
            'assetAmount' => $assetAmount,
            'transferTimestamp' => $transferTimestamp
        ]);

        return json_decode($response->body(), true);

    }

    public function getUsersSentTransfers(string $userId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/transfers/sent');

        return json_decode($response->body(), true);

    }

    public function registerUsersWithdrawalAddresses(
        string $userId,
        string $network,
        string $asset,
        string $txId,
        string $outputAddress,
        string $assetAmount,
        string $transferTimestamp
    )
    {

        $response =  $this->client->post(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/withdrawaladdresses', [
            'network' => $network,
            'asset' => $asset,
            'transferReference' => $txId . ':' . $outputAddress,
            'assetAmount' => $assetAmount,
            'transferTimestamp' => $transferTimestamp
        ]);

        return json_decode($response->body(), true);

    }

    public function getUsersWithdrawalAddresses(string $userId)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/withdrawaladdresses');

        return json_decode($response->body(), true);

    }

    public function deleteUsersDepositAddresses(string $userId, string $asset, string $address)
    {

        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId . '/depositaddresses/' . $asset . '/' . $address);

        return json_decode($response->body(), true);

    }

    public function getAlerts()
    {
        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/alerts');

        return json_decode($response->body(), true);

    }

    public function createAlertsIdentifierAssignment(string $alertIdentifier, string $alertAssignee)
    {
        $response =  $this->client->post(config('cratos.chainalysis_api_url') . '/v1/alerts/' . $alertIdentifier . '/assignment', [
            'alertAssignee' => $alertAssignee
        ]);

        return json_decode($response->body(), true);

    }

    public function getUsers()
    {
        $response =  $this->client->get(config('cratos.chainalysis_api_url'). '/v1/users');

        return json_decode($response->body(), true);
    }

    /**
     * @param string $userId
     * @return array
     */
    public function getUserById(string $userId): array
    {
        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/users/' . $userId);

        return json_decode($response->body(), true);
    }

    public function getAssets()
    {
        $response =  $this->client->get(config('cratos.chainalysis_api_url') . '/v1/assets/');

        return json_decode($response->body(), true);
    }


    /**
     * @param Account $account
     * @param string $address
     * @param string $currency
     * @param float|null $amount
     * @return string
     * @throws RegisterWithdrawalAttemptFail
     */
    public function getRiskScore(Account $account, string $address, string $currency, ?float $amount = null): ?float
    {
        try {
            $network = Currency::FULL_NAMES[$currency];
            $currency = $currency === Currency::CURRENCY_MCDAI ? 'DAI' : $currency;

            $time = date(DATE_ISO8601, strtotime('-1 minute'));
            $withdrewAttempt = self::registerWithdrawalAttempts(
                $account->id,
                $network,
                $currency,
                $address,
                $account->cProfile->cUser->id . ':' . $account->id,
                $amount ?: 0,
                $time
            );

            if ($withdrewAttempt->status() !== 202) {
                throw new RegisterWithdrawalAttemptFail("Cannot get register withdrawal attempt message: $withdrewAttempt");
            }
            sleep(9);

            $riskScore = self::getUserById($account->id)['riskScore'];
            if (empty($riskName)) {
                sleep(5);
                $riskScore = self::getUserById($account->id)['riskScore'];
            }
            return $this->getPercentByRiscScore($riskScore ?: self::RISK_LOW);
        } catch (Exception $exception) {
            logger()->error('ChainalysisError', [$exception->getMessage()]);
            return null;
        }
    }

    /**
     * @param string|null $riskScore
     * @return array
     */
    public function getPercentByRiscScore(?string $riskScore): ?float
    {
        switch ($riskScore) {
            case self::RISK_LOW:
                $riskScore = config('cratos.chainalysis_risk_score.low');
                break;
            case self::RISK_MEDIUM:
                $riskScore = config('cratos.chainalysis_risk_score.medium');
                break;
            case self::RISK_HIGH:
                $riskScore = config('cratos.chainalysis_risk_score.high');
                break;
            case self::RISK_SEVERE:
                $riskScore = config('cratos.chainalysis_risk_score.severe');
                break;
            default:
                $riskScore = null;
        }

        return $riskScore;

    }

    public function accountIsVrified(Account $account)
    {
        $verified = $account->cryptoAccountDetail->verified_at;
        if (is_null($verified)) {
            return false;
        }

        return $verified->toDateTimeString() > Carbon::now()->subDays(config(AccountService::CONFIG_RISK_SCORE_DAYS))->toDateTimeString();
    }

    public function isValidRisk(?string $riskScore): bool
    {
        return (is_null($riskScore) || $riskScore < config(AccountService::CONFIG_RISK_SCORE))
            && $riskScore !== self::RISK_SEVERE;
    }
}
