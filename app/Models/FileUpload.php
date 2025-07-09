<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FileUpload extends Model
{
    protected $fillable = [
        'file_path', 'file_type', 'file_name', 'is_main', 'order'
    ];

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
