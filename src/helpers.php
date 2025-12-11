<?php

if (!function_exists('ledger')) {
    /**
     * Get the LedgerService instance.
     *
     * @return \MartinLechene\LedgerManager\Services\LedgerService
     */
    function ledger()
    {
        return app(\MartinLechene\LedgerManager\Services\LedgerService::class);
    }
}

if (!function_exists('ledger_discover')) {
    /**
     * Discover Ledger devices.
     *
     * @param string $transport
     * @return \Illuminate\Support\Collection
     */
    function ledger_discover(string $transport = 'usb')
    {
        return ledger()->discoverDevices($transport);
    }
}

if (!function_exists('ledger_connect')) {
    /**
     * Connect to a Ledger device.
     *
     * @param string $deviceId
     * @param string $transport
     * @return bool
     */
    function ledger_connect(string $deviceId, string $transport = 'usb'): bool
    {
        return ledger()->connect($deviceId, $transport);
    }
}

if (!function_exists('ledger_devices')) {
    /**
     * Get all registered devices.
     *
     * @return \Illuminate\Support\Collection
     */
    function ledger_devices()
    {
        return ledger()->getAllDevices();
    }
}

