<?php

namespace MartinLechene\LedgerManager\Chains;

use MartinLechene\LedgerManager\Contracts\ChainHandlerInterface;
use MartinLechene\LedgerManager\Exceptions\LedgerException;
use MartinLechene\LedgerManager\Services\LedgerBridgeClient;

abstract class AbstractChainHandler implements ChainHandlerInterface
{
    protected ?LedgerBridgeClient $bridge = null;

    protected ?string $sessionId = null;

    public function __construct(protected array $config) {}

    public function setBridge(LedgerBridgeClient $bridge): void
    {
        $this->bridge = $bridge;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    abstract protected function chainName(): string;

    abstract protected function defaultCoinType(): int;

    protected function bridgeOrFail(): LedgerBridgeClient
    {
        if (! $this->bridge || ! $this->bridge->enabled()) {
            throw new LedgerException('Ledger bridge is not available (disabled or not configured) for chain '.$this->chainName());
        }

        return $this->bridge;
    }

    protected function coinType(): int
    {
        return (int) ($this->config['coin_type'] ?? $this->defaultCoinType());
    }

    protected function decodePayload(string $payload): mixed
    {
        $decoded = json_decode($payload, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $payload;
    }

    protected function extract(array $result, string $key, string $operation): string
    {
        if (! isset($result[$key]) || ! is_string($result[$key]) || $result[$key] === '') {
            throw new LedgerException("Ledger bridge returned no {$key} for {$operation} on chain {$this->chainName()}");
        }

        return $result[$key];
    }
}
