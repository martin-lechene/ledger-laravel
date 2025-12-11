<?php

namespace MartinLechene\LedgerManager\Services;

class DeviceDiscoveryService
{
    public function __construct(
        protected TransportFactory $transportFactory,
        protected HIDService $hidService
    ) {}

    public function discover(string $transportType = 'usb'): array
    {
        $transport = $this->transportFactory->create($transportType);
        return $transport->discoverDevices();
    }

    public function discoverAll(): array
    {
        $allDevices = [];
        
        foreach (['usb', 'bluetooth', 'webusb'] as $transport) {
            try {
                $devices = $this->discover($transport);
                $allDevices = array_merge($allDevices, $devices);
            } catch (\Exception $e) {
                // Continue with other transports
            }
        }
        
        return $allDevices;
    }

    public function getModelName(string $productId): string
    {
        $models = [
            '0x0001' => 'Ledger Nano S',
            '0x0004' => 'Ledger Nano X',
            '0x0005' => 'Ledger Blue',
            // Add more mappings as needed
        ];
        
        return $models[$productId] ?? 'Unknown Ledger Device';
    }
}

