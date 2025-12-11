<?php

namespace YourVendor\LedgerManager\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use YourVendor\LedgerManager\Services\LedgerService;
use YourVendor\LedgerManager\Models\LedgerAccount;

class AccountsController
{
    public function __construct(protected LedgerService $ledger) {}

    public function list(?string $chain = null): JsonResponse
    {
        $accounts = $this->ledger->getAllAccounts($chain);

        return response()->json([
            'success' => true,
            'data' => $accounts,
            'count' => $accounts->count(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $account = LedgerAccount::with(['device', 'transactions'])->find($id);

        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    public function generateAddresses(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'chain' => 'required|string',
                'count' => 'integer|min:1|max:100',
                'start_index' => 'integer|min:0',
            ]);

            $device = \YourVendor\LedgerManager\Models\LedgerDevice::find($validated['device_id']);
            if (!$device) {
                return response()->json(['success' => false, 'error' => 'Device not found'], 404);
            }

            $this->ledger->connect($device->device_id, $device->transport_type);
            $addresses = $this->ledger->generateAddressRange(
                $validated['chain'],
                $validated['start_index'] ?? 0,
                $validated['count'] ?? 10
            );

            return response()->json([
                'success' => true,
                'data' => $addresses,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

