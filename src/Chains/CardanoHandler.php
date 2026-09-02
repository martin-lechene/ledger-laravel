<?php

namespace MartinLechene\LedgerManager\Chains;

use MartinLechene\LedgerManager\Exceptions\LedgerException;

class CardanoHandler extends AbstractChainHandler
{
    protected function chainName(): string
    {
        return 'cardano';
    }

    protected function defaultCoinType(): int
    {
        return 1815;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        $result = $this->bridgeOrFail()->address('cardano', $derivationPath, $display, $this->sessionId, [
            'network' => (string) ($this->config['network'] ?? 'mainnet'),
        ]);

        return $this->extract($result, 'address', 'getAddress');
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        $result = $this->bridgeOrFail()->signTransaction(
            'cardano',
            $derivationPath,
            $this->decodePayload($txData),
            $this->sessionId
        );

        return $this->encodeSigned($result, 'signTransaction');
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $result = $this->bridgeOrFail()->signMessage('cardano', $derivationPath, $this->decodePayload($message), $this->sessionId);

        return $this->encodeSigned($result, 'signMessage');
    }

    public function getAppVersion(): string
    {
        $result = $this->bridgeOrFail()->appVersion('cardano', $this->sessionId);

        return $this->extract($result, 'version', 'getAppVersion');
    }

    public function getCoinType(): int
    {
        return $this->coinType();
    }

    public function validateAddress(string $address): bool
    {
        return str_starts_with($address, 'addr1') && strlen($address) >= 50
            || preg_match('/^0x[a-fA-F0-9]{2,}$/', $address) === 1;
    }

    public function getAddressFormat(): string
    {
        return 'bech32';
    }

    protected function encodeSigned(array $result, string $operation): string
    {
        if (! array_key_exists('signed', $result)) {
            throw new LedgerException("Ledger bridge returned no signed payload for {$operation} on chain cardano");
        }

        $signed = $result['signed'];

        if (is_string($signed)) {
            return $signed;
        }

        $encoded = json_encode($signed);
        if ($encoded === false) {
            throw new LedgerException("Could not encode cardano {$operation} result");
        }

        return $encoded;
    }
}
