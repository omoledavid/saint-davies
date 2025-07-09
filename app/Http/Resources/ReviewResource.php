<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'review',
            'id' => $this->id,
            'attributes' => [
                'user' => new UserResource($this->user),
                'rating' => $this->rating,
                'comment' => $this->comment,
                'is_verified' => $this->is_verified,
            ],
        ];
    }
}
