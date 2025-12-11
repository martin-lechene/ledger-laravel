# 🔐 Ledger Manager - Enterprise-Grade Laravel Package

A complete, production-ready Laravel package for managing Ledger hardware wallets with advanced security auditing, REST API, and real-time webhooks.

## ✨ Features

### Core Functionality
✅ Multi-transport support (USB, Bluetooth, WebUSB)
✅ Multi-blockchain support (Bitcoin, Ethereum, Solana, Polkadot, Cardano)
✅ BIP44 address derivation and generation
✅ Transaction signing and message signing
✅ Complete transaction history
✅ Activity logging and audit trails

### Advanced Features
✅ **APDU Protocol Implementation** - Native Ledger device communication
✅ **REST API** - Full-featured JSON API with rate limiting
✅ **Security Auditing** - Automated vulnerability detection
✅ **Real-time Webhooks** - Event-driven notifications
✅ **Dashboard** - Web interface for device management
✅ **Security Monitoring** - Anomaly detection and alerts
✅ **Throttling** - Smart rate limiting per endpoint
✅ **Security Headers** - HSTS, CSP, XSS protection

## 📦 Installation

```bash
composer require martin-lechene/ledger-laravel
php artisan vendor:publish --provider="MartinLechene\LedgerManager\ServiceProvider"
php artisan migrate
```

## 🚀 Quick Start

### Via Facade
```php
use MartinLechene\LedgerManager\Facades\Ledger;

// Discover devices
$devices = Ledger::discoverDevices('usb');

// Connect and sign
Ledger::connect($devices[0]['id'])
    ->selectChain('ethereum')
    ->signTransaction("m/44'/60'/0'/0/0", $txData);
```

### Via REST API
```bash
# List devices
curl -X GET http://localhost:8000/api/ledger/devices \
  -H "Authorization: Bearer TOKEN"

# Generate addresses
curl -X POST http://localhost:8000/api/ledger/accounts/generate \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": 1,
    "chain": "ethereum",
    "count": 10
  }'
```

### Dashboard
Access at `/ledger-dashboard` (requires authentication)

## 🔐 Security Audit

```bash
php artisan ledger:audit
```

Returns comprehensive security report:
- Device health status
- Vulnerability detection
- Activity anomalies
- Security score (A-F)
- Recommendations

## 🔔 Webhooks

Configure in `config/ledger.php`:

```php
'webhooks' => [
    'https://api.example.com/webhooks/ledger' => [
        'secret' => 'webhook_secret',
        'events' => ['transaction.signed', 'security.alert'],
    ],
],
```

## 📊 Database Schema

- `ledger_devices` - Device registry
- `ledger_accounts` - Addresses and accounts
- `ledger_transactions` - Transaction history
- `ledger_activity_logs` - Audit trail
- `ledger_webhooks` - Webhook delivery tracking
- `ledger_security_audits` - Security checkup results

## 🎯 API Endpoints

```
GET  /api/ledger/devices
POST /api/ledger/devices/{id}/connect
POST /api/ledger/devices/{id}/disconnect

GET  /api/ledger/accounts
POST /api/ledger/accounts/generate

GET  /api/ledger/transactions
POST /api/ledger/transactions/sign

GET  /api/ledger/audit
GET  /api/ledger/audit/logs/{days}
GET  /api/ledger/audit/statistics
```

## ⚙️ Rate Limiting

- Sign transactions: 10/min, 50/hour
- Device discovery: 5/min, 30/hour
- General: 60/min, 500/hour

## 📝 Commands

```bash
php artisan ledger:discover --transport=usb
php artisan ledger:list
php artisan ledger:generate-address <device_id> <chain>
php artisan ledger:sign-transaction <device_id> <chain> <path>
php artisan ledger:cleanup --days=90
php artisan ledger:audit
```

## 📄 License

MIT

