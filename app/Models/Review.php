<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'service_type',
        'rating',
        'comment',
        'is_verified',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
