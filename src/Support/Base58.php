<?php

namespace MartinLechene\LedgerManager\Support;

final class Base58
{
    public const ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    public static function encode(string $bytes): string
    {
        if ($bytes === '') {
            return '';
        }

        $num = '0';
        foreach (unpack('C*', $bytes) as $byte) {
            $num = bcmul($num, '256');
            $num = bcadd($num, (string) $byte);
        }

        $encoded = '';
        while (bccomp($num, '0') > 0) {
            $div = bcdiv($num, '58');
            $rem = (int) bcsub($num, bcmul($div, '58'));
            $encoded .= self::ALPHABET[$rem];
            $num = $div;
        }

        $zeros = 0;
        $length = strlen($bytes);
        while ($zeros < $length && $bytes[$zeros] === "\0") {
            $zeros++;
        }

        return str_repeat('1', $zeros).strrev($encoded);
    }

    public static function decode(string $address): string
    {
        if ($address === '') {
            return '';
        }

        $num = '0';
        $length = strlen($address);

        for ($i = 0; $i < $length; $i++) {
            $c = strpos(self::ALPHABET, $address[$i]);
            if ($c === false) {
                throw new \InvalidArgumentException("Invalid base58 character: '{$address[$i]}'");
            }
            $num = bcadd(bcmul($num, '58'), (string) $c);
        }

        $bytes = '';
        while (bccomp($num, '0') > 0) {
            $div = bcdiv($num, '256');
            $rem = (int) bcsub($num, bcmul($div, '256'));
            $bytes = chr($rem).$bytes;
            $num = $div;
        }

        $zeros = 0;
        while ($zeros < $length && $address[$zeros] === '1') {
            $zeros++;
        }

        return str_repeat("\0", $zeros).$bytes;
    }
}
