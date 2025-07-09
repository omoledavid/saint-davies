<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    protected $fillable = [
        'user_id',
        'hotel_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'special_requests',
        'status',
        'is_paid',
        'total_amount',
        'paystack_reference',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function room()
    {
        return $this->belongsTo(HotelRoom::class);
    }
}
