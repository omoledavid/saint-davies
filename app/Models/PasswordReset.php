<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public function isExpired()
    {
        return $this->expires_at < now();
    }

    public static function createToken($email)
    {
        $token = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
        
        // Delete any existing tokens for this email
        self::where('email', $email)->delete();
        
        // Create new token
        self::create([
            'email' => $email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(1), // Token expires in 1 hour
        ]);
        
        return $token;
    }
}
