<?php

namespace MartinLechene\LedgerManager\Chains;

class BitcoinHandler extends AbstractChainHandler
{
    protected function chainName(): string
    {
        return 'bitcoin';
    }

    protected function defaultCoinType(): int
    {
        return 0;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        $result = $this->bridgeOrFail()->address(
            'bitcoin',
            $derivationPath,
            $display,
            $this->sessionId,
            ['format' => $this->formatFor($derivationPath)]
        );

        return $this->extract($result, 'address', 'getAddress');
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        $result = $this->bridgeOrFail()->signTransaction(
            'bitcoin',
            $derivationPath,
            $this->decodePayload($txData),
            $this->sessionId
        );

        return $this->extract($result, 'signed', 'signTransaction');
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $result = $this->bridgeOrFail()->signMessage('bitcoin', $derivationPath, $message, $this->sessionId);

        return $this->extract($result, 'signature', 'signMessage');
    }

    public function getAppVersion(): string
    {
        $result = $this->bridgeOrFail()->appVersion('bitcoin', $this->sessionId);

        return $this->extract($result, 'version', 'getAppVersion');
    }

    public function getCoinType(): int
    {
        return $this->coinType();
    }

    public function validateAddress(string $address): bool
    {
        return preg_match('/^[123][1-9A-HJ-NP-Za-km-z]{25,34}$/', $address) === 1
            || preg_match('/^(bc1|tb1|bcrt1)[qpzry9x8gf2tvdw0s3jn54khce6mua7l]{14,80}$/', $address) === 1;
    }

    public function getAddressFormat(): string
    {
        return (string) ($this->config['address_format'] ?? 'bech32');
    }

    protected function formatFor(string $path): string
    {
        if (str_contains($path, "/84'")) {
            return 'bech32';
        }
        if (str_contains($path, "/86'")) {
            return 'bech32m';
        }
        if (str_contains($path, "/49'")) {
            return 'p2sh';
        }

        return 'legacy';
    }
}
