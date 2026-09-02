<?php

namespace MartinLechene\LedgerManager\Services;

use Illuminate\Support\Collection;
use MartinLechene\LedgerManager\Models\LedgerAccount;
use MartinLechene\LedgerManager\Models\LedgerActivityLog;
use MartinLechene\LedgerManager\Models\LedgerDevice;
use MartinLechene\LedgerManager\Models\LedgerTransaction;

class LedgerService
{
    protected ?LedgerDevice $currentDevice = null;

    protected ?string $currentChain = null;

    protected ?string $sessionId = null;

    public function __construct(
        protected ChainFactory $chainFactory,
        protected LedgerBridgeClient $bridge,
        protected DeviceDiscoveryService $discoveryService
    ) {}

    public function discoverDevices(string $transportType = 'usb'): Collection
    {
        $devices = $this->discoveryService->discover($transportType);
        $devicesCollection = is_array($devices) ? collect($devices) : $devices;

        LedgerActivityLog::log('discover_devices', 'success', null, null, "Discovered {$devicesCollection->count()} devices via {$transportType}");

        return $devicesCollection;
    }

    public function connect(string $deviceId, string $transportType = 'usb'): bool
    {
        $device = LedgerDevice::where('device_id', $deviceId)->first();

        if (! $device) {
            $device = LedgerDevice::create([
                'device_id' => $deviceId,
                'transport_type' => $transportType,
                'is_active' => false,
            ]);
        }

        $session = $this->bridge->connect($deviceId);
        $this->sessionId = $session['sessionId'] ?? null;

        $device->update([
            'is_active' => true,
            'last_connected_at' => now(),
        ]);

        $this->currentDevice = $device;

        LedgerActivityLog::log('connect', 'success', $device);

        return true;
    }

    public function disconnect(): bool
    {
        if ($this->sessionId) {
            try {
                $this->bridge->disconnect($this->sessionId);
            } catch (\Throwable) {
                // Best effort: the bridge may already have dropped the session.
            }
        }

        if ($this->currentDevice) {
            $this->currentDevice->update(['is_active' => false]);
            LedgerActivityLog::log('disconnect', 'success', $this->currentDevice);
        }

        $this->currentDevice = null;
        $this->currentChain = null;
        $this->sessionId = null;

        return true;
    }

    public function getCurrentDevice(): ?LedgerDevice
    {
        return $this->currentDevice;
    }

    public function getDevice(string $deviceId): ?LedgerDevice
    {
        return LedgerDevice::where('device_id', $deviceId)->first();
    }

    public function getAllDevices(): Collection
    {
        return LedgerDevice::all();
    }

    public function selectChain(string $chainName): self
    {
        if (! $this->currentDevice) {
            throw new \Exception('No device connected');
        }

        $this->currentChain = $chainName;

        return $this;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        if (! $this->currentChain) {
            throw new \Exception('No chain selected');
        }

        $handler = $this->chainFactory->createWithSession($this->currentChain, $this->sessionId);
        $address = $handler->getAddress($derivationPath, $display);

        LedgerActivityLog::log('get_address', 'success', $this->currentDevice, null, "Path: {$derivationPath}");

        return $address;
    }

    public function generateAddressRange(string $chain, int $startIndex = 0, int $count = 10): Collection
    {
        $addresses = collect();

        for ($i = 0; $i < $count; $i++) {
            $index = $startIndex + $i;
            $path = "m/44'/60'/0'/0/{$index}";
            $address = $this->getAddress($path);

            $account = LedgerAccount::create([
                'ledger_device_id' => $this->currentDevice->id,
                'chain' => $chain,
                'derivation_path' => $path,
                'public_address' => $address,
                'account_index' => $index,
            ]);

            $addresses->push([
                'index' => $index,
                'path' => $path,
                'address' => $address,
                'account_id' => $account->id,
            ]);
        }

        return $addresses;
    }

    public function signTransaction(string $derivationPath, string $txData): LedgerTransaction
    {
        if (! $this->currentDevice) {
            throw new \Exception('No device connected');
        }

        $account = LedgerAccount::where('derivation_path', $derivationPath)
            ->where('ledger_device_id', $this->currentDevice->id)
            ->first();

        if (! $account) {
            throw new \Exception('Account not found for derivation path');
        }

        $chain = $this->currentChain ?? config('ledger.default_chain', 'ethereum');
        $handler = $this->chainFactory->createWithSession($chain, $this->sessionId);
        $signedData = $handler->signTransaction($derivationPath, $txData);

        $transaction = LedgerTransaction::create([
            'ledger_account_id' => $account->id,
            'chain' => $chain,
            'type' => 'send',
            'status' => 'signed',
            'from_address' => $account->public_address,
            'raw_data' => $txData,
            'signed_data' => $signedData,
            'signed_at' => now(),
        ]);

        LedgerActivityLog::log('sign_transaction', 'success', $this->currentDevice, $account);

        return $transaction;
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $chain = $this->currentChain ?? config('ledger.default_chain', 'ethereum');
        $handler = $this->chainFactory->createWithSession($chain, $this->sessionId);
        $signature = $handler->signMessage($derivationPath, $message);

        LedgerActivityLog::log('sign_message', 'success', $this->currentDevice);

        return $signature;
    }

    public function getAllAccounts(?string $chain = null): Collection
    {
        $query = LedgerAccount::query();

        if ($chain) {
            $query->where('chain', $chain);
        }

        return $query->get();
    }

    public function getTransactionHistory(?string $deviceId = null, ?string $chain = null, int $limit = 50): Collection
    {
        $query = LedgerTransaction::query();

        if ($deviceId) {
            $device = LedgerDevice::where('device_id', $deviceId)->first();
            if ($device) {
                $query->whereHas('account', function ($q) use ($device) {
                    $q->where('ledger_device_id', $device->id);
                });
            }
        }

        if ($chain) {
            $query->where('chain', $chain);
        }

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }

    public function getAppVersion(): string
    {
        $chain = $this->currentChain ?? config('ledger.default_chain', 'ethereum');
        $handler = $this->chainFactory->createWithSession($chain, $this->sessionId);

        return $handler->getAppVersion();
    }
}
