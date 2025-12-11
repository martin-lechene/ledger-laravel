<?php

namespace MartinLechene\LedgerManager\Tests\Unit\Models;

use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerTransaction;
use MartinLechene\LedgerManager\Models\LedgerActivityLog;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_account(): void
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
            'public_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
            'account_index' => 0,
        ]);

        $this->assertDatabaseHas('ledger_accounts', [
            'chain' => 'ethereum',
            'public_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
        ]);
    }

    public function test_account_has_device_relationship(): void
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

        $this->assertNotNull($account->device);
        $this->assertEquals($device->id, $account->device->id);
    }

    public function test_account_has_transactions_relationship(): void
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
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);

        $this->assertCount(1, $account->transactions);
    }

    public function test_get_pending_transactions(): void
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
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);

        LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => 'ethereum',
            'type' => 'send',
            'status' => 'signed',
            'from_address' => $account->public_address,
        ]);

        $pending = $account->getPendingTransactions();
        $this->assertCount(1, $pending);
    }

    public function test_get_balance(): void
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
            'balance' => ['ETH' => '1.5', 'USDT' => '1000'],
        ]);

        $allBalance = $account->getBalance();
        $this->assertEquals(['ETH' => '1.5', 'USDT' => '1000'], $allBalance);

        $ethBalance = $account->getBalance('ETH');
        $this->assertEquals('1.5', $ethBalance);

        $this->assertNull($account->getBalance('BTC'));
    }

    public function test_get_qr_code(): void
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
            'public_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
            'account_index' => 0,
        ]);

        $qrCode = $account->getQRCode();
        $this->assertStringStartsWith('data:image/png;base64,', $qrCode);
    }

    public function test_is_locked(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $activeAccount = LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/0",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 0,
            'is_active' => true,
        ]);

        $inactiveAccount = LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/1",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 1,
            'is_active' => false,
        ]);

        $this->assertFalse($activeAccount->isLocked());
        $this->assertTrue($inactiveAccount->isLocked());
    }

    public function test_by_chain_scope(): void
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

        $ethereumAccounts = LedgerAccount::byChain('ethereum')->get();
        $this->assertCount(1, $ethereumAccounts);
    }

    public function test_active_scope(): void
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
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/1",
            'public_address' => '0x' . bin2hex(random_bytes(20)),
            'account_index' => 1,
            'is_active' => false,
        ]);

        $activeAccounts = LedgerAccount::active()->get();
        $this->assertCount(1, $activeAccounts);
    }

    public function test_by_address_scope(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $address = '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb';
        
        LedgerAccount::create([
            'ledger_device_id' => $device->id,
            'chain' => 'ethereum',
            'derivation_path' => "m/44'/60'/0'/0/0",
            'public_address' => $address,
            'account_index' => 0,
        ]);

        $found = LedgerAccount::byAddress($address)->first();
        $this->assertNotNull($found);
        $this->assertEquals($address, $found->public_address);
    }
}
