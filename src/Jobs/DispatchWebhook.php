<?php

namespace YourVendor\LedgerManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $endpoint,
        protected string $event,
        protected array $payload,
        protected ?string $secret = null
    ) {}

    public function handle(): void
    {
        $client = new Client();
        $body = json_encode([
            'event' => $this->event,
            'data' => $this->payload,
            'timestamp' => now()->toIso8601String(),
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'X-Ledger-Event' => $this->event,
        ];

        if ($this->secret) {
            $headers['X-Ledger-Signature'] = hash_hmac('sha256', $body, $this->secret);
        }

        try {
            $client->post($this->endpoint, [
                'headers' => $headers,
                'body' => $body,
                'timeout' => 10,
                'verify' => true,
            ]);
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }

    public function failed(\Exception $exception): void
    {
        Log::warning("Webhook dispatch failed: {$exception->getMessage()}");
    }
}

