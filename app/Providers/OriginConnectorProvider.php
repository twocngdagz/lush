<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OriginConnector\Connector;

class OriginConnectorProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Connector::class, function ($app) {
            switch (env('ORIGIN_CONNECTOR_IDENTIFIER')) {
                case 'lush-cms-v1':
                    return new \App\Services\OriginConnector\Providers\Lush\LushConnector;
                default:
                    throw new \App\Services\OriginConnector\Exceptions\ConnectorException('Origin Connector Identifier not recognized.', 503);
            }
        });
    }
}
