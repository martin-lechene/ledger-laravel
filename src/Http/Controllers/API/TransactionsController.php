<?php

namespace YourVendor\LedgerManager\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use YourVendor\LedgerManager\Services\LedgerService;
use YourVendor\LedgerManager\Models\LedgerTransaction;

class TransactionsController
{
    public function __construct(protected LedgerService $ledger) {}

    public function list(Request $request): JsonResponse
    {
        $history = $this->ledger->getTransactionHistory(
            $request->query('device_id'),
            $request->query('chain'),
            $request->query('limit', 50)
        );

        return response()->json([
            'success' => true,
            'data' => $history,
            'count' => $history->count(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = LedgerTransaction::with('account')->find($id);

        if (!$transaction) {
            return response()->json(['success' => false, 'error' => 'Transaction not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    public function sign(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'device_id' => 'required|integer',
                'chain' => 'required|string',
                'derivation_path' => 'required|string',
                'tx_data' => 'required|string',
            ]);

            $device = \YourVendor\LedgerManager\Models\LedgerDevice::find($validated['device_id']);
            if (!$device) {
                return response()->json(['success' => false, 'error' => 'Device not found'], 404);
            }

            $this->ledger->connect($device->device_id, $device->transport_type);
            $this->ledger->selectChain($validated['chain']);

            $transaction = $this->ledger->signTransaction(
                $validated['derivation_path'],
                $validated['tx_data']
            );

            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}

