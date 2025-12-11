<?php

namespace MartinLechene\LedgerManager\Services;

use MartinLechene\LedgerManager\Contracts\ChainHandlerInterface;
use MartinLechene\LedgerManager\Exceptions\LedgerException;

class ChainFactory
{
    public function __construct(protected array $config) {}

    public function create(string $chain): ChainHandlerInterface
    {
        return match(strtolower($chain)) {
            'bitcoin', 'btc' => new \MartinLechene\LedgerManager\Chains\BitcoinHandler($this->config['bitcoin'] ?? []),
            'ethereum', 'eth' => new \MartinLechene\LedgerManager\Chains\EthereumHandler($this->config['ethereum'] ?? []),
            'solana', 'sol' => new \MartinLechene\LedgerManager\Chains\SolanaHandler($this->config['solana'] ?? []),
            'polkadot', 'dot' => new \MartinLechene\LedgerManager\Chains\PolkadotHandler($this->config['polkadot'] ?? []),
            'cardano', 'ada' => new \MartinLechene\LedgerManager\Chains\CardanoHandler($this->config['cardano'] ?? []),
            default => throw new LedgerException("Unsupported chain: {$chain}"),
        };
    }

    public function getSupportedChains(): array
    {
        return ['bitcoin', 'ethereum', 'solana', 'polkadot', 'cardano'];
    }
}

