<?php

namespace MartinLechene\LedgerManager\Tests\Unit\Models;

use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerTransaction;
use MartinLechene\LedgerManager\Models\LedgerActivityLog;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerDeviceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_device(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'model' => 'Nano S',
            'transport_type' => 'usb',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ledger_devices', [
            'device_id' => 'test-device-123',
            'model' => 'Nano S',
        ]);
    }

    public function test_device_has_accounts_relationship(): void
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

        $this->assertCount(1, $device->accounts);
        $this->assertEquals($account->id, $device->accounts->first()->id);
    }

    public function test_device_has_transactions_relationship(): void
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

        $transaction = LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => 'ethereum',
            'type' => 'send',
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);

        $this->assertCount(1, $device->transactions);
    }

    public function test_device_has_logs_relationship(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        LedgerActivityLog::create([
            'ledger_device_id' => $device->id,
            'action' => 'connect',
            'status' => 'success',
        ]);

        $this->assertCount(1, $device->logs);
    }

    public function test_get_display_name(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'name' => 'My Ledger',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $this->assertEquals('My Ledger', $device->getDisplayName());

        $device2 = LedgerDevice::create([
            'device_id' => 'test-device-456',
            'model' => 'Nano X',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $this->assertEquals('Nano X', $device2->getDisplayName());

        $device3 = LedgerDevice::create([
            'device_id' => 'test-device-789',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $this->assertStringContainsString('Device #', $device3->getDisplayName());
    }

    public function test_is_connected(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => true,
            'last_connected_at' => now(),
        ]);

        $this->assertTrue($device->isConnected());

        $device->update(['is_active' => false]);
        $this->assertFalse($device->isConnected());
    }

    public function test_get_active_accounts_count(): void
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
            'is_active' => true,
        ]);

        LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'bitcoin',
            'derivation_path' => "m/44'/0'/0'/0/0",
            'public_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
            'account_index' => 0,
            'is_active' => false,
        ]);

        $this->assertEquals(1, $device->getActiveAccountsCount());
    }

    public function test_active_scope(): void
    {
        LedgerDevice::create([
            'device_id' => 'device-1',
            'transport_type' => 'usb',
            'is_active' => true,
        ]);

        LedgerDevice::create([
            'device_id' => 'device-2',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $activeDevices = LedgerDevice::active()->get();
        $this->assertCount(1, $activeDevices);
    }

    public function test_by_transport_scope(): void
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

        $usbDevices = LedgerDevice::byTransport('usb')->get();
        $this->assertCount(1, $usbDevices);
    }

    public function test_by_model_scope(): void
    {
        LedgerDevice::create([
            'device_id' => 'device-1',
            'model' => 'Nano S',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        LedgerDevice::create([
            'device_id' => 'device-2',
            'model' => 'Nano X',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $nanoSDevices = LedgerDevice::byModel('Nano S')->get();
        $this->assertCount(1, $nanoSDevices);
    }
}
