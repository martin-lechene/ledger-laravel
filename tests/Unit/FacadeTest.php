<?php

namespace MartinLechene\LedgerManager\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use MartinLechene\LedgerManager\Facades\Ledger;
use MartinLechene\LedgerManager\Services\LedgerService;
use MartinLechene\LedgerManager\Tests\TestCase;

class FacadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_facade_resolves_ledger_service(): void
    {
        $service = Ledger::getFacadeRoot();
        $this->assertInstanceOf(LedgerService::class, $service);
    }

    public function test_facade_can_discover_devices(): void
    {
        $devices = Ledger::discoverDevices('usb');
        $this->assertInstanceOf(Collection::class, $devices);
    }

    public function test_facade_can_connect(): void
    {
        $result = Ledger::connect('test-device-123', 'usb');
        $this->assertTrue($result);
    }

    public function test_facade_can_get_current_device(): void
    {
        Ledger::connect('test-device-123', 'usb');
        $device = Ledger::getCurrentDevice();
        $this->assertNotNull($device);
    }

    public function test_facade_can_get_all_devices(): void
    {
        $devices = Ledger::getAllDevices();
        $this->assertInstanceOf(Collection::class, $devices);
    }

    public function test_facade_can_select_chain(): void
    {
        Ledger::connect('test-device-123', 'usb');
        $result = Ledger::selectChain('ethereum');
        $this->assertInstanceOf(LedgerService::class, $result);
    }

    public function test_facade_can_get_address(): void
    {
        Ledger::connect('test-device-123', 'usb');
        Ledger::selectChain('ethereum');

        $address = Ledger::getAddress("m/44'/60'/0'/0/0");
        $this->assertIsString($address);
        $this->assertStringStartsWith('0x', $address);
    }

    public function test_facade_can_get_all_accounts(): void
    {
        $accounts = Ledger::getAllAccounts();
        $this->assertInstanceOf(Collection::class, $accounts);
    }

    public function test_facade_can_get_transaction_history(): void
    {
        $history = Ledger::getTransactionHistory();
        $this->assertInstanceOf(Collection::class, $history);
    }

    public function test_facade_can_get_app_version(): void
    {
        $version = Ledger::getAppVersion();
        $this->assertIsString($version);
    }
}
