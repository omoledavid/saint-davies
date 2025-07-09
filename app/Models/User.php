<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use MannikJ\Laravel\Wallet\Traits\HasWallet;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasWallet;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'location',
        'role',
        'status',
        'image',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'email_verification_token',
        'email_verification_token_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_token_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->email_verification_token = null;
        $this->email_verification_token_expires_at = null;
        $this->save();
    }

    public function generateEmailVerificationToken()
    {
        $this->email_verification_token = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $this->email_verification_token_expires_at = now()->addHours(24);
        $this->save();
    }

    public function isEmailVerificationTokenValid($token)
    {
        return $this->email_verification_token === $token &&
            $this->email_verification_token_expires_at > now();
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    public function tenants()
    {
        return $this->hasMany(Tenancy::class, 'manager_id');
    }
}
