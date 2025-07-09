<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'maintenance_request',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'priority' => $this->priority,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'tenancy' => new TenancyResource($this->whenLoaded('tenancy')),
            ],
        ];
    }
}
