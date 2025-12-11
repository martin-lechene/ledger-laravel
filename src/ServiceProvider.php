<?php

namespace MartinLechene\LedgerManager;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use MartinLechene\LedgerManager\Services\LedgerService;
use MartinLechene\LedgerManager\Services\TransportFactory;
use MartinLechene\LedgerManager\Services\ChainFactory;
use MartinLechene\LedgerManager\Services\DeviceDiscoveryService;
use MartinLechene\LedgerManager\Services\HIDService;
use MartinLechene\LedgerManager\Security\SecurityAuditor;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ledger.php',
            'ledger'
        );

        // Register services as singletons
        $this->app->singleton(TransportFactory::class, function ($app) {
            return new TransportFactory(config('ledger.transports', []));
        });

        $this->app->singleton(ChainFactory::class, function ($app) {
            return new ChainFactory(config('ledger.chains', []));
        });

        $this->app->singleton(HIDService::class);

        $this->app->singleton(DeviceDiscoveryService::class, function ($app) {
            return new DeviceDiscoveryService(
                $app->make(TransportFactory::class),
                $app->make(HIDService::class)
            );
        });

        $this->app->singleton(LedgerService::class, function ($app) {
            return new LedgerService(
                $app->make(TransportFactory::class),
                $app->make(ChainFactory::class),
                $app->make(DeviceDiscoveryService::class)
            );
        });

        $this->app->singleton(SecurityAuditor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/ledger.php' => config_path('ledger.php'),
        ], 'ledger-config');

        // Publish migrations
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'ledger-migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ledger');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register middleware
        $this->app['router']->aliasMiddleware(
            'ledger.throttle',
            \MartinLechene\LedgerManager\Http\Middleware\LedgerThrottle::class
        );
        $this->app['router']->aliasMiddleware(
            'ledger.security',
            \MartinLechene\LedgerManager\Http\Middleware\LedgerSecurityHeaders::class
        );
    }
}

