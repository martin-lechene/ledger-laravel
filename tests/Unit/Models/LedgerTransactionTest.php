<?php

namespace MartinLechene\LedgerManager\Tests\Unit\Models;

use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerTransaction;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_transaction(): void
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
            'to_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb',
            'amount' => '1000000000000000000',
        ]);

        $this->assertDatabaseHas('ledger_transactions', [
            'chain' => 'ethereum',
            'status' => 'pending',
        ]);
    }

    public function test_transaction_has_account_relationship(): void
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

        $this->assertNotNull($transaction->account);
        $this->assertEquals($account->id, $transaction->account->id);
    }

    public function test_mark_as_signed(): void
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

        $signedData = '0x' . bin2hex(random_bytes(65));
        $transaction->markAsSigned($signedData);

        $this->assertEquals('signed', $transaction->fresh()->status);
        $this->assertEquals($signedData, $transaction->fresh()->signed_data);
        $this->assertNotNull($transaction->fresh()->signed_at);
    }

    public function test_mark_as_submitted(): void
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
            'status' => 'signed',
            'from_address' => $account->public_address,
        ]);

        $txHash = '0x' . bin2hex(random_bytes(32));
        $transaction->markAsSubmitted($txHash);

        $this->assertEquals('submitted', $transaction->fresh()->status);
        $this->assertEquals($txHash, $transaction->fresh()->tx_hash);
        $this->assertNotNull($transaction->fresh()->submitted_at);
    }

    public function test_mark_as_confirmed(): void
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
            'status' => 'submitted',
            'from_address' => $account->public_address,
        ]);

        $transaction->markAsConfirmed();

        $this->assertEquals('confirmed', $transaction->fresh()->status);
        $this->assertNotNull($transaction->fresh()->confirmed_at);
    }

    public function test_mark_as_failed(): void
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

        $error = 'Transaction failed: Insufficient funds';
        $transaction->markAsFailed($error);

        $this->assertEquals('failed', $transaction->fresh()->status);
        $this->assertEquals($error, $transaction->fresh()->error_message);
    }

    public function test_by_chain_scope(): void
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
            'chain' => 'bitcoin',
            'type' => 'send',
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);

        $ethereumTxs = LedgerTransaction::byChain('ethereum')->get();
        $this->assertCount(1, $ethereumTxs);
    }

    public function test_by_status_scope(): void
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

        $pendingTxs = LedgerTransaction::byStatus('pending')->get();
        $this->assertCount(1, $pendingTxs);
    }

    public function test_recent_scope(): void
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

        $recentTx = LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => 'ethereum',
            'type' => 'send',
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);
        $recentTx->timestamps = false;
        $recentTx->created_at = now();
        $recentTx->save();

        $oldTx = LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => 'ethereum',
            'type' => 'send',
            'status' => 'pending',
            'from_address' => $account->public_address,
        ]);
        $oldTx->timestamps = false;
        $oldTx->created_at = now()->subDays(60);
        $oldTx->save();

        $recentTxs = LedgerTransaction::recent(30)->get();
        $this->assertCount(1, $recentTxs);
    }

    public function test_pending_scope(): void
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

        $pendingTxs = LedgerTransaction::pending()->get();
        $this->assertCount(1, $pendingTxs);
    }
}
