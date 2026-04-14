<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockCredential extends Model
{
    use HasUuids;

    protected $fillable = [
        'mock_server_id',
        'label',
        'is_active',
        'credentials',
        'profile',
    ];

    protected $casts = [
        'credentials' => 'array',
        'profile' => 'array',
        'is_active' => 'boolean',
    ];

    public function mockServer(): BelongsTo
    {
        return $this->belongsTo(MockServer::class);
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }
}
