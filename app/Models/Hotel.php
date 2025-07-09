<?php

namespace App\Models;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Hotel extends Model
{
    protected $fillable = [
        'manager_id',
        'name',
        'description',
        'address',
        'city',
        'state',
        'video_url',
        'country',
        'price',
        'policies',
        'cancellation_policy',
        'check_in_time',
        'amenities',
        'is_active',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_active' => 'boolean',
    ];
    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function roomCategories()
    {
        return $this->hasMany(HotelRoomCategory::class);
    }

    public function files()
    {
        return $this->morphMany(FileUpload::class, 'uploadable')->orderBy('order');
    }
    public function mainImage()
    {
        return $this->morphOne(FileUpload::class, 'uploadable')->where('is_main', true);
    }
    public function wishlists()
    {
        return $this->morphMany(Wishlist::class, 'wishlistable');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }
    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }
}
