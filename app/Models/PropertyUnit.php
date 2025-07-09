<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyUnit extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'description',
        'rent_amount',
        'rent_frequency',
        'is_occupied',
        'image',
        'video',
        'agreement_file',
        'payment_receipt',
        'bed_room',
        'bath_room',
        'parking',
        'security',
        'water',
        'electricity',
        'internet',
        'tv',
    ];
    protected $casts = [
        'parking' => 'boolean',
        'security' => 'boolean',
        'water' => 'boolean',
        'electricity' => 'boolean',
        'internet' => 'boolean',
        'tv' => 'boolean',
    ];
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenancy()
    {
        return $this->hasOne(Tenancy::class);
    }
    public function images()
    {
        return $this->hasMany(PropertyImage::class, 'property_id', 'property_id');
    }
}
