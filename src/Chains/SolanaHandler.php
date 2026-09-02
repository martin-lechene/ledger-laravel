<?php

namespace MartinLechene\LedgerManager\Chains;

use MartinLechene\LedgerManager\Support\Base58;

class SolanaHandler extends AbstractChainHandler
{
    protected function chainName(): string
    {
        return 'solana';
    }

    protected function defaultCoinType(): int
    {
        return 501;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        $result = $this->bridgeOrFail()->address('solana', $derivationPath, $display, $this->sessionId);
        $hex = $this->extract($result, 'address', 'getAddress');
        $hex = preg_replace('/^0x/', '', $hex);

        return Base58::encode(hex2bin($hex));
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        $result = $this->bridgeOrFail()->signTransaction(
            'solana',
            $derivationPath,
            $this->decodePayload($txData),
            $this->sessionId
        );

        return $this->extract($result, 'signature', 'signTransaction');
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $result = $this->bridgeOrFail()->signMessage('solana', $derivationPath, $message, $this->sessionId);

        return $this->extract($result, 'signature', 'signMessage');
    }

    public function getAppVersion(): string
    {
        $result = $this->bridgeOrFail()->appVersion('solana', $this->sessionId);

        return $this->extract($result, 'version', 'getAppVersion');
    }

    public function getCoinType(): int
    {
        return $this->coinType();
    }

    public function validateAddress(string $address): bool
    {
        try {
            return strlen(Base58::decode($address)) === 32;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }

    public function getAddressFormat(): string
    {
        return 'base58';
    }
}
