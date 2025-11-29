<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Message extends Model
{
    use HasFactory;

    // তোমার DB টেবিলের নাম যদি Messages (M ক্যাপিটাল) হয়
    // protected $table = 'messages';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'class_id',
        'message_scope',
        'content',
        'message_type',
        'file_path',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}