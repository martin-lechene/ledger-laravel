<?php

namespace MartinLechene\LedgerManager\Chains;

class PolkadotHandler extends AbstractChainHandler
{
    protected function chainName(): string
    {
        return 'polkadot';
    }

    protected function defaultCoinType(): int
    {
        return 354;
    }

    public function getAddress(string $derivationPath, bool $display = false): string
    {
        $result = $this->bridgeOrFail()->address('polkadot', $derivationPath, $display, $this->sessionId, [
            'ss58prefix' => (int) ($this->config['ss58_prefix'] ?? 42),
        ]);

        return $this->extract($result, 'address', 'getAddress');
    }

    public function signTransaction(string $derivationPath, string $txData): string
    {
        $result = $this->bridgeOrFail()->signTransaction(
            'polkadot',
            $derivationPath,
            $this->decodePayload($txData),
            $this->sessionId
        );

        return $this->extract($result, 'signature', 'signTransaction');
    }

    public function signMessage(string $derivationPath, string $message): string
    {
        $result = $this->bridgeOrFail()->signMessage('polkadot', $derivationPath, $message, $this->sessionId);

        return $this->extract($result, 'signature', 'signMessage');
    }

    public function getAppVersion(): string
    {
        $result = $this->bridgeOrFail()->appVersion('polkadot', $this->sessionId);

        return $this->extract($result, 'version', 'getAppVersion');
    }

    public function getCoinType(): int
    {
        return $this->coinType();
    }

    public function validateAddress(string $address): bool
    {
        $length = strlen($address);

        return $length >= 32 && $length <= 48;
    }

    public function getAddressFormat(): string
    {
        return 'ss58';
    }
}
