<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */

    // 🔹 JWTAuth requires these two methods:
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',       // ✅ এই লাইনটা থাকতে হবে
        'avatar',
        'status',
        //'token'
    ];

    /**
     * Attributes hidden during serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'preferences' => '{"notifications": true, "dark_mode": false}',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'preferences'       => 'array',
        'password'          => 'hashed',
    ];

    /**
     * User ↔ Role relationship.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}