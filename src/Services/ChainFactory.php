<?php

namespace YourVendor\LedgerManager\Services;

use YourVendor\LedgerManager\Contracts\ChainHandlerInterface;
use YourVendor\LedgerManager\Exceptions\LedgerException;

class ChainFactory
{
    public function __construct(protected array $config) {}

    public function create(string $chain): ChainHandlerInterface
    {
        return match(strtolower($chain)) {
            'bitcoin', 'btc' => new \YourVendor\LedgerManager\Chains\BitcoinHandler($this->config['bitcoin'] ?? []),
            'ethereum', 'eth' => new \YourVendor\LedgerManager\Chains\EthereumHandler($this->config['ethereum'] ?? []),
            'solana', 'sol' => new \YourVendor\LedgerManager\Chains\SolanaHandler($this->config['solana'] ?? []),
            'polkadot', 'dot' => new \YourVendor\LedgerManager\Chains\PolkadotHandler($this->config['polkadot'] ?? []),
            'cardano', 'ada' => new \YourVendor\LedgerManager\Chains\CardanoHandler($this->config['cardano'] ?? []),
            default => throw new LedgerException("Unsupported chain: {$chain}"),
        };
    }

    public function getSupportedChains(): array
    {
        return ['bitcoin', 'ethereum', 'solana', 'polkadot', 'cardano'];
    }
}

