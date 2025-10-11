<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailVerification extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'otp', 'token', 'expires_at', 'is_used', 'purpose'];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}