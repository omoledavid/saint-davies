<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'manager_id',
        'title',
        'make',
        'model',
        'year',
        'condition',
        'transmission',
        'fuel_type',
        'price',
        'type',
        'rent_frequency',
        'location',
        'description',
        'is_available',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function files()
    {
        return $this->morphMany(FileUpload::class, 'uploadable')->orderBy('order');
    }
    public function mainImage()
    {
        return $this->morphOne(FileUpload::class, 'uploadable')->where('is_main', true);
    }
    public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}
}
