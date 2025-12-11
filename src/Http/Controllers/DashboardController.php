<?php

namespace YourVendor\LedgerManager\Http\Controllers;

use Illuminate\View\View;
use YourVendor\LedgerManager\Services\LedgerService;
use YourVendor\LedgerManager\Security\SecurityAuditor;
use YourVendor\LedgerManager\Models\LedgerDevice;
use YourVendor\LedgerManager\Models\LedgerTransaction;
use YourVendor\LedgerManager\Models\LedgerActivityLog;

class DashboardController
{
    public function __construct(
        protected LedgerService $ledger,
        protected SecurityAuditor $auditor
    ) {}

    public function index(): View
    {
        $stats = [
            'devices' => LedgerDevice::count(),
            'active_devices' => LedgerDevice::where('is_active', true)->count(),
            'accounts' => \YourVendor\LedgerManager\Models\LedgerAccount::count(),
            'transactions' => LedgerTransaction::count(),
            'today_operations' => LedgerActivityLog::where('created_at', '>=', now()->startOfDay())->count(),
        ];

        $recentTransactions = LedgerTransaction::with('account')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $devices = LedgerDevice::with('accounts')->get();

        $audit = $this->auditor->performAudit();

        return view('ledger::dashboard.index', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'devices' => $devices,
            'audit' => $audit,
        ]);
    }

    public function devices(): View
    {
        $devices = LedgerDevice::with(['accounts', 'transactions'])->paginate(15);

        return view('ledger::dashboard.devices', ['devices' => $devices]);
    }

    public function deviceShow(int $id): View
    {
        $device = LedgerDevice::with(['accounts', 'transactions', 'logs'])->findOrFail($id);

        $stats = [
            'accounts_count' => $device->accounts()->count(),
            'transactions_count' => $device->transactions()->count(),
            'last_activity' => $device->logs()->latest()->first(),
        ];

        return view('ledger::dashboard.device-show', [
            'device' => $device,
            'stats' => $stats,
        ]);
    }

    public function transactions(): View
    {
        $transactions = LedgerTransaction::with('account')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $stats = [
            'total' => LedgerTransaction::count(),
            'pending' => LedgerTransaction::where('status', 'pending')->count(),
            'confirmed' => LedgerTransaction::where('status', 'confirmed')->count(),
            'failed' => LedgerTransaction::where('status', 'failed')->count(),
        ];

        return view('ledger::dashboard.transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
        ]);
    }

    public function security(): View
    {
        $audit = $this->auditor->performAudit();

        $recentLogs = LedgerActivityLog::orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('ledger::dashboard.security', [
            'audit' => $audit,
            'logs' => $recentLogs,
        ]);
    }

    public function accounts(): View
    {
        $accounts = \YourVendor\LedgerManager\Models\LedgerAccount::with('device')
            ->paginate(25);

        return view('ledger::dashboard.accounts', ['accounts' => $accounts]);
    }

    public function apiDocumentation(): View
    {
        return view('ledger::dashboard.api-docs');
    }
}

