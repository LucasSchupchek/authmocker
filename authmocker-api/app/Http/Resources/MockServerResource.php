<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MockServerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'auth_type' => $this->auth_type->value,
            'auth_type_label' => $this->auth_type->label(),
            'config' => $this->config,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'mock_url' => url("/mock/{$this->slug}"),
            'endpoints_count' => $this->whenCounted('endpoints'),
            'endpoints' => MockEndpointResource::collection($this->whenLoaded('endpoints')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
