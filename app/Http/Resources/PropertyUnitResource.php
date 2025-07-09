<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'property_unit',
            'id' => $this?->id,
            'attributes' => [
                'name' => $this?->name,
                'description' => $this?->description,
                'rent_amount' => $this?->rent_amount,
                'rent_frequency' => $this?->rent_frequency,
                'is_occupied' => $this?->is_occupied,
                'image' => $this?->image,
                'video' => $this?->video,
                'agreement_file' => $this?->agreement_file ? asset('storage/' . $this?->agreement_file) : null,
                'payment_receipt' => $this?->payment_receipt ? asset('storage/' . $this?->payment_receipt) : null,
                'bed_room' => $this?->bed_room,
                'bath_room' => $this?->bath_room,
                'parking' => $this?->parking,
                'security' => $this?->security,
                'water' => $this?->water,
                'electricity' => $this?->electricity,
                'internet' => $this?->internet,
                'tv' => $this?->tv,
                'created_at' => $this?->created_at->toDateTimeString(),
                'updated_at' => $this?->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'property' => new PropertyResource($this->property),
                'files' => FileUploadResource::collection($this->whenLoaded('files')),
            ],
        ];
    }
}
