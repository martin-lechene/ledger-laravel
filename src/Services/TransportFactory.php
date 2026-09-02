<?php

namespace MartinLechene\LedgerManager\Services;

use MartinLechene\LedgerManager\Contracts\TransportInterface;
use MartinLechene\LedgerManager\Exceptions\LedgerException;
use MartinLechene\LedgerManager\Transport\BluetoothTransport;
use MartinLechene\LedgerManager\Transport\USBTransport;
use MartinLechene\LedgerManager\Transport\WebUSBTransport;

class TransportFactory
{
    public function __construct(protected array $config) {}

    public function create(string $type): TransportInterface
    {
        return match ($type) {
            'usb' => new USBTransport($this->config['usb'] ?? []),
            'bluetooth' => new BluetoothTransport($this->config['bluetooth'] ?? []),
            'webusb' => new WebUSBTransport($this->config['webusb'] ?? []),
            default => throw new LedgerException("Unsupported transport type: {$type}"),
        };
    }
}
