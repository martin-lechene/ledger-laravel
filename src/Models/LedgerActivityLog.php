<?php

namespace MartinLechene\LedgerManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerActivityLog extends Model
{
    protected $fillable = [
        'ledger_device_id',
        'ledger_account_id',
        'action',
        'chain',
        'status',
        'details',
        'error_details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'user_agent' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(LedgerDevice::class, 'ledger_device_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_id');
    }

    public static function log(
        string $action,
        string $status,
        ?LedgerDevice $device = null,
        ?LedgerAccount $account = null,
        ?string $details = null,
        ?string $error = null
    ): self {
        return self::create([
            'ledger_device_id' => $device?->id,
            'ledger_account_id' => $account?->id,
            'action' => $action,
            'status' => $status,
            'details' => $details,
            'error_details' => $error,
            'ip_address' => request()->ip(),
            'user_agent' => [
                'user_agent' => request()->userAgent(),
                'referer' => request()->header('referer'),
            ],
        ]);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

