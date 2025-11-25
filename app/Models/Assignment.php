<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'instructions',
        'deadline',
        'max_marks',
        'assignment_type',
        'is_published',
        'file_path',
        'file_name',
    ];

    protected $casts = [
        'deadline'     => 'datetime',
        'is_published' => 'boolean',
        'max_marks'    => 'float',
    ];

    // সংশ্লিষ্ট ক্লাস
    public function classroom()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // সংশ্লিষ্ট সাবমিশনগুলো
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}