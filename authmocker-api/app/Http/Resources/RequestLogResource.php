<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mock_server_id' => $this->mock_server_id,
            'mock_endpoint_id' => $this->mock_endpoint_id,
            'method' => $this->method,
            'path' => $this->path,
            'headers' => $this->headers,
            'body' => $this->body,
            'query_params' => $this->query_params,
            'ip' => $this->ip,
            'auth_status' => $this->auth_status->value,
            'response_status' => $this->response_status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
