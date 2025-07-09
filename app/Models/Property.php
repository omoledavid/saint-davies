<?php

namespace App\Models;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [
        'manager_id',
        'title',
        'description',
        'address',
        'city',
        'state',
        'price',
        'is_available',
        'rent_price',
        'rent_frequency',
        'image',
        'video',
        'status',
        'property_type',
        'property_status',
        'property_category',
        'sop',
    ];
    protected $casts = [
        'is_available' => 'boolean',
        'manager_id' => 'integer',
    ];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function units()
    {
        return $this->hasMany(PropertyUnit::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function files()
    {
        return $this->morphMany(FileUpload::class, 'uploadable')->orderBy('order');
    }
    public function mainImage()
    {
        return $this->morphOne(FileUpload::class, 'uploadable')->where('is_main', true);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }
    public function tenants()
    {
        return $this->hasMany(Tenancy::class, 'property_unit_id');
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
}
