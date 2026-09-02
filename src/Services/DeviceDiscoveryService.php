<?php

namespace MartinLechene\LedgerManager\Services;

class DeviceDiscoveryService
{
    public function __construct(protected LedgerBridgeClient $bridge) {}

    public function discover(string $transportType = 'usb'): array
    {
        return $this->bridge->devices();
    }

    public function discoverAll(): array
    {
        return $this->bridge->devices();
    }

    public function getModelName(string $productId): string
    {
        $models = [
            '0x0001' => 'Ledger Nano S',
            '0x0004' => 'Ledger Nano X',
            '0x0005' => 'Ledger Blue',
            '0x0006' => 'Ledger Nano S Plus',
            '0x0007' => 'Ledger Nano X',
            '0x0008' => 'Ledger Stax',
            '0x0009' => 'Ledger Flex',
            '0x0015' => 'Ledger Stax',
            // Add more mappings as needed
        ];

        return $models[$productId] ?? 'Unknown Ledger Device';
    }
}
