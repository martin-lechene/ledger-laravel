<?php

return [
    // Activation globale
    'enabled' => env('LEDGER_ENABLED', true),
    'debug' => env('LEDGER_DEBUG', false),
    
    // Configuration par défaut
    'default_transport' => env('LEDGER_TRANSPORT', 'usb'),
    'default_chain' => env('LEDGER_CHAIN', 'ethereum'),
    
    // Configuration des transports
    'transports' => [
        'usb' => [
            'enabled' => true,
            'timeout' => 5000,
            'vendor_id' => '0x2c97',
            'product_ids' => ['0x0001', '0x0004', '0x0005', '0x0006', '0x0007', '0x0008', '0x0009', '0x000a', '0x000b', '0x000c', '0x000d', '0x000e', '0x000f', '0x0010', '0x0011', '0x0012', '0x0013', '0x0014', '0x0015', '0x0016', '0x0017', '0x0018', '0x0019', '0x001a', '0x001b', '0x001c', '0x001d', '0x001e', '0x001f', '0x0020'],
            'retry_attempts' => 3,
            'retry_delay' => 500,
        ],
        'bluetooth' => [
            'enabled' => true,
            'timeout' => 10000,
            'scan_timeout' => 30000,
            'reconnect_attempts' => 5,
        ],
        'webusb' => [
            'enabled' => true,
            'timeout' => 5000,
        ],
    ],
    
    // Configuration des blockchains
    'chains' => [
        'bitcoin' => [
            'coin_type' => 0,
            'derivation_path' => "m/44'/0'/0'/0",
        ],
        'ethereum' => [
            'coin_type' => 60,
            'derivation_path' => "m/44'/60'/0'/0",
        ],
        'solana' => [
            'coin_type' => 501,
            'derivation_path' => "m/44'/501'/0'/0'",
        ],
        'polkadot' => [
            'coin_type' => 354,
            'derivation_path' => "m/44'/354'/0'/0'",
        ],
        'cardano' => [
            'coin_type' => 1815,
            'derivation_path' => "m/44'/1815'/0'/0",
        ],
    ],
    
    // Stockage et cache
    'storage' => [
        'cache_addresses' => true,
        'cache_ttl' => 3600,
        'store' => env('LEDGER_STORE', 'file'),
    ],
    
    // Sécurité
    'security' => [
        'require_pin_confirmation' => true,
        'log_all_operations' => true,
        'encrypt_sensitive_data' => true,
        'ip_whitelist' => env('LEDGER_IP_WHITELIST', ''),
        'two_factor_enabled' => env('LEDGER_2FA_ENABLED', false),
    ],
    
    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'signing_per_minute' => 10,
        'signing_per_hour' => 50,
        'discovery_per_minute' => 5,
        'discovery_per_hour' => 30,
        'general_per_minute' => 60,
        'general_per_hour' => 500,
    ],
    
    // Webhooks
    'webhooks' => [
        'enabled' => env('LEDGER_WEBHOOKS_ENABLED', true),
        'endpoints' => [
            // Example:
            // 'https://example.com/webhooks/ledger' => [
            //     'secret' => 'webhook_secret_key',
            //     'events' => ['transaction.signed', 'security.alert'],
            // ],
        ],
    ],
    
    // Audit
    'audit' => [
        'enabled' => true,
        'retention_days' => 365,
        'alert_on_failures' => true,
        'failure_threshold' => 5,
    ],
];

