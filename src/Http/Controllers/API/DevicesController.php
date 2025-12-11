<?php

namespace YourVendor\LedgerManager\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use YourVendor\LedgerManager\Services\LedgerService;
use YourVendor\LedgerManager\Models\LedgerDevice;

class DevicesController
{
    public function __construct(protected LedgerService $ledger) {}

    public function discover(string $transport = 'usb'): JsonResponse
    {
        try {
            $devices = $this->ledger->discoverDevices($transport);
            return response()->json([
                'success' => true,
                'data' => $devices,
                'count' => $devices->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function list(): JsonResponse
    {
        $devices = LedgerDevice::with(['accounts', 'transactions'])->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'device_id' => $d->device_id,
                'model' => $d->model,
                'serial_number' => $d->serial_number,
                'transport_type' => $d->transport_type,
                'is_active' => $d->is_active,
                'last_connected_at' => $d->last_connected_at,
                'accounts_count' => $d->accounts()->count(),
                'transactions_count' => $d->transactions()->count(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $device = LedgerDevice::with(['accounts', 'logs'])->find($id);

        if (!$device) {
            return response()->json([
                'success' => false,
                'error' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $device,
        ]);
    }

    public function connect(int $id): JsonResponse
    {
        try {
            $device = LedgerDevice::find($id);
            if (!$device) {
                return response()->json(['success' => false, 'error' => 'Device not found'], 404);
            }

            $connected = $this->ledger->connect($device->device_id, $device->transport_type);

            return response()->json([
                'success' => $connected,
                'message' => $connected ? 'Connected successfully' : 'Connection failed',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function disconnect(int $id): JsonResponse
    {
        try {
            $this->ledger->disconnect();
            LedgerDevice::find($id)?->update(['is_active' => false]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

