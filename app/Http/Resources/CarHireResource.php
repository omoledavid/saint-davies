<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarHireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'car_hire',
            'id' => $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'car_id' => $this->car_id,
                'pickup_datetime' => $this->pickup_datetime,
                'return_datetime' => $this->return_datetime,
                'pickup_location' => $this->pickup_location,
                'dropoff_location' => $this->dropoff_location,
                'duration_in_days' => $this->duration_in_days,
                'total_price' => $this->total_price,
                'is_paid' => $this->is_paid,
                'paystack_reference' => $this->paystack_reference,
                'with_driver' => $this->with_driver,
                'insurance' => $this->insurance,
                'status' => $this->status,
                'created_at' => $this->created_at->toDateTimeString(),
                'updated_at' => $this->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'user' => new UserResource($this->user),
                'car' => new CarResource($this->car),
            ],
        ];
    }
}
