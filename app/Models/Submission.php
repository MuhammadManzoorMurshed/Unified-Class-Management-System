<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'file_name',
        'submission_date',
        'status',
        'marks_obtained',
        'feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'submission_date' => 'datetime',
        'graded_at'       => 'datetime',
        'marks_obtained'  => 'float',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grader()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}