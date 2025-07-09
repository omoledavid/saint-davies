<?php

namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Tenancy extends Authenticatable
{
    use HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'tenant_number',
        'manager_id',
        'phone',
        'marital_status',
        'gender',
        'nationality',
        'occupation',
        'income',
        'id_number',
        'id_type',
        'id_front_image',
        'id_back_image',
        'user_image',
        'property_unit_id',
        'rent_start',
        'rent_end',
        'is_active',
        'password',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenancy) {
            do {
                $tenantNumber = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            } while (static::where('tenant_number', $tenantNumber)->exists());

            $tenancy->tenant_number = $tenantNumber;
        });
    }
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    protected $hidden = ['password', 'remember_token'];

    public function unit()
    {
        return $this->belongsTo(PropertyUnit::class, 'property_unit_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
