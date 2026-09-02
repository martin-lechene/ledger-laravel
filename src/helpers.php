<?php

use Illuminate\Support\Collection;
use MartinLechene\LedgerManager\Services\LedgerService;

if (! function_exists('ledger')) {
    /**
     * Get the LedgerService instance.
     *
     * @return LedgerService
     */
    function ledger()
    {
        return app(LedgerService::class);
    }
}

if (! function_exists('ledger_discover')) {
    /**
     * Discover Ledger devices.
     *
     * @return Collection
     */
    function ledger_discover(string $transport = 'usb')
    {
        return ledger()->discoverDevices($transport);
    }
}

if (! function_exists('ledger_connect')) {
    /**
     * Connect to a Ledger device.
     */
    function ledger_connect(string $deviceId, string $transport = 'usb'): bool
    {
        return ledger()->connect($deviceId, $transport);
    }
}

if (! function_exists('ledger_devices')) {
    /**
     * Get all registered devices.
     *
     * @return Collection
     */
    function ledger_devices()
    {
        return ledger()->getAllDevices();
    }
}
