<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarHire extends Model
{
    protected $fillable = [
        'user_id',
        'car_id',
        'pickup_datetime',
        'return_datetime',
        'pickup_location',
        'dropoff_location',
        'duration_in_days',
        'total_price',
        'is_paid',
        'paystack_reference',
        'with_driver',
        'insurance',
        'status',
    ];
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function car() {
        return $this->belongsTo(Car::class);
    }
}
