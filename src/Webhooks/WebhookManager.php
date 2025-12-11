<?php

namespace YourVendor\LedgerManager\Webhooks;

use Illuminate\Support\Facades\Queue;
use YourVendor\LedgerManager\Models\LedgerDevice;
use YourVendor\LedgerManager\Models\LedgerTransaction;
use YourVendor\LedgerManager\Models\LedgerAccount;

class WebhookManager
{
    public static function deviceConnected(LedgerDevice $device): void
    {
        self::dispatch('device.connected', ['device' => $device]);
    }

    public static function deviceDisconnected(LedgerDevice $device): void
    {
        self::dispatch('device.disconnected', ['device' => $device]);
    }

    public static function transactionSigned(LedgerTransaction $transaction): void
    {
        self::dispatch('transaction.signed', ['transaction' => $transaction]);
    }

    public static function transactionConfirmed(LedgerTransaction $transaction): void
    {
        self::dispatch('transaction.confirmed', ['transaction' => $transaction]);
    }

    public static function transactionFailed(LedgerTransaction $transaction, string $error): void
    {
        self::dispatch('transaction.failed', [
            'transaction' => $transaction,
            'error' => $error,
        ]);
    }

    public static function addressGenerated(LedgerAccount $account): void
    {
        self::dispatch('address.generated', ['account' => $account]);
    }

    public static function securityAlert(string $type, array $data): void
    {
        self::dispatch('security.alert', [
            'type' => $type,
            'data' => $data,
            'timestamp' => now(),
        ]);
    }

    private static function dispatch(string $event, array $payload): void
    {
        $webhookEndpoints = config('ledger.webhooks.endpoints', []);

        foreach ($webhookEndpoints as $endpoint => $config) {
            if (in_array($event, $config['events'] ?? [])) {
                Queue::push(new \YourVendor\LedgerManager\Jobs\DispatchWebhook(
                    $endpoint,
                    $event,
                    $payload,
                    $config['secret'] ?? null
                ));
            }
        }
    }
}

