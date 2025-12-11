<?php

namespace MartinLechene\LedgerManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerTransaction extends Model
{
    protected $fillable = [
        'ledger_account_id',
        'tx_hash',
        'chain',
        'type',
        'status',
        'from_address',
        'to_address',
        'amount',
        'token_symbol',
        'token_address',
        'gas_data',
        'raw_data',
        'signed_data',
        'error_message',
        'metadata',
        'signed_at',
        'submitted_at',
        'confirmed_at',
    ];

    protected $casts = [
        'gas_data' => 'array',
        'metadata' => 'array',
        'signed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function markAsSigned(string $data): void
    {
        $this->update([
            'status' => 'signed',
            'signed_data' => $data,
            'signed_at' => now(),
        ]);
    }

    public function markAsSubmitted(string $hash): void
    {
        $this->update([
            'status' => 'submitted',
            'tx_hash' => $hash,
            'submitted_at' => now(),
        ]);
    }

    public function markAsConfirmed(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function scopeByChain($query, string $chain)
    {
        return $query->where('chain', $chain);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

