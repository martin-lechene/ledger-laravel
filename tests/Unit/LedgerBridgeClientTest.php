<?php

namespace MartinLechene\LedgerManager\Tests\Unit;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use MartinLechene\LedgerManager\Exceptions\LedgerException;
use MartinLechene\LedgerManager\Services\LedgerBridgeClient;
use MartinLechene\LedgerManager\Tests\TestCase;

class LedgerBridgeClientTest extends TestCase
{
    protected function client(array $config = []): LedgerBridgeClient
    {
        return new LedgerBridgeClient(array_merge([
            'enabled' => true,
            'host' => '127.0.0.1',
            'port' => 8756,
            'timeout' => 2,
            'token' => '',
        ], $config));
    }

    public function test_health_parses_success_response(): void
    {
        $response = $this->client()->health();

        $this->assertSame('ok', $response['status']);
        $this->assertSame('1.0.0', $response['version']);
    }

    public function test_connect_returns_session(): void
    {
        $client = $this->client();
        $result = $client->connect('usb:1');

        $this->assertSame('sess-1', $result['sessionId']);
        $this->assertSame('usb:1', $result['device']);
    }

    public function test_address_sends_the_expected_payload(): void
    {
        $client = $this->client();
        $client->address('ethereum', "m/44'/60'/0'/0/0", true, 'sess-9', ['ss58prefix' => 42]);

        Http::assertSent(function ($request) {
            $this->assertSame('http://127.0.0.1:8756/address', $request->url());
            $this->assertSame('ethereum', $request['chain']);
            $this->assertSame("m/44'/60'/0'/0/0", $request['path']);
            $this->assertTrue($request['display']);
            $this->assertSame('sess-9', $request['sessionId']);
            $this->assertSame(['ss58prefix' => 42], $request['params']);

            return true;
        });
    }

    public function test_uses_bearer_token_when_configured(): void
    {
        $client = $this->client(['token' => 's3cret']);
        $client->devices();

        Http::assertSent(function ($request) {
            $this->assertSame('Bearer s3cret', $request->header('Authorization')[0]);

            return true;
        });
    }

    public function test_base_url_accepts_full_host(): void
    {
        $client = new LedgerBridgeClient(['host' => 'https://bridge.internal.example', 'port' => 9876]);
        $this->assertSame('https://bridge.internal.example', $client->baseUrl());
        $this->assertTrue($client->enabled());
    }

    public function test_throws_ledger_exception_on_error_response(): void
    {
        Http::fake([
            '127.0.0.1:8756/public-key' => Http::response([
                'error' => ['code' => 'unsupported_chain', 'message' => 'Unsupported chain "doge"'],
            ], 400),
        ]);

        $this->expectException(LedgerException::class);
        $this->expectExceptionMessage('Ledger bridge error: [unsupported_chain] Unsupported chain "doge"');

        $this->client()->publicKey('doge', "m/44'/0'/0'/0/0");
    }

    public function test_throws_ledger_exception_on_network_failure(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->expectException(LedgerException::class);
        $this->expectExceptionMessage('Ledger bridge request failed: Connection refused');

        $this->client()->devices();
    }

    public function test_disabled_client_still_reports_enabled_state(): void
    {
        $client = new LedgerBridgeClient(['enabled' => false]);
        $this->assertFalse($client->enabled());
    }
}
