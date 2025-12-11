<?php

namespace MartinLechene\LedgerManager\Chains;

use MartinLechene\LedgerManager\Contracts\ChainHandlerInterface;
use MartinLechene\LedgerManager\Contracts\TransportInterface;

class PolkadotHandler implements ChainHandlerInterface
{
    protected ?TransportInterface $transport = null;

    public function __construct(protected array $config) {}

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        return '1' . bin2hex(random_bytes(31));
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
        return 354;
    }

    public function validateAddress(string $address): bool
    {
        return strlen($address) >= 32 && strlen($address) <= 48;
    }

    public function getAddressFormat(): string
    {
        return 'ss58';
    }
}

