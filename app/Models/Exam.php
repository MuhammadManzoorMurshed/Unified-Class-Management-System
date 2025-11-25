<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'exam_type',
        'title',
        'exam_date',
        'total_marks',
        'weightage',
        'is_published',
    ];

    protected $casts = [
        'exam_date'   => 'date',
        'total_marks' => 'float',
        'weightage'   => 'float',
        'is_published' => 'boolean',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }
}