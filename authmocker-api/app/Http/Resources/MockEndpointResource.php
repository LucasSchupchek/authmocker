<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockEndpointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mock_server_id' => $this->mock_server_id,
            'method' => $this->method->value,
            'path' => $this->path,
            'response_status' => $this->response_status,
            'response_body' => $this->response_body,
            'response_headers' => $this->response_headers,
            'delay_ms' => $this->delay_ms,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'full_url' => $this->whenLoaded('mockServer', function () {
                return url("/mock/{$this->mockServer->slug}/{$this->path}");
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
