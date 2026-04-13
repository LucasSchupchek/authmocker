<?php

namespace App\Services;

use App\Models\MockServer;
use App\Models\RequestLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RequestLogService
{
    public function listByServer(MockServer $server, int $perPage = 50): LengthAwarePaginator
    {
        return RequestLog::where('mock_server_id', $server->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function clearByServer(MockServer $server): int
    {
        return RequestLog::where('mock_server_id', $server->id)->delete();
    }
}
