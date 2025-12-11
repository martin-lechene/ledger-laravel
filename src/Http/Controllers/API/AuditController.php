<?php

namespace YourVendor\LedgerManager\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use YourVendor\LedgerManager\Security\SecurityAuditor;
use YourVendor\LedgerManager\Models\LedgerActivityLog;

class AuditController
{
    public function __construct(protected SecurityAuditor $auditor) {}

    public function audit(): JsonResponse
    {
        $audit = $this->auditor->performAudit();

        return response()->json([
            'success' => true,
            'data' => $audit,
        ]);
    }

    public function logs(int $days = 30): JsonResponse
    {
        $logs = LedgerActivityLog::where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'count' => $logs->count(),
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_operations' => LedgerActivityLog::count(),
            'operations_24h' => LedgerActivityLog::where('created_at', '>=', now()->subHours(24))->count(),
            'success_rate' => LedgerActivityLog::where('status', 'success')->count() / max(1, LedgerActivityLog::count()) * 100,
            'actions_breakdown' => LedgerActivityLog::groupBy('action')
                ->selectRaw('action, count(*) as count, status')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

