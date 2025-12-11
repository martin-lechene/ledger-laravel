<?php

namespace MartinLechene\LedgerManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ledger_device_id',
        'chain',
        'account_name',
        'derivation_path',
        'public_address',
        'public_key',
        'account_index',
        'is_active',
        'abi',
        'balance',
        'last_balance_check',
        'metadata',
    ];

    protected $casts = [
        'balance' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_balance_check' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(LedgerDevice::class, 'ledger_device_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LedgerActivityLog::class);
    }

    public function getPendingTransactions()
    {
        return $this->transactions()->where('status', 'pending')->get();
    }

    public function getBalance(?string $asset = null)
    {
        if (!$asset) {
            return $this->balance;
        }
        return $this->balance[$asset] ?? null;
    }

    public function getQRCode(): string
    {
        // Generate QR code data URL for the address
        // In production, use a QR code library
        return "data:image/png;base64," . base64_encode("QR_CODE_FOR_{$this->public_address}");
    }

    public function isLocked(): bool
    {
        return !$this->is_active;
    }

    public function scopeByChain($query, string $chain)
    {
        return $query->where('chain', $chain);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAddress($query, string $address)
    {
        return $query->where('public_address', $address);
    }
}

