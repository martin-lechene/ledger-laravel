<?php

namespace MartinLechene\LedgerManager\Chains;

use MartinLechene\LedgerManager\Contracts\ChainHandlerInterface;
use MartinLechene\LedgerManager\Contracts\TransportInterface;

class SolanaHandler implements ChainHandlerInterface
{
    protected ?TransportInterface $transport = null;

    public function __construct(protected array $config) {}

    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        return base64_encode(random_bytes(32));
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        return base64_encode(random_bytes(64));
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        return base64_encode(random_bytes(64));
    }

    public function getAppVersion(): string
    {
        return '1.0.0';
    }

    public function getCoinType(): int
    {
        return 501;
    }

    public function validateAddress(string $address): bool
    {
        return strlen($address) === 44;
    }

    public function getAddressFormat(): string
    {
        return 'base58';
    }
}

