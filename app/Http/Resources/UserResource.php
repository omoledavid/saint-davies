<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this?->name,
                'email' => $this?->email,
                'image' => $this?->image ? asset('storage/' . $this?->image) : null,
                'phone' => $this?->phone,
                'role' => $this?->role,
                'status' => $this?->status,
                'address' => $this?->address,
                'email_verified_at' => $this?->email_verified_at,
                'created_at' => $this?->created_at->toDateTimeString(),
                'updated_at' => $this?->updated_at?->toDateTimeString(),
            ]
        ];
    }
}
