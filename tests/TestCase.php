<?php

namespace MartinLechene\LedgerManager\Tests;

use Illuminate\Support\Facades\Http;
use MartinLechene\LedgerManager\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->fakeBridge();
    }

    protected function fakeBridge(): void
    {
        Http::fake([
            '127.0.0.1:8756/health' => Http::response([
                'status' => 'ok',
                'version' => '1.0.0',
                'chains' => ['bitcoin', 'ethereum', 'solana', 'polkadot', 'cardano'],
            ], 200),
            '127.0.0.1:8756/devices' => Http::response([
                'devices' => [
                    ['descriptor' => 'usb:1', 'productName' => 'Ledger Nano X'],
                ],
            ], 200),
            '127.0.0.1:8756/connect' => Http::response([
                'sessionId' => 'sess-1',
                'device' => 'usb:1',
            ], 200),
            '127.0.0.1:8756/disconnect' => Http::response(['ok' => true], 200),
            '127.0.0.1:8756/address' => function ($request) {
                $path = (string) $request['path'];
                $last = (int) substr($path, strrpos($path, '/') + 1);
                $address = '0x'.str_pad(dechex($last), 40, '0', STR_PAD_LEFT);

                return [
                    'chain' => 'ethereum',
                    'result' => [
                        'address' => $address,
                        'publicKey' => '0x11',
                        'chainCode' => '0x22',
                    ],
                ];
            },
            '127.0.0.1:8756/sign/transaction' => Http::response([
                'chain' => 'ethereum',
                'result' => ['signature' => '0x'.str_repeat('ab', 65)],
            ], 200),
            '127.0.0.1:8756/sign/message' => Http::response([
                'chain' => 'ethereum',
                'result' => ['signature' => '0x'.str_repeat('cd', 65)],
            ], 200),
            '127.0.0.1:8756/app-version' => Http::response([
                'chain' => 'ethereum',
                'result' => ['version' => '2.1.0'],
            ], 200),
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
