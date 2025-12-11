<?php

namespace MartinLechene\LedgerManager\Tests\Unit\Models;

use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerActivityLog;
use MartinLechene\LedgerManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LedgerActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_activity_log(): void
    {
        $log = LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'success',
            'details' => 'Device connected successfully',
        ]);

        $this->assertDatabaseHas('ledger_activity_logs', [
            'action' => 'connect',
            'status' => 'success',
        ]);
    }

    public function test_static_log_method(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $log = LedgerActivityLog::log('connect', 'success', $device, null, 'Device connected');

        $this->assertInstanceOf(LedgerActivityLog::class, $log);
        $this->assertEquals('connect', $log->action);
        $this->assertEquals('success', $log->status);
        $this->assertEquals($device->id, $log->ledger_device_id);
        $this->assertEquals('Device connected', $log->details);
    }

    public function test_log_with_account(): void
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

        $log = LedgerActivityLog::log('sign_transaction', 'success', $device, $account, 'Transaction signed');

        $this->assertEquals($device->id, $log->ledger_device_id);
        $this->assertEquals($account->id, $log->ledger_account_id);
    }

    public function test_log_with_error(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $log = LedgerActivityLog::log('connect', 'failed', $device, null, null, 'Connection timeout');

        $this->assertEquals('failed', $log->status);
        $this->assertEquals('Connection timeout', $log->error_details);
    }

    public function test_log_has_device_relationship(): void
    {
        $device = LedgerDevice::create([
            'device_id' => 'test-device-123',
            'transport_type' => 'usb',
            'is_active' => false,
        ]);

        $log = LedgerActivityLog::create([
            'ledger_device_id' => $device->id,
            'action' => 'connect',
            'status' => 'success',
        ]);

        $this->assertNotNull($log->device);
        $this->assertEquals($device->id, $log->device->id);
    }

    public function test_log_has_account_relationship(): void
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

        $log = LedgerActivityLog::create([
            'ledger_account_id' => $account->id,
            'action' => 'sign_transaction',
            'status' => 'success',
        ]);

        $this->assertNotNull($log->account);
        $this->assertEquals($account->id, $log->account->id);
    }

    public function test_by_action_scope(): void
    {
        LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'success',
        ]);

        LedgerActivityLog::create([
            'action' => 'disconnect',
            'status' => 'success',
        ]);

        $connectLogs = LedgerActivityLog::byAction('connect')->get();
        $this->assertCount(1, $connectLogs);
    }

    public function test_by_status_scope(): void
    {
        LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'success',
        ]);

        LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'failed',
        ]);

        $successLogs = LedgerActivityLog::byStatus('success')->get();
        $this->assertCount(1, $successLogs);
    }

    public function test_recent_scope(): void
    {
        $recentLog = LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'success',
        ]);
        $recentLog->timestamps = false;
        $recentLog->created_at = now();
        $recentLog->save();

        $oldLog = LedgerActivityLog::create([
            'action' => 'connect',
            'status' => 'success',
        ]);
        $oldLog->timestamps = false;
        $oldLog->created_at = now()->subDays(60);
        $oldLog->save();

        $recentLogs = LedgerActivityLog::recent(30)->get();
        $this->assertCount(1, $recentLogs);
    }
}
