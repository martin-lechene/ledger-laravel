# Ledger Manager for Laravel

[![Tests](https://github.com/martin-lechene/ledger-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/martin-lechene/ledger-laravel/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-%5E8.2-777bb4.svg)](composer.json)
[![Laravel](https://img.shields.io/badge/Laravel-12-ff2d20.svg)](composer.json)

Laravel package to work with Ledger hardware wallets: device discovery, address derivation,
transaction and message signing, an activity/audit log, a REST API, webhooks and a small dashboard.
Supported chain handlers: Bitcoin, Ethereum, Solana, Polkadot, Cardano.

> **Status: beta.** There is no tagged release yet and the package is **not on Packagist**.
> Install it from the GitHub repository (see below). The API may change before `v1.0.0`.

## How it works

PHP cannot talk to USB-HID or Bluetooth devices natively. Device access goes through a local
Node.js sidecar, **ledger-bridge**, that this package calls over HTTP
(`LedgerBridgeClient`, default `http://127.0.0.1:8756`). You must run that bridge yourself;
it is a separate project that is not published on GitHub or npm yet, so without it device
operations (connect, addresses, signing) cannot work. Database models, API, webhooks, audit and
dashboard work without a device.

## Requirements

- PHP 8.2+
- Laravel 12
- A running ledger-bridge for real device access

## Installation (beta, from GitHub)

Add the repository to your application's `composer.json`, then require the package:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/martin-lechene/ledger-laravel" }
    ]
}
```

```bash
composer require martin-lechene/ledger-laravel:dev-main
php artisan vendor:publish --tag=ledger-config
php artisan vendor:publish --tag=ledger-migrations
php artisan migrate
```

The service provider and the `Ledger` facade are auto-discovered.

## Configuration

Published to `config/ledger.php`. Environment variables:

| Variable | Default | Purpose |
|---|---|---|
| `LEDGER_ENABLED` | `true` | Global switch |
| `LEDGER_DEBUG` | `false` | Debug mode |
| `LEDGER_TRANSPORT` | `usb` | Default transport (`usb`, `bluetooth`, `webusb`) |
| `LEDGER_CHAIN` | `ethereum` | Default chain |
| `LEDGER_BRIDGE_ENABLED` | `true` | Use the ledger-bridge sidecar |
| `LEDGER_BRIDGE_HOST` / `LEDGER_BRIDGE_PORT` | `127.0.0.1` / `8756` | Bridge address |
| `LEDGER_BRIDGE_TIMEOUT` | `2` | HTTP timeout (seconds) |
| `LEDGER_BRIDGE_RETRY_ATTEMPTS` / `LEDGER_BRIDGE_RETRY_DELAY` | `0` / `300` | Retries and delay (ms) |
| `LEDGER_BRIDGE_TOKEN` | empty | Token sent to the bridge |
| `LEDGER_STORE` | `file` | Cache store for addresses |
| `LEDGER_IP_WHITELIST` | empty | IP whitelist |
| `LEDGER_2FA_ENABLED` | `false` | Two-factor flag |
| `LEDGER_WEBHOOKS_ENABLED` | `true` | Webhook dispatching |

Other keys without env variable: `transports.*`, `chains.*` (coin type, derivation path),
`storage.*`, `security.*`, `rate_limit.*`, `webhooks.endpoints`, `audit.*`.

## Usage

### Facade

`connect()` returns a boolean, so call the other methods separately. `signTransaction()` returns a
`LedgerTransaction` model.

```php
use MartinLechene\LedgerManager\Facades\Ledger;

$devices = Ledger::discoverDevices('usb');   // Illuminate\Support\Collection

Ledger::connect($deviceId, 'usb');           // bool
Ledger::selectChain('ethereum');             // returns the service (chainable)

$address = Ledger::getAddress("m/44'/60'/0'/0/0");
$transaction = Ledger::signTransaction("m/44'/60'/0'/0/0", $txData);
$signature = Ledger::signMessage("m/44'/60'/0'/0/0", 'hello');

Ledger::disconnect();
```

Also available: `getCurrentDevice()`, `getDevice()`, `getAllDevices()`, `generateAddressRange()`,
`getAllAccounts()`, `getTransactionHistory()`, `getAppVersion()`. Global helpers `ledger()` and
`ledger_discover()` are provided in `src/helpers.php`.

### REST API

Routes are registered under `/api/ledger` with the `api` and `throttle:60,1` middleware.

```
GET  /api/ledger/devices/discover/{transport?}
GET  /api/ledger/devices
GET  /api/ledger/devices/{id}
POST /api/ledger/devices/{id}/connect
POST /api/ledger/devices/{id}/disconnect
GET  /api/ledger/accounts
GET  /api/ledger/accounts/{id}
POST /api/ledger/accounts/generate      device_id (int), chain, count (1-100), start_index
GET  /api/ledger/transactions
GET  /api/ledger/transactions/{id}
POST /api/ledger/transactions/sign      device_id (int), chain, derivation_path, tx_data
GET  /api/ledger/audit
GET  /api/ledger/audit/logs/{days?}
GET  /api/ledger/audit/statistics
```

> **Important:** the package does **not** add authentication to these API routes. Protect them
> yourself (for example by wrapping them in your own middleware or restricting access at the
> network level) before exposing them. The signing endpoint is sensitive.

### Dashboard

`GET /ledger-dashboard` (middleware `web`, `auth`). Only the index view
(`resources/views/dashboard/index.blade.php`) ships with the package; the other dashboard routes
(`devices`, `accounts`, `transactions`, `security`, `api-docs`) point to views that are not included yet.

### Webhooks

Configure endpoints in `config/ledger.php` under `webhooks.endpoints`:

```php
'webhooks' => [
    'enabled' => env('LEDGER_WEBHOOKS_ENABLED', true),
    'endpoints' => [
        'https://example.com/webhooks/ledger' => [
            'secret' => 'change-me',
            'events' => ['transaction.signed', 'security.alert'],
        ],
    ],
],
```

Events available in `WebhookManager`: `device.connected`, `device.disconnected`,
`transaction.signed`, `transaction.confirmed`, `transaction.failed`, `address.generated`,
`security.alert`. Each call is queued as a `DispatchWebhook` job that sends the headers
`X-Ledger-Event` and, when a secret is set, `X-Ledger-Signature` (HMAC-SHA256 of the body).

### Database tables

`ledger_devices`, `ledger_accounts`, `ledger_transactions`, `ledger_activity_logs`,
`ledger_webhooks`, `ledger_security_audits`.

### Middleware aliases

`ledger.throttle` and `ledger.security` (security headers) are registered and can be applied to your own routes.

## Not included

There are **no Artisan commands** in this package (earlier README versions listed some by mistake).

## Testing

```bash
composer install
vendor/bin/phpunit
```

The suite (87 tests) uses Orchestra Testbench with in-memory SQLite and does not need a device or a bridge.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Security

Never commit seed phrases or keys. Report vulnerabilities privately to contact@doganddev.eu, see [SECURITY.md](SECURITY.md).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## License

MIT, see [LICENSE](LICENSE). Contact: contact@doganddev.eu
