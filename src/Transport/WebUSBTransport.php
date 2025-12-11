<?php

namespace MartinLechene\LedgerManager\Transport;

use MartinLechene\LedgerManager\Contracts\TransportInterface;

class WebUSBTransport implements TransportInterface
{
    protected ?string $connectedDeviceId = null;

    public function __construct(protected array $config) {}

    public function discoverDevices(): array
    {
        return [];
    }

    public function connect(string $deviceId): bool
    {
        $this->connectedDeviceId = $deviceId;
        return true;
    }

    public function disconnect(): bool
    {
        $this->connectedDeviceId = null;
        return true;
    }

    public function send(string $data): string
    {
        return '';
    }

    public function receive(): string
    {
        return '';
    }

    public function isConnected(): bool
    {
        return $this->connectedDeviceId !== null;
    }

    public function getDeviceInfo(): array
    {
        return [];
    }
}

