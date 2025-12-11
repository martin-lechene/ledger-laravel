@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Ledger Management Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage your hardware wallets securely</p>
    </div>

    <!-- Security Score -->
    @if(isset($audit['security_score']))
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Security Score</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $audit['security_score']['score'] }}</p>
                </div>
                <div class="text-5xl font-bold @if($audit['security_score']['score'] >= 80) text-green-600 @elseif($audit['security_score']['score'] >= 60) text-yellow-600 @else text-red-600 @endif">
                    {{ $audit['security_score']['grade'] }}
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $audit['security_score']['status'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Active Devices</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['active_devices'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-2">of {{ $stats['devices'] ?? 0 }} total</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Total Accounts</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['accounts'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm">Today's Operations</p>
            <p class="text-3xl font-bold text-gray-900">{{ $stats['today_operations'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(!empty($audit['activity_anomalies']))
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">⚠️ Detected Anomalies</h2>
        <div class="space-y-3">
            @foreach($audit['activity_anomalies'] as $anomaly)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-red-900">{{ $anomaly['description'] ?? 'Unknown' }}</p>
                        <p class="text-sm text-red-700 mt-1">Type: {{ $anomaly['type'] ?? 'N/A' }}</p>
                    </div>
                    <span class="inline-block bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold">
                        {{ ucfirst($anomaly['severity'] ?? 'unknown') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Recent Transactions</h2>
            <a href="{{ route('ledger.transactions') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
        </div>

        @if(isset($recentTransactions) && count($recentTransactions) > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">ID</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Type</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Chain</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">From</th>
                        <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm text-gray-900">#{{ $tx->id }}</td>
                        <td class="py-3 px-4 text-sm">
                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                {{ ucfirst($tx->type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <span class="inline-block @if($tx->status === 'confirmed') bg-green-100 text-green-800 @elseif($tx->status === 'failed') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif px-2 py-1 rounded text-xs">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-900 font-mono">{{ strtoupper($tx->chain) }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 font-mono text-xs">{{ substr($tx->from_address, 0, 10) }}...</td>
                        <td class="py-3 px-4 text-sm text-gray-500">{{ $tx->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-600">No transactions yet</p>
        @endif
    </div>

    <!-- Devices Overview -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900">Connected Devices</h2>
            <a href="{{ route('ledger.devices') }}" class="text-blue-600 hover:text-blue-800 text-sm">View All</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($devices as $device)
            <a href="{{ route('ledger.device.show', $device->id) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-gray-900">{{ $device->getDisplayName() }}</h3>
                    <span class="inline-block w-3 h-3 rounded-full @if($device->is_active) bg-green-500 @else bg-gray-300 @endif"></span>
                </div>
                <p class="text-sm text-gray-600">{{ $device->model ?? 'Unknown Model' }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ $device->accounts()->count() }} accounts</p>
                <p class="text-xs text-gray-500">Last: {{ $device->last_connected_at?->diffForHumans() ?? 'Never' }}</p>
            </a>
            @empty
            <p class="text-gray-600 col-span-3">No devices configured</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

