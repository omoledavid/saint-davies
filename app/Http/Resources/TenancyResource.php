<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenancyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'tenancy',
            'id' => $this->id,
            'attributes' => [
                'name' => $this?->name,
                'email' => $this?->email,
                'tenant_number' => $this?->tenant_number,
                'phone' => $this?->phone,
                'marital_status' => $this?->marital_status,
                'gender' => $this?->gender,
                'nationality' => $this?->nationality,
                'occupation' => $this?->occupation,
                'income' => $this?->income,
                'id_number' => $this?->id_number,
                'id_type' => $this?->id_type,
                'id_front_image' => $this?->id_front_image ? asset('storage/' . $this?->id_front_image) : null,
                'id_back_image' => $this?->id_back_image ? asset('storage/' . $this?->id_back_image) : null,
                'user_image' => $this?->user_image ? asset('storage/' . $this?->user_image) : null,
                'property_unit_id' => $this?->property_unit_id,
                'rent_start' => $this?->rent_start,
                'rent_end' => $this?->rent_end,
                'is_active' => $this?->is_active,
                'created_at' => $this?->created_at->toDateTimeString(),
                'updated_at' => $this?->updated_at?->toDateTimeString(),
            ],
            'relationships' => [
                'unit' => new PropertyUnitResource($this->whenLoaded('unit')),
                'manager' => new UserResource($this->whenLoaded('manager')),
            ],
        ];
    }
}
