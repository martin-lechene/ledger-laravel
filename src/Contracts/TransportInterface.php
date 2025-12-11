<?php

namespace YourVendor\LedgerManager\Contracts;

interface TransportInterface
{
    public function discoverDevices(): array;
    public function connect(string $deviceId): bool;
    public function disconnect(): bool;
    public function send(string $data): string;
    public function receive(): string;
    public function isConnected(): bool;
    public function getDeviceInfo(): array;
}

