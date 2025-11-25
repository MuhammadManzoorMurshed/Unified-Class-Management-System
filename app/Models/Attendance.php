<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    
    protected $table = 'attendance';
    
    protected $fillable = [
        'class_id',
        'student_id',
        'date',
        'status',   // present / absent
        'marked_by', // যে ইউজার attendance mark করেছে
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // সম্পর্ক – একটি Attendance একটি ক্লাসের সাথে সম্পর্কিত
    public function classroom()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // সম্পর্ক – একটি Attendance একটি Student ইউজারের সাথে সম্পর্কিত
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}