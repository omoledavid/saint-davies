<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_category_id',
        'name',
        'description',
        'room_number',
        'is_available',
    ];
    protected $casts = [
        'is_available' => 'boolean',
    ];
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function roomCategory()
    {
        return $this->belongsTo(HotelRoomCategory::class);
    }
}
