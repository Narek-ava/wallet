<?php

namespace App\Services;

use App\Enums\Currency;
use App\Models\Account;
use App\Models\ClientSystemWallet;

class ClientSystemWalletService
{
    public function checkIsClientWalletSystemWallet(Account $clientAccount): bool
    {
        if (!in_array($clientAccount->currency, Currency::getList())) {
            return false;
        }

        $systemAccount = $this->getSystemWalletByCurrency($clientAccount->currency, $clientAccount->cProfile->cUser->project_id);

        return $clientAccount->cryptoAccountDetail->wallet_id === $systemAccount->wallet_id;
    }

    public function getSystemWalletByCurrency(?string $currency, ?string $projectId = null)
    {
        $currency = strtoupper($currency);
        if (!in_array($currency, Currency::MAIN_CRYPTOCURRENCIES)) {
            foreach (Currency::TOKENS_WITH_SUBTOKENS as $cryptocurrency => $subtokens) {
                if (in_array($currency, $subtokens)) {
                    $currency = $cryptocurrency;
                    break;
                }
            }
        }

        $query = ClientSystemWallet::query()->where('currency', $currency);

        if ($projectId) {
             $query->where('project_id', $projectId);
        }
        return $query->first();
    }


    public function getClientSystemWalletsForProjects(?array $projectIds = null)
    {
        $query = ClientSystemWallet::query();
        if ($projectIds) {
            $query->whereIn('project_id', $projectIds);
        }
        return $query->get();
    }
}
