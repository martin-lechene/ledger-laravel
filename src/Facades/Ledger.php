<?php

namespace MartinLechene\LedgerManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection discoverDevices(string $transportType = 'usb')
 * @method static bool connect(string $deviceId, string $transportType = 'usb')
 * @method static bool disconnect()
 * @method static \MartinLechene\LedgerManager\Models\LedgerDevice|null getCurrentDevice()
 * @method static \MartinLechene\LedgerManager\Models\LedgerDevice|null getDevice(string $deviceId)
 * @method static \Illuminate\Support\Collection getAllDevices()
 * @method static self selectChain(string $chainName)
 * @method static string getAddress(string $derivationPath, bool $display = false)
 * @method static \Illuminate\Support\Collection generateAddressRange(string $chain, int $startIndex = 0, int $count = 10)
 * @method static \MartinLechene\LedgerManager\Models\LedgerTransaction signTransaction(string $derivationPath, string $txData)
 * @method static string signMessage(string $derivationPath, string $message)
 * @method static \Illuminate\Support\Collection getAllAccounts(?string $chain = null)
 * @method static \Illuminate\Support\Collection getTransactionHistory(?string $deviceId = null, ?string $chain = null, int $limit = 50)
 * @method static string getAppVersion()
 *
 * @see \MartinLechene\LedgerManager\Services\LedgerService
 */
class Ledger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \MartinLechene\LedgerManager\Services\LedgerService::class;
    }
}

