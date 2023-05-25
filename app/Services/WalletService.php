<?php


namespace App\Services;


use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Enums\Notification;
use App\Enums\NotificationRecipients;
use App\Enums\Providers;
use App\Facades\ActivityLogFacade;
use App\Facades\EmailFacade;
use App\Models\Account;
use App\Models\Cabinet\CProfile;
use App\Models\ClientSystemWallet;
use App\Models\CryptoAccountDetail;
use App\Models\Project;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class WalletService
{

    public $cUser;
    public $cProfile;
    public $passphrase;
    protected $notificationService;
    protected $notificationUserService;

    public function __construct()
    {
        $this->passphrase = Str::random(5);
        $this->notificationService = new NotificationService();
        $this->notificationUserService = new NotificationUserService();
    }

    public function generateWallet(BitGOAPIService $service, string $coin, CProfile $cProfile)
    {
        $coin = strtolower($coin);
        $success = LogResult::RESULT_SUCCESS;

        $coinPrefix = $this->getConfigs($cProfile->cUser->project_id, 'coin_prefix');

        try {
            $allowedCurrencies = Currency::getBitGoAllowedCurrencies($cProfile->cUser->project_id);
            $data = [
                'label' => $cProfile->getFullName() . ' ' . $allowedCurrencies[$coinPrefix . strtolower($coin)],
                'passphrase' => $this->passphrase
            ];
            logger()->info('WalletGenerateData', $data);

            /* @var ClientSystemWalletService $clientSystemWalletService */
            $clientSystemWalletService = resolve(ClientSystemWalletService::class);
            $clientSystemWallet = $clientSystemWalletService->getSystemWalletByCurrency($coin, $cProfile->cUser->project_id);

            if (!$clientSystemWallet) {
                throw new \Exception('MissingClientSystemWallet' . $coin);
            }
            $walletId = $clientSystemWallet->wallet_id;
            $this->passphrase = $clientSystemWallet->passphrase;

            $generatedWalletJson = $service->generateWalletAddress($coin, $walletId, $cProfile->getFullName() . ' ' . $coin);
            $generatedWallet = json_decode($generatedWalletJson);
            $coin = strtoupper($coin);


            /* @var ProviderService $providerService*/
            $providerService = resolve(ProviderService::class);
            $project = Project::getCurrentProject();
            $walletProvider = $providerService->getWalletProviderForProject($project->id ?? null);

            if (!$walletProvider) {
                throw new \Exception(t('withdraw_crypto_error_message'));
            }

            $account = new Account([
                'name' => $generatedWallet->label,
                'c_profile_id' => $cProfile->id,
                'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
                'account_type' => AccountType::TYPE_CRYPTO,
                'currency' => $coin,
                'payment_provider_id' => $walletProvider->id
            ]);
            $account->save();
            $account->refresh();
            $cryptoAccountDetails = new CryptoAccountDetail([
                'coin' => $coin,
                'label' => $generatedWallet->label,
                'passphrase' => $this->passphrase,
                'address' => $generatedWallet->address,
                'wallet_id' => $walletId,
                'account_id' => $account->id,
                'wallet_data' => $generatedWalletJson,
            ]);
            $cryptoAccountDetails->save();
            $file = fopen(storage_path('app/coins.txt'), 'a');
            fputs($file, date('Y-m-d H:i:s'). "{$generatedWalletJson} - {$this->passphrase}" . PHP_EOL);
            fclose($file);
        } catch (\Exception $e) {
            $success = LogResult::RESULT_FAILURE;
            logger()->error('WalletGenerateError', [$e->getMessage(), $e->getTraceAsString()]);
            $message = $e->getMessage();
        }
        $result = [
            'success' => $success,
            'message' => $message ?? '',
            'coin' => $coin,
            'address' => $cryptoAccountDetails->address ?? null,
            'account' => $account ?? null,
        ];

        return $result;
    }

    public function getConfigs(string $projectId, $key)
    {
        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);
        $project = Project::find($projectId);

        $walletProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_WALLET, $project->id);

        $configKey = 'walletproviders.' . $walletProvider->api . '.' . $walletProvider->api_account . '.' . $key;

        return config($configKey);
    }

    public function addNewWallet(BitGOAPIService $bitGOAPIService, string $coin, CProfile $cProfile): ?Account
    {
        $result = $this->generateWallet($bitGOAPIService, $coin, $cProfile);
        $success = $result['success'];

        if ($success !== LogResult::RESULT_SUCCESS) {
            sleep(7);
            $result = $this->generateWallet($bitGOAPIService, $coin, $cProfile);
            $success = $result['success'];
        }

        if ($success === LogResult::RESULT_SUCCESS) {
            EmailFacade::sendCreatingNewWalletForClient($cProfile->cUser, $result['coin'], $result['address']);
        }

        ActivityLogFacade::saveLog(LogMessage::CREATE_NEW_WALLET_REQUEST,
            ['newStatus' => $success == LogResult::RESULT_SUCCESS ? 'Success' : 'Failed'],
            $success,
            LogType::TYPE_ADD_NEW_WALLET,
            $cProfile->cUser->id
        );

        return $result['account'];
    }

    public function getAllowedFiatForNewWallets(CProfile $cProfile)
    {
        $usedCurrencies = $this->getFiatWallets($cProfile)->pluck('currency')->toArray();

        return array_diff(Currency::FIAT_CURRENCY_NAMES, $usedCurrencies);
    }

    public function getFiatWallets(CProfile $cProfile)
    {
        return $cProfile->accounts()
            ->where([
                'is_external' => !AccountType::ACCOUNT_EXTERNAL,
                'account_type' => AccountType::TYPE_FIAT
            ])
            ->orderByDesc('balance')
            ->whereIn('currency', Currency::FIAT_CURRENCY_NAMES)->get();
    }

    public function createFiatWallet(CProfile $cProfile, string $currency): Account
    {
        $account = new Account();
        $account->fill([
            'account_type' => AccountType::TYPE_FIAT,
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT,
            'c_profile_id' => $cProfile->id,
            'status' => AccountStatuses::STATUS_ACTIVE,
            'name' => $cProfile->getFullName() . ' ' . $currency,
            'currency' => $currency,
            'is_external' => 0,
            'balance' => 0
        ]);
        $account->save();

        EmailFacade::sendCreatingNewFiatWalletForClient($cProfile->cUser, $currency);

        ActivityLogFacade::saveLog(LogMessage::CREATE_NEW_FIAT_WALLET_REQUEST,
            ['newStatus' => 'Success'],
            LogResult::RESULT_SUCCESS,
            LogType::TYPE_ADD_NEW_FIAT_WALLET,
            $cProfile->cUser->id
        );

        return $account;
    }

    public function getFiatWallet(CProfile $cProfile, string $id): Account
    {
        return $cProfile->accounts()->where([
            'id' => $id,
            'account_type' => AccountType::TYPE_FIAT
        ])->firstOrFail();
    }
}
