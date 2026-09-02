<?php

namespace MartinLechene\LedgerManager\Contracts;

use MartinLechene\LedgerManager\Services\LedgerBridgeClient;

interface ChainHandlerInterface
{
    public function setBridge(LedgerBridgeClient $bridge): void;

    public function setSessionId(?string $sessionId): void;

    public function getAddress(string $derivationPath, bool $display = false): string;

    public function signTransaction(string $derivationPath, string $txData): string;

    public function signMessage(string $derivationPath, string $message): string;

    public function getAppVersion(): string;

    public function getCoinType(): int;

    public function validateAddress(string $address): bool;

    public function getAddressFormat(): string;
}
