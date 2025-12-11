<?php

namespace YourVendor\LedgerManager\Services;

use YourVendor\LedgerManager\Contracts\TransportInterface;
use YourVendor\LedgerManager\Exceptions\LedgerException;

class TransportFactory
{
    public function __construct(protected array $config) {}

    public function create(string $type): TransportInterface
    {
        return match($type) {
            'usb' => new \YourVendor\LedgerManager\Transport\USBTransport($this->config['usb'] ?? []),
            'bluetooth' => new \YourVendor\LedgerManager\Transport\BluetoothTransport($this->config['bluetooth'] ?? []),
            'webusb' => new \YourVendor\LedgerManager\Transport\WebUSBTransport($this->config['webusb'] ?? []),
            default => throw new LedgerException("Unsupported transport type: {$type}"),
        };
    }
}

