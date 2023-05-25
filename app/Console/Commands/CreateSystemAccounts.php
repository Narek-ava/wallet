<?php

namespace App\Console\Commands;

use App\Enums\AccountStatuses;
use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSystemAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:system-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create system accounts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach (AccountType::NAMES as $accountType => $accountTypeName) {
            foreach (array_merge(Currency::FIAT_CURRENCY_NAMES, Currency::getList()) as $currency) {
                if (in_array($currency, Currency::getList())) {
                    if ($accountType != AccountType::TYPE_CRYPTO) {
                        continue;
                    }
                } else {
                    if (empty(AccountType::ACCOUNT_WIRE_TYPES[$accountType])) {
                        continue;
                    }
                }

                $dataForAccount = [
                    'currency' => $currency,
                    'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                    'account_type' => $accountType,
                    'status' => AccountStatuses::STATUS_ACTIVE
                ];

                $systemAccountExists = Account::query()->where($dataForAccount)->whereNull('payment_provider_id')->exists();

                if (!$systemAccountExists) {
                    Account::create(array_merge([
                        'id' => Str::uuid(),
                        'name' => 'System account ' . AccountType::getName($accountType) . ' ' . $currency,
                    ], $dataForAccount));
                }
            }
        }

        return 0;
    }
}
