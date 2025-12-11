<?php

namespace MartinLechene\LedgerManager\Tests\Unit;

use MartinLechene\LedgerManager\ServiceProvider;
use MartinLechene\LedgerManager\Services\LedgerService;
use MartinLechene\LedgerManager\Services\TransportFactory;
use MartinLechene\LedgerManager\Services\ChainFactory;
use MartinLechene\LedgerManager\Services\DeviceDiscoveryService;
use MartinLechene\LedgerManager\Services\HIDService;
use MartinLechene\LedgerManager\Security\SecurityAuditor;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Application;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(ServiceProvider::class, $providers);
    }

    public function test_ledger_service_is_registered_as_singleton(): void
    {
        $service1 = $this->app->make(LedgerService::class);
        $service2 = $this->app->make(LedgerService::class);

        $this->assertInstanceOf(LedgerService::class, $service1);
        $this->assertSame($service1, $service2);
    }

    public function test_transport_factory_is_registered(): void
    {
        $factory = $this->app->make(TransportFactory::class);
        $this->assertInstanceOf(TransportFactory::class, $factory);
    }

    public function test_chain_factory_is_registered(): void
    {
        $factory = $this->app->make(ChainFactory::class);
        $this->assertInstanceOf(ChainFactory::class, $factory);
    }

    public function test_device_discovery_service_is_registered(): void
    {
        $service = $this->app->make(DeviceDiscoveryService::class);
        $this->assertInstanceOf(DeviceDiscoveryService::class, $service);
    }

    public function test_hid_service_is_registered(): void
    {
        $service = $this->app->make(HIDService::class);
        $this->assertInstanceOf(HIDService::class, $service);
    }

    public function test_security_auditor_is_registered(): void
    {
        $auditor = $this->app->make(SecurityAuditor::class);
        $this->assertInstanceOf(SecurityAuditor::class, $auditor);
    }

    public function test_config_is_merged(): void
    {
        $config = config('ledger');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('transports', $config);
        $this->assertArrayHasKey('chains', $config);
    }

    public function test_middleware_aliases_are_registered(): void
    {
        $middleware = $this->app['router']->getMiddleware();
        $this->assertArrayHasKey('ledger.throttle', $middleware);
        $this->assertArrayHasKey('ledger.security', $middleware);
    }
}
