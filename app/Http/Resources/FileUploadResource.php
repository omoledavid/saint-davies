<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'file',
            'id' => $this->id,
            'attributes' => [
                'name' => $this?->file_name,
                'path' => $this?->file_path ? asset('storage/' . $this?->file_path) : null,
                'is_main' => $this?->is_main,
                'order' => $this?->order,
                'created_at' => $this?->created_at->toDateTimeString(),
                'updated_at' => $this?->updated_at?->toDateTimeString(),
            ],
        ];
    }
}
