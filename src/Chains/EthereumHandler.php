<?php

namespace MartinLechene\LedgerManager\Chains;

class EthereumHandler extends AbstractChainHandler
{
    protected function chainName(): string
    {
        return 'ethereum';
    }

    protected function defaultCoinType(): int
    {
        return 60;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        $result = $this->bridgeOrFail()->address('ethereum', $derivationPath, $display, $this->sessionId);

        return $this->extract($result, 'address', 'getAddress');
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        $result = $this->bridgeOrFail()->signTransaction(
            'ethereum',
            $derivationPath,
            $this->decodePayload($txData),
            $this->sessionId
        );

        return $this->extract($result, 'signature', 'signTransaction');
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $result = $this->bridgeOrFail()->signMessage('ethereum', $derivationPath, $message, $this->sessionId);

        return $this->extract($result, 'signature', 'signMessage');
    }

    public function getAppVersion(): string
    {
        $result = $this->bridgeOrFail()->appVersion('ethereum', $this->sessionId);

        return $this->extract($result, 'version', 'getAppVersion');
    }

    public function getCoinType(): int
    {
        return $this->coinType();
    }

    public function validateAddress(string $address): bool
    {
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;
    }

    public function getAddressFormat(): string
    {
        return 'ethereum';
    }
}
