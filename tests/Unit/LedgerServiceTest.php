<?php

namespace MartinLechene\LedgerManager\Tests\Unit;

use MartinLechene\LedgerManager\Services\LedgerService;
use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerTransaction;
use MartinLechene\LedgerManager\Models\LedgerActivityLog;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LedgerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(LedgerService::class);
    }

    public function test_can_discover_devices(): void
    {
        $devices = $this->service->discoverDevices('usb');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $devices);
    }

    public function test_can_connect_to_device(): void
    {
        $deviceId = 'test-device-123';
        $result = $this->service->connect($deviceId, 'usb');

        $this->assertTrue($result);
        $this->assertDatabaseHas('ledger_devices', [
            'device_id' => $deviceId,
            'is_active' => true,
        ]);

        $device = $this->service->getCurrentDevice();
        $this->assertNotNull($device);
        $this->assertEquals($deviceId, $device->device_id);
    }

    public function test_can_disconnect_device(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');
        
        $result = $this->service->disconnect();
        $this->assertTrue($result);
        $this->assertNull($this->service->getCurrentDevice());

        $device = LedgerDevice::where('device_id', $deviceId)->first();
        $this->assertFalse($device->is_active);
    }

    public function test_can_get_device_by_id(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-456',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $found = $this->service->getDevice('test-device-456');
        $this->assertNotNull($found);
        $this->assertEquals($device->id, $found->id);
    }

    public function test_can_get_all_devices(): void
    {
        LedgerDevice::create([
            'device_id' => 'device-1',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        LedgerDevice::create([
            'device_id' => 'device-2',
            'transport_type' => 'bluetooth',
            'is_active' => false,
        ]);

        $devices = $this->service->getAllDevices();
        $this->assertCount(2, $devices);
    }

    public function test_can_select_chain(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');

        $result = $this->service->selectChain('ethereum');
        $this->assertInstanceOf(LedgerService::class, $result);
    }

    public function test_select_chain_throws_exception_without_device(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No device connected');
        
        $this->service->selectChain('ethereum');
    }

    public function test_can_get_address(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');
        $this->service->selectChain('ethereum');

        $address = $this->service->getAddress("m/44'/60'/0'/0/0");
        $this->assertIsString($address);
        $this->assertStringStartsWith('0x', $address);
        $this->assertEquals(42, strlen($address)); // Ethereum address length
    }

    public function test_get_address_throws_exception_without_chain(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No chain selected');
        
        $this->service->getAddress("m/44'/60'/0'/0/0");
    }

    public function test_can_generate_address_range(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');
        $this->service->selectChain('ethereum');

        $addresses = $this->service->generateAddressRange('ethereum', 0, 5);
        
        $this->assertCount(5, $addresses);
        $this->assertDatabaseCount('ledger_accounts', 5);

        foreach ($addresses as $addr) {
            $this->assertArrayHasKey('index', $addr);
            $this->assertArrayHasKey('path', $addr);
            $this->assertArrayHasKey('address', $addr);
            $this->assertArrayHasKey('account_id', $addr);
        }
    }

    public function test_can_sign_transaction(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => true,
        ]);

        $account = LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/0",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 0,
        ]);

        $this->service->connect('test-device-123', 'usb');
        $this->service->selectChain('ethereum');

        $txData = json_encode(['to' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb', 'value' => '1000000000000000000']);
        $transaction = $this->service->signTransaction("m/44'/60'/0'/0/0", $txData);

        $this->assertInstanceOf(LedgerTransaction::class, $transaction);
        $this->assertEquals('signed', $transaction->status);
        $this->assertNotNull($transaction->signed_data);
        $this->assertNotNull($transaction->signed_at);
    }

    public function test_sign_transaction_throws_exception_without_device(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No device connected');
        
        $this->service->signTransaction("m/44'/60'/0'/0/0", '{}');
    }

    public function test_can_sign_message(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');
        $this->service->selectChain('ethereum');

        $signature = $this->service->signMessage("m/44'/60'/0'/0/0", 'Hello World');
        
        $this->assertIsString($signature);
        $this->assertStringStartsWith('0x', $signature);
    }

    public function test_can_get_all_accounts(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/0",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 0,
        ]);

        LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'bitcoin',
            'derivation_path' => "m/44'/0'/0'/0/0",
            'public_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
            'account_index' => 0,
        ]);

        $allAccounts = $this->service->getAllAccounts();
        $this->assertCount(2, $allAccounts);

        $ethereumAccounts = $this->service->getAllAccounts('ethereum');
        $this->assertCount(1, $ethereumAccounts);
    }

    public function test_can_get_transaction_history(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $account = LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/0",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 0,
        ]);

        LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => 'ethereum',
            'type' => 'send',
            'status' => 'signed',
            'from_address' => $account->public_address,
        ]);

        $history = $this->service->getTransactionHistory();
        $this->assertCount(1, $history);

        $filteredHistory = $this->service->getTransactionHistory($device->device_id, 'ethereum');
        $this->assertCount(1, $filteredHistory);
    }

    public function test_can_get_app_version(): void
    {
        $version = $this->service->getAppVersion();
        $this->assertIsString($version);
        $this->assertNotEmpty($version);
    }

    public function test_activity_logs_are_created(): void
    {
        $deviceId = 'test-device-123';
        $this->service->connect($deviceId, 'usb');

        $logs = LedgerActivityLog::where('action', 'connect')->get();
        $this->assertGreaterThan(0, $logs->count());
    }
}
