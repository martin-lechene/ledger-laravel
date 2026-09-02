<?php

namespace MartinLechene\LedgerManager\Services;

use MartinLechene\LedgerManager\Chains\BitcoinHandler;
use MartinLechene\LedgerManager\Chains\CardanoHandler;
use MartinLechene\LedgerManager\Chains\EthereumHandler;
use MartinLechene\LedgerManager\Chains\PolkadotHandler;
use MartinLechene\LedgerManager\Chains\SolanaHandler;
use MartinLechene\LedgerManager\Contracts\ChainHandlerInterface;
use MartinLechene\LedgerManager\Exceptions\LedgerException;

class ChainFactory
{
    public function __construct(
        protected array $config,
        protected LedgerBridgeClient $bridge
    ) {}

    public function create(string $chain): ChainHandlerInterface
    {
        $handler = match (strtolower($chain)) {
            'bitcoin', 'btc' => new BitcoinHandler($this->config['bitcoin'] ?? []),
            'ethereum', 'eth' => new EthereumHandler($this->config['ethereum'] ?? []),
            'solana', 'sol' => new SolanaHandler($this->config['solana'] ?? []),
            'polkadot', 'dot' => new PolkadotHandler($this->config['polkadot'] ?? []),
            'cardano', 'ada' => new CardanoHandler($this->config['cardano'] ?? []),
            default => throw new LedgerException("Unsupported chain: {$chain}"),
        };

        $handler->setBridge($this->bridge);

        return $handler;
    }

    public function createWithSession(string $chain, ?string $sessionId): ChainHandlerInterface
    {
        $handler = $this->create($chain);
        $handler->setSessionId($sessionId);

        return $handler;
    }

    public function getSupportedChains(): array
    {
        return ['bitcoin', 'ethereum', 'solana', 'polkadot', 'cardano'];
    }
}
