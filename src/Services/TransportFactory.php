<?php

namespace MartinLechene\LedgerManager\Services;

use MartinLechene\LedgerManager\Contracts\TransportInterface;
use MartinLechene\LedgerManager\Exceptions\LedgerException;

class TransportFactory
{
    public function __construct(protected array $config) {}

    public function create(string $type): TransportInterface
    {
        return match($type) {
            'usb' => new \MartinLechene\LedgerManager\Transport\USBTransport($this->config['usb'] ?? []),
            'bluetooth' => new \MartinLechene\LedgerManager\Transport\BluetoothTransport($this->config['bluetooth'] ?? []),
            'webusb' => new \MartinLechene\LedgerManager\Transport\WebUSBTransport($this->config['webusb'] ?? []),
            default => throw new LedgerException("Unsupported transport type: {$type}"),
        };
    }
}

