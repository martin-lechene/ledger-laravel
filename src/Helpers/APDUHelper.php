<?php

namespace MartinLechene\LedgerManager\Helpers;

use MartinLechene\LedgerManager\APDU\APDUResponse;
use MartinLechene\LedgerManager\APDU\BitcoinAPDU;
use MartinLechene\LedgerManager\APDU\EthereumAPDU;

class APDUHelper
{
    public static function buildCommand(string $chain, string $method, ...$args): string
    {
        $handler = match (strtolower($chain)) {
            'ethereum', 'eth' => EthereumAPDU::class,
            'bitcoin', 'btc' => BitcoinAPDU::class,
            default => throw new \Exception("Unsupported chain: {$chain}"),
        };

        $command = call_user_func_array([$handler, $method], $args);

        return $command->toHex();
    }

    public static function parseResponse(string $responseHex): array
    {
        $response = new APDUResponse($responseHex);

        return [
            'success' => $response->isSuccess(),
            'data' => $response->getData(),
            'status_word' => $response->getStatusWord(),
            'error' => ! $response->isSuccess() ? $response->getErrorMessage() : null,
        ];
    }
}
