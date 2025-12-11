<?php

namespace YourVendor\LedgerManager\APDU;

class BitcoinAPDU
{
    public static function getAppVersion(): APDUCommand
    {
        return new APDUCommand('E0', '01', '00', '00');
    }

    public static function getPublicKey(string $derivationPath): APDUCommand
    {
        $pathBytes = self::encodeDerivationPath($derivationPath);
        return new APDUCommand('E0', '02', '00', '01', $pathBytes);
    }

    public static function signTransaction(
        string $derivationPath,
        string $txData
    ): APDUCommand {
        $pathBytes = self::encodeDerivationPath($derivationPath);
        $combined = $pathBytes . bin2hex($txData);
        return new APDUCommand('E0', '04', '00', '00', $combined);
    }

    public static function encodeDerivationPath(string $path): string
    {
        $parts = array_filter(explode('/', $path));
        $pathLen = count($parts) - 1;
        $hex = dechex($pathLen);

        foreach (array_slice($parts, 1) as $part) {
            $hardened = str_ends_with($part, "'");
            $num = (int) str_replace("'", '', $part);

            if ($hardened) {
                $num |= 0x80000000;
            }

            $hex .= str_pad(dechex($num), 8, '0', STR_PAD_LEFT);
        }

        return $hex;
    }
}

