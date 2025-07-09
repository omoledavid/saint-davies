<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'property',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'description' => $this->description,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'price' => $this->price,
                'is_available' => $this->is_available,
                'rent_price' => $this->rent_price,
                'rent_frequency' => $this->rent_frequency,
                'image' => $this->image ? asset('storage/' . $this->image) : null,
                'video' => $this->video,
                'status' => $this->status,
                'property_type' => $this->property_type,
                'property_category' => $this->property_category,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at->toDateTimeString(),
            ],
            'relationships' => [
                'manager' => new UserResource($this->whenLoaded('manager')),
                'tenants' => TenancyResource::collection($this->whenLoaded('tenants')),
                'property_units' => PropertyUnitResource::collection($this->whenLoaded('units')),
                'files' => FileUploadResource::collection($this->whenLoaded('files')),
            ],
            'links' => [
                'self' => route('properties.show', $this->id),
            ]
        ];
    }
}
