<?php

namespace MartinLechene\LedgerManager\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use MartinLechene\LedgerManager\Exceptions\LedgerException;
use Throwable;

class LedgerBridgeClient
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    public function baseUrl(): string
    {
        $host = (string) ($this->config['host'] ?? '127.0.0.1');
        $port = (int) ($this->config['port'] ?? 8756);

        if (str_contains($host, '://')) {
            return rtrim($host, '/');
        }

        return 'http://'.trim($host, '/').':'.$port;
    }

    public function health(): array
    {
        return $this->get('/health');
    }

    public function devices(): array
    {
        return $this->get('/devices')['devices'] ?? [];
    }

    public function connect(?string $descriptor = null): array
    {
        return $this->post('/connect', [
            'descriptor' => $descriptor,
        ]);
    }

    public function disconnect(?string $sessionId = null): array
    {
        return $this->post('/disconnect', [
            'sessionId' => $sessionId,
        ]);
    }

    public function address(string $chain, string $path, bool $display = false, ?string $sessionId = null, array $params = []): array
    {
        return $this->post('/address', [
            'sessionId' => $sessionId,
            'chain' => $chain,
            'path' => $path,
            'display' => $display,
            'params' => $params,
        ])['result'] ?? [];
    }

    public function publicKey(string $chain, string $path, bool $display = false, ?string $sessionId = null, array $params = []): array
    {
        return $this->post('/public-key', [
            'sessionId' => $sessionId,
            'chain' => $chain,
            'path' => $path,
            'display' => $display,
            'params' => $params,
        ])['result'] ?? [];
    }

    public function signTransaction(string $chain, string $path, mixed $tx, ?string $sessionId = null, array $params = []): array
    {
        return $this->post('/sign/transaction', [
            'sessionId' => $sessionId,
            'chain' => $chain,
            'path' => $path,
            'tx' => $tx,
            'params' => $params,
        ])['result'] ?? [];
    }

    public function signMessage(string $chain, string $path, mixed $message, ?string $sessionId = null, array $params = []): array
    {
        return $this->post('/sign/message', [
            'sessionId' => $sessionId,
            'chain' => $chain,
            'path' => $path,
            'message' => $message,
            'params' => $params,
        ])['result'] ?? [];
    }

    public function appVersion(string $chain, ?string $sessionId = null): array
    {
        return $this->post('/app-version', [
            'sessionId' => $sessionId,
            'chain' => $chain,
        ])['result'] ?? [];
    }

    protected function client(): PendingRequest
    {
        $client = Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->timeout((int) ($this->config['timeout'] ?? 2))
            ->retry(
                (int) ($this->config['retry_attempts'] ?? 0),
                (int) ($this->config['retry_delay_ms'] ?? 300)
            );

        $token = (string) ($this->config['token'] ?? '');
        if ($token !== '') {
            $client = $client->withToken($token);
        }

        return $client;
    }

    protected function get(string $path): array
    {
        try {
            $response = $this->client()->get($path);
            $this->throwIfFailed($response, $path);

            return $response->json() ?? [];
        } catch (LedgerException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new LedgerException('Ledger bridge request failed: '.$e->getMessage(), 0, $e);
        }
    }

    protected function post(string $path, array $payload): array
    {
        try {
            $response = $this->client()->post($path, $payload);
            $this->throwIfFailed($response, $path);

            return $response->json() ?? [];
        } catch (LedgerException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new LedgerException('Ledger bridge request failed: '.$e->getMessage(), 0, $e);
        }
    }

    protected function throwIfFailed(Response $response, string $path): void
    {
        if ($response->successful()) {
            return;
        }

        $code = $response->json('error.code');
        $message = $response->json('error.message');
        $status = $response->status();

        $detail = $message !== null
            ? ($code !== null ? "[{$code}] {$message}" : $message)
            : "Ledger bridge returned HTTP {$status} for {$path}";

        throw new LedgerException("Ledger bridge error: {$detail}", $status);
    }
}
