<?php

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SystemAccountSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        foreach (Currency::FIAT_CURRENCY_NAMES as $currency) {
            foreach (AccountType::ACCOUNT_WIRE_TYPES as $wireType => $wireName) {
                $account = Account::query()->where('currency', $currency)
                    ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                    ->whereNull('payment_provider_id')
                    ->where('account_type', $wireType)
                    ->first();
                if (!$account) {
                    Account::create([
                        'id' => Str::uuid(),
                        'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                        'currency' => $currency,
                        'account_type' => $wireType,
                        'name' => "System account {$wireName} {$currency}"
                    ]);
                }
            }

        }

        foreach (Currency::getList() as $currency) {

            $account = Account::query()->where('currency', $currency)
                ->where('owner_type', AccountType::ACCOUNT_OWNER_TYPE_SYSTEM)
                ->whereNull('payment_provider_id')
                ->first();
            if (!$account) {
                Account::create([
                    'id' => Str::uuid(),
                    'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_SYSTEM,
                    'currency' => $currency,
                    'account_type' => AccountType::TYPE_CRYPTO,
                    'name' => "System account {$currency}"
                ]);
            }

        }
    }
}
