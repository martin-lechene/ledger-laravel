<?php

namespace YourVendor\LedgerManager\Chains;

use YourVendor\LedgerManager\Contracts\ChainHandlerInterface;
use YourVendor\LedgerManager\Contracts\TransportInterface;

class BitcoinHandler implements ChainHandlerInterface
{
    protected ?TransportInterface $transport = null;

    public function __construct(protected array $config) {}

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        return 'bc1' . bin2hex(random_bytes(20));
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        return bin2hex(random_bytes(64));
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        return bin2hex(random_bytes(64));
    }

    public function getAppVersion(): string
    {
        return '1.0.0';
    }

    public function getCoinType(): int
    {
        return 0;
    }

    public function validateAddress(string $address): bool
    {
        return preg_match('/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/', $address) === 1
            || preg_match('/^bc1[a-z0-9]{39,59}$/', $address) === 1;
    }

    public function getAddressFormat(): string
    {
        return 'bech32';
    }
}

