<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelBookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'hotel_booking',
            'id' => $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'hotel_id' => $this->hotel_id,
                'room_id' => $this->room_id,
                'check_in_date' => $this->check_in_date,
                'check_out_date' => $this->check_out_date,
                'number_of_guests' => $this->number_of_guests,
                'special_requests' => $this->special_requests,
                'status' => $this->status,
                'is_paid' => $this->is_paid,
                'total_amount' => $this->total_amount,
                'paystack_reference' => $this->paystack_reference,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'user' => new UserResource($this->whenLoaded('user')),
                'hotel' => new HotelResource($this->whenLoaded('hotel')),
                'room' => new HotelRoomResource($this->whenLoaded('room')),
            ],
        ];
    }
}
