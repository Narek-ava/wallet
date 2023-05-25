<?php

namespace App\Providers;

use App\Enums\ExchangeApiProviders;
use App\Enums\Providers;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Operation;
use App\Models\Project;
use App\Observers\OperationObserver;
use App\Services\ExchangeInterface;
use App\Services\KrakenService;
use App\Services\NotificationUserService;
use App\Services\ProviderService;
use App\Services\SumSubNotificationsService;
use App\Services\TicketService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind(ExchangeInterface::class, function () {
            $exchangeApi = app(\Illuminate\Http\Request::class)->exchange_api;
            $request = request();
            if (!$exchangeApi && $request->transaction_type == TransactionType::EXCHANGE_TRX && $request->from_account) {
                //for manual exchange transactions
                $fromAccount = Account::find($request->from_account);
                $liquidityProvider = $fromAccount->provider ?? null;
                $exchangeApi = $liquidityProvider->api ?? null;
            }


            if (empty($liquidityProvider) ) {
                $project = config('projects.project');
                /* @var ProviderService $providerService */
                $providerService = resolve(ProviderService::class);
                if (!$project && request()->route()) {
                    $prefix = request()->route()->getPrefix();
                    if ($prefix == 'cabinet' || $prefix == 'ajax'|| $prefix == 'api' || $prefix == 'webhook') {
                        $project = \App\Models\Project::getCurrentProject();
                    }
                    $liquidityProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_LIQUIDITY, $project->id ?? null);
                } else {
                    $liquidityProvider = $providerService->getDefaultProviderByType(Providers::PROVIDER_LIQUIDITY);
                }

            }


            if ($liquidityProvider) {
                if ($exchangeApi) {
                    switch ($exchangeApi) {
                        case ExchangeApiProviders::EXCHANGE_KRAKEN:
                            return new KrakenService($liquidityProvider);


                    }
                }
                return new KrakenService($liquidityProvider);
            }

            return new KrakenService(Project::getCurrentProject());
        });

        $this->app->singleton(SumSubNotificationsService::class, function () {
            return new SumSubNotificationsService();
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        View::composer('backoffice.layouts._menu', function($view)
        {
            $view->with('notifications_count', (new NotificationUserService())->getNotificationUsersActiveDataCount() ?? null);
        });
        View::composer(['cabinet.notifications.index', 'cabinet.layouts.cabinet', 'cabinet.layouts.cabinet-auth'], function($view)
        {
            $view->with('notifications_count_client', (new NotificationUserService())->getNotificationUsersActiveDataCount() ?? null);
        });
        View::composer(['cabinet.layouts.cabinet', 'cabinet.help-desk.index'], function($view)
        {
            $view->with('active_tickets', (new TicketService())->getActiveTicketsCount());
        });
        View::composer(['cabinet.help-desk.index'], function($view)
        {
            $view->with('closed_tickets', (new TicketService())->getClosedTicketsCount());
        });
        View::composer('backoffice.layouts._menu', function($view)
        {
            $view->with('tickets_count', (new TicketService())->getBackofficeActiveTicketsCount());
        });
        View::composer('backoffice.tickets.index', function($view)
        {
            $view->with('backoffice_open_tickets_count', (new TicketService())->getBackofficeOpernTicketsCount());
        });
        View::composer('backoffice.tickets.index', function($view)
        {
            $view->with('backoffice_closed_tickets_count', (new TicketService())->getBackofficeClosedTicketsCount());
        });
        View::composer('backoffice.tickets.index', function($view)
        {
            $view->with('backoffice_new_tickets_count', (new TicketService())->getBackofficeNewTicketsCount());
        });
        View::composer('*', function ($view) {
            $view->with('currentProject', config('projects.currentProject'));
        });

        View::composer('backoffice.*', function ($view) {
            setPermissionsTeamId(config('projects.currentProject'));
            $view->with('currentAdmin', auth()->guard('bUser')->user());
        });

        Operation::observe(OperationObserver::class);
    }
}
