<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'hotel_room',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'room_number' => $this->room_number,
                'is_available' => $this->is_available,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'hotel' => new HotelResource($this->whenLoaded('hotel')),
                'room_category' => new HotelRoomCategoryResource($this->whenLoaded('roomCategory')),
            ],
        ];
    }
}
