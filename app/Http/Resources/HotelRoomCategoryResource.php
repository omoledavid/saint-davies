<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelRoomCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'hotel_room_category',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'price' => $this->price,
                'is_active' => $this->is_active,
                'is_featured' => $this->is_featured,
                'is_deleted' => $this->is_deleted,
                'capacity' => $this->capacity,
                'bed_type' => $this->bed_type,
                'amenities' => $this->amenities,
                'image' => $this->image ? asset('storage/' . $this->image) : null,
                'created_at' => $this->created_at,
            ],
            'relationships' => [
                'hotel' => new HotelResource($this->whenLoaded('hotel')),
                'images' => FileUploadResource::collection($this->whenLoaded('files')),
            ],
        ];
    }
}
