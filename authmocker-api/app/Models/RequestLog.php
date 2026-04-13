<?php

namespace App\Models;

use App\Enums\AuthStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'mock_server_id',
        'mock_endpoint_id',
        'method',
        'path',
        'headers',
        'body',
        'query_params',
        'ip',
        'auth_status',
        'response_status',
    ];

    protected $casts = [
        'headers' => 'array',
        'body' => 'array',
        'query_params' => 'array',
        'auth_status' => AuthStatus::class,
        'response_status' => 'integer',
        'created_at' => 'datetime',
    ];

    public function mockServer(): BelongsTo
    {
        return $this->belongsTo(MockServer::class);
    }

    public function mockEndpoint(): BelongsTo
    {
        return $this->belongsTo(MockEndpoint::class);
    }
}
