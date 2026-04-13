<?php

namespace App\Models;

use App\Enums\HttpMethod;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockEndpoint extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'mock_server_id',
        'method',
        'path',
        'response_status',
        'response_body',
        'response_headers',
        'delay_ms',
        'is_active',
        'description',
    ];

    protected $casts = [
        'method' => HttpMethod::class,
        'response_body' => 'array',
        'response_headers' => 'array',
        'delay_ms' => 'integer',
        'response_status' => 'integer',
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
