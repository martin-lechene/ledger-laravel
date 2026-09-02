<?php

namespace MartinLechene\LedgerManager\Tests\Unit\Support;

use MartinLechene\LedgerManager\Support\Base58;
use MartinLechene\LedgerManager\Tests\TestCase;

class Base58Test extends TestCase
{
    public function test_encodes_known_reference_vectors(): void
    {
        $this->assertSame('', Base58::encode(''));
        $this->assertSame('2g', Base58::encode("\x61"));
        $this->assertSame('a3gV', Base58::encode(hex2bin('626262')));
        $this->assertSame('aPEr', Base58::encode(hex2bin('636363')));
        $this->assertSame(
            '1NS17iag9jJgTHD1VXjvLCEnZuQ3rJDE9L',
            Base58::encode(hex2bin('00eb15231dfceb60925886b67d065299925915aeb172c06647'))
        );
        $this->assertSame('1', Base58::encode("\x00"));
        $this->assertSame('11', Base58::encode("\x00\x00"));
    }

    public function test_roundtrips_a_real_bitcoin_address(): void
    {
        $address = '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa';
        $decoded = Base58::decode($address);
        $this->assertSame(25, strlen($decoded));
        $this->assertSame($address, Base58::encode($decoded));
    }

    public function test_roundtrips_arbitrary_bytes(): void
    {
        $bytes = random_bytes(32);
        $encoded = Base58::encode($bytes);
        $this->assertSame($bytes, Base58::decode($encoded));
        $this->assertGreaterThanOrEqual(43, strlen($encoded));
        $this->assertLessThanOrEqual(44, strlen($encoded));
    }

    public function test_rejects_invalid_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Base58::decode('0OIl');
    }
}
