<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'car',
            'id' => $this->id,
            'attributes' => [
                'title' => $this->title,
                'make' => $this->make,
                'model' => $this->model,
                'year' => $this->year,
                'condition' => $this->condition,
                'transmission' => $this->transmission,
                'fuel_type' => $this->fuel_type,
                'price' => $this->price,
                'type' => $this->type,
                'rent_frequency' => $this->rent_frequency,
                'location' => $this->location,
                'description' => $this->description,
                'is_available' => $this->is_available,
                'created_at' => $this->created_at,
            ],
            'relationships' => [
                'manager' => new UserResource($this->whenLoaded('manager')),
                'files' => FileUploadResource::collection($this->whenLoaded('files')),
            ],
        ];
    }
}
