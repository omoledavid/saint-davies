<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'wishlist',
            'id' => $this->id,
            'attributes' => [
                'created_at' => $this->created_at->toDateTimeString(),
                'wishlistable' => $this->wishlistable,
            ],
        ];
    }
}
