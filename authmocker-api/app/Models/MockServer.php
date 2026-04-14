<?php

namespace App\Models;

use App\Enums\AuthType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MockServer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'auth_type',
        'config',
        'is_active',
        'description',
    ];

    protected $casts = [
        'auth_type' => AuthType::class,
        'config' => 'array',
        'is_active' => 'boolean',
    ];

    public function endpoints(): HasMany
    {
        return $this->hasMany(MockEndpoint::class);
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(MockCredential::class);
    }

    public function requestLogs(): HasMany
    {
        return $this->hasMany(RequestLog::class);
    }
}
