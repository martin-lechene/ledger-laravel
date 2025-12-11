<?php

namespace YourVendor\LedgerManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class LedgerDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'device_id',
        'model',
        'serial_number',
        'transport_type',
        'device_path',
        'name',
        'firmware_version',
        'capabilities',
        'is_active',
        'last_connected_at',
        'last_used_at',
        'metadata',
    ];

    protected $casts = [
        'capabilities' => 'collection',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(LedgerAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LedgerTransaction::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LedgerActivityLog::class);
    }

    public function getDisplayName(): string
    {
        return $this->name ?? $this->model ?? "Device #{$this->id}";
    }

    public function isConnected(): bool
    {
        return $this->is_active && $this->last_connected_at && $this->last_connected_at->isRecent();
    }

    public function getActiveAccountsCount(): int
    {
        return $this->accounts()->where('is_active', true)->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTransport($query, string $type)
    {
        return $query->where('transport_type', $type);
    }

    public function scopeByModel($query, string $model)
    {
        return $query->where('model', $model);
    }
}

