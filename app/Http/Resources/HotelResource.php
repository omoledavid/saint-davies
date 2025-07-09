<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'hotel',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'video_url' => $this->video_url,
                'price' => $this->price,
                'policies' => $this->policies,
                'cancellation_policy' => $this->cancellation_policy,
                'check_in_time' => $this->check_in_time,
                'amenities' => $this->amenities,
                'is_active' => $this->is_active,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'manager' => new UserResource($this->whenLoaded('manager')),
                'images' => FileUploadResource::collection($this->whenLoaded('files')),
                'rooms' => HotelRoomResource::collection($this->whenLoaded('rooms')),
            ],
        ];
    }
}
