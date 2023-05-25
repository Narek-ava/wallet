<?php

namespace App\Providers;

use App\Services\ExchangeRatesBitstampService;
use Illuminate\Support\ServiceProvider;

class ExchangeRatesBitstampServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ExchangeRatesBitstampFacade', function () {
            return new ExchangeRatesBitstampService;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
