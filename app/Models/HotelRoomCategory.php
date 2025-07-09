<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoomCategory extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'price',
        'capacity',
        'amenities',
        'bed_type',
        'image',
    ];

    protected $casts = [
        'amenities' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
