<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    // Mass Assignment এর জন্য fillable প্রপার্টি ব্যবহার করুন
    protected $fillable = [
        'user_id',      // এটি fillable হিসাবে যোগ করুন
        'class_id',     // অন্যান্য প্রপার্টি
        'status',       // প্রয়োজনীয় প্রপার্টি
    ];

    // ক্লাসের সম্পর্ক
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // শিক্ষার্থীর সম্পর্ক
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}